<?php
/**
 * Admin Panel & Settings Management for Niyati AI Astrology Chat
 */

if (!defined('ABSPATH')) {
    exit;
}

class Niyati_Admin {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_admin_menu']);
        add_action('admin_post_niyati_save_settings', [__CLASS__, 'handle_save_settings']);
        add_action('admin_post_niyati_save_pack', [__CLASS__, 'handle_save_pack']);
        add_action('admin_post_niyati_delete_pack', [__CLASS__, 'handle_delete_pack']);
        add_action('admin_post_niyati_toggle_pack', [__CLASS__, 'handle_toggle_pack']);
        add_action('admin_post_niyati_adjust_credits', [__CLASS__, 'handle_adjust_credits']);
        add_action('admin_post_niyati_save_nudge_settings', [__CLASS__, 'handle_save_nudge_settings']);
        add_action('admin_post_niyati_save_nudge_campaign', [__CLASS__, 'handle_save_nudge_campaign']);
        add_action('admin_post_niyati_toggle_nudge_campaign', [__CLASS__, 'handle_toggle_nudge_campaign']);
        add_action('admin_post_niyati_delete_nudge_campaign', [__CLASS__, 'handle_delete_nudge_campaign']);
        add_action('wp_ajax_niyati_test_gemini', [__CLASS__, 'ajax_test_gemini']);
        add_action('wp_ajax_niyati_test_vedic', [__CLASS__, 'ajax_test_vedic']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    public static function register_admin_menu() {
        add_menu_page(
            __('Niyati AI Astrology', 'merarashifal'),
            __('Niyati AI Chat', 'merarashifal'),
            'manage_options',
            'niyati-chat-settings',
            [__CLASS__, 'render_settings_page'],
            'dashicons-format-chat',
            31
        );
    }

    public static function enqueue_admin_assets($hook) {
        if (strpos($hook, 'niyati-chat-settings') === false) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_style(
            'niyati-admin-css',
            NIYATI_PLUGIN_URL . 'assets/css/niyati-admin.css',
            [],
            NIYATI_VERSION
        );

        wp_enqueue_script(
            'niyati-admin-js',
            NIYATI_PLUGIN_URL . 'assets/js/niyati-admin.js',
            ['jquery', 'wp-color-picker'],
            NIYATI_VERSION,
            true
        );

        wp_localize_script('niyati-admin-js', 'niyatiAdmin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('niyati_admin_nonce'),
        ]);
    }

    public static function handle_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('niyati_save_settings_action', 'niyati_nonce');

        $active_tab = sanitize_key($_POST['active_tab'] ?? 'api');
        $current_settings = get_option('niyati_chat_settings', []);

        // Sanitize incoming fields
        if (isset($_POST['gemini_api_key'])) {
            $current_settings['gemini_api_key'] = sanitize_text_field(trim($_POST['gemini_api_key']));
        }
        if (isset($_POST['gemini_model'])) {
            $current_settings['gemini_model'] = sanitize_text_field(trim($_POST['gemini_model']));
        }
        if (isset($_POST['vedic_api_key'])) {
            $current_settings['vedic_api_key'] = sanitize_text_field(trim($_POST['vedic_api_key']));
        }
        if (isset($_POST['bot_name'])) {
            $current_settings['bot_name'] = sanitize_text_field(trim($_POST['bot_name']));
        }
        if (isset($_POST['bot_title'])) {
            $current_settings['bot_title'] = sanitize_text_field(trim($_POST['bot_title']));
        }
        if (isset($_POST['theme_color'])) {
            $current_settings['theme_color'] = sanitize_hex_color($_POST['theme_color']) ?: '#D97706';
        }

        $current_settings['floating_enabled'] = !empty($_POST['floating_enabled']) ? '1' : '0';

        // Limits
        if (isset($_POST['free_limit_new_user'])) {
            $current_settings['free_limit_new_user'] = max(0, intval($_POST['free_limit_new_user']));
        }
        if (isset($_POST['free_limit_old_user'])) {
            $current_settings['free_limit_old_user'] = max(0, intval($_POST['free_limit_old_user']));
        }
        if (isset($_POST['limit_reset_period'])) {
            $current_settings['limit_reset_period'] = sanitize_key($_POST['limit_reset_period']);
        }
        if (isset($_POST['limit_exceeded_msg_hi'])) {
            $current_settings['limit_exceeded_msg_hi'] = sanitize_textarea_field($_POST['limit_exceeded_msg_hi']);
        }
        if (isset($_POST['limit_exceeded_msg_en'])) {
            $current_settings['limit_exceeded_msg_en'] = sanitize_textarea_field($_POST['limit_exceeded_msg_en']);
        }

        // Welcome messages
        if (isset($_POST['welcome_msg_hi'])) {
            $current_settings['welcome_msg_hi'] = sanitize_textarea_field($_POST['welcome_msg_hi']);
        }
        if (isset($_POST['welcome_msg_en'])) {
            $current_settings['welcome_msg_en'] = sanitize_textarea_field($_POST['welcome_msg_en']);
        }
        if (isset($_POST['welcome_msg_hinglish'])) {
            $current_settings['welcome_msg_hinglish'] = sanitize_textarea_field($_POST['welcome_msg_hinglish']);
        }

        // Predefined questions
        if (isset($_POST['questions_hi'])) {
            $current_settings['questions_hi'] = sanitize_textarea_field($_POST['questions_hi']);
        }
        if (isset($_POST['questions_en'])) {
            $current_settings['questions_en'] = sanitize_textarea_field($_POST['questions_en']);
        }
        if (isset($_POST['questions_hinglish'])) {
            $current_settings['questions_hinglish'] = sanitize_textarea_field($_POST['questions_hinglish']);
        }

        // Payment Gateway Settings
        if (isset($_POST['razorpay_key_id'])) {
            $current_settings['razorpay_key_id'] = sanitize_text_field(trim($_POST['razorpay_key_id']));
        }
        if (isset($_POST['razorpay_key_secret'])) {
            $current_settings['razorpay_key_secret'] = sanitize_text_field(trim($_POST['razorpay_key_secret']));
        }
        $current_settings['razorpay_test_mode'] = !empty($_POST['razorpay_test_mode']) ? '1' : '0';

        // Chat Archiving & Retention Settings
        if (isset($_POST['archive_retention_days'])) {
            $current_settings['archive_retention_days'] = max(7, intval($_POST['archive_retention_days']));
        }

        update_option('niyati_chat_settings', $current_settings);

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => $active_tab,
            'updated' => 'true'
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Add or Update a Credit Pack
     */
    public static function handle_save_pack() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('niyati_save_pack_action', 'niyati_pack_nonce');

        $key = sanitize_key($_POST['pack_key'] ?? '');
        if (empty($key)) {
            $key = 'pack_' . time();
        }

        $title         = sanitize_text_field($_POST['pack_title'] ?? 'Chat Pack');
        $base_price    = max(0, floatval($_POST['base_price'] ?? 0));
        $offer_price   = max(0, floatval($_POST['offer_price'] ?? 0));
        $base_credits  = max(1, intval($_POST['base_credits'] ?? 10));
        $bonus_credits = max(0, intval($_POST['bonus_credits'] ?? 0));
        $validity_days = max(0, intval($_POST['validity_days'] ?? 0));
        $badge         = sanitize_text_field($_POST['badge'] ?? '');
        $description   = sanitize_text_field($_POST['description'] ?? '');
        $featured      = !empty($_POST['featured']) ? 1 : 0;
        $active        = !empty($_POST['active']) ? 1 : 0;

