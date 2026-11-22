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

// Automatically purge expired unapproved drafts whose festival/event date has passed
MRCB_Scheduler::cleanup_expired_posts();

$today = current_time( 'Y-m-d' );

// Filter - default to pending_review so unapproved drafts stay in this workspace until approved
$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : 'pending_review';
$where = "1=1 AND (q.scheduled_date >= '{$today}' OR q.admin_status = 'published')";
if ( $status_filter !== 'all' && ! empty( $status_filter ) ) {
    $where .= $wpdb->prepare( " AND q.admin_status = %s", $status_filter );
}

// Fetch queue items - current & future posts, plus published items
$date_where = '';
if ( $status_filter === 'published' ) {
    $date_where = " AND q.scheduled_date >= DATE_SUB('{$today}', INTERVAL 30 DAY)";
}

$items = $wpdb->get_results(
    "SELECT q.*, f.festival_name_hi, f.festival_name_en, f.tier 
     FROM {$t_queue} q
     LEFT JOIN {$t_fest} f ON q.festival_id = f.id
     WHERE {$where} {$date_where}
     ORDER BY q.scheduled_date ASC, q.publish_time ASC
     LIMIT 100",
    ARRAY_A
);

// Metrics (only active/future posts + published posts)
$counts = $wpdb->get_results(
    "SELECT admin_status, COUNT(*) as cnt 
     FROM {$t_queue} 
     WHERE (scheduled_date >= '{$today}' OR admin_status = 'published')
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
            <p class="mrcb-subtitle">Daily schedule guaranteed (7:00 AM primary + 9:00 AM secondary) &bull; Expired past drafts auto-removed &bull; Approved posts schedule in <strong>WordPress Posts &gt; Scheduled</strong></p>
        </div>
        <div class="mrcb-header-actions">
            <button id="mrcb-btn-ensure-schedule" class="button button-primary" style="margin-right: 8px;">
                ⚡ Ensure 14-Day Schedule
            </button>
            <button id="mrcb-btn-sync-wp" class="button" style="margin-right: 8px;">
                🔄 Sync to WP Posts
            </button>
            <a href="<?php echo admin_url( 'edit.php?post_status=future' ); ?>" class="button button-secondary" target="_blank">
                📋 View WP Scheduled Posts (<?php echo $metrics['approved']; ?>)
            </a>
        </div>
    </div>

    <!-- Status Overview Metrics -->
    <div class="mrcb-stats-grid">
        <div class="mrcb-stat-card stat-pending <?php echo $status_filter === 'pending_review' ? 'active-filter' : ''; ?>">
            <div class="stat-num"><?php echo $metrics['pending_review']; ?></div>
            <div class="stat-label">Pending Review (Action Required)</div>
            <div class="stat-sub">Stays here until you approve</div>
        </div>
        <div class="mrcb-stat-card stat-approved <?php echo $status_filter === 'approved' ? 'active-filter' : ''; ?>">
            <div class="stat-num"><?php echo $metrics['approved']; ?></div>
            <div class="stat-label">Scheduled in WordPress Posts</div>
            <div class="stat-sub"><a href="<?php echo admin_url( 'edit.php?post_status=future' ); ?>" target="_blank" style="color:inherit; text-decoration:underline;">View in Posts &gt; Scheduled &rarr;</a></div>
        </div>
        <div class="mrcb-stat-card stat-published <?php echo $status_filter === 'published' ? 'active-filter' : ''; ?>">
            <div class="stat-num"><?php echo $metrics['published']; ?></div>
            <div class="stat-label">Published Live</div>
            <div class="stat-sub"><a href="<?php echo admin_url( 'edit.php?post_status=publish' ); ?>" target="_blank" style="color:inherit; text-decoration:underline;">View in Posts &gt; Published &rarr;</a></div>
        </div>
        <div class="mrcb-stat-card stat-total">
            <div class="stat-num"><?php echo count( $items ); ?></div>
            <div class="stat-label">Posts in Active Filter</div>
            <div class="stat-sub">Filter: <?php echo esc_html( ucwords( str_replace( '_', ' ', $status_filter ) ) ); ?></div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="mrcb-toolbar">
        <ul class="subsubsub">
            <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&status=pending_review' ); ?>" class="<?php echo $status_filter === 'pending_review' ? 'current' : ''; ?>">Needs Review (Pending) <span class="count">(<?php echo $metrics['pending_review']; ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&status=approved' ); ?>" class="<?php echo $status_filter === 'approved' ? 'current' : ''; ?>">Scheduled in WP <span class="count">(<?php echo $metrics['approved']; ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&status=published' ); ?>" class="<?php echo $status_filter === 'published' ? 'current' : ''; ?>">Published <span class="count">(<?php echo $metrics['published']; ?>)</span></a> |</li>
            <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&status=all' ); ?>" class="<?php echo $status_filter === 'all' ? 'current' : ''; ?>">All Queue Items</a></li>
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
                <th style="width: 140px;">Status</th>
                <th style="width: 230px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $items ) ) : ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 35px 20px;">
                        <?php if ( $status_filter === 'pending_review' ) : ?>
                            <p style="font-size: 15px; margin: 0 0 10px 0;">🎉 <strong>All caught up! No drafts currently waiting for review.</strong></p>
                            <p style="color: #64748b; margin: 0 0 15px 0;">Any posts you approved are already scheduled in WordPress under <a href="<?php echo admin_url( 'edit.php?post_status=future' ); ?>" target="_blank"><strong>Posts &gt; Scheduled</strong></a>.</p>
                            <button id="mrcb-trigger-advance-cron" class="button button-primary">⚡ Generate Draft for Day+10 Now</button>
                        <?php else : ?>
                            <em>No content in queue matching this filter.</em>
                        <?php endif; ?>
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
                ?>
                    <tr data-id="<?php echo esc_attr( $it['id'] ); ?>">
                        <td>
                            <strong><?php echo esc_html( wp_date( 'd M Y', strtotime( $it['scheduled_date'] ) ) ); ?></strong>
                            <br>
                            <span class="mrcb-time-badge" style="display:inline-block; margin:3px 0; padding:2px 6px; font-size:11px; background:#e0f2fe; color:#0369a1; border-radius:4px; font-weight:600;">
                                ⏰ <?php echo esc_html( wp_date( 'h:i A', strtotime( $it['publish_time'] ?: '07:00:00' ) ) ); ?>
                            </span>
                            <br><?php echo $timing_label; ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&action=review&id=' . $it['id'] ); ?>" style="text-decoration: none;">
                                <strong class="mrcb-post-title"><?php echo esc_html( $it['topic_title_hi'] ); ?></strong>
                            </a>
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
                            <?php if ( $it['admin_status'] === 'approved' ) : ?>
                                <span class="mrcb-badge status-approved">Scheduled in WP</span>
                                <?php if ( ! empty( $it['wp_post_id'] ) ) : ?>
                                    <br><a href="<?php echo admin_url( 'post.php?post=' . $it['wp_post_id'] . '&action=edit' ); ?>" target="_blank" style="font-size:11px; font-weight:600;">Post #<?php echo $it['wp_post_id']; ?> &rarr;</a>
                                <?php endif; ?>
                            <?php elseif ( $it['admin_status'] === 'published' ) : ?>
                                <span class="mrcb-badge status-published">Published Live</span>
                                <?php if ( ! empty( $it['wp_post_id'] ) ) : ?>
                                    <br><a href="<?php echo get_permalink( $it['wp_post_id'] ); ?>" target="_blank" style="font-size:11px;">View Live &nearr;</a>
                                <?php endif; ?>
                            <?php elseif ( $it['admin_status'] === 'pending_review' ) : ?>
                                <span class="mrcb-badge status-pending_review">Needs Review</span>
                            <?php else : ?>
                                <span class="mrcb-badge <?php echo $status_class; ?>">
                                    <?php echo esc_html( ucwords( str_replace( '_', ' ', $it['admin_status'] ) ) ); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="mrcb-actions-cell">
                            <?php if ( $it['admin_status'] === 'pending_review' ) : ?>
                                <a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&action=review&id=' . $it['id'] ); ?>" class="button button-primary button-small">
                                    👁️ Review & Approve
                                </a>
                                <button class="button button-small btn-quick-approve" data-id="<?php echo esc_attr( $it['id'] ); ?>" title="Approve and Schedule in WordPress Posts">
                                    ✅ Schedule
                                </button>
                                <button class="button button-small btn-regenerate" data-id="<?php echo esc_attr( $it['id'] ); ?>" title="Regenerate with Gemini">
                                    🔄
                                </button>
                            <?php elseif ( $it['admin_status'] === 'approved' ) : ?>
                                <?php if ( ! empty( $it['wp_post_id'] ) ) : ?>
                                    <a href="<?php echo admin_url( 'post.php?post=' . $it['wp_post_id'] . '&action=edit' ); ?>" class="button button-small button-primary" target="_blank">
                                        ✏️ Edit in Posts
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&action=review&id=' . $it['id'] ); ?>" class="button button-small">
                                    👁️ Review
                                </a>
                                <button class="button button-small btn-publish-now" data-id="<?php echo esc_attr( $it['id'] ); ?>" title="Publish immediately to site">
                                    🚀 Publish
                                </button>
                            <?php elseif ( $it['admin_status'] === 'published' ) : ?>
                                <?php if ( ! empty( $it['wp_post_id'] ) ) : ?>
                                    <a href="<?php echo get_permalink( $it['wp_post_id'] ); ?>" class="button button-small" target="_blank">
                                        👁️ View Live ↗
                                    </a>
                                    <a href="<?php echo admin_url( 'post.php?post=' . $it['wp_post_id'] . '&action=edit' ); ?>" class="button button-small" target="_blank">
                                        ✏️ Edit
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&action=review&id=' . $it['id'] ); ?>" class="button button-small">
                                    Queue Details
                                </a>
                            <?php else : ?>
                                <a href="<?php echo admin_url( 'admin.php?page=mrcb-queue&action=review&id=' . $it['id'] ); ?>" class="button button-small">
                                    👁️ Review
                                </a>
                                <button class="button button-small btn-regenerate" data-id="<?php echo esc_attr( $it['id'] ); ?>" title="Regenerate with Gemini">
                                    🔄
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
