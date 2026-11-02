<?php
/**
 * WordPress Admin Interface for Report Bundles & Best Buy Upsell Manager
 *
 * @package MR_Subscription_Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Bundles_Admin {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu'], 36);
        add_action('admin_post_mr_save_bundles', [__CLASS__, 'handle_save']);
        add_action('admin_post_mr_reset_bundles', [__CLASS__, 'handle_reset']);
    }

    public static function register_menu() {
        add_submenu_page(
            'mr-subscriptions',
            __('Report Bundles & Upsell Manager', 'merarashifal'),
            __('Bundles & Best Buy', 'merarashifal'),
            'manage_options',
            'mr-report-bundles',
            [__CLASS__, 'render_admin_page']
        );
    }

    public static function handle_save() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user', 'merarashifal'));
        }
        check_admin_referer('mr_save_bundles_action', 'mr_bundles_nonce');

        $active_report = sanitize_key($_POST['active_report_slug'] ?? 'yearly_horoscope_long');
        $all_bundles   = MR_Bundles::get_all_bundles();

        // 1. Process Global Settings
        $global_settings = [
            'enabled'                => !empty($_POST['global_enabled']),
            'default_duo_credits'    => intval($_POST['global_duo_credits'] ?? 15),
            'default_trio_credits'   => intval($_POST['global_trio_credits'] ?? 30),
            'default_currency_symbol'=> sanitize_text_field($_POST['global_currency_symbol'] ?? '₹'),
            'show_chat_credit_badge' => !empty($_POST['global_show_chat_badge']),
            'show_fbt_bundle_box'    => !empty($_POST['global_show_fbt_box']),
            'show_hero_selector'     => !empty($_POST['global_show_hero_selector']),
        ];
        MR_Bundles::save_global_settings($global_settings);

        // 2. Process Current Report Settings
        if (isset($_POST['bundle'][$active_report])) {
            $input = $_POST['bundle'][$active_report];

            $config = [
                'report_slug'    => $active_report,
                'bundle_enabled' => !empty($input['bundle_enabled']),
                'tier_single'    => [
                    'enabled'       => true,
                    'title'         => sanitize_text_field($input['tier_single']['title'] ?? 'Single Report'),
                    'subtitle'      => sanitize_text_field($input['tier_single']['subtitle'] ?? ''),
                    'price_regular' => floatval($input['tier_single']['price_regular'] ?? 599),
                    'price_sale'    => floatval($input['tier_single']['price_sale'] ?? 299),
                    'badge'         => sanitize_text_field($input['tier_single']['badge'] ?? 'Standard'),
                    'chat_credits'  => intval($input['tier_single']['chat_credits'] ?? 0),
                ],
                'tier_duo'       => [
                    'enabled'        => !empty($input['tier_duo']['enabled']),
                    'title'          => sanitize_text_field($input['tier_duo']['title'] ?? 'Smart Duo (Pack of 2)'),
                    'subtitle'       => sanitize_text_field($input['tier_duo']['subtitle'] ?? ''),
                    'paired_reports' => [sanitize_key($input['tier_duo']['paired_report_1'] ?? '')],
                    'price_regular'  => floatval($input['tier_duo']['price_regular'] ?? 1198),
                    'price_sale'     => floatval($input['tier_duo']['price_sale'] ?? 449),
                    'badge'          => sanitize_text_field($input['tier_duo']['badge'] ?? 'POPULAR'),
                    'chat_credits'   => intval($input['tier_duo']['chat_credits'] ?? 15),
                    'highlights'     => sanitize_textarea_field($input['tier_duo']['highlights'] ?? ''),
                ],
                'tier_trio'      => [
                    'enabled'        => !empty($input['tier_trio']['enabled']),
                    'title'          => sanitize_text_field($input['tier_trio']['title'] ?? 'Cosmic Trinity (Pack of 3 + AI Chat)'),
                    'subtitle'       => sanitize_text_field($input['tier_trio']['subtitle'] ?? ''),
                    'is_best_buy'    => !empty($input['tier_trio']['is_best_buy']),
                    'paired_reports' => [
                        sanitize_key($input['tier_trio']['paired_report_1'] ?? ''),
                        sanitize_key($input['tier_trio']['paired_report_2'] ?? '')
                    ],
                    'price_regular'  => floatval($input['tier_trio']['price_regular'] ?? 2097),
                    'price_sale'     => floatval($input['tier_trio']['price_sale'] ?? 599),
                    'badge'          => sanitize_text_field($input['tier_trio']['badge'] ?? 'BEST BUY - SAVE 71%'),
                    'chat_credits'   => intval($input['tier_trio']['chat_credits'] ?? 30),
                    'highlights'     => sanitize_textarea_field($input['tier_trio']['highlights'] ?? ''),
                ]
            ];

            MR_Bundles::save_bundle_config($active_report, $config);
        }

        wp_redirect(add_query_arg([
            'page'   => 'mr-report-bundles',
            'report' => $active_report,
            'saved'  => '1'
        ], admin_url('admin.php')));
        exit;
    }

    public static function handle_reset() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized user', 'merarashifal'));
        }
        check_admin_referer('mr_reset_bundles_action', 'mr_reset_nonce');

        MR_Bundles::reset_to_defaults();

        wp_redirect(add_query_arg([
            'page'    => 'mr-report-bundles',
            'reset'   => '1'
        ], admin_url('admin.php')));
        exit;
    }

    public static function render_admin_page() {
        $all_reports   = MR_Bundles::get_available_reports();
        $global_opts   = MR_Bundles::get_global_settings();
        $active_report = sanitize_key($_GET['report'] ?? 'yearly_horoscope_long');
        if (!isset($all_reports[$active_report])) {
            $active_report = 'yearly_horoscope_long';
        }

        $bundle_cfg = MR_Bundles::get_bundle_for_report($active_report);
        $saved      = !empty($_GET['saved']);
        $reset      = !empty($_GET['reset']);
        ?>
        <div class="wrap mr-admin-wrap" style="max-width: 1200px; margin: 25px auto;">
            <h1 style="display:flex; align-items:center; gap:12px; font-size:24px; font-weight:700; color:#1e293b; margin-bottom:18px;">
                <span style="background:linear-gradient(135deg, #F58220 0%, #7A1F3D 100%); color:#fff; width:40px; height:40px; border-radius:10px; display:inline-flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(245, 130, 32, 0.3);">
                    <i class="dashicons dashicons-cart" style="font-size:22px; width:22px; height:22px; margin-top:2px;"></i>
                </span>
                <?php _e('Report Bundles & Best Buy Manager', 'merarashifal'); ?>
            </h1>
            <p style="color:#64748b; font-size:14px; margin-top:-8px; margin-bottom:24px;">
                <?php _e('Configure custom discounted packs (Pack of 2, Pack of 3 / Best Buy) and bonus Niyati AI Chat Credits for each report page. Everything is 100% variable and editable.', 'merarashifal'); ?>
            </p>

            <?php if ($saved): ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#10b981;">
                    <p><strong><?php _e('Settings Saved Successfully!', 'merarashifal'); ?></strong> <?php _e('The bundle pricing, pairings, and bonus credits for this report are now live on your site.', 'merarashifal'); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($reset): ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong><?php _e('Bundles Reset to Smart Defaults.', 'merarashifal'); ?></strong></p>
                </div>
            <?php endif; ?>

            <!-- NAVIGATION TABS FOR ALL 9 REPORTS -->
            <div style="background:#fff; border:1px solid #cbd5e1; border-radius:12px 12px 0 0; padding:12px 16px 0 16px; display:flex; flex-wrap:wrap; gap:6px;">
                <?php foreach ($all_reports as $r_slug => $r_meta): 
                    $is_active = ($r_slug === $active_report);
                ?>
                    <a href="<?php echo esc_url(add_query_arg(['page' => 'mr-report-bundles', 'report' => $r_slug], admin_url('admin.php'))); ?>" 
                       style="text-decoration:none; padding:10px 16px; border-radius:8px 8px 0 0; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:8px; transition:all 0.15s ease; <?php echo $is_active ? 'background:#7A1F3D; color:#ffffff; border:1px solid #7A1F3D; border-bottom:none;' : 'background:#f8fafc; color:#475569; border:1px solid #e2e8f0;'; ?>">
                        <i class="<?php echo esc_attr($r_meta['icon']); ?>"></i>
                        <span><?php echo esc_html(wp_trim_words($r_meta['name'], 3)); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- MAIN EDITING FORM -->
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="background:#ffffff; border:1px solid #cbd5e1; border-top:none; border-radius:0 0 12px 12px; padding:28px 32px; box-shadow:0 10px 25px rgba(0,0,0,0.04);">
                <?php wp_nonce_field('mr_save_bundles_action', 'mr_bundles_nonce'); ?>
                <input type="hidden" name="action" value="mr_save_bundles">
                <input type="hidden" name="active_report_slug" value="<?php echo esc_attr($active_report); ?>">

                <!-- REPORT HEADER SUMMARY -->
                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:20px; border-bottom:2px solid #f1f5f9; margin-bottom:24px;">
                    <div>
                        <span style="background:#fef3c7; color:#b45309; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; padding:3px 10px; border-radius:999px;">Currently Editing</span>
                        <h2 style="font-size:22px; font-weight:800; color:#7A1F3D; margin:6px 0 2px 0;">
                            <?php echo esc_html($all_reports[$active_report]['name']); ?>
                        </h2>
                        <span style="font-size:13px; color:#64748b;">
                            Slug: <code><?php echo esc_html($active_report); ?></code> &bull; 
                            <a href="<?php echo esc_url(MR_Bundles::get_report_page_url($active_report)); ?>" target="_blank" style="color:#F58220; text-decoration:none;">View Live Report Page <i class="dashicons dashicons-external" style="font-size:14px; vertical-align:middle;"></i></a>
                        </span>
                    </div>

                    <div style="display:flex; align-items:center; gap:12px;">
                        <label style="display:flex; align-items:center; gap:8px; font-weight:600; font-size:14px; cursor:pointer; background:#fff7ed; padding:8px 14px; border-radius:8px; border:1px solid #fed7aa;">
                            <input type="checkbox" name="bundle[<?php echo esc_attr($active_report); ?>][bundle_enabled]" value="1" <?php checked($bundle_cfg['bundle_enabled'], true); ?>>
                            <span>Enable Bundle Selector On This Page</span>
                        </label>
                    </div>
                </div>

                <!-- 3 BUNDLE TIERS DISPLAY -->
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:24px; margin-bottom:30px;">
                    
                    <!-- TIER 1: SINGLE REPORT -->
                    <div style="background:#f8fafc; border:1.5px solid #cbd5e1; border-radius:12px; padding:20px; position:relative;">
                        <div style="position:absolute; top:-12px; left:16px; background:#64748b; color:#fff; font-size:11px; font-weight:700; padding:2px 10px; border-radius:999px;">
                            TIER 1 &bull; BASELINE
                        </div>
                        <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin:10px 0 14px 0;">
                            <?php _e('Single Report A la Carte', 'merarashifal'); ?>
                        </h3>

                        <p style="font-size:12px; color:#64748b; margin-bottom:14px;">The base individual report purchase without extra bundles.</p>

                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Tier Title:</label>
                            <input type="text" name="bundle[<?php echo esc_attr($active_report); ?>][tier_single][title]" value="<?php echo esc_attr($bundle_cfg['tier_single']['title']); ?>" style="width:100%; border-radius:6px;">
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Sale Price (₹):</label>
                                <input type="number" step="1" name="bundle[<?php echo esc_attr($active_report); ?>][tier_single][price_sale]" value="<?php echo esc_attr($bundle_cfg['tier_single']['price_sale']); ?>" style="width:100%; font-weight:700; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">MRP Price (₹):</label>
                                <input type="number" step="1" name="bundle[<?php echo esc_attr($active_report); ?>][tier_single][price_regular]" value="<?php echo esc_attr($bundle_cfg['tier_single']['price_regular']); ?>" style="width:100%; border-radius:6px;">
                            </div>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Badge Text:</label>
                            <input type="text" name="bundle[<?php echo esc_attr($active_report); ?>][tier_single][badge]" value="<?php echo esc_attr($bundle_cfg['tier_single']['badge'] ?? 'Standard'); ?>" style="width:100%; border-radius:6px;">
                        </div>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Bonus Chat Credits:</label>
                            <input type="number" name="bundle[<?php echo esc_attr($active_report); ?>][tier_single][chat_credits]" value="<?php echo esc_attr($bundle_cfg['tier_single']['chat_credits'] ?? 0); ?>" style="width:100%; border-radius:6px;" placeholder="0">
                        </div>
                    </div>

                    <!-- TIER 2: SMART DUO (PACK OF 2) -->
                    <div style="background:#fff7ed; border:1.5px solid #fdba74; border-radius:12px; padding:20px; position:relative;">
                        <div style="position:absolute; top:-12px; left:16px; background:#ea580c; color:#fff; font-size:11px; font-weight:700; padding:2px 10px; border-radius:999px;">
                            TIER 2 &bull; POPULAR DUO
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin:10px 0 14px 0;">
                            <h3 style="font-size:16px; font-weight:700; color:#9a3412; margin:0;">
                                <?php _e('Smart Duo (Pack of 2)', 'merarashifal'); ?>
                            </h3>
                            <label style="font-size:12px; font-weight:600; display:flex; align-items:center; gap:4px; cursor:pointer;">
                                <input type="checkbox" name="bundle[<?php echo esc_attr($active_report); ?>][tier_duo][enabled]" value="1" <?php checked($bundle_cfg['tier_duo']['enabled'], true); ?>>
                                <span>Active</span>
                            </label>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Duo Package Title:</label>
                            <input type="text" name="bundle[<?php echo esc_attr($active_report); ?>][tier_duo][title]" value="<?php echo esc_attr($bundle_cfg['tier_duo']['title']); ?>" style="width:100%; border-radius:6px;">
                        </div>

                        <!-- SISTER REPORT 1 DROPDOWN -->
                        <div style="margin-bottom:12px; background:#fff; padding:10px; border-radius:8px; border:1px solid #fed7aa;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#ea580c; margin-bottom:4px;">
                                <i class="dashicons dashicons-plus" style="font-size:14px; vertical-align:middle;"></i> Paired Sister Report:
                            </label>
                            <?php $selected_duo = $bundle_cfg['tier_duo']['paired_reports'][0] ?? ''; ?>
                            <select name="bundle[<?php echo esc_attr($active_report); ?>][tier_duo][paired_report_1]" style="width:100%; border-radius:6px;">
                                <?php foreach ($all_reports as $s_slug => $s_meta): 
                                    if ($s_slug === $active_report) continue; // Don't pair with itself
                                ?>
                                    <option value="<?php echo esc_attr($s_slug); ?>" <?php selected($selected_duo, $s_slug); ?>>
                                        <?php echo esc_html($s_meta['name']); ?> (<?php echo esc_html($s_meta['pages']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Sale Price (₹):</label>
                                <input type="number" step="1" name="bundle[<?php echo esc_attr($active_report); ?>][tier_duo][price_sale]" value="<?php echo esc_attr($bundle_cfg['tier_duo']['price_sale']); ?>" style="width:100%; font-weight:700; color:#c2410c; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">MRP Price (₹):</label>
                                <input type="number" step="1" name="bundle[<?php echo esc_attr($active_report); ?>][tier_duo][price_regular]" value="<?php echo esc_attr($bundle_cfg['tier_duo']['price_regular']); ?>" style="width:100%; border-radius:6px;">
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Badge Text:</label>
                                <input type="text" name="bundle[<?php echo esc_attr($active_report); ?>][tier_duo][badge]" value="<?php echo esc_attr($bundle_cfg['tier_duo']['badge'] ?? 'POPULAR'); ?>" style="width:100%; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Bonus Chat Credits:</label>
                                <input type="number" name="bundle[<?php echo esc_attr($active_report); ?>][tier_duo][chat_credits]" value="<?php echo esc_attr($bundle_cfg['tier_duo']['chat_credits'] ?? 15); ?>" style="width:100%; font-weight:700; color:#0284c7; border-radius:6px;">
                            </div>
                        </div>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Highlight Bullets (short):</label>
                            <textarea name="bundle[<?php echo esc_attr($active_report); ?>][tier_duo][highlights]" rows="2" style="width:100%; font-size:12px; border-radius:6px;"><?php echo esc_textarea($bundle_cfg['tier_duo']['highlights'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- TIER 3: COSMIC TRINITY (PACK OF 3 - BEST BUY) -->
                    <div style="background:#fffdfa; border:2px solid #D9A441; box-shadow:0 6px 20px rgba(217, 164, 65, 0.2); border-radius:12px; padding:20px; position:relative;">
                        <div style="position:absolute; top:-12px; left:16px; background:linear-gradient(135deg, #7A1F3D 0%, #D9A441 100%); color:#fff; font-size:11px; font-weight:800; padding:2px 12px; border-radius:999px; box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                            ⭐ TIER 3 &bull; BEST BUY / VIP
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin:10px 0 14px 0;">
                            <h3 style="font-size:16px; font-weight:800; color:#7A1F3D; margin:0;">
                                <?php _e('Cosmic Trinity (Pack of 3 + Chat)', 'merarashifal'); ?>
                            </h3>
                            <label style="font-size:12px; font-weight:600; display:flex; align-items:center; gap:4px; cursor:pointer;">
                                <input type="checkbox" name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][enabled]" value="1" <?php checked($bundle_cfg['tier_trio']['enabled'], true); ?>>
                                <span>Active</span>
                            </label>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Trinity Package Title:</label>
                            <input type="text" name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][title]" value="<?php echo esc_attr($bundle_cfg['tier_trio']['title']); ?>" style="width:100%; border-radius:6px;">
                        </div>

                        <!-- SISTER REPORT 2 & 3 DROPDOWNS -->
                        <div style="background:#fff9f0; padding:10px; border-radius:8px; border:1px solid #fde68a; margin-bottom:12px;">
                            <label style="display:block; font-size:12px; font-weight:700; color:#b45309; margin-bottom:4px;">
                                <i class="dashicons dashicons-plus" style="font-size:14px; vertical-align:middle;"></i> Paired Sister Report #2:
                            </label>
                            <?php $selected_trio_1 = $bundle_cfg['tier_trio']['paired_reports'][0] ?? ''; ?>
                            <select name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][paired_report_1]" style="width:100%; border-radius:6px; margin-bottom:8px;">
                                <?php foreach ($all_reports as $s_slug => $s_meta): 
                                    if ($s_slug === $active_report) continue;
                                ?>
                                    <option value="<?php echo esc_attr($s_slug); ?>" <?php selected($selected_trio_1, $s_slug); ?>>
                                        <?php echo esc_html($s_meta['name']); ?> (<?php echo esc_html($s_meta['pages']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label style="display:block; font-size:12px; font-weight:700; color:#b45309; margin-bottom:4px;">
                                <i class="dashicons dashicons-plus" style="font-size:14px; vertical-align:middle;"></i> Paired Sister Report #3:
                            </label>
                            <?php $selected_trio_2 = $bundle_cfg['tier_trio']['paired_reports'][1] ?? ''; ?>
                            <select name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][paired_report_2]" style="width:100%; border-radius:6px;">
                                <?php foreach ($all_reports as $s_slug => $s_meta): 
                                    if ($s_slug === $active_report) continue;
                                ?>
                                    <option value="<?php echo esc_attr($s_slug); ?>" <?php selected($selected_trio_2, $s_slug); ?>>
                                        <?php echo esc_html($s_meta['name']); ?> (<?php echo esc_html($s_meta['pages']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Sale Price (₹):</label>
                                <input type="number" step="1" name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][price_sale]" value="<?php echo esc_attr($bundle_cfg['tier_trio']['price_sale']); ?>" style="width:100%; font-weight:800; color:#7A1F3D; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">MRP Price (₹):</label>
                                <input type="number" step="1" name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][price_regular]" value="<?php echo esc_attr($bundle_cfg['tier_trio']['price_regular']); ?>" style="width:100%; border-radius:6px;">
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Badge Text:</label>
                                <input type="text" name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][badge]" value="<?php echo esc_attr($bundle_cfg['tier_trio']['badge'] ?? 'BEST BUY - SAVE 71%'); ?>" style="width:100%; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Bonus Chat Credits:</label>
                                <input type="number" name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][chat_credits]" value="<?php echo esc_attr($bundle_cfg['tier_trio']['chat_credits'] ?? 30); ?>" style="width:100%; font-weight:800; color:#0284c7; border-radius:6px;">
                            </div>
                        </div>

                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">Highlight Bullets (short):</label>
                            <textarea name="bundle[<?php echo esc_attr($active_report); ?>][tier_trio][highlights]" rows="2" style="width:100%; font-size:12px; border-radius:6px;"><?php echo esc_textarea($bundle_cfg['tier_trio']['highlights'] ?? ''); ?></textarea>
                        </div>
                    </div>

                </div>

                <!-- GLOBAL SITEWIDE SETTINGS -->
                <div style="background:#f1f5f9; border-radius:10px; padding:18px 22px; margin-bottom:24px;">
                    <h4 style="margin:0 0 12px 0; font-size:14px; font-weight:700; color:#334155; display:flex; align-items:center; gap:8px;">
                        <i class="dashicons dashicons-admin-generic"></i> Sitewide Global Preferences
                    </h4>
                    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:16px;">
                        <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="global_enabled" value="1" <?php checked($global_opts['enabled'], true); ?>>
                            <span>Enable Bundles Globally</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="global_show_chat_badge" value="1" <?php checked($global_opts['show_chat_credit_badge'], true); ?>>
                            <span>Show "Niyati Chat Questions" Badge</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="global_show_fbt_box" value="1" <?php checked($global_opts['show_fbt_bundle_box'], true); ?>>
                            <span>Show Bottom "Frequently Bought" Bar</span>
                        </label>
                    </div>
                </div>

                <!-- SAVE / RESET BUTTONS -->
                <div style="display:flex; justify-content:space-between; align-items:center; padding-top:10px;">
                    <button type="submit" class="button button-primary button-hero" style="background:#7A1F3D; border-color:#5c152c; box-shadow:0 4px 12px rgba(122, 31, 61, 0.3); font-weight:700; padding:4px 32px;">
                        <i class="dashicons dashicons-saved" style="vertical-align:middle; margin-right:4px;"></i> Save All Bundle Settings For This Report
                    </button>
            </form>

                    <!-- RESET TO DEFAULTS BUTTON -->
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Reset all bundle pairings, prices, and credits across all reports to factory defaults?');">
                        <?php wp_nonce_field('mr_reset_bundles_action', 'mr_reset_nonce'); ?>
                        <input type="hidden" name="action" value="mr_reset_bundles">
                        <button type="submit" class="button" style="color:#ef4444; border-color:#fca5a5;">
                            <i class="dashicons dashicons-image-rotate"></i> Reset to Defaults
                        </button>
                    </form>
                </div>
        </div>
        <?php
    }
}