        $packs = get_option('niyati_credit_packs', Niyati_Wallet::get_default_packs());
        $packs[$key] = [
            'key'           => $key,
            'title'         => $title,
            'base_price'    => $base_price,
            'offer_price'   => $offer_price,
            'base_credits'  => $base_credits,
            'bonus_credits' => $bonus_credits,
            'validity_days' => $validity_days,
            'badge'         => $badge,
            'description'   => $description,
            'featured'      => $featured,
            'active'        => $active
        ];

        update_option('niyati_credit_packs', $packs);

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => 'pricing',
            'message' => 'pack_saved'
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Delete a Credit Pack
     */
    public static function handle_delete_pack() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('niyati_delete_pack_action', 'niyati_nonce');

        $key = sanitize_key($_POST['pack_key'] ?? '');
        $packs = get_option('niyati_credit_packs', Niyati_Wallet::get_default_packs());

        if (isset($packs[$key])) {
            unset($packs[$key]);
            update_option('niyati_credit_packs', $packs);
        }

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => 'pricing',
            'message' => 'pack_deleted'
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Toggle (Suspend / Activate) a Credit Pack
     */
    public static function handle_toggle_pack() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('niyati_toggle_pack_action', 'niyati_nonce');

        $key = sanitize_key($_POST['pack_key'] ?? '');
        $status = Niyati_Wallet::toggle_pack_status($key);

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => 'pricing',
            'message' => ($status ? 'pack_activated' : 'pack_suspended')
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Manually Adjust User Credits
     */
    public static function handle_adjust_credits() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('niyati_adjust_credits_action', 'niyati_nonce');

        $identifier = sanitize_text_field($_POST['user_identifier'] ?? '');
        $credits = intval($_POST['adjust_credits'] ?? 0);
        $reason = sanitize_text_field($_POST['adjust_reason'] ?? 'Admin manual adjustment');

        if (!empty($identifier) && $credits != 0) {
            $user_id = 0;
            $visitor_id = $identifier;

            if (is_numeric($identifier)) {
                $user_id = intval($identifier);
                $visitor_id = 'user_' . $user_id;
            } elseif (is_email($identifier)) {
                $user = get_user_by('email', $identifier);
                if ($user) {
                    $user_id = $user->ID;
                    $visitor_id = 'user_' . $user_id;
                }
            }

            if ($credits > 0) {
                Niyati_Wallet::add_credits($visitor_id, $user_id, $credits, 0.00, 'admin_' . time(), 'admin_adjust', 'manual_admin', $reason, 0, ['manual' => true]);
            } else {
                for ($i = 0; $i < abs($credits); $i++) {
                    Niyati_Wallet::consume_credit($visitor_id, $user_id);
                }
            }
        }

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => 'pricing',
            'message' => 'credits_adjusted'
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Save General Nudge Settings
     */
    public static function handle_save_nudge_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('niyati_save_nudge_settings_action', 'niyati_nonce');

        if (class_exists('Niyati_Nudge')) {
            Niyati_Nudge::save_settings($_POST);
        }

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => 'nudges',
            'message' => 'nudge_settings_saved'
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Save or Add a Scheduled Campaign
     */
    public static function handle_save_nudge_campaign() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('niyati_save_nudge_campaign_action', 'niyati_nonce');

        if (class_exists('Niyati_Nudge')) {
            Niyati_Nudge::save_campaign($_POST);
        }

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => 'nudges',
            'message' => 'campaign_saved'
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Toggle a Scheduled Campaign Status (Active / Paused)
     */
    public static function handle_toggle_nudge_campaign() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('niyati_toggle_nudge_campaign_action', 'niyati_nonce');

        $id = sanitize_key($_POST['campaign_id'] ?? '');
        if (class_exists('Niyati_Nudge') && !empty($id)) {
            Niyati_Nudge::toggle_campaign($id);
        }

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => 'nudges',
            'message' => 'campaign_toggled'
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Delete a Scheduled Campaign
     */
    public static function handle_delete_nudge_campaign() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('niyati_delete_nudge_campaign_action', 'niyati_nonce');

        $id = sanitize_key($_POST['campaign_id'] ?? '');
        if (class_exists('Niyati_Nudge') && !empty($id)) {
            Niyati_Nudge::delete_campaign($id);
        }

