<?php
/**
 * MeraRashifal Content Bot - Admin Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MRCB_Admin {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );

        // AJAX Actions
        add_action( 'wp_ajax_mrcb_approve_draft', [ __CLASS__, 'ajax_approve_draft' ] );
        add_action( 'wp_ajax_mrcb_reject_draft', [ __CLASS__, 'ajax_reject_draft' ] );
        add_action( 'wp_ajax_mrcb_skip_draft', [ __CLASS__, 'ajax_skip_draft' ] );
        add_action( 'wp_ajax_mrcb_regenerate_draft', [ __CLASS__, 'ajax_regenerate_draft' ] );
        add_action( 'wp_ajax_mrcb_publish_now', [ __CLASS__, 'ajax_publish_now' ] );
        add_action( 'wp_ajax_mrcb_generate_for_date', [ __CLASS__, 'ajax_generate_for_date' ] );
        add_action( 'wp_ajax_mrcb_test_connection', [ __CLASS__, 'ajax_test_connection' ] );
        add_action( 'wp_ajax_mrcb_toggle_festival', [ __CLASS__, 'ajax_toggle_festival' ] );
        add_action( 'wp_ajax_mrcb_sync_festivals', [ __CLASS__, 'ajax_sync_festivals' ] );
        add_action( 'wp_ajax_mrcb_plan_campaign', [ __CLASS__, 'ajax_plan_campaign' ] );
        add_action( 'wp_ajax_mrcb_save_filler', [ __CLASS__, 'ajax_save_filler' ] );
        add_action( 'wp_ajax_mrcb_delete_filler', [ __CLASS__, 'ajax_delete_filler' ] );
    }

    /**
     * Register Admin Menus
     */
    public static function register_menus() {
        add_menu_page(
            'Content Bot',
            'Content Bot ✍️',
            'manage_options',
            'mrcb-queue',
            [ __CLASS__, 'render_queue' ],
            'dashicons-edit-large',
            26
        );

        add_submenu_page(
            'mrcb-queue',
            'Content Queue',
            'Content Queue',
            'manage_options',
            'mrcb-queue',
            [ __CLASS__, 'render_queue' ]
        );

        add_submenu_page(
            'mrcb-queue',
            'Festivals Manager',
            'Festivals Manager',
            'manage_options',
            'mrcb-festivals',
            [ __CLASS__, 'render_festivals' ]
        );

        add_submenu_page(
            'mrcb-queue',
            'Filler Bank',
            'Filler Bank',
            'manage_options',
            'mrcb-filler-bank',
            [ __CLASS__, 'render_filler_bank' ]
        );

        add_submenu_page(
            'mrcb-queue',
            'Settings & AI',
            'Settings & AI',
            'manage_options',
            'mrcb-settings',
            [ __CLASS__, 'render_settings' ]
        );
    }

    /**
     * Enqueue Admin Assets
     */
    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'mrcb' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'mrcb-admin-css',
            MRCB_PLUGIN_URL . 'assets/css/admin.css',
            [],
            MRCB_VERSION
        );

        wp_enqueue_script(
            'mrcb-admin-js',
            MRCB_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            MRCB_VERSION,
            true
        );

        wp_localize_script( 'mrcb-admin-js', 'mrcbConfig', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mrcb_admin_nonce' ),
        ] );
    }

    /**
     * View: Content Queue & Review
     */
    public static function render_queue() {
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'review' && ! empty( $_GET['id'] ) ) {
            require_once MRCB_PLUGIN_DIR . 'admin/views/page-review.php';
        } else {
            require_once MRCB_PLUGIN_DIR . 'admin/views/page-queue.php';
        }
    }

    /**
     * View: Festivals Manager
     */
    public static function render_festivals() {
        require_once MRCB_PLUGIN_DIR . 'admin/views/page-festivals.php';
    }

    /**
     * View: Filler Bank
     */
    public static function render_filler_bank() {
        require_once MRCB_PLUGIN_DIR . 'admin/views/page-filler-bank.php';
    }

    /**
     * View: Settings
     */
    public static function render_settings() {
        require_once MRCB_PLUGIN_DIR . 'admin/views/page-settings.php';
    }

    // ── AJAX Handlers ─────────────────────────────────────────────────────────

    public static function ajax_approve_draft() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        global $wpdb;
        $t_queue = MRCB_DB_Setup::table_queue();
        $id = (int) ( $_POST['id'] ?? 0 );

        if ( ! $id ) {
            wp_send_json_error( [ 'message' => 'Invalid ID' ] );
        }

        $draft_content = isset( $_POST['content'] ) ? wp_unslash( $_POST['content'] ) : '';
        $meta_title    = isset( $_POST['meta_title'] ) ? sanitize_text_field( wp_unslash( $_POST['meta_title'] ) ) : '';
        $meta_desc     = isset( $_POST['meta_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['meta_desc'] ) ) : '';
        $slug          = isset( $_POST['slug'] ) ? sanitize_title( $_POST['slug'] ) : '';

        $data = [
            'admin_status' => 'approved',
            'approved_by'  => get_current_user_id(),
            'approved_at'  => current_time( 'mysql' ),
        ];

        if ( ! empty( $draft_content ) ) {
            $data['admin_draft'] = $draft_content;
        }
        if ( ! empty( $meta_title ) ) {
            $data['admin_meta_title'] = $meta_title;
        }
        if ( ! empty( $meta_desc ) ) {
            $data['admin_meta_desc'] = $meta_desc;
        }
        if ( ! empty( $slug ) ) {
            $data['wp_slug'] = $slug;
        }

        $wpdb->update( $t_queue, $data, [ 'id' => $id ] );

        wp_send_json_success( [ 'message' => 'Post approved successfully! It will be published at 7:00 AM on scheduled date.' ] );
    }

    public static function ajax_reject_draft() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        global $wpdb;
        $id = (int) ( $_POST['id'] ?? 0 );
        $notes = sanitize_textarea_field( $_POST['notes'] ?? '' );

        $wpdb->update(
            MRCB_DB_Setup::table_queue(),
            [ 'admin_status' => 'rejected', 'admin_notes' => $notes ],
            [ 'id' => $id ]
        );

        wp_send_json_success( [ 'message' => 'Draft marked as rejected.' ] );
    }

    public static function ajax_skip_draft() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        global $wpdb;
        $id = (int) ( $_POST['id'] ?? 0 );

        $wpdb->update(
            MRCB_DB_Setup::table_queue(),
            [ 'admin_status' => 'skipped' ],
            [ 'id' => $id ]
        );

        wp_send_json_success( [ 'message' => 'Draft skipped.' ] );
    }

    public static function ajax_regenerate_draft() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $id = (int) ( $_POST['id'] ?? 0 );
        if ( ! $id ) {
            wp_send_json_error( [ 'message' => 'Invalid ID' ] );
        }

        $res = MRCB_Writer::generate_for_queue_item( $id );
        if ( $res['success'] ) {
            wp_send_json_success( [ 'message' => 'Draft regenerated successfully!', 'data' => $res ] );
        } else {
            wp_send_json_error( [ 'message' => 'Regeneration failed: ' . $res['error'] ] );
        }
    }

    public static function ajax_publish_now() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $id = (int) ( $_POST['id'] ?? 0 );
        // First ensure it's approved
        global $wpdb;
        $wpdb->update(
            MRCB_DB_Setup::table_queue(),
            [
                'admin_status' => 'approved',
                'approved_by'  => get_current_user_id(),
                'approved_at'  => current_time( 'mysql' ),
            ],
            [ 'id' => $id ]
        );

        $res = MRCB_Publisher::publish_queue_item( $id );
        if ( $res['success'] ) {
            wp_send_json_success( [
                'message' => 'Published successfully!',
                'url'     => $res['url'],
                'post_id' => $res['wp_post_id']
            ] );
        } else {
            wp_send_json_error( [ 'message' => 'Publishing failed: ' . $res['error'] ] );
        }
    }

    public static function ajax_generate_for_date() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $date = sanitize_text_field( $_POST['date'] ?? '' );
        if ( empty( $date ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
            wp_send_json_error( [ 'message' => 'Invalid date format (use YYYY-MM-DD).' ] );
        }

        $res = MRCB_Scheduler::ensure_content_for_date( $date, true );
        wp_send_json_success( [ 'message' => 'Generated draft for ' . $date, 'result' => $res ] );
    }

    public static function ajax_test_connection() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $res = MRCB_Writer::test_connection();
        if ( $res['success'] ) {
            wp_send_json_success( $res );
        } else {
            wp_send_json_error( $res );
        }
    }

    public static function ajax_toggle_festival() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        global $wpdb;
        $id = (int) ( $_POST['id'] ?? 0 );
        $status = sanitize_text_field( $_POST['status'] ?? 'active' );
        $tier = (int) ( $_POST['tier'] ?? 3 );
        $post_count = (int) ( $_POST['post_count'] ?? 1 );

        $wpdb->update(
            MRCB_DB_Setup::table_festivals(),
            [
                'coverage_status' => in_array( $status, [ 'active', 'skipped' ] ) ? $status : 'active',
                'tier'            => $tier,
                'post_count'      => $post_count,
            ],
            [ 'id' => $id ]
        );

        wp_send_json_success( [ 'message' => 'Festival settings updated.' ] );
    }

    public static function ajax_sync_festivals() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $scope = sanitize_text_field( $_POST['scope'] ?? 'upcoming_1_year' );
        $today = current_time( 'Y-m-d' );

        $start_date = $today;
        $end_date   = date( 'Y-m-d', strtotime( '+1 year', strtotime( $today ) ) );

        if ( $scope === '2_years' ) {
            $end_date = date( 'Y-m-d', strtotime( '+2 years', strtotime( $today ) ) );
        } elseif ( $scope === 'all' ) {
            $start_date = '2026-01-01';
            $end_date   = '2028-12-31';
        }

        $res = MRCB_DB_Setup::sync_festivals_from_master( $start_date, $end_date );

        wp_send_json_success( [
            'message'      => sprintf( 'Synced %d festivals from calendar (%d newly added) for %s to %s.', $res['total_master'], $res['inserted'], $res['start_date'], $res['end_date'] ),
            'total_master' => $res['total_master'],
            'inserted'     => $res['inserted'],
            'start_date'   => $res['start_date'],
            'end_date'     => $res['end_date'],
        ] );
    }

    public static function ajax_plan_campaign() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $festival_id = (int) ( $_POST['festival_id'] ?? 0 );
        if ( ! $festival_id ) {
            wp_send_json_error( [ 'message' => 'Invalid festival ID.' ] );
        }

        $auto_generate = ! empty( $_POST['auto_generate'] );
        $res = MRCB_Scheduler::plan_campaign_for_festival( $festival_id, $auto_generate );

        if ( $res['success'] ) {
            wp_send_json_success( [
                'message'    => sprintf( 'Successfully planned %d campaign posts for "%s"!', $res['post_count'], $res['festival'] ),
                'post_count' => $res['post_count'],
                'series_id'  => $res['series_id'],
                'queue_ids'  => $res['queue_ids'],
            ] );
        } else {
            wp_send_json_error( [ 'message' => $res['error'] ?? 'Failed to plan campaign.' ] );
        }
    }

    public static function ajax_save_filler() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        global $wpdb;
        $t_filler = MRCB_DB_Setup::table_filler();

        $title    = sanitize_text_field( $_POST['title_hi'] ?? '' );
        $category = sanitize_text_field( $_POST['category'] ?? 'astrology' );
        $keyword  = sanitize_text_field( $_POST['focus_keyword'] ?? '' );
        $priority = (int) ( $_POST['priority'] ?? 5 );
        $id       = (int) ( $_POST['id'] ?? 0 );

        if ( empty( $title ) ) {
            wp_send_json_error( [ 'message' => 'Title is required' ] );
        }

        if ( $id > 0 ) {
            $wpdb->update(
                $t_filler,
                [
                    'topic_title_hi' => $title,
                    'category'       => $category,
                    'focus_keyword'  => $keyword,
                    'priority'       => $priority,
                ],
                [ 'id' => $id ]
            );
        } else {
            $wpdb->insert(
                $t_filler,
                [
                    'topic_title_hi' => $title,
                    'category'       => $category,
                    'focus_keyword'  => $keyword,
                    'priority'       => $priority,
                    'is_active'      => 1,
                ]
            );
        }

        wp_send_json_success( [ 'message' => 'Filler topic saved.' ] );
    }

    public static function ajax_delete_filler() {
        check_ajax_referer( 'mrcb_admin_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        global $wpdb;
        $id = (int) ( $_POST['id'] ?? 0 );
        $wpdb->delete( MRCB_DB_Setup::table_filler(), [ 'id' => $id ] );

        wp_send_json_success( [ 'message' => 'Filler topic deleted.' ] );
    }
}
