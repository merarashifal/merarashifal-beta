<?php
/**
 * MeraRashifal Admin Dashboard: Coupons, Redemptions & Bucket Upsell Manager
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Coupons_Admin {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_admin_submenu'], 35);
        add_action('admin_post_mr_save_coupon', [__CLASS__, 'handle_save_coupon']);
        add_action('admin_post_mr_delete_coupon', [__CLASS__, 'handle_delete_coupon']);
        add_action('admin_post_mr_toggle_coupon_status', [__CLASS__, 'handle_toggle_coupon_status']);
        add_action('admin_post_mr_save_bucket_rule', [__CLASS__, 'handle_save_bucket_rule']);
        add_action('admin_post_mr_delete_bucket_rule', [__CLASS__, 'handle_delete_bucket_rule']);
        add_action('admin_post_mr_toggle_bucket_rule', [__CLASS__, 'handle_toggle_bucket_rule']);
    }

    /**
     * Register submenu under Subscriptions & Packs
     */
    public static function register_admin_submenu() {
        add_submenu_page(
            'mr-subscriptions',
            __('Coupons & Discounts Manager', 'merarashifal'),
            __('Coupons & Discounts', 'merarashifal'),
            'manage_options',
            'mr-coupons',
            [__CLASS__, 'render_admin_page']
        );
    }

    /**
     * Render the admin page
     */
    public static function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'merarashifal'));
        }

        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'coupons';
        $edit_id    = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $coupon_to_edit = $edit_id > 0 ? MR_Coupons::get_coupon_by_id($edit_id) : null;

        $coupons = MR_Coupons::get_all_coupons();
        $redemptions = MR_Coupons::get_recent_redemptions(100);
        $product_types = MR_Coupons::get_supported_product_types();

        $bucket_rules = class_exists('MR_Bucket_Discount') ? MR_Bucket_Discount::get_rules() : [];
        $edit_rule_id = isset($_GET['edit_rule']) ? sanitize_key($_GET['edit_rule']) : '';
        $bucket_rule_to_edit = (!empty($edit_rule_id) && isset($bucket_rules[$edit_rule_id])) ? $bucket_rules[$edit_rule_id] : null;
        $supported_categories = class_exists('MR_Bucket_Discount') ? MR_Bucket_Discount::get_supported_categories() : [];

        ?>
        <div class="wrap" style="max-width: 1200px;">
            <h1 style="display:flex; align-items:center; gap:10px; font-weight:800; color:#7A1F3D;">
                <span class="dashicons dashicons-tickets-alt" style="font-size:32px; width:32px; height:32px; color:#F58220;"></span>
                <span><?php _e('Coupons, Redemptions & Bucket Discounts', 'merarashifal'); ?></span>
            </h1>
            <p class="description">
                <?php _e('Manage promotional codes, flat / percentage pre-tax discounts, FCFS redemption limits (e.g. 100 uses max), per-user limits, and in-cart bucket upsells.', 'merarashifal'); ?>
            </p>

            <?php if (!empty($_GET['msg'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php 
                        if ($_GET['msg'] === 'saved') _e('Coupon saved successfully!', 'merarashifal');
                        elseif ($_GET['msg'] === 'deleted') _e('Coupon deleted successfully.', 'merarashifal');
                        elseif ($_GET['msg'] === 'status_toggled') _e('Coupon status updated.', 'merarashifal');
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['err'])): ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html(urldecode($_GET['err'])); ?></p>
                </div>
            <?php endif; ?>

            <nav class="nav-tab-wrapper" style="margin-top:20px; margin-bottom:20px;">
                <a href="?page=mr-coupons&tab=coupons" class="nav-tab <?php echo $active_tab === 'coupons' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-tag" style="vertical-align:text-bottom;"></span> <?php _e('Active Coupons & Limits', 'merarashifal'); ?>
                </a>
                <a href="?page=mr-coupons&tab=edit" class="nav-tab <?php echo $active_tab === 'edit' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-plus-alt" style="vertical-align:text-bottom;"></span> <?php echo $coupon_to_edit ? __('Edit Coupon', 'merarashifal') : __('Create New Coupon', 'merarashifal'); ?>
                </a>
                <a href="?page=mr-coupons&tab=redemptions" class="nav-tab <?php echo $active_tab === 'redemptions' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-list-view" style="vertical-align:text-bottom;"></span> <?php _e('Redemption Audit Log', 'merarashifal'); ?>
                </a>
                <a href="?page=mr-coupons&tab=bucket" class="nav-tab <?php echo $active_tab === 'bucket' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-cart" style="vertical-align:text-bottom;"></span> <?php _e('In-Cart Bucket Upsells', 'merarashifal'); ?>
                </a>
            </nav>

            <?php if ($active_tab === 'coupons'): ?>
                <!-- ================= TAB 1: COUPONS LIST ================= -->
                <div class="tab-content" style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                        <h2 style="margin:0; font-size:18px; color:#1e293b;"><?php _e('Master Coupon Registry', 'merarashifal'); ?></h2>
                        <a href="?page=mr-coupons&tab=edit" class="button button-primary" style="background:#7A1F3D; border-color:#7A1F3D;">
                            + <?php _e('Create New Coupon Code', 'merarashifal'); ?>
                        </a>
                    </div>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:120px; font-weight:700;">Code</th>
                                <th style="width:180px; font-weight:700;">Title</th>
                                <th style="width:120px; font-weight:700;">Discount</th>
                                <th style="width:130px; font-weight:700;">Product Scope</th>
                                <th style="width:160px; font-weight:700;">Redemption Limit</th>
                                <th style="width:90px; font-weight:700;">Per User</th>
                                <th style="width:90px; font-weight:700;">Status</th>
                                <th style="width:140px; text-align:right; font-weight:700;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($coupons)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding:20px; color:#64748b;">
                                        <?php _e('No coupons configured yet. Click "Create New Coupon Code" to add your first promotion.', 'merarashifal'); ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($coupons as $c): 
                                    $limit = intval($c['total_usage_limit']);
                                    $used  = intval($c['total_redeemed_count']);
                                    $pct   = $limit > 0 ? min(100, round(($used / $limit) * 100)) : 0;
                                    $bar_color = ($pct >= 90) ? '#DC2626' : (($pct >= 50) ? '#F59E0B' : '#10B981');
                                    $scope_label = $product_types[$c['product_scope']] ?? $c['product_scope'];
                                ?>
                                    <tr>
                                        <td>
                                            <strong style="font-family:monospace; font-size:14px; color:#7A1F3D; background:#FFF3E1; padding:3px 8px; border-radius:4px; border:1px solid #FED7AA;">
                                                <?php echo esc_html($c['code']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <strong><?php echo esc_html($c['title']); ?></strong>
                                            <?php if (!empty($c['description'])): ?>
                                                <div style="font-size:11.5px; color:#64748b;"><?php echo esc_html($c['description']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($c['discount_type'] === 'flat'): ?>
                                                <span style="color:#059669; font-weight:700;">₹<?php echo number_format($c['discount_value'], 2); ?> OFF</span>
                                            <?php else: ?>
                                                <span style="color:#059669; font-weight:700;"><?php echo floatval($c['discount_value']); ?>% OFF</span>
                                                <?php if ($c['max_discount_cap'] > 0): ?>
                                                    <div style="font-size:11px; color:#64748b;">(Cap: ₹<?php echo number_format($c['max_discount_cap'], 2); ?>)</div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ($c['min_order_value'] > 0): ?>
                                                <div style="font-size:10.5px; color:#94a3b8;">Min: ₹<?php echo number_format($c['min_order_value']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="display:inline-block; font-size:11.5px; background:#F1F5F9; color:#334155; padding:2px 8px; border-radius:12px;">
                                                <?php echo esc_html($scope_label); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($limit > 0): ?>
                                                <div><strong><?php echo esc_html($used); ?></strong> / <?php echo esc_html($limit); ?> used</div>
                                                <div style="background:#E2E8F0; border-radius:9999px; height:6px; width:100%; margin-top:4px; overflow:hidden;">
                                                    <div style="background:<?php echo esc_attr($bar_color); ?>; width:<?php echo esc_attr($pct); ?>%; height:100%;"></div>
                                                </div>
                                                <div style="font-size:10.5px; color:#64748b; margin-top:2px;">(FCFS <?php echo $limit - $used; ?> left)</div>
                                            <?php else: ?>
                                                <span style="color:#64748b;">Unlimited (<?php echo esc_html($used); ?> used)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo intval($c['per_user_limit']); ?></strong> max
                                        </td>
                                        <td>
                                            <?php 
                                            $st = $c['status'];
                                            $st_bg = ($st === 'active') ? '#ECFDF5' : (($st === 'paused') ? '#FFFBEB' : '#FEF2F2');
                                            $st_col = ($st === 'active') ? '#059669' : (($st === 'paused') ? '#D97706' : '#DC2626');
                                            ?>
                                            <span style="display:inline-block; font-size:11px; font-weight:700; text-transform:uppercase; background:<?php echo $st_bg; ?>; color:<?php echo $st_col; ?>; padding:3px 8px; border-radius:6px;">
                                                <?php echo esc_html($st); ?>
                                            </span>
                                        </td>
                                        <td style="text-align:right;">
                                            <a href="?page=mr-coupons&tab=edit&edit=<?php echo intval($c['id']); ?>" class="button button-small">Edit</a>
                                            
                                            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline-block; margin-left:4px;">
                                                <input type="hidden" name="action" value="mr_toggle_coupon_status">
                                                <input type="hidden" name="coupon_id" value="<?php echo intval($c['id']); ?>">
                                                <?php wp_nonce_field('mr_coupon_action', 'nonce'); ?>
                                                <button type="submit" class="button button-small" title="Toggle Active / Paused">
                                                    <?php echo $c['status'] === 'active' ? 'Pause' : 'Activate'; ?>
                                                </button>
                                            </form>

                                            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline-block; margin-left:4px;" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                                <input type="hidden" name="action" value="mr_delete_coupon">
                                                <input type="hidden" name="coupon_id" value="<?php echo intval($c['id']); ?>">
                                                <?php wp_nonce_field('mr_coupon_action', 'nonce'); ?>
                                                <button type="submit" class="button button-small" style="color:#DC2626;">✕</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($active_tab === 'edit'): ?>
                <!-- ================= TAB 2: CREATE / EDIT COUPON ================= -->
                <div class="tab-content" style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:24px; max-width:800px;">
                    <h2 style="margin-top:0; font-size:18px; color:#7A1F3D;">
                        <?php echo $coupon_to_edit ? sprintf(__('Edit Coupon: %s', 'merarashifal'), esc_html($coupon_to_edit['code'])) : __('Create New Promotional Coupon', 'merarashifal'); ?>
                    </h2>

                    <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                        <input type="hidden" name="action" value="mr_save_coupon">
                        <input type="hidden" name="coupon_id" value="<?php echo intval($edit_id); ?>">
                        <?php wp_nonce_field('mr_coupon_action', 'nonce'); ?>

                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="code">Coupon Code <span style="color:#DC2626;">*</span></label></th>
                                <td>
                                    <input name="code" type="text" id="code" value="<?php echo esc_attr($coupon_to_edit['code'] ?? ''); ?>" class="regular-text" style="text-transform:uppercase; font-weight:700; letter-spacing:1px;" required placeholder="e.g. JYOTISH25">
                                    <p class="description">Uppercase alphanumeric code entered by customers at checkout.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="title">Title / Name <span style="color:#DC2626;">*</span></label></th>
                                <td>
                                    <input name="title" type="text" id="title" value="<?php echo esc_attr($coupon_to_edit['title'] ?? ''); ?>" class="regular-text" required placeholder="e.g. 25% Off Festive Astrology Offer">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="description">Customer Description</label></th>
                                <td>
                                    <textarea name="description" id="description" rows="2" class="large-text" placeholder="Short description displayed to customer upon applying"><?php echo esc_textarea($coupon_to_edit['description'] ?? ''); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="discount_type">Discount Type <span style="color:#DC2626;">*</span></label></th>
                                <td>
                                    <select name="discount_type" id="discount_type">
                                        <option value="percent" <?php selected($coupon_to_edit['discount_type'] ?? 'percent', 'percent'); ?>>Percentage (%) Discount</option>
                                        <option value="flat" <?php selected($coupon_to_edit['discount_type'] ?? '', 'flat'); ?>>Flat Rupee (₹) Discount</option>
                                    </select>
                                    <p class="description">Discounts are calculated strictly on values <strong>before taxes</strong>.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="discount_value">Discount Value <span style="color:#DC2626;">*</span></label></th>
                                <td>
                                    <input name="discount_value" type="number" step="0.01" min="0" id="discount_value" value="<?php echo esc_attr($coupon_to_edit['discount_value'] ?? '25'); ?>" class="small-text" required>
                                    <span class="description">Enter percent (e.g. 25 for 25%) or flat rupees (e.g. 50 for ₹50).</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="max_discount_cap">Max Discount Cap (₹)</label></th>
                                <td>
                                    <input name="max_discount_cap" type="number" step="0.01" min="0" id="max_discount_cap" value="<?php echo esc_attr($coupon_to_edit['max_discount_cap'] ?? '0'); ?>" class="small-text">
                                    <span class="description">Optional maximum discount cap for % coupons (e.g. ₹150 max off). Set 0 for uncapped.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="min_order_value">Minimum Order Pre-Tax (₹)</label></th>
                                <td>
                                    <input name="min_order_value" type="number" step="0.01" min="0" id="min_order_value" value="<?php echo esc_attr($coupon_to_edit['min_order_value'] ?? '0'); ?>" class="small-text">
                                    <span class="description">Minimum cart subtotal before taxes required to unlock this discount.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="product_scope">Product Scope <span style="color:#DC2626;">*</span></label></th>
                                <td>
                                    <select name="product_scope" id="product_scope">
                                        <?php foreach ($product_types as $key => $label): ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($coupon_to_edit['product_scope'] ?? 'all', $key); ?>>
                                                <?php echo esc_html($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">Specify whether this coupon is valid for reports, Niyati chat credits, subscriptions, or all products.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="total_usage_limit">Global Redemption Limit (FCFS)</label></th>
                                <td>
                                    <input name="total_usage_limit" type="number" min="0" id="total_usage_limit" value="<?php echo esc_attr($coupon_to_edit['total_usage_limit'] ?? '100'); ?>" class="small-text">
                                    <span class="description">Total times this coupon can be redeemed across all users (e.g. 100 on first-come, first-served basis). 0 = unlimited.</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="per_user_limit">Per-User Limit</label></th>
                                <td>
                                    <input name="per_user_limit" type="number" min="1" id="per_user_limit" value="<?php echo esc_attr($coupon_to_edit['per_user_limit'] ?? '1'); ?>" class="small-text">
                                    <span class="description">How many times an individual user can redeem this code (tracked by user ID, email & phone).</span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="status">Status</label></th>
                                <td>
                                    <select name="status" id="status">
                                        <option value="active" <?php selected($coupon_to_edit['status'] ?? 'active', 'active'); ?>>Active</option>
                                        <option value="paused" <?php selected($coupon_to_edit['status'] ?? '', 'paused'); ?>>Paused</option>
                                        <option value="expired" <?php selected($coupon_to_edit['status'] ?? '', 'expired'); ?>>Expired</option>
                                    </select>
                                </td>
                            </tr>
                        </table>

                        <p class="submit" style="margin-top:20px;">
                            <button type="submit" class="button button-primary" style="background:#7A1F3D; border-color:#7A1F3D; padding:6px 20px; font-weight:700;">
                                <?php echo $coupon_to_edit ? __('Update Coupon', 'merarashifal') : __('Publish Coupon', 'merarashifal'); ?>
                            </button>
                            <a href="?page=mr-coupons&tab=coupons" class="button button-secondary" style="margin-left:8px;">Cancel</a>
                        </p>
                    </form>
                </div>

            <?php elseif ($active_tab === 'redemptions'): ?>
                <!-- ================= TAB 3: REDEMPTION AUDIT LOG ================= -->
                <div class="tab-content" style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:20px;">
                    <h2 style="margin-top:0; font-size:18px; color:#1e293b;"><?php _e('Coupon Redemptions & Usage Audit', 'merarashifal'); ?></h2>
                    <p class="description"><?php _e('Complete chronological log of every coupon redemption with financial audit trail.', 'merarashifal'); ?></p>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:140px; font-weight:700;">Date / Time</th>
                                <th style="width:110px; font-weight:700;">Coupon</th>
                                <th style="width:160px; font-weight:700;">Customer</th>
                                <th style="width:100px; font-weight:700;">Product</th>
                                <th style="width:100px; font-weight:700;">Pre-Tax Base</th>
                                <th style="width:100px; font-weight:700;">Discount Saved</th>
                                <th style="width:100px; font-weight:700;">Net Paid</th>
                                <th style="width:140px; font-weight:700;">Order Ref</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($redemptions)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding:20px; color:#64748b;">
                                        <?php _e('No redemptions recorded yet.', 'merarashifal'); ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($redemptions as $r): ?>
                                    <tr>
                                        <td>
                                            <?php echo esc_html(date_i18n('d M Y, h:i A', strtotime($r['redeemed_at']))); ?>
                                        </td>
                                        <td>
                                            <strong style="color:#7A1F3D; font-family:monospace;"><?php echo esc_html($r['coupon_code']); ?></strong>
                                        </td>
                                        <td>
                                            <div><?php echo esc_html($r['user_email'] ?: ('User #' . $r['user_id'])); ?></div>
                                            <?php if (!empty($r['user_phone'])): ?>
                                                <div style="font-size:11px; color:#64748b;"><?php echo esc_html($r['user_phone']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-size:11.5px; text-transform:capitalize;"><?php echo esc_html($r['product_type']); ?></span>
                                        </td>
                                        <td>
                                            ₹<?php echo number_format($r['subtotal_pretax'], 2); ?>
                                        </td>
                                        <td style="color:#059669; font-weight:700;">
                                            - ₹<?php echo number_format($r['discount_applied'], 2); ?>
                                        </td>
                                        <td>
                                            <strong>₹<?php echo number_format($r['total_paid'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <span style="font-family:monospace; font-size:11px; background:#f1f5f9; padding:2px 6px; border-radius:4px;">
                                                <?php echo esc_html($r['internal_txn_id']); ?>
                                            </span>
                                            <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=mr_view_invoice&txn_id=' . urlencode($r['internal_txn_id']))); ?>" target="_blank" style="margin-left:4px; font-size:11px; color:#2563EB; text-decoration:none;" title="<?php esc_attr_e('View Retail Tax Invoice', 'merarashifal'); ?>">
                                                <span class="dashicons dashicons-media-document" style="font-size:14px; vertical-align:middle; width:14px; height:14px;"></span> Invoice
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($active_tab === 'bucket'): ?>
                <!-- ================= TAB 4: BUCKET DISCOUNT & UPSELLS (FULLY CUSTOMIZABLE) ================= -->
                <div class="tab-content" style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:24px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px; border-bottom:1.5px solid #F1F5F9; padding-bottom:16px;">
                        <div>
                            <h2 style="margin:0 0 6px 0; font-size:19px; color:#7A1F3D; display:flex; align-items:center; gap:8px;">
                                <span class="dashicons dashicons-cart" style="color:#F58220; font-size:24px; width:24px; height:24px;"></span>
                                <span><?php _e('In-Cart Bucket Upsells & Dynamic Bundle Offers', 'merarashifal'); ?></span>
                            </h2>
                            <p class="description" style="margin:0;">
                                <?php _e('Configure intelligent in-cart prompts that ask customers to add companion reports, bundle packs, chat credits, or subscription upgrades in exchange for flat or percentage discounts on their entire bill.', 'merarashifal'); ?>
                            </p>
                        </div>
                        <div>
                            <a href="#upsell-rule-form" class="button button-primary button-large" style="background:#7A1F3D; border-color:#5E172E; font-weight:700;">
                                <span class="dashicons dashicons-plus-alt2" style="vertical-align:middle;"></span> <?php _e('Add New Upsell Rule', 'merarashifal'); ?>
                            </a>
                        </div>
                    </div>

                    <?php if (isset($_GET['msg'])): ?>
                        <?php if ($_GET['msg'] === 'rule_saved'): ?>
                            <div class="notice notice-success is-dismissible" style="margin:0 0 20px 0;"><p><?php _e('Upsell rule saved successfully!', 'merarashifal'); ?></p></div>
                        <?php elseif ($_GET['msg'] === 'rule_deleted'): ?>
                            <div class="notice notice-success is-dismissible" style="margin:0 0 20px 0;"><p><?php _e('Upsell rule removed successfully.', 'merarashifal'); ?></p></div>
                        <?php elseif ($_GET['msg'] === 'rule_toggled'): ?>
                            <div class="notice notice-success is-dismissible" style="margin:0 0 20px 0;"><p><?php _e('Upsell rule status updated.', 'merarashifal'); ?></p></div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Active Rules Table -->
                    <h3 style="font-size:15px; font-weight:700; color:#1E293B; margin:0 0 12px 0;">
                        <?php _e('Configured In-Cart Rules & Triggers', 'merarashifal'); ?>
                    </h3>

                    <table class="wp-list-table widefat fixed striped" style="margin-bottom:30px;">
                        <thead>
                            <tr>
                                <th style="width:90px; font-weight:700;">Status</th>
                                <th style="width:200px; font-weight:700;">Rule Title &amp; Pitch</th>
                                <th style="width:140px; font-weight:700;">Target Category</th>
                                <th style="width:140px; font-weight:700;">Cart Trigger</th>
                                <th style="width:180px; font-weight:700;">Upsell Product Offered</th>
                                <th style="width:130px; font-weight:700;">Billing Discount</th>
                                <th style="width:140px; font-weight:700; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bucket_rules)): ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:30px; color:#64748B;">
                                        <?php _e('No rules configured yet. Click "Add New Upsell Rule" below.', 'merarashifal'); ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bucket_rules as $r_id => $r): 
                                    $st = $r['status'] ?? 'active';
                                    $is_active = ($st === 'active');
                                    $cat = $r['category'] ?? 'reports';
                                    $cat_colors = [
                                        'reports'       => ['bg' => '#EFF6FF', 'fg' => '#1D4ED8', 'border' => '#BFDBFE'],
                                        'chat_credits'  => ['bg' => '#F3E8FF', 'fg' => '#7E22CE', 'border' => '#DDD6FE'],
                                        'combo_deals'   => ['bg' => '#ECFDF5', 'fg' => '#047857', 'border' => '#A7F3D0'],
                                        'subscriptions' => ['bg' => '#FEF3C7', 'fg' => '#B45309', 'border' => '#FDE68A'],
                                        'all'           => ['bg' => '#F1F5F9', 'fg' => '#334155', 'border' => '#CBD5E1'],
                                    ];
                                    $c_theme = $cat_colors[$cat] ?? $cat_colors['all'];
                                ?>
                                    <tr>
                                        <td>
                                            <span style="display:inline-block; font-size:11px; font-weight:700; text-transform:uppercase; padding:3px 8px; border-radius:6px; background:<?php echo $is_active ? '#ECFDF5' : '#FFFBEB'; ?>; color:<?php echo $is_active ? '#059669' : '#D97706'; ?>;">
                                                <?php echo esc_html($st); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong style="color:#7A1F3D; font-size:13.5px;"><?php echo esc_html($r['title']); ?></strong>
                                            <div style="font-size:11.5px; color:#64748B; margin-top:3px; line-height:1.4;">
                                                <em>"<?php echo esc_html(wp_trim_words($r['headline'], 10)); ?>"</em>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="display:inline-block; font-size:11px; font-weight:700; padding:3px 9px; border-radius:6px; background:<?php echo $c_theme['bg']; ?>; color:<?php echo $c_theme['fg']; ?>; border:1px solid <?php echo $c_theme['border']; ?>;">
                                                <?php echo esc_html($supported_categories[$cat] ?? ucfirst($cat)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['min_cart_value']) && floatval($r['min_cart_value']) > 0): ?>
                                                <div style="font-weight:700; color:#1E293B;">Cart &ge; ₹<?php echo number_format($r['min_cart_value'], 0); ?></div>
                                                <div style="font-size:11px; color:#64748B;">Min pre-tax cart value</div>
                                            <?php else: ?>
                                                <span style="font-size:12px; color:#059669; font-weight:600;">Any product in category</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong style="color:#1E293B; font-size:13px;"><?php echo esc_html($r['upsell_name']); ?></strong>
                                            <div style="font-size:11.5px; color:#D97706; font-weight:700; margin-top:2px;">
                                                + ₹<?php echo number_format($r['upsell_price'], 2); ?>
                                                <?php if (($r['upsell_type'] ?? '') === 'companion_selector'): ?>
                                                    <span style="font-size:10px; color:#64748B; font-weight:normal;">(Catalog Dropdown)</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (($r['discount_type'] ?? 'percent') === 'flat'): ?>
                                                <span style="display:inline-block; font-size:12px; font-weight:800; background:#FFF1F2; color:#BE123C; padding:3px 8px; border-radius:6px; border:1px solid #FECDD3;">
                                                    Flat -₹<?php echo number_format($r['discount_value'], 2); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="display:inline-block; font-size:12px; font-weight:800; background:#ECFDF5; color:#047857; padding:3px 8px; border-radius:6px; border:1px solid #A7F3D0;">
                                                    <?php echo floatval($r['discount_value']); ?>% OFF Total
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right;">
                                            <a href="?page=mr-coupons&tab=bucket&edit_rule=<?php echo esc_attr($r_id); ?>#upsell-rule-form" class="button button-small">Edit</a>

                                            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline-block; margin-left:4px;">
                                                <input type="hidden" name="action" value="mr_toggle_bucket_rule">
                                                <input type="hidden" name="rule_id" value="<?php echo esc_attr($r_id); ?>">
                                                <?php wp_nonce_field('mr_bucket_rule_action', 'nonce'); ?>
                                                <button type="submit" class="button button-small">
                                                    <?php echo $is_active ? 'Pause' : 'Activate'; ?>
                                                </button>
                                            </form>

                                            <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline-block; margin-left:4px;" onsubmit="return confirm('Are you sure you want to delete this upsell rule?');">
                                                <input type="hidden" name="action" value="mr_delete_bucket_rule">
                                                <input type="hidden" name="rule_id" value="<?php echo esc_attr($r_id); ?>">
                                                <?php wp_nonce_field('mr_bucket_rule_action', 'nonce'); ?>
                                                <button type="submit" class="button button-small" style="color:#DC2626;">✕</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- ================= ADD / EDIT RULE FORM ================= -->
                    <div id="upsell-rule-form" style="background:#FFF9F2; border:1.5px solid #D9A441; border-radius:12px; padding:24px; max-width:860px; margin-top:30px;">
                        <h3 style="margin-top:0; font-size:17px; color:#7A1F3D; display:flex; align-items:center; gap:8px;">
                            <span class="dashicons dashicons-edit" style="color:#F58220;"></span>
                            <?php echo $bucket_rule_to_edit ? sprintf(__('Edit Upsell Rule: %s', 'merarashifal'), esc_html($bucket_rule_to_edit['title'])) : __('Create New In-Cart Bucket Upsell Rule', 'merarashifal'); ?>
                        </h3>
                        <p class="description" style="margin-bottom:20px;">
                            <?php _e('Define which product triggers the offer, what upsell product or bundle to recommend, and what percentage or flat rupee discount to award on total billing.', 'merarashifal'); ?>
                        </p>

                        <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="hidden" name="action" value="mr_save_bucket_rule">
                            <input type="hidden" name="rule_id" value="<?php echo esc_attr($bucket_rule_to_edit['id'] ?? ''); ?>">
                            <?php wp_nonce_field('mr_bucket_rule_action', 'nonce'); ?>

                            <table class="form-table" role="presentation">
                                <tr>
                                    <th scope="row"><label for="rule_title">Rule Title <span style="color:#DC2626;">*</span></label></th>
                                    <td>
                                        <input name="title" type="text" id="rule_title" value="<?php echo esc_attr($bucket_rule_to_edit['title'] ?? ''); ?>" class="regular-text" required placeholder="e.g. Single Report to Master Bundle Upgrade">
                                        <p class="description">Internal name used to identify this promotion rule.</p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_category">Trigger Category <span style="color:#DC2626;">*</span></label></th>
                                    <td>
                                        <select name="category" id="rule_category">
                                            <?php foreach ($supported_categories as $cat_key => $cat_lbl): ?>
                                                <option value="<?php echo esc_attr($cat_key); ?>" <?php selected($bucket_rule_to_edit['category'] ?? 'reports', $cat_key); ?>>
                                                    <?php echo esc_html($cat_lbl); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description">Which category of purchase triggers this in-cart upsell prompt at checkout.</p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_min_cart">Minimum Cart Value (₹)</label></th>
                                    <td>
                                        <input name="min_cart_value" type="number" step="1" min="0" id="rule_min_cart" value="<?php echo esc_attr($bucket_rule_to_edit['min_cart_value'] ?? '0'); ?>" class="small-text">
                                        <span class="description">Enter 0 to trigger on any item in category, or enter a cart amount (e.g. ₹99 or ₹199).</span>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_upsell_type">Upsell Type</label></th>
                                    <td>
                                        <select name="upsell_type" id="rule_upsell_type">
                                            <option value="companion_selector" <?php selected($bucket_rule_to_edit['upsell_type'] ?? 'companion_selector', 'companion_selector'); ?>>Catalog Report Selector (Customer picks 2nd report from dropdown)</option>
                                            <option value="bundle" <?php selected($bucket_rule_to_edit['upsell_type'] ?? '', 'bundle'); ?>>Bundle Pack Upgrade (e.g. 5-Report Master Bundle)</option>
                                            <option value="fixed_item" <?php selected($bucket_rule_to_edit['upsell_type'] ?? '', 'fixed_item'); ?>>Specific Add-on Product / Service / Chat Pack</option>
                                        </select>
                                        <p class="description">Choose how the upsell item is presented at checkout.</p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_upsell_name">Upsell Product Name <span style="color:#DC2626;">*</span></label></th>
                                    <td>
                                        <input name="upsell_name" type="text" id="rule_upsell_name" value="<?php echo esc_attr($bucket_rule_to_edit['upsell_name'] ?? '5-Report Complete Master Bundle Upgrade'); ?>" class="regular-text" required placeholder="e.g. 5-Report Complete Master Bundle Upgrade">
                                        <p class="description">Name of the product or upgrade presented to the customer.</p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_upsell_slug">Upsell Product Identifier / Key</label></th>
                                    <td>
                                        <input name="upsell_slug" type="text" id="rule_upsell_slug" value="<?php echo esc_attr($bucket_rule_to_edit['upsell_slug'] ?? 'bundle_5_reports'); ?>" class="regular-text" placeholder="e.g. bundle_5_reports or chat_credits_100">
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_upsell_price">Upsell Add-on Price (₹) <span style="color:#DC2626;">*</span></label></th>
                                    <td>
                                        <input name="upsell_price" type="number" step="0.01" min="0" id="rule_upsell_price" value="<?php echo esc_attr($bucket_rule_to_edit['upsell_price'] ?? '99'); ?>" class="small-text" required>
                                        <span class="description">Pre-tax price of this add-on product before the bucket discount is applied.</span>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_headline">In-Cart Prompt Headline <span style="color:#DC2626;">*</span></label></th>
                                    <td>
                                        <input name="headline" type="text" id="rule_headline" value="<?php echo esc_attr($bucket_rule_to_edit['headline'] ?? '🎁 Upgrade to Master Report Bundle & Unlock 25% OFF Total Billing!'); ?>" class="large-text" required placeholder="e.g. 🎁 Upgrade to Master Report Bundle & Unlock 25% OFF Total Billing!">
                                        <p class="description">Catchy title displayed inside the checkout prompt banner.</p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_subtext">In-Cart Pitch / Subtext</label></th>
                                    <td>
                                        <textarea name="subtext" id="rule_subtext" rows="2" class="large-text" placeholder="Why should the customer add this? E.g. Add 100 extra lifetime consultation credits to your order and get flat 20% off your entire recharge."><?php echo esc_textarea($bucket_rule_to_edit['subtext'] ?? ''); ?></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_discount_type">Total Billing Discount Type <span style="color:#DC2626;">*</span></label></th>
                                    <td>
                                        <select name="discount_type" id="rule_discount_type">
                                            <option value="percent" <?php selected($bucket_rule_to_edit['discount_type'] ?? 'percent', 'percent'); ?>>Percentage (%) Discount on Total Bill</option>
                                            <option value="flat" <?php selected($bucket_rule_to_edit['discount_type'] ?? '', 'flat'); ?>>Flat Rupee (₹) Discount on Total Bill</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_discount_value">Discount Value <span style="color:#DC2626;">*</span></label></th>
                                    <td>
                                        <input name="discount_value" type="number" step="0.01" min="0" id="rule_discount_value" value="<?php echo esc_attr($bucket_rule_to_edit['discount_value'] ?? '25'); ?>" class="small-text" required>
                                        <span class="description">Enter percent (e.g. 25 for 25% off total bill) or flat rupees (e.g. 100 for ₹100 off).</span>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_status">Status</label></th>
                                    <td>
                                        <select name="status" id="rule_status">
                                            <option value="active" <?php selected($bucket_rule_to_edit['status'] ?? 'active', 'active'); ?>>Active (Live at Checkout)</option>
                                            <option value="paused" <?php selected($bucket_rule_to_edit['status'] ?? '', 'paused'); ?>>Paused (Inactive)</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="rule_priority">Priority / Ordering</label></th>
                                    <td>
                                        <input name="priority" type="number" step="1" id="rule_priority" value="<?php echo esc_attr($bucket_rule_to_edit['priority'] ?? '10'); ?>" class="small-text">
                                        <span class="description">Lower numbers take higher priority if multiple rules match.</span>
                                    </td>
                                </tr>
                            </table>

                            <p class="submit" style="margin-top:20px;">
                                <button type="submit" class="button button-primary button-large" style="background:#7A1F3D; border-color:#5E172E; font-weight:700;">
                                    <?php echo $bucket_rule_to_edit ? __('Update Upsell Rule', 'merarashifal') : __('Save & Activate Upsell Rule', 'merarashifal'); ?>
                                </button>
                                <?php if ($bucket_rule_to_edit): ?>
                                    <a href="?page=mr-coupons&tab=bucket" class="button button-secondary button-large" style="margin-left:10px;">Cancel Edit</a>
                                <?php endif; ?>
                            </p>
                        </form>
                    </div>

                </div>
            <?php endif; ?>

        </div>
        <?php
    }

    /**
     * Handle save coupon post
     */
    public static function handle_save_coupon() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'merarashifal'));
        }
        check_admin_referer('mr_coupon_action', 'nonce');

        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        $data = [
            'code'              => sanitize_text_field($_POST['code'] ?? ''),
            'title'             => sanitize_text_field($_POST['title'] ?? ''),
            'description'       => sanitize_textarea_field($_POST['description'] ?? ''),
            'discount_type'     => sanitize_key($_POST['discount_type'] ?? 'percent'),
            'discount_value'    => floatval($_POST['discount_value'] ?? 0),
            'max_discount_cap'  => floatval($_POST['max_discount_cap'] ?? 0),
            'min_order_value'   => floatval($_POST['min_order_value'] ?? 0),
            'product_scope'     => sanitize_key($_POST['product_scope'] ?? 'all'),
            'total_usage_limit' => intval($_POST['total_usage_limit'] ?? 0),
            'per_user_limit'    => intval($_POST['per_user_limit'] ?? 1),
            'status'            => sanitize_key($_POST['status'] ?? 'active'),
        ];

        $res = MR_Coupons::save_coupon($data, $coupon_id);

        if (is_wp_error($res)) {
            wp_redirect(admin_url('admin.php?page=mr-coupons&tab=edit&err=' . urlencode($res->get_error_message())));
            exit;
        }

        wp_redirect(admin_url('admin.php?page=mr-coupons&tab=coupons&msg=saved'));
        exit;
    }

    /**
     * Handle delete coupon post
     */
    public static function handle_delete_coupon() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'merarashifal'));
        }
        check_admin_referer('mr_coupon_action', 'nonce');

        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        if ($coupon_id > 0) {
            MR_Coupons::delete_coupon($coupon_id);
        }

        wp_redirect(admin_url('admin.php?page=mr-coupons&tab=coupons&msg=deleted'));
        exit;
    }

    /**
     * Handle toggle status
     */
    public static function handle_toggle_coupon_status() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'merarashifal'));
        }
        check_admin_referer('mr_coupon_action', 'nonce');

        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        $coupon = MR_Coupons::get_coupon_by_id($coupon_id);
        if ($coupon) {
            $new_status = ($coupon['status'] === 'active') ? 'paused' : 'active';
            global $wpdb;
            $wpdb->update($wpdb->prefix . MR_Coupons::TABLE_COUPONS, ['status' => $new_status], ['id' => $coupon_id]);
        }

        wp_redirect(admin_url('admin.php?page=mr-coupons&tab=coupons&msg=status_toggled'));
        exit;
    }

    /**
     * Handle save bucket upsell rule post
     */
    public static function handle_save_bucket_rule() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'merarashifal'));
        }
        check_admin_referer('mr_bucket_rule_action', 'nonce');

        $data = [
            'id'             => sanitize_key($_POST['rule_id'] ?? ''),
            'title'          => sanitize_text_field($_POST['title'] ?? ''),
            'category'       => sanitize_key($_POST['category'] ?? 'reports'),
            'min_cart_value' => floatval($_POST['min_cart_value'] ?? 0),
            'upsell_type'    => sanitize_key($_POST['upsell_type'] ?? 'fixed_item'),
            'upsell_name'    => sanitize_text_field($_POST['upsell_name'] ?? ''),
            'upsell_slug'    => sanitize_key($_POST['upsell_slug'] ?? ''),
            'upsell_price'   => floatval($_POST['upsell_price'] ?? 0),
            'headline'       => sanitize_text_field($_POST['headline'] ?? ''),
            'subtext'        => sanitize_textarea_field($_POST['subtext'] ?? ''),
            'discount_type'  => sanitize_key($_POST['discount_type'] ?? 'percent'),
            'discount_value' => floatval($_POST['discount_value'] ?? 0),
            'status'         => sanitize_key($_POST['status'] ?? 'active'),
            'priority'       => intval($_POST['priority'] ?? 10),
        ];

        if (class_exists('MR_Bucket_Discount')) {
            MR_Bucket_Discount::save_rule($data);
        }

        wp_redirect(admin_url('admin.php?page=mr-coupons&tab=bucket&msg=rule_saved'));
        exit;
    }

    /**
     * Handle delete bucket upsell rule
     */
    public static function handle_delete_bucket_rule() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'merarashifal'));
        }
        check_admin_referer('mr_bucket_rule_action', 'nonce');

        $rule_id = sanitize_key($_POST['rule_id'] ?? '');
        if (!empty($rule_id) && class_exists('MR_Bucket_Discount')) {
            MR_Bucket_Discount::delete_rule($rule_id);
        }

        wp_redirect(admin_url('admin.php?page=mr-coupons&tab=bucket&msg=rule_deleted'));
        exit;
    }

    /**
     * Handle toggle bucket rule status
     */
    public static function handle_toggle_bucket_rule() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Permission denied', 'merarashifal'));
        }
        check_admin_referer('mr_bucket_rule_action', 'nonce');

        $rule_id = sanitize_key($_POST['rule_id'] ?? '');
        if (!empty($rule_id) && class_exists('MR_Bucket_Discount')) {
            MR_Bucket_Discount::toggle_rule_status($rule_id);
        }

        wp_redirect(admin_url('admin.php?page=mr-coupons&tab=bucket&msg=rule_toggled'));
        exit;
    }
}