        wp_redirect(add_query_arg([
            'page'    => 'niyati-chat-settings',
            'tab'     => 'nudges',
            'message' => 'campaign_deleted'
        ], admin_url('admin.php')));
        exit;
    }

    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $settings = Niyati_REST_API::get_settings();
        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'api';

        ?>
        <div class="wrap niyati-admin-wrap">
            <div class="niyati-admin-header">
                <div class="niyati-header-logo">
                    <span class="dashicons dashicons-format-chat"></span>
                </div>
                <div>
                    <h1><?php echo esc_html__('Niyati - AI Vedic Astrology Chatbot Settings', 'merarashifal'); ?></h1>
                    <p class="description"><?php echo esc_html__('Configure Gemini AI, VedicAstroAPI, chat credits pricing, and free question quotas.', 'merarashifal'); ?></p>
                </div>
            </div>

            <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('Niyati configuration saved successfully!', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'pack_saved'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('Credit pack saved successfully! Existing user balances remain completely unaffected.', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'pack_suspended'): ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong><?php echo esc_html__('Credit pack suspended! It is now hidden from the chatbot recharge window.', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'pack_activated'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('Credit pack activated! It is now live in the chatbot recharge window.', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'pack_deleted'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('Credit pack deleted successfully.', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'credits_adjusted'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('User wallet credits updated successfully!', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'nudge_settings_saved'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('Chat nudge settings saved successfully!', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'campaign_saved'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('Scheduled event campaign saved successfully!', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'campaign_toggled'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('Campaign status updated successfully!', 'merarashifal'); ?></strong></p>
                </div>
            <?php elseif (isset($_GET['message']) && $_GET['message'] === 'campaign_deleted'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php echo esc_html__('Scheduled campaign deleted.', 'merarashifal'); ?></strong></p>
                </div>
            <?php endif; ?>

            <div class="nav-tab-wrapper niyati-tabs">
                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'api'], admin_url('admin.php'))); ?>" 
                   class="nav-tab <?php echo $active_tab === 'api' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-network"></span> <?php echo esc_html__('API Keys & Engines', 'merarashifal'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'pricing'], admin_url('admin.php'))); ?>" 
                   class="nav-tab <?php echo $active_tab === 'pricing' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-cart"></span> <?php echo esc_html__('Credit Packs & Pricing', 'merarashifal'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'questions'], admin_url('admin.php'))); ?>" 
                   class="nav-tab <?php echo $active_tab === 'questions' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-editor-help"></span> <?php echo esc_html__('Pre-defined Questions', 'merarashifal'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'limits'], admin_url('admin.php'))); ?>" 
                   class="nav-tab <?php echo $active_tab === 'limits' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-clock"></span> <?php echo esc_html__('Free Question Limits', 'merarashifal'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'persona'], admin_url('admin.php'))); ?>" 
                   class="nav-tab <?php echo $active_tab === 'persona' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-customizer"></span> <?php echo esc_html__('Bot Persona & Appearance', 'merarashifal'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'nudges'], admin_url('admin.php'))); ?>" 
                   class="nav-tab <?php echo $active_tab === 'nudges' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-format-chat"></span> <?php echo esc_html__('Nudges & Festive Scheduler', 'merarashifal'); ?>
                </a>
            </div>

            <?php if ($active_tab === 'pricing'): ?>
                <?php 
                $all_packs = Niyati_Wallet::get_packs(false); 
                $edit_key  = isset($_GET['edit_pack']) ? sanitize_key($_GET['edit_pack']) : '';
                $edit_pack = $edit_key ? Niyati_Wallet::get_pack($edit_key) : null;
                ?>

                <!-- TAB: CREDIT PACKS & PRICING -->
                <div class="niyati-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:12px; margin-bottom:20px;">
                        <div>
                            <h2 style="margin:0;"><?php echo esc_html__('Credit Packs & Special Offers Management', 'merarashifal'); ?></h2>
                            <p class="description" style="margin:4px 0 0 0;">
                                <?php echo esc_html__('Rule: 1 Question = 1 Credit. Users can buy multiple packs anytime — credits and validity days will stack automatically.', 'merarashifal'); ?>
                            </p>
                        </div>
                        <div>
                            <a href="#pack-form" class="button button-primary"><span class="dashicons dashicons-plus" style="margin-top:4px;"></span> <?php echo esc_html__('Create New Pack', 'merarashifal'); ?></a>
                        </div>
                    </div>

                    <table class="widefat fixed striped" style="margin-bottom:25px;">
                        <thead>
                            <tr>
                                <th style="width:24%;"><?php echo esc_html__('Pack Title & Tag', 'merarashifal'); ?></th>
                                <th style="width:13%;"><?php echo esc_html__('Selling vs MRP', 'merarashifal'); ?></th>
                                <th style="width:16%;"><?php echo esc_html__('Credits (Base + Bonus)', 'merarashifal'); ?></th>
                                <th style="width:12%;"><?php echo esc_html__('Discount', 'merarashifal'); ?></th>
                                <th style="width:10%;"><?php echo esc_html__('Validity', 'merarashifal'); ?></th>
                                <th style="width:10%;"><?php echo esc_html__('Status', 'merarashifal'); ?></th>
                                <th style="width:15%;"><?php echo esc_html__('Actions', 'merarashifal'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_packs as $p): ?>
                                <tr style="<?php echo empty($p['active']) ? 'opacity:0.65; background:#fafafa;' : ''; ?>">
                                    <td>
                                        <strong><?php echo esc_html($p['title']); ?></strong>
                                        <?php if (!empty($p['badge'])): ?>
                                            <span style="display:inline-block; font-size:11px; padding:2px 8px; border-radius:10px; background:#fef3c7; color:#b45309; font-weight:700; margin-left:6px;">
                                                <?php echo esc_html($p['badge']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <div style="font-size:12px; color:#64748b; margin-top:3px;"><?php echo esc_html($p['description']); ?></div>
                                    </td>
                                    <td>
                                        <span style="font-size:16px; font-weight:700; color:#16a34a;">₹<?php echo esc_html($p['offer_price']); ?></span>
                                        <?php if ($p['base_price'] > $p['offer_price']): ?>
                                            <del style="color:#94a3b8; margin-left:5px;">₹<?php echo esc_html($p['base_price']); ?></del>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong style="font-size:14px;"><?php echo esc_html($p['total_credits']); ?> Credits</strong>
                                        <div style="font-size:11px; color:#64748b;">
                                            <?php echo esc_html($p['base_credits']); ?> Base
                                            <?php if ($p['bonus_credits'] > 0): ?>
                                                + <span style="color:#d97706; font-weight:700;"><?php echo esc_html($p['bonus_credits']); ?> Free Bonus</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($p['discount_pct'] > 0): ?>
                                            <span style="background:#dcfce7; color:#15803d; font-weight:700; padding:2px 8px; border-radius:4px; font-size:12px;">
                                                <?php echo esc_html($p['discount_pct']); ?>% OFF
                                            </span>
                                            <span style="font-size:11px; color:#64748b; display:block; margin-top:2px;">
                                                Save ₹<?php echo esc_html($p['savings_amount']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:#94a3b8;">Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo !empty($p['validity_days']) ? esc_html($p['validity_days']) . ' Days' : 'Lifetime'; ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['active'])): ?>
                                            <span style="color:#16a34a; font-weight:700;">● Active</span>
                                        <?php else: ?>
                                            <span style="color:#b45309; font-weight:700;">⏸ Suspended</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Edit Button -->
                                        <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'pricing', 'edit_pack' => $p['key']], admin_url('admin.php'))); ?>#pack-form" 
                                           class="button button-small" style="margin-right:3px;" title="Edit this pack">
                                            <span class="dashicons dashicons-edit" style="margin-top:2px;"></span> Edit
                                        </a>

                                        <!-- Suspend / Activate Toggle Button -->
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline; margin-right:3px;">
                                            <?php wp_nonce_field('niyati_toggle_pack_action', 'niyati_nonce'); ?>
                                            <input type="hidden" name="action" value="niyati_toggle_pack">
                                            <input type="hidden" name="pack_key" value="<?php echo esc_attr($p['key']); ?>">
                                            <?php if (!empty($p['active'])): ?>
                                                <button type="submit" class="button button-small" style="color:#b45309;" title="Suspend this pack">
                                                    <span class="dashicons dashicons-hidden" style="margin-top:2px;"></span> Suspend
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="button button-small" style="color:#16a34a;" title="Activate this pack">
                                                    <span class="dashicons dashicons-visibility" style="margin-top:2px;"></span> Activate
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- Delete Button -->
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Are you sure you want to delete this pack? Existing purchasers will remain unaffected.');" style="display:inline;">
                                            <?php wp_nonce_field('niyati_delete_pack_action', 'niyati_nonce'); ?>
                                            <input type="hidden" name="action" value="niyati_delete_pack">
                                            <input type="hidden" name="pack_key" value="<?php echo esc_attr($p['key']); ?>">
                                            <button type="submit" class="button button-small button-link-delete" style="color:#ef4444;" title="Delete pack">
                                                <span class="dashicons dashicons-trash" style="margin-top:2px;"></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- ADD / EDIT PACK FORM -->
                    <div id="pack-form" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:22px; margin-top:25px;">
                        <?php if ($edit_pack): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <h3 style="margin-top:0; color:#1e293b;">
                                    <span class="dashicons dashicons-edit"></span> <?php echo esc_html(sprintf(__('Edit Credit Pack: %s', 'merarashifal'), $edit_pack['title'])); ?>
                                </h3>
                                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'pricing'], admin_url('admin.php'))); ?>" class="button button-secondary">
                                    ✕ <?php echo esc_html__('Cancel Edit', 'merarashifal'); ?>
                                </a>
                            </div>
                            <div style="background:#eff6ff; border-left:4px solid #3b82f6; padding:10px 14px; margin-bottom:18px; border-radius:4px; font-size:13px; color:#1e40af;">
                                <strong><?php echo esc_html__('Immutability Guarantee:', 'merarashifal'); ?></strong>
                                <?php echo esc_html__('Editing this pack will only apply to new subscribers who purchase after saving. Any user who already bought this pack will continue to enjoy their granted credits and validity as per their original purchase configuration.', 'merarashifal'); ?>
                            </div>
                        <?php else: ?>
                            <h3 style="margin-top:0; color:#1e293b;">
                                <span class="dashicons dashicons-plus-alt"></span> <?php echo esc_html__('Create New Credit Pack', 'merarashifal'); ?>
                            </h3>
                        <?php endif; ?>

                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('niyati_save_pack_action', 'niyati_pack_nonce'); ?>
                            <input type="hidden" name="action" value="niyati_save_pack">

                            <div style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:15px;">
                                <div style="flex:1; min-width:200px;">
                                    <label><strong><?php echo esc_html__('Pack Unique Key (Slug)', 'merarashifal'); ?>:</strong></label>
                                    <input type="text" name="pack_key" value="<?php echo esc_attr($edit_pack['key'] ?? ''); ?>" 
                                           <?php echo $edit_pack ? 'readonly style="background:#e2e8f0; width:100%;"' : 'style="width:100%;"'; ?> 
                                           placeholder="e.g. pack_festive_100" class="regular-text">
                                    <span class="description"><?php echo $edit_pack ? esc_html__('Slug cannot be changed while editing.', 'merarashifal') : esc_html__('Leave blank to auto-generate.', 'merarashifal'); ?></span>
                                </div>
                                <div style="flex:2; min-width:280px;">
                                    <label><strong><?php echo esc_html__('Pack Title', 'merarashifal'); ?>:</strong></label>
                                    <input type="text" name="pack_title" value="<?php echo esc_attr($edit_pack['title'] ?? ''); ?>" 
                                           placeholder="e.g. 120 Questions Special Offer" class="regular-text" style="width:100%;" required>
                                </div>
                            </div>

                            <div style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:15px;">
                                <div style="flex:1; min-width:140px;">
                                    <label><strong><?php echo esc_html__('Selling Price (₹)', 'merarashifal'); ?>:</strong></label>
                                    <input type="number" step="0.01" name="offer_price" value="<?php echo esc_attr($edit_pack['offer_price'] ?? ''); ?>" 
                                           placeholder="100" class="regular-text" style="width:100%;" required>
                                </div>
                                <div style="flex:1; min-width:140px;">
                                    <label><strong><?php echo esc_html__('MRP / Base Price (₹)', 'merarashifal'); ?>:</strong></label>
                                    <input type="number" step="0.01" name="base_price" value="<?php echo esc_attr($edit_pack['base_price'] ?? ''); ?>" 
                                           placeholder="150" class="regular-text" style="width:100%;">
                                    <span class="description"><?php echo esc_html__('Strike-through price to show discount', 'merarashifal'); ?></span>
                                </div>
                                <div style="flex:1; min-width:130px;">
                                    <label><strong><?php echo esc_html__('Base Credits', 'merarashifal'); ?>:</strong></label>
                                    <input type="number" name="base_credits" value="<?php echo esc_attr($edit_pack['base_credits'] ?? ''); ?>" 
                                           placeholder="100" class="regular-text" style="width:100%;" required>
                                </div>
                                <div style="flex:1; min-width:130px;">
                                    <label><strong><?php echo esc_html__('Bonus Free Credits', 'merarashifal'); ?>:</strong></label>
                                    <input type="number" name="bonus_credits" value="<?php echo esc_attr($edit_pack['bonus_credits'] ?? '0'); ?>" 
                                           placeholder="20" class="regular-text" style="width:100%;">
                                    <span class="description"><?php echo esc_html__('Extra questions offer', 'merarashifal'); ?></span>
                                </div>
                                <div style="flex:1; min-width:140px;">
                                    <label><strong><?php echo esc_html__('Validity (Days)', 'merarashifal'); ?>:</strong></label>
                                    <input type="number" name="validity_days" value="<?php echo esc_attr($edit_pack['validity_days'] ?? '0'); ?>" 
                                           placeholder="0" class="regular-text" style="width:100%;">
                                    <span class="description"><?php echo esc_html__('0 = Lifetime (No Expiry)', 'merarashifal'); ?></span>
                                </div>
                            </div>

                            <div style="display:flex; gap:15px; flex-wrap:wrap; margin-bottom:15px;">
                                <div style="flex:1; min-width:200px;">
                                    <label><strong><?php echo esc_html__('Badge / Ribbon Tag', 'merarashifal'); ?>:</strong></label>
                                    <input type="text" name="badge" value="<?php echo esc_attr($edit_pack['badge'] ?? ''); ?>" 
                                           placeholder="e.g. Best Value (33% OFF) or Most Popular" class="regular-text" style="width:100%;">
                                </div>
                                <div style="flex:2; min-width:300px;">
                                    <label><strong><?php echo esc_html__('Short Description / Benefits', 'merarashifal'); ?>:</strong></label>
                                    <input type="text" name="description" value="<?php echo esc_attr($edit_pack['description'] ?? ''); ?>" 
                                           placeholder="e.g. 100 + 20 Free Bonus • Instant Activation • Valid Lifetime" class="regular-text" style="width:100%;">
                                </div>
                            </div>

                            <div style="margin-bottom:18px;">
                                <label style="margin-right:20px;">
                                    <input type="checkbox" name="active" value="1" <?php checked($edit_pack['active'] ?? 1, 1); ?>> 
                                    <strong><?php echo esc_html__('Active (Visible for users to buy)', 'merarashifal'); ?></strong>
                                </label>
                                <label>
                                    <input type="checkbox" name="featured" value="1" <?php checked($edit_pack['featured'] ?? 0, 1); ?>> 
                                    <strong><?php echo esc_html__('Featured / Highlighted Offer Card', 'merarashifal'); ?></strong>
                                </label>
                            </div>

                            <button type="submit" class="button button-primary button-large">
                                <?php echo $edit_pack ? esc_html__('Update Pack', 'merarashifal') : esc_html__('Create Credit Pack', 'merarashifal'); ?>
                            </button>
                            <?php if ($edit_pack): ?>
                                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'pricing'], admin_url('admin.php'))); ?>" class="button button-secondary" style="margin-left:10px;">
                                    <?php echo esc_html__('Cancel', 'merarashifal'); ?>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <hr>

                    <!-- PAYMENT GATEWAY CONFIGURATION -->
                    <h2><?php echo esc_html__('Payment Gateway Settings (Razorpay / UPI)', 'merarashifal'); ?></h2>
                    <p class="description"><?php echo esc_html__('Allows users to recharge credits directly via UPI (Google Pay, PhonePe, Paytm), Netbanking, and Cards.', 'merarashifal'); ?></p>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('niyati_save_settings_action', 'niyati_nonce'); ?>
                        <input type="hidden" name="action" value="niyati_save_settings">
                        <input type="hidden" name="active_tab" value="pricing">

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="razorpay_key_id"><?php echo esc_html__('Razorpay Key ID', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="text" id="razorpay_key_id" name="razorpay_key_id" value="<?php echo esc_attr($settings['razorpay_key_id'] ?? ''); ?>" class="regular-text" placeholder="rzp_live_...">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="razorpay_key_secret"><?php echo esc_html__('Razorpay Key Secret', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="password" id="razorpay_key_secret" name="razorpay_key_secret" value="<?php echo esc_attr($settings['razorpay_key_secret'] ?? ''); ?>" class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Sandbox / Test Simulation Mode', 'merarashifal'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="razorpay_test_mode" value="1" <?php checked($settings['razorpay_test_mode'] ?? '1', '1'); ?>>
                                        <strong><?php echo esc_html__('Enable Simulation Mode (Allows instant test recharges without real payment)', 'merarashifal'); ?></strong>
                                    </label>
                                    <p class="description"><?php echo esc_html__('Uncheck this when ready to accept real payments with your live Razorpay keys.', 'merarashifal'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php echo esc_html__('Save Gateway Settings', 'merarashifal'); ?></button>
                        </p>
                    </form>

                    <hr>

                    <!-- MANUAL CREDIT ADJUSTMENT -->
                    <h2><?php echo esc_html__('Manual Credit Adjustment (Admin Tool)', 'merarashifal'); ?></h2>
                    <p class="description"><?php echo esc_html__('Manually credit or deduct tokens from any user wallet for support or bonus rewards.', 'merarashifal'); ?></p>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="background:#f8fafc; padding:15px; border:1px solid #e2e8f0; border-radius:6px; max-width:650px;">
                        <?php wp_nonce_field('niyati_adjust_credits_action', 'niyati_nonce'); ?>
                        <input type="hidden" name="action" value="niyati_adjust_credits">

                        <div style="display:flex; gap:10px; margin-bottom:10px;">
                            <div style="flex:2;">
                                <label><strong><?php echo esc_html__('User ID / Visitor ID / Email', 'merarashifal'); ?>:</strong></label>
                                <input type="text" name="user_identifier" placeholder="e.g. 1 or user@example.com or guest_uuid" class="regular-text" style="width:100%;" required>
                            </div>
                            <div style="flex:1;">
                                <label><strong><?php echo esc_html__('Credits (+ / -)', 'merarashifal'); ?>:</strong></label>
                                <input type="number" name="adjust_credits" placeholder="+50 or -10" class="regular-text" style="width:100%;" required>
                            </div>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label><strong><?php echo esc_html__('Reason / Note', 'merarashifal'); ?>:</strong></label>
                            <input type="text" name="adjust_reason" placeholder="e.g. Complimentary credits / Testing" class="regular-text" style="width:100%;">
                        </div>

                        <button type="submit" class="button button-secondary"><?php echo esc_html__('Update Credits Balance', 'merarashifal'); ?></button>
                    </form>
                </div>

            <?php elseif ($active_tab === 'api'): ?>
                <!-- TAB 1: API KEYS -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="niyati-form">
                    <?php wp_nonce_field('niyati_save_settings_action', 'niyati_nonce'); ?>
                    <input type="hidden" name="action" value="niyati_save_settings">
                    <input type="hidden" name="active_tab" value="api">

                    <div class="niyati-card">
                        <h2><?php echo esc_html__('1. Google Gemini AI Engine', 'merarashifal'); ?></h2>
                        <p class="description"><?php echo esc_html__('Niyati uses Google Gemini to read and interpret the Vedic birth chart with authentic empathy and wisdom.', 'merarashifal'); ?></p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="gemini_api_key"><?php echo esc_html__('Gemini API Key', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="password" id="gemini_api_key" name="gemini_api_key" value="<?php echo esc_attr($settings['gemini_api_key'] ?? ''); ?>" class="regular-text" placeholder="AIzaSy...">
                                    <button type="button" class="button" id="btn-toggle-gemini-key"><span class="dashicons dashicons-visibility"></span></button>
                                    <button type="button" class="button button-secondary" id="btn-test-gemini"><?php echo esc_html__('Test Gemini API', 'merarashifal'); ?></button>
                                    <span id="gemini-test-result" class="niyati-test-badge"></span>
                                    <p class="description"><?php echo esc_html__('Get your key from Google AI Studio (https://aistudio.google.com).', 'merarashifal'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gemini_model"><?php echo esc_html__('Gemini Model', 'merarashifal'); ?></label></th>
                                <td>
                                    <?php $selected_model = $settings['gemini_model'] ?? 'gemini-3.1-flash-lite'; ?>
                                    <select id="gemini_model" name="gemini_model">
                                        <option value="gemini-3.1-flash-lite" <?php selected($selected_model, 'gemini-3.1-flash-lite'); ?>>Gemini 3.1 Flash-Lite (Recommended - Blazing Fast & Economical)</option>
                                        <option value="gemini-3.8-flash" <?php selected($selected_model, 'gemini-3.8-flash'); ?>>Gemini 3.8 Flash</option>
                                        <option value="gemini-flash-latest" <?php selected($selected_model, 'gemini-flash-latest'); ?>>Gemini Flash Latest</option>
                                        <option value="gemini-flash-lite-latest" <?php selected($selected_model, 'gemini-flash-lite-latest'); ?>>Gemini Flash-Lite Latest</option>
                                        <option value="gemini-pro-latest" <?php selected($selected_model, 'gemini-pro-latest'); ?>>Gemini Pro Latest</option>
                                    </select>
                                </td>
                            </tr>
                        </table>

                        <hr>

                        <h2><?php echo esc_html__('2. VedicAstroAPI Engine', 'merarashifal'); ?></h2>
                        <p class="description"><?php echo esc_html__('Computes astronomical birth charts, planetary degrees, Vimshottari Mahadasha/Antardasha, and Doshas.', 'merarashifal'); ?></p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="vedic_api_key"><?php echo esc_html__('VedicAstroAPI Key', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="password" id="vedic_api_key" name="vedic_api_key" value="<?php echo esc_attr($settings['vedic_api_key'] ?? ''); ?>" class="regular-text" placeholder="e.g. 8d9d828d-...">
                                    <button type="button" class="button" id="btn-toggle-vedic-key"><span class="dashicons dashicons-visibility"></span></button>
                                    <button type="button" class="button button-secondary" id="btn-test-vedic"><?php echo esc_html__('Test VedicAstroAPI', 'merarashifal'); ?></button>
                                    <span id="vedic-test-result" class="niyati-test-badge"></span>
                                    <p class="description"><?php echo esc_html__('Your API key from https://api.vedicastroapi.com/v3-json/', 'merarashifal'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <p class="submit">
                        <button type="submit" class="button button-primary button-hero"><?php echo esc_html__('Save API Settings', 'merarashifal'); ?></button>
                    </p>
                </form>

            <?php elseif ($active_tab === 'questions'): ?>
                <!-- TAB 2: PRE-DEFINED QUESTIONS -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="niyati-form">
                    <?php wp_nonce_field('niyati_save_settings_action', 'niyati_nonce'); ?>
                    <input type="hidden" name="action" value="niyati_save_settings">
                    <input type="hidden" name="active_tab" value="questions">

                    <div class="niyati-card">
                        <h2><?php echo esc_html__('Draft Pre-defined Questions by Language', 'merarashifal'); ?></h2>
                        <p class="description"><?php echo esc_html__('Enter one question per line. These prompt chips appear inside the chat window for instant 1-click asking based on user language.', 'merarashifal'); ?></p>

                        <div class="niyati-lang-section">
                            <h3><span class="dashicons dashicons-translation"></span> <?php echo esc_html__('Hindi (हिंदी) Suggestions', 'merarashifal'); ?></h3>
                            <textarea name="questions_hi" rows="6" class="large-text code"><?php echo esc_textarea($settings['questions_hi']); ?></textarea>
                        </div>

                        <div class="niyati-lang-section">
                            <h3><span class="dashicons dashicons-translation"></span> <?php echo esc_html__('English Suggestions', 'merarashifal'); ?></h3>
                            <textarea name="questions_en" rows="6" class="large-text code"><?php echo esc_textarea($settings['questions_en']); ?></textarea>
                        </div>

                        <div class="niyati-lang-section">
                            <h3><span class="dashicons dashicons-translation"></span> <?php echo esc_html__('Hinglish Suggestions', 'merarashifal'); ?></h3>
                            <textarea name="questions_hinglish" rows="6" class="large-text code"><?php echo esc_textarea($settings['questions_hinglish']); ?></textarea>
                        </div>
                    </div>

                    <p class="submit">
                        <button type="submit" class="button button-primary button-hero"><?php echo esc_html__('Save Question Drafts', 'merarashifal'); ?></button>
                    </p>
                </form>

            <?php elseif ($active_tab === 'limits'): ?>
                <!-- TAB 3: FREE QUESTION LIMITS -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="niyati-form">
                    <?php wp_nonce_field('niyati_save_settings_action', 'niyati_nonce'); ?>
                    <input type="hidden" name="action" value="niyati_save_settings">
                    <input type="hidden" name="active_tab" value="limits">

                    <div class="niyati-card">
                        <h2><?php echo esc_html__('User Free Question Quota Management', 'merarashifal'); ?></h2>
                        <p class="description"><?php echo esc_html__('Control how many free questions new (first-time) visitors and regular (returning) visitors get to ask Niyati before needing paid credits.', 'merarashifal'); ?></p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="free_limit_new_user"><?php echo esc_html__('New User Limit (First-Time User)', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="number" min="0" max="100" id="free_limit_new_user" name="free_limit_new_user" value="<?php echo esc_attr($settings['free_limit_new_user']); ?>" class="small-text">
                                    <span class="description"><?php echo esc_html__('Number of free questions allowed for a brand new user (Default: 3)', 'merarashifal'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="free_limit_old_user"><?php echo esc_html__('Regular User Limit (Old User)', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="number" min="0" max="100" id="free_limit_old_user" name="free_limit_old_user" value="<?php echo esc_attr($settings['free_limit_old_user']); ?>" class="small-text">
                                    <span class="description"><?php echo esc_html__('Number of free questions allowed for returning users (Default: 1)', 'merarashifal'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="limit_reset_period"><?php echo esc_html__('Quota Reset Period', 'merarashifal'); ?></label></th>
                                <td>
                                    <select id="limit_reset_period" name="limit_reset_period">
                                        <option value="daily" <?php selected($settings['limit_reset_period'], 'daily'); ?>><?php echo esc_html__('Daily Reset (Users get their quota refreshed every midnight)', 'merarashifal'); ?></option>
                                        <option value="lifetime" <?php selected($settings['limit_reset_period'], 'lifetime'); ?>><?php echo esc_html__('Lifetime / One-time (Total questions allowed ever unless upgraded)', 'merarashifal'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="limit_exceeded_msg_hi"><?php echo esc_html__('Quota Reached Notice (Hindi)', 'merarashifal'); ?></label></th>
                                <td>
                                    <textarea id="limit_exceeded_msg_hi" name="limit_exceeded_msg_hi" rows="3" class="large-text"><?php echo esc_textarea($settings['limit_exceeded_msg_hi'] ?? ''); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="limit_exceeded_msg_en"><?php echo esc_html__('Quota Reached Notice (English)', 'merarashifal'); ?></label></th>
                                <td>
                                    <textarea id="limit_exceeded_msg_en" name="limit_exceeded_msg_en" rows="3" class="large-text"><?php echo esc_textarea($settings['limit_exceeded_msg_en'] ?? ''); ?></textarea>
                                </td>
                            </tr>
                        </table>

                        <div class="niyati-info-box">
                            <span class="dashicons dashicons-info"></span>
                            <strong><?php echo esc_html__('Note:', 'merarashifal'); ?></strong>
                            <?php echo esc_html__('Once a user exhausts their free limit, Niyati will automatically draw from their purchased Credit Packs wallet (1 credit = 1 question).', 'merarashifal'); ?>
                        </div>
                    </div>

                    <div class="niyati-card" style="margin-top:20px;">
                        <h2><?php echo esc_html__('Server Disk Chat Archiving & Retention', 'merarashifal'); ?></h2>
                        <p class="description">
                            <?php echo esc_html__('Niyati stores family Kundli profiles and active chat conversations as lightweight JSON files on server disk to keep your MySQL database completely clean. Inactive chats are automatically compressed into .json.gz format.', 'merarashifal'); ?>
                        </p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="archive_retention_days"><?php echo esc_html__('Archive Retention Period (Days)', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="number" min="7" max="365" id="archive_retention_days" name="archive_retention_days" value="<?php echo esc_attr($settings['archive_retention_days'] ?? 90); ?>" class="small-text">
                                    <span class="description"><?php echo esc_html__('Automatically delete compressed .json.gz chat archives older than this number of days (Default: 90 days).', 'merarashifal'); ?></span>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:15px; padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; display:flex; gap:24px; flex-wrap:wrap; font-size:13px;">
                            <div><strong>Storage Path:</strong> <code>wp-content/uploads/niyati-data/</code></div>
                            <div><strong>MySQL Database Usage:</strong> <span style="color:#16a34a; font-weight:700;">0 KB (Zero DB Clutter)</span></div>
                        </div>
                    </div>

                    <p class="submit">
                        <button type="submit" class="button button-primary button-hero"><?php echo esc_html__('Save Quota & Archiving Settings', 'merarashifal'); ?></button>
                    </p>
                </form>

            <?php elseif ($active_tab === 'persona'): ?>
                <!-- TAB 4: PERSONA & APPEARANCE -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="niyati-form">
                    <?php wp_nonce_field('niyati_save_settings_action', 'niyati_nonce'); ?>
                    <input type="hidden" name="action" value="niyati_save_settings">
                    <input type="hidden" name="active_tab" value="persona">

                    <div class="niyati-card">
                        <h2><?php echo esc_html__('Chatbot Persona & Welcome Messages', 'merarashifal'); ?></h2>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="bot_name"><?php echo esc_html__('Chatbot Name', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="text" id="bot_name" name="bot_name" value="<?php echo esc_attr($settings['bot_name']); ?>" class="regular-text">
                                    <p class="description"><?php echo esc_html__('Set as "Niyati" by default. The bot introduces herself with this name.', 'merarashifal'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="bot_title"><?php echo esc_html__('Chatbot Subtitle/Tagline', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="text" id="bot_title" name="bot_title" value="<?php echo esc_attr($settings['bot_title']); ?>" class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="theme_color"><?php echo esc_html__('Primary Brand Color', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="text" id="theme_color" name="theme_color" value="<?php echo esc_attr($settings['theme_color']); ?>" class="niyati-color-field">
                                    <p class="description"><?php echo esc_html__('Accent color for headers, send button, and user bubbles (Default: #D97706 Golden/Amber).', 'merarashifal'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Floating Chat Widget', 'merarashifal'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="floating_enabled" value="1" <?php checked($settings['floating_enabled'], '1'); ?>>
                                        <?php echo esc_html__('Show floating circular chat launcher bubble on all pages', 'merarashifal'); ?>
                                    </label>
                                    <p class="description"><?php echo esc_html__('You can also embed the chat on any specific page using the shortcode: [niyati_chat]', 'merarashifal'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="welcome_msg_hi"><?php echo esc_html__('Welcome Greeting (Hindi)', 'merarashifal'); ?></label></th>
                                <td>
                                    <textarea id="welcome_msg_hi" name="welcome_msg_hi" rows="3" class="large-text"><?php echo esc_textarea($settings['welcome_msg_hi']); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="welcome_msg_en"><?php echo esc_html__('Welcome Greeting (English)', 'merarashifal'); ?></label></th>
                                <td>
                                    <textarea id="welcome_msg_en" name="welcome_msg_en" rows="3" class="large-text"><?php echo esc_textarea($settings['welcome_msg_en']); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="welcome_msg_hinglish"><?php echo esc_html__('Welcome Greeting (Hinglish)', 'merarashifal'); ?></label></th>
                                <td>
                                    <textarea id="welcome_msg_hinglish" name="welcome_msg_hinglish" rows="3" class="large-text"><?php echo esc_textarea($settings['welcome_msg_hinglish']); ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <p class="submit">
                        <button type="submit" class="button button-primary button-hero"><?php echo esc_html__('Save Persona Settings', 'merarashifal'); ?></button>
                    </p>
                </form>

            <?php elseif ($active_tab === 'nudges'): ?>
                <?php
                $nudge_settings = class_exists('Niyati_Nudge') ? Niyati_Nudge::get_settings() : [];
                $all_campaigns  = class_exists('Niyati_Nudge') ? Niyati_Nudge::get_campaigns() : [];
                $active_nudge   = class_exists('Niyati_Nudge') ? Niyati_Nudge::get_active_nudge() : [];
                $edit_cid       = isset($_GET['edit_campaign']) ? sanitize_key($_GET['edit_campaign']) : '';
                $edit_camp      = (!empty($edit_cid) && isset($all_campaigns[$edit_cid])) ? $all_campaigns[$edit_cid] : null;
                $now_ts         = current_time('timestamp');
                ?>

                <!-- TAB: CHAT NUDGES & EVENT CAMPAIGNS -->

                <!-- Live Status Banner -->
                <div class="niyati-card" style="background:#FFFDF9; border: 1.5px solid #F58220; box-shadow: 0 4px 15px rgba(245, 130, 32, 0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                        <div style="display:flex; align-items:center; gap:14px;">
                            <div style="width:48px; height:48px; border-radius:50%; background:#FDF3E7; display:flex; align-items:center; justify-content:center; font-size:24px; color:#F58220;">
                                ✨
                            </div>
                            <div>
                                <h3 style="margin:0 0 4px 0; color:#7A1F3D;">
                                    <?php echo esc_html__('Live Nudge Status:', 'merarashifal'); ?>
                                    <?php if (empty($active_nudge['enabled'])): ?>
                                        <span style="font-size:12px; background:#FEE2E2; color:#B91C1C; padding:3px 10px; border-radius:12px; font-weight:700; margin-left:6px;">Disabled</span>
                                    <?php elseif (!empty($active_nudge['is_scheduled'])): ?>
                                        <span style="font-size:12px; background:#DCFCE7; color:#15803D; padding:3px 10px; border-radius:12px; font-weight:700; margin-left:6px;">Festival Campaign Live (<?php echo esc_html($active_nudge['campaign']); ?>)</span>
                                    <?php else: ?>
                                        <span style="font-size:12px; background:#FEF3C7; color:#B45309; padding:3px 10px; border-radius:12px; font-weight:700; margin-left:6px;">Default Nudge Active</span>
                                    <?php endif; ?>
                                </h3>
                                <p style="margin:0; font-size:13px; color:#3A312D;">
                                    <strong>Preview Message:</strong> "<em><?php echo esc_html($active_nudge['message'] ?? 'Disabled'); ?></em>"
                                    <span style="margin-left:8px; color:#786C65; font-size:12px;">(Pops out every <?php echo esc_html($active_nudge['interval_minutes'] ?? 30); ?> mins • Fades after <?php echo esc_html($active_nudge['display_seconds'] ?? 10); ?>s)</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1. GENERAL NUDGE SETTINGS -->
                <div class="niyati-card">
                    <h2><?php echo esc_html__('1. General Chat Nudge Settings', 'merarashifal'); ?></h2>
                    <p class="description">
                        <?php echo esc_html__('Configures the automatic periodic speech bubble that pops out from the floating "Ask Niyati" launcher icon.', 'merarashifal'); ?>
                    </p>

                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('niyati_save_nudge_settings_action', 'niyati_nonce'); ?>
                        <input type="hidden" name="action" value="niyati_save_nudge_settings">

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Enable Chat Nudge', 'merarashifal'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="enabled" value="1" <?php checked(!empty($nudge_settings['enabled']), true); ?>>
                                        <strong><?php echo esc_html__('Enable proactive speech bubble popup from Ask Niyati button', 'merarashifal'); ?></strong>
                                    </label>
                                    <p class="description"><?php echo esc_html__('When enabled, a friendly speech bubble pops out from the Niyati launcher to invite visitors to ask questions.', 'merarashifal'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="nudge_interval"><?php echo esc_html__('Trigger Interval (Minutes)', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="number" id="nudge_interval" name="interval_minutes" value="<?php echo esc_attr($nudge_settings['interval_minutes'] ?? 30); ?>" min="1" max="1440" class="small-text">
                                    <span class="description"><?php echo esc_html__('Minutes between proactive nudges (Default: 30 minutes). Starts on first load for the user.', 'merarashifal'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="nudge_display_seconds"><?php echo esc_html__('Display Duration (Seconds)', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="number" id="nudge_display_seconds" name="display_seconds" value="<?php echo esc_attr($nudge_settings['display_seconds'] ?? 30); ?>" min="0" max="120" class="small-text">
                                    <span class="description"><?php echo esc_html__('How many seconds the bubble stays visible before smoothly auto-hiding (Default: 30 seconds. Set 0 to keep until closed or clicked).', 'merarashifal'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="default_message"><?php echo esc_html__('Default Nudge Message', 'merarashifal'); ?></label></th>
                                <td>
                                    <input type="text" id="default_message" name="default_message" value="<?php echo esc_attr($nudge_settings['default_message'] ?? 'Ask Niyati is active to answer all your questions'); ?>" class="large-text" required>
                                    <p class="description"><?php echo esc_html__('This default message is shown whenever there is no active scheduled festival campaign.', 'merarashifal'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php echo esc_html__('Save General Settings', 'merarashifal'); ?></button>
                        </p>
                    </form>
                </div>

                <!-- 2. SCHEDULED EVENT & FESTIVE CAMPAIGNS -->
                <div class="niyati-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:12px; margin-bottom:20px;">
                        <div>
                            <h2 style="margin:0;"><?php echo esc_html__('2. Scheduled Festival & Event Campaigns', 'merarashifal'); ?></h2>
                            <p class="description" style="margin:4px 0 0 0;">
                                <?php echo esc_html__('Schedule special greetings and wishes for specific festival dates (e.g. Diwali, Holi, Navratri, New Year, Solar Eclipse). When a campaign date is live, its message automatically displays. Once the date passes or campaign is paused, Niyati automatically falls back to your default message!', 'merarashifal'); ?>
                            </p>
                        </div>
                        <div>
                            <a href="#campaign-form" class="button button-primary"><span class="dashicons dashicons-calendar-alt" style="margin-top:4px;"></span> <?php echo esc_html__('Schedule New Campaign', 'merarashifal'); ?></a>
                        </div>
                    </div>

                    <table class="widefat fixed striped" style="margin-bottom:25px;">
                        <thead>
                            <tr>
                                <th style="width:22%;"><?php echo esc_html__('Campaign / Event Name', 'merarashifal'); ?></th>
                                <th style="width:34%;"><?php echo esc_html__('Nudge Message', 'merarashifal'); ?></th>
                                <th style="width:16%;"><?php echo esc_html__('Schedule Window', 'merarashifal'); ?></th>
                                <th style="width:14%;"><?php echo esc_html__('Status', 'merarashifal'); ?></th>
                                <th style="width:14%;"><?php echo esc_html__('Actions', 'merarashifal'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_campaigns)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:24px; color:#64748b;">
                                        <em><?php echo esc_html__('No scheduled campaigns yet. Create your first festive greeting below!', 'merarashifal'); ?></em>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_campaigns as $camp): 
                                    $c_start = !empty($camp['start_date']) ? strtotime($camp['start_date']) : 0;
                                    $c_end   = !empty($camp['end_date']) ? strtotime($camp['end_date']) : PHP_INT_MAX;
                                    $is_paused = (!empty($camp['status']) && $camp['status'] === 'paused');
                                    $is_live = (!$is_paused && $now_ts >= $c_start && $now_ts <= $c_end);
                                    $is_upcoming = (!$is_paused && $now_ts < $c_start);
                                    $is_expired = (!$is_paused && $now_ts > $c_end);
                                ?>
                                    <tr style="<?php echo $is_paused ? 'opacity:0.65; background:#fafafa;' : ($is_live ? 'background:#F0FDF4;' : ''); ?>">
                                        <td>
                                            <strong><?php echo esc_html($camp['name']); ?></strong>
                                        </td>
                                        <td>
                                            <span style="font-size:13px; color:#3A312D;">"<?php echo esc_html($camp['message']); ?>"</span>
                                        </td>
                                        <td style="font-size:12px; color:#475569;">
                                            <div><strong>From:</strong> <?php echo !empty($camp['start_date']) ? esc_html(date_i18n('d M Y, h:i A', $c_start)) : 'Immediate'; ?></div>
                                            <div><strong>To:</strong> <?php echo !empty($camp['end_date']) ? esc_html(date_i18n('d M Y, h:i A', $c_end)) : 'No Expiry'; ?></div>
                                        </td>
                                        <td>
                                            <?php if ($is_paused): ?>
                                                <span style="display:inline-block; font-size:11px; padding:3px 8px; border-radius:10px; background:#f1f5f9; color:#64748b; font-weight:700;">⏸️ Paused</span>
                                            <?php elseif ($is_live): ?>
                                                <span style="display:inline-block; font-size:11px; padding:3px 8px; border-radius:10px; background:#dcfce7; color:#15803d; font-weight:700;">🟢 Live Now</span>
                                            <?php elseif ($is_upcoming): ?>
                                                <span style="display:inline-block; font-size:11px; padding:3px 8px; border-radius:10px; background:#fef3c7; color:#b45309; font-weight:700;">⏳ Upcoming</span>
                                            <?php elseif ($is_expired): ?>
                                                <span style="display:inline-block; font-size:11px; padding:3px 8px; border-radius:10px; background:#fee2e2; color:#b91c1c; font-weight:700;">⌛ Expired</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:6px; align-items:center;">
                                                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'nudges', 'edit_campaign' => $camp['id']], admin_url('admin.php'))); ?>#campaign-form" 
                                                   class="button button-small" title="Edit Campaign">Edit</a>
                                                
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                                    <?php wp_nonce_field('niyati_toggle_nudge_campaign_action', 'niyati_nonce'); ?>
                                                    <input type="hidden" name="action" value="niyati_toggle_nudge_campaign">
                                                    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($camp['id']); ?>">
                                                    <button type="submit" class="button button-small" title="<?php echo $is_paused ? 'Resume Campaign' : 'Pause Campaign'; ?>">
                                                        <?php echo $is_paused ? '▶ Resume' : '⏸ Pause'; ?>
                                                    </button>
                                                </form>

                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;" onsubmit="return confirm('Delete this campaign?');">
                                                    <?php wp_nonce_field('niyati_delete_nudge_campaign_action', 'niyati_nonce'); ?>
                                                    <input type="hidden" name="action" value="niyati_delete_nudge_campaign">
                                                    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($camp['id']); ?>">
                                                    <button type="submit" class="button button-small button-link-delete" title="Delete">🗑</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Add / Edit Campaign Form -->
                    <div id="campaign-form" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin-top:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h3 style="margin:0; color:#1e293b;">
                                <?php if ($edit_camp): ?>
                                    <span class="dashicons dashicons-edit"></span> <?php echo esc_html__('Edit Campaign:', 'merarashifal'); ?> <?php echo esc_html($edit_camp['name']); ?>
                                <?php else: ?>
                                    <span class="dashicons dashicons-plus-alt"></span> <?php echo esc_html__('Schedule New Festive Campaign / Greeting', 'merarashifal'); ?>
                                <?php endif; ?>
                            </h3>
                            <?php if ($edit_camp): ?>
                                <a href="<?php echo esc_url(add_query_arg(['page' => 'niyati-chat-settings', 'tab' => 'nudges'], admin_url('admin.php'))); ?>" class="button button-secondary">
                                    ✕ <?php echo esc_html__('Cancel Edit', 'merarashifal'); ?>
                                </a>
                            <?php endif; ?>
                        </div>

                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('niyati_save_nudge_campaign_action', 'niyati_nonce'); ?>
                            <input type="hidden" name="action" value="niyati_save_nudge_campaign">
                            <?php if ($edit_camp): ?>
                                <input type="hidden" name="id" value="<?php echo esc_attr($edit_camp['id']); ?>">
                            <?php endif; ?>

                            <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:14px;">
                                <div style="flex:1; min-width:240px;">
                                    <label><strong><?php echo esc_html__('Event / Campaign Name', 'merarashifal'); ?>:</strong></label>
                                    <input type="text" name="name" value="<?php echo esc_attr($edit_camp['name'] ?? ''); ?>" 
                                           placeholder="e.g. Happy Diwali from Niyati" class="regular-text" style="width:100%;" required>
                                </div>
                                <div style="flex:1; min-width:180px;">
                                    <label><strong><?php echo esc_html__('Campaign Status', 'merarashifal'); ?>:</strong></label>
                                    <select name="status" style="width:100%;">
                                        <option value="active" <?php selected($edit_camp['status'] ?? 'active', 'active'); ?>>Active (Will run during schedule window)</option>
                                        <option value="paused" <?php selected($edit_camp['status'] ?? 'active', 'paused'); ?>>Paused (Draft / Inactive)</option>
                                    </select>
                                </div>
                            </div>

                            <div style="margin-bottom:14px;">
                                <label><strong><?php echo esc_html__('Nudge Message (Speech Bubble Text)', 'merarashifal'); ?>:</strong></label>
                                <input type="text" name="message" value="<?php echo esc_attr($edit_camp['message'] ?? ''); ?>" 
                                       placeholder="e.g. 🪔 Happy Diwali from Niyati! Ask your festive Kundli questions today" class="large-text" style="width:100%;" required>
                                <span class="description"><?php echo esc_html__('Keep it concise, friendly, and enticing. Emojis (✨, 🪔, 🌸, 🕉️) are fully supported.', 'merarashifal'); ?></span>
                            </div>

                            <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:16px;">
                                <div style="flex:1; min-width:220px;">
                                    <label><strong><?php echo esc_html__('Start Date & Time', 'merarashifal'); ?>:</strong></label>
                                    <input type="datetime-local" name="start_date" value="<?php echo esc_attr($edit_camp['start_date'] ?? ''); ?>" style="width:100%;" required>
                                    <span class="description"><?php echo esc_html__('When this greeting should begin displaying to users.', 'merarashifal'); ?></span>
                                </div>
                                <div style="flex:1; min-width:220px;">
                                    <label><strong><?php echo esc_html__('End Date & Time', 'merarashifal'); ?>:</strong></label>
                                    <input type="datetime-local" name="end_date" value="<?php echo esc_attr($edit_camp['end_date'] ?? ''); ?>" style="width:100%;" required>
                                    <span class="description"><?php echo esc_html__('When this greeting expires and reverts back to the default message.', 'merarashifal'); ?></span>
                                </div>
                            </div>

                            <p class="submit" style="margin:0;">
                                <button type="submit" class="button button-primary">
                                    <span class="dashicons dashicons-saved" style="margin-top:4px;"></span>
                                    <?php echo $edit_camp ? esc_html__('Update Scheduled Campaign', 'merarashifal') : esc_html__('Save & Schedule Campaign', 'merarashifal'); ?>
                                </button>
                            </p>
                        </form>
                    </div>
                </div>

            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * AJAX Test Gemini API
     */
    public static function ajax_test_gemini() {
        check_ajax_referer('niyati_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $model = sanitize_text_field($_POST['model'] ?? '');

        $result = Niyati_Gemini_API::test_connection($api_key, $model);
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * AJAX Test Vedic API
     */
    public static function ajax_test_vedic() {
        check_ajax_referer('niyati_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');

        $result = Niyati_Vedic_API::test_connection($api_key);
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}
