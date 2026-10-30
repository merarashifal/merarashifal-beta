<?php
/**
 * MeraRashifal Content Bot - Queue Dashboard View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$t_queue = MRCB_DB_Setup::table_queue();
$t_fest  = MRCB_DB_Setup::table_festivals();

// Filter
$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
$where = '1=1';
if ( ! empty( $status_filter ) ) {
    $where .= $wpdb->prepare( " AND q.admin_status = %s", $status_filter );
}

// Fetch queue items for next 30 days and past 3 days
$today = current_time( 'Y-m-d' );
$items = $wpdb->get_results(
    "SELECT q.*, f.festival_name_hi, f.festival_name_en, f.tier 
     FROM {$t_queue} q
     LEFT JOIN {$t_fest} f ON q.festival_id = f.id
     WHERE {$where} AND q.scheduled_date >= DATE_SUB('{$today}', INTERVAL 3 DAY)
     ORDER BY q.scheduled_date ASC
     LIMIT 50",
    ARRAY_A
);

// Metrics
$counts = $wpdb->get_results(
    "SELECT admin_status, COUNT(*) as cnt 
     FROM {$t_queue} 
     WHERE scheduled_date >= '{$today}' AND scheduled_date <= DATE_ADD('{$today}', INTERVAL 30 DAY)
     GROUP BY admin_status",
    ARRAY_A
);
$metrics = [ 'pending_review' => 0, 'approved' => 0, 'published' => 0, 'rejected' => 0 ];
foreach ( $counts as $c ) {
    $metrics[ $c['admin_status'] ] = (int) $c['cnt'];
}
?>

<div class="wrap mrcb-wrap">
    <div class="mrcb-header">
        <div class="mrcb-header-title">
            <h1>✍️ MeraRashifal Content Bot — Content Queue</h1>
            <p class="mrcb-subtitle">10-Day Advance Hindi Festival Content &bull; Admin Approval Workflow &bull; Gemini AI Engine</p>
        </div>
        <div class="mrcb-header-actions">
            <button id="mrcb-trigger-advance-cron" class="button button-primary">
                ⚡ Generate for Day+10 Now
            </button>
        </div>
    </div>

    <!-- Status Overview Metrics -->
    <div class="mrcb-stats-grid">
        <div class="mrcb-stat-card stat-pending">
            <div class="stat-num"><?php echo $metrics['pending_review']; ?></div>
            <div class="stat-label">Pending Review (Action Required)</div>
            <div class="stat-sub">Review & approve at your own pace</div>
        </div>
        <div class="mrcb-stat-card stat-approved">
            <div class="stat-num"><?php echo $metrics['approved']; ?></div>
            <div class="stat-label">Approved (Scheduled)</div>
            <div class="stat-sub">Ready to publish at 7:00 AM IST</div>
        </div>
        <div class="mrcb-stat-card stat-published">
            <div class="stat-num"><?php echo $metrics['published']; ?></div>
            <div class="stat-label">Published Live</div>
            <div class="stat-sub">Active on merarashifal.com</div>
        </div>
        <div class="mrcb-stat-card stat-total">
            <div class="stat-num"><?php echo count( $items ); ?></div>
            <div class="stat-label">Visible Posts in Window</div>
            <div class="stat-sub">Next 30 days schedule</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="mrcb-toolbar">
        <ul class="subsubsub">
            <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-queue' ); ?>" class="<?php echo empty( $status_filter ) ? 'current' : ''; ?>">All</a> |</li>
            <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&status=pending_review' ); ?>" class="<?php echo $status_filter === 'pending_review' ? 'current' : ''; ?>">Pending Review <span class="count">(<?php echo $metrics['pending_review']; ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&status=approved' ); ?>" class="<?php echo $status_filter === 'approved' ? 'current' : ''; ?>">Approved <span class="count">(<?php echo $metrics['approved']; ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&status=published' ); ?>" class="<?php echo $status_filter === 'published' ? 'current' : ''; ?>">Published <span class="count">(<?php echo $metrics['published']; ?>)</span></a></li>
        </ul>

        <div class="mrcb-quick-generate">
            <label for="mrcb-custom-date"><strong>Generate Date:</strong></label>
            <input type="date" id="mrcb-custom-date" value="<?php echo wp_date( 'Y-m-d', strtotime( '+10 days' ) ); ?>" />
            <button id="mrcb-btn-generate-date" class="button">🚀 Draft for Date</button>
        </div>
    </div>

    <!-- Queue Table -->
    <table class="wp-list-table widefat fixed striped table-view-list mrcb-table">
        <thead>
            <tr>
                <th style="width: 120px;">Scheduled Date</th>
                <th>Hindi Topic Title & Angle</th>
                <th style="width: 130px;">Type / Tier</th>
                <th style="width: 180px;">Focus Keyword</th>
                <th style="width: 130px;">Status</th>
                <th style="width: 220px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $items ) ) : ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px;">
                        <em>No content in queue for this filter. Click <strong>"Generate for Day+10 Now"</strong> above to start your 10-day buffer!</em>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $items as $it ) : 
                    $diff = (int) round( ( strtotime( $it['scheduled_date'] ) - strtotime( $today ) ) / DAY_IN_SECONDS );
                    $timing_label = '';
                    if ( $diff === 0 ) {
                        $timing_label = '<span class="badge-timing today">Today</span>';
                    } elseif ( $diff === 1 ) {
                        $timing_label = '<span class="badge-timing tomorrow">Tomorrow</span>';
                    } elseif ( $diff > 1 ) {
                        $timing_label = "<span class=\"badge-timing future\">In {$diff} days</span>";
                    } else {
                        $timing_label = '<span class="badge-timing past">Past</span>';
                    }

                    $status_class = 'status-' . esc_attr( $it['admin_status'] );
                    $status_text  = ucwords( str_replace( '_', ' ', $it['admin_status'] ) );
                ?>
                    <tr data-id="<?php echo esc_attr( $it['id'] ); ?>">
                        <td>
                            <strong><?php echo esc_html( wp_date( 'd M Y', strtotime( $it['scheduled_date'] ) ) ); ?></strong>
                            <br><?php echo $timing_label; ?>
                        </td>
                        <td>
                            <strong class="mrcb-post-title"><?php echo esc_html( $it['topic_title_hi'] ); ?></strong>
                            <div class="mrcb-meta-sub">
                                <span class="mrcb-angle-tag">Angle: <?php echo esc_html( $it['topic_angle'] ); ?></span>
                                <?php if ( ! empty( $it['festival_name_hi'] ) ) : ?>
                                    &bull; <span>पर्व: <?php echo esc_html( $it['festival_name_hi'] ); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ( $it['post_type'] === 'festival' ) : ?>
                                <span class="badge-type festival">Festival (Tier <?php echo esc_html( $it['tier'] ?: '1' ); ?>)</span>
                            <?php else : ?>
                                <span class="badge-type filler">Evergreen Filler</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo esc_html( $it['focus_keyword'] ?: '—' ); ?></code>
                            <?php if ( ! empty( $it['wp_slug'] ) ) : ?>
                                <br><small class="text-muted">/<?php echo esc_html( $it['wp_slug'] ); ?>/</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="mrcb-badge <?php echo $status_class; ?>">
                                <?php echo esc_html( $status_text ); ?>
                            </span>
                        </td>
                        <td class="mrcb-actions-cell">
                            <a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&action=review&id=' . $it['id'] ); ?>" class="button button-primary button-small">
                                👁️ Review & Edit
                            </a>

                            <?php if ( $it['admin_status'] === 'pending_review' ) : ?>
                                <button class="button button-small btn-quick-approve" data-id="<?php echo esc_attr( $it['id'] ); ?>" title="Approve as-is">
                                    ✅
                                </button>
                            <?php endif; ?>

                            <?php if ( $it['admin_status'] === 'approved' && empty( $it['wp_post_id'] ) ) : ?>
                                <button class="button button-small btn-publish-now" data-id="<?php echo esc_attr( $it['id'] ); ?>" title="Publish immediately to site">
                                    🚀 Publish
                                </button>
                            <?php endif; ?>

                            <button class="button button-small btn-regenerate" data-id="<?php echo esc_attr( $it['id'] ); ?>" title="Regenerate with Gemini">
                                🔄
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
