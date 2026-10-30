<?php
/**
 * MeraRashifal Content Bot - Settings View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$api_key = MRCB_Writer::get_api_key();
$masked_key = ! empty( $api_key ) ? substr( $api_key, 0, 8 ) . '...' . substr( $api_key, -6 ) : 'Not Configured';
$model   = MRCB_Writer::get_model();

// Count link map
$link_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . MRCB_DB_Setup::table_link_map() );
$fest_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . MRCB_DB_Setup::table_festivals() );
$fill_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . MRCB_DB_Setup::table_filler() );
?>

<div class="wrap mrcb-wrap">
    <div class="mrcb-header">
        <div class="mrcb-header-title">
            <h1>⚙️ Content Bot Settings & AI Diagnostic</h1>
            <p class="mrcb-subtitle">Verify Google Gemini API connectivity, manage intelligence rules, and inspect internal link maps.</p>
        </div>
    </div>

    <div class="mrcb-review-container">
        <!-- Main Column: Settings & Connectivity -->
        <div class="mrcb-review-main">
            <!-- Gemini Diagnostic Card -->
            <div class="mrcb-card">
                <h2>🤖 Google Gemini AI Connection</h2>
                <p>MeraRashifal Content Bot shares your verified Gemini API integration from the Niyati AI engine.</p>

                <table class="form-table">
                    <tr>
                        <th scope="row">Gemini API Key</th>
                        <td>
                            <code><?php echo esc_html( $masked_key ); ?></code>
                            <p class="description">Loaded automatically from WordPress options (zero extra setup needed).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Active Primary Model</th>
                        <td>
                            <code><?php echo esc_html( $model ); ?></code>
                            <p class="description">Fallback chain: <code>gemini-3.5-flash-lite</code> &rarr; <code>gemini-3.1-flash-lite</code> &rarr; <code>gemini-3.5-flash</code></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Connection Test</th>
                        <td>
                            <button id="btn-test-connection" class="button button-primary">
                                🔌 Test Gemini Connection Now
                            </button>
                            <div id="mrcb-test-result" style="margin-top: 10px;"></div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Workflow & Guardrails Card -->
            <div class="mrcb-card">
                <h2>🛡️ Workflow Guardrails & Scheduling Rules</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Publishing Policy</th>
                        <td>
                            <strong style="color: #047857;">🔒 Strictly Admin-Approval-Only</strong>
                            <p class="description">No post will ever be published without explicit admin approval. You maintain 100% editorial control.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Advance Buffer</th>
                        <td>
                            <strong>10 Days Ahead (Nightly at 2:00 AM IST)</strong>
                            <p class="description">Bot prepares drafts 10 days before the festival date. You have a full 10-day buffer to review, refine, or reschedule at your own pace.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Publishing Time</th>
                        <td>
                            <strong>07:00 AM IST</strong>
                            <p class="description">Approved posts go live automatically at 7:00 AM IST on their scheduled date.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Notifications</th>
                        <td>
                            <strong>Disabled (No Telegram Alerts)</strong>
                            <p class="description">As requested, no intrusive mobile alerts or Telegram bots. The WP Admin dashboard queue is your clean, self-paced workspace.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Language & Formatting</th>
                        <td>
                            <strong>Hindi (शुद्ध हिंदी) + Automatic Entity Tagging</strong>
                            <p class="description">Bolds all deities, holy places, festivals, and rituals. Italicizes mantras and shlokas. Forbids robotic AI opening phrases.</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Sidebar: System Stats -->
        <div class="mrcb-review-sidebar">
            <div class="mrcb-card">
                <h3>📊 Database & Knowledge Base</h3>
                <ul class="mrcb-stat-list">
                    <li>Festivals in Database: <strong><?php echo esc_html( $fest_count ); ?></strong></li>
                    <li>Evergreen Filler Topics: <strong><?php echo esc_html( $fill_count ); ?></strong></li>
                    <li>Internal Link Map Pages: <strong><?php echo esc_html( $link_count ); ?></strong></li>
                    <li>Plugin Version: <strong><?php echo esc_html( MRCB_VERSION ); ?></strong></li>
                </ul>
            </div>

            <div class="mrcb-card">
                <h3>💡 Quick Tips</h3>
                <p>To test the complete workflow from generation to approval, visit the <strong>Content Queue</strong> page and click <strong>"⚡ Generate for Day+10 Now"</strong>.</p>
            </div>
        </div>
    </div>
</div>
