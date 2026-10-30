<?php
/**
 * Plugin Name: MeraRashifal Content Bot
 * Plugin URI: https://merarashifal.com
 * Description: Intelligent Hindi festival content writing bot. Generates SEO-optimized posts 10 days in advance using Gemini AI. All posts require admin approval before publishing.
 * Version: 1.0.0
 * Author: MeraRashifal Team
 * Author URI: https://merarashifal.com
 * Text Domain: mr-content-bot
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Constants ──────────────────────────────────────────────────────────────────
define( 'MRCB_VERSION',    '1.0.0' );
define( 'MRCB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MRCB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MRCB_ADVANCE_DAYS', 10 ); // Generate content this many days ahead

// ── Load Components ────────────────────────────────────────────────────────────
require_once MRCB_PLUGIN_DIR . 'includes/class-mrcb-db-setup.php';
require_once MRCB_PLUGIN_DIR . 'includes/class-mrcb-writer.php';
require_once MRCB_PLUGIN_DIR . 'includes/class-mrcb-scheduler.php';
require_once MRCB_PLUGIN_DIR . 'includes/class-mrcb-publisher.php';

// ── Activation / Deactivation ─────────────────────────────────────────────────
register_activation_hook( __FILE__, function () {
    MRCB_DB_Setup::create_tables();
    MRCB_DB_Setup::seed_festivals();
    MRCB_DB_Setup::seed_filler_bank();
    MRCB_DB_Setup::seed_link_map();
    MRCB_Scheduler::register_cron_events();
    MRCB_Publisher::register_cron_events();
} );

register_deactivation_hook( __FILE__, function () {
    MRCB_Scheduler::clear_cron_events();
    MRCB_Publisher::clear_cron_events();
} );

// ── Bootstrap ─────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', function () {

    // Admin panel
    if ( is_admin() ) {
        require_once MRCB_PLUGIN_DIR . 'admin/class-mrcb-admin.php';
        MRCB_Admin::init();
    }

    // Register cron callbacks (must be always registered, not just in admin)
    MRCB_Scheduler::init();
    MRCB_Publisher::init();
} );

// ── Frontend Assets ───────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
    if ( is_singular( 'post' ) ) {
        wp_enqueue_style(
            'mrcb-frontend-article',
            MRCB_PLUGIN_URL . 'assets/css/frontend-article.css',
            [],
            MRCB_VERSION
        );
    }
} );
