<?php
/**
 * Plugin Name: MeraRashifal Subscriptions & Report Engine
 * Plugin URI: https://merarashifal.com
 * Description: Token-based Vedic astrology subscription management, admin bypass pack assignment, birth details capture, asynchronous report generation with bottom progress banner, and user report archive.
 * Version: 1.0.0
 * Author: MeraRashifal Team
 * Author URI: https://merarashifal.com
 * Text Domain: merarashifal
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Require Core Components
require_once MR_PLUGIN_DIR . 'includes/class-mr-packs.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-db.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-tokens.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-razorpay.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-transactions.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-vedic-api.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-gemini-ai.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-astrological-rules.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-monthly-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-yearly-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-short-yearly-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-long-yearly-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-kundali-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-dasha-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-dosh-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-matching-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-numero-report-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-pdf-engine.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-validator.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-admin.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-frontend.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-passbook-ui.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-catalog-manager.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-palmistry-builder.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-account-ui.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-report-downloader.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-mailer.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-report-embed.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-report-landing-data.php';
require_once MR_PLUGIN_DIR . 'includes/class-mr-report-landing.php';

// Plugin Activation: Create Database Tables
register_activation_hook(__FILE__, ['MR_DB', 'create_tables']);

// Initialize Admin & Frontend
add_action('plugins_loaded', function() {
    // Ensure tables exist on boot
    MR_DB::create_tables();

    if (is_admin()) {
        MR_Admin::init();
    }
    MR_Frontend::init();
    MR_Passbook_UI::init();
    MR_Razorpay::init();
    MR_Catalog_Manager::init();
    MR_Palmistry_Builder::init();
    MR_Account_UI::init();
    MR_Report_Downloader::init();
    MR_Mailer::init();
    MR_Report_Embed::init();
    MR_Report_Landing::init();
});


