<?php
/**
 * Database Tables Manager for Subscriptions, Tokens & Generated Reports
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_DB {

    /**
     * Create required database tables
     */
    public static function create_tables() {
        global $wpdb;

        if (get_option('mr_db_tables_created') === 'yes') {
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();

        // 1. Subscriptions table
        $table_subs = $wpdb->prefix . 'mr_user_subscriptions';
        $sql_subs = "CREATE TABLE IF NOT EXISTS $table_subs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            pack_key varchar(50) NOT NULL,
            pack_name varchar(100) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            start_date datetime NOT NULL,
            expiry_date datetime NOT NULL,
            assigned_by bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        $wpdb->query($sql_subs);

        // 2. Tokens balance table
        $table_tokens = $wpdb->prefix . 'mr_user_tokens';
        $sql_tokens = "CREATE TABLE IF NOT EXISTS $table_tokens (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            subscription_id bigint(20) unsigned DEFAULT NULL,
            report_slug varchar(50) NOT NULL,
            tokens_total int(11) NOT NULL DEFAULT 0,
            tokens_used int(11) NOT NULL DEFAULT 0,
            tokens_remaining int(11) NOT NULL DEFAULT 0,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_report (user_id, report_slug),
            KEY user_id (user_id)
        ) $charset_collate;";
        $wpdb->query($sql_tokens);

        // 3. Generated reports archive
        $table_reports = $wpdb->prefix . 'mr_generated_reports';
        $sql_reports = "CREATE TABLE IF NOT EXISTS $table_reports (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            download_token varchar(64) DEFAULT NULL,
            report_slug varchar(50) NOT NULL,
            report_title varchar(150) NOT NULL,
            person_name varchar(100) NOT NULL,
            gender varchar(20) NOT NULL DEFAULT 'Male',
            dob date NOT NULL,
            tob time NOT NULL,
            pob varchar(150) NOT NULL,
            recipient_email varchar(150) DEFAULT NULL,
            pdf_filename varchar(255) DEFAULT NULL,
            pdf_url varchar(500) DEFAULT NULL,
            status varchar(30) NOT NULL DEFAULT 'completed',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY report_slug (report_slug),
            UNIQUE KEY download_token (download_token)
        ) $charset_collate;";
        $wpdb->query($sql_reports);

        // Migration: Ensure download_token column exists if table was created previously
        $col_check = $wpdb->get_results("SHOW COLUMNS FROM `$table_reports` LIKE 'download_token'");
        if (empty($col_check)) {
            $wpdb->query("ALTER TABLE `$table_reports` ADD COLUMN download_token varchar(64) DEFAULT NULL, ADD UNIQUE KEY download_token (download_token)");
        }

        // Migration: Ensure recipient_email column exists if table was created previously
        $col_check_email = $wpdb->get_results("SHOW COLUMNS FROM `$table_reports` LIKE 'recipient_email'");
        if (empty($col_check_email)) {
            $wpdb->query("ALTER TABLE `$table_reports` ADD COLUMN recipient_email varchar(150) DEFAULT NULL AFTER pob");
        }

        // 4. Universal Transactions Ledger
        if (class_exists('MR_Transactions')) {
            MR_Transactions::create_table();
        }

        update_option('mr_db_tables_created', 'yes');
    }
}
