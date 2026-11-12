<?php
// Enqueue parent and child theme styles
add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_styles' );
function astra_child_enqueue_styles() {
    // Parent style
    wp_enqueue_style( 'astra-parent-style', get_template_directory_uri() . '/style.css' );

    // Child style (optional, if you want extra CSS file)
    wp_enqueue_style( 'astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-parent-style'), wp_get_theme()->get('Version') );
}

// Eliminate WordPress top admin bar completely on the frontend
add_filter('show_admin_bar', '__return_false');
add_action('get_header', function() {
    remove_action('wp_head', '_admin_bar_bump_cb');
});

// Load Homepage Placement & Content Manager
require_once get_stylesheet_directory() . '/inc/class-mr-homepage-manager.php';

// Ensure Reports Showcase shortcode engine is loaded
if (!class_exists('MR_Reports_Showcase') && file_exists(WP_PLUGIN_DIR . '/mr-subscription-reports/includes/class-mr-reports-showcase.php')) {
    require_once WP_PLUGIN_DIR . '/mr-subscription-reports/includes/class-mr-reports-showcase.php';
    add_action('init', ['MR_Reports_Showcase', 'init']);
}
