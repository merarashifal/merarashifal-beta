<?php
/**
 * MeraRashifal Content Bot - Festivals Coverage & Tier Manager
 * Fully supports 1-Year Upcoming Festivals view, Main & Secondary tiers, and complete pagination.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$t_fest = MRCB_DB_Setup::table_festivals();

$today = current_time( 'Y-m-d' );
$plus_one_year = date( 'Y-m-d', strtotime( '+1 year', strtotime( $today ) ) );
$plus_30       = date( 'Y-m-d', strtotime( '+30 days', strtotime( $today ) ) );
$plus_90       = date( 'Y-m-d', strtotime( '+90 days', strtotime( $today ) ) );

// Filters
$scope         = isset( $_GET['scope'] ) ? sanitize_text_field( $_GET['scope'] ) : 'upcoming_1_year';
$tier_filter   = isset( $_GET['tier'] ) ? sanitize_text_field( $_GET['tier'] ) : '';
$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
$search        = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$per_page_raw  = isset( $_GET['per_page'] ) ? sanitize_text_field( $_GET['per_page'] ) : '50';
$per_page      = ( $per_page_raw === 'all' ) ? 9999 : max( 10, min( 500, (int) $per_page_raw ) );
$paged         = max( 1, (int) ( $_GET['paged'] ?? 1 ) );

// Construct WHERE clause
$where = "1=1";

if ( $scope === 'upcoming_1_year' ) {
    $where .= $wpdb->prepare( " AND festival_date >= %s AND festival_date <= %s", $today, $plus_one_year );
} elseif ( $scope === 'upcoming_30' ) {
    $where .= $wpdb->prepare( " AND festival_date >= %s AND festival_date <= %s", $today, $plus_30 );
} elseif ( $scope === 'upcoming_90' ) {
    $where .= $wpdb->prepare( " AND festival_date >= %s AND festival_date <= %s", $today, $plus_90 );
} elseif ( $scope === 'year_2026' ) {
    $where .= " AND festival_date >= '2026-01-01' AND festival_date <= '2026-12-31'";
} elseif ( $scope === 'year_2027' ) {
    $where .= " AND festival_date >= '2027-01-01' AND festival_date <= '2027-12-31'";
} elseif ( $scope === 'year_2028' ) {
    $where .= " AND festival_date >= '2028-01-01' AND festival_date <= '2028-12-31'";
}
// 'all' has no date restrictions

if ( $tier_filter !== '' ) {
    $where .= $wpdb->prepare( " AND tier = %d", (int) $tier_filter );
}
if ( ! empty( $status_filter ) ) {
    $where .= $wpdb->prepare( " AND coverage_status = %s", $status_filter );
}
if ( ! empty( $search ) ) {
    $where .= $wpdb->prepare(
        " AND (festival_name_en LIKE %s OR festival_name_hi LIKE %s)",
        '%' . $wpdb->esc_like( $search ) . '%',
        '%' . $wpdb->esc_like( $search ) . '%'
    );
}

// Scope Counts for Quick Tabs
$base_scope_where = "1=1";
if ( $scope === 'upcoming_1_year' ) {
    $base_scope_where .= $wpdb->prepare( " AND festival_date >= %s AND festival_date <= %s", $today, $plus_one_year );
} elseif ( $scope === 'upcoming_30' ) {
    $base_scope_where .= $wpdb->prepare( " AND festival_date >= %s AND festival_date <= %s", $today, $plus_30 );
} elseif ( $scope === 'upcoming_90' ) {
    $base_scope_where .= $wpdb->prepare( " AND festival_date >= %s AND festival_date <= %s", $today, $plus_90 );
} elseif ( $scope === 'year_2026' ) {
    $base_scope_where .= " AND festival_date >= '2026-01-01' AND festival_date <= '2026-12-31'";
} elseif ( $scope === 'year_2027' ) {
    $base_scope_where .= " AND festival_date >= '2027-01-01' AND festival_date <= '2027-12-31'";
} elseif ( $scope === 'year_2028' ) {
    $base_scope_where .= " AND festival_date >= '2028-01-01' AND festival_date <= '2028-12-31'";
}

$counts_raw = $wpdb->get_results(
    "SELECT tier, coverage_status, COUNT(*) as cnt FROM {$t_fest} WHERE {$base_scope_where} GROUP BY tier, coverage_status",
    ARRAY_A
);

$scope_counts = [
    'total'  => 0,
    'tier_1' => 0,
    'tier_2' => 0,
    'tier_3' => 0,
    'tier_4' => 0,
    'tier_5' => 0,
    'active' => 0,
];

foreach ( $counts_raw as $cr ) {
    $c = (int) $cr['cnt'];
    $t = (int) $cr['tier'];
    $st = $cr['coverage_status'];
    $scope_counts['total'] += $c;
    if ( isset( $scope_counts['tier_' . $t] ) ) {
        $scope_counts['tier_' . $t] += $c;
    }
    if ( $st === 'active' ) {
        $scope_counts['active'] += $c;
    }
}

// Pagination calculation
$total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t_fest} WHERE {$where}" );
$total_pages = ( $per_page >= 9999 ) ? 1 : max( 1, (int) ceil( $total_items / $per_page ) );
$paged       = min( $paged, $total_pages );
$offset      = ( $paged - 1 ) * $per_page;

$festivals = $wpdb->get_results(
    "SELECT * FROM {$t_fest} 
     WHERE {$where} 
     ORDER BY festival_date ASC 
     LIMIT {$offset}, {$per_page}",
    ARRAY_A
);

// Helper for pagination link preserving query params
function mrcb_fest_page_url( $page_num ) {
    $params = $_GET;
    $params['paged'] = $page_num;
    return add_query_arg( $params, admin_url( 'admin.php' ) );
}
?>

<div class="wrap mrcb-wrap">
    <div class="mrcb-header">
        <div class="mrcb-header-title">
            <h1>🗓️ Festivals Coverage & Tier Manager</h1>
            <p class="mrcb-subtitle">
                Comprehensive 1-Year festival calendar. <strong>Tier 1 defines Main Mega Festivals</strong> (multi-post campaigns) and <strong>Tier 2 defines Secondary Major Festivals</strong> (focused 1-2 posts).
            </p>
        </div>
        <div class="mrcb-header-actions" style="display: flex; gap: 10px; align-items: center;">
            <button type="button" id="btn-sync-festivals" class="button button-primary" style="background:#0284c7; border-color:#0369a1; display:flex; align-items:center; gap:6px;">
                🔄 Sync 1-Year Festivals from Calendar
            </button>
        </div>
    </div>

    <!-- Tier Guide Banner (Main vs Secondary Definitions) -->
    <div class="mrcb-tier-guide">
        <div class="tier-pill tier-1">
            <strong>Tier 1 (Main Festivals / Mega Campaigns):</strong> Diwali (5), Navratri (14), Dhanteras (2), Holi (5), Maha Shivratri (4), Karwa Chauth (3), Chhath Puja (3), Janmashtami (4), Ram Navami (3), Makar Sankranti (3), Ganesh Chaturthi (3)
        </div>
        <div class="tier-pill tier-2">
            <strong>Tier 2 (Secondary Festivals / Major Campaigns):</strong> Hanuman Jayanti (2), Vasant Panchami (2), Govardhan Puja (1), Bhai Dooj (1), Tulsi Vivah (1), Dev Uthani Ekadashi (2), Ahoi Ashtami (1), Akshaya Tritiya (2), Guru Purnima (2), etc.
        </div>
        <div class="tier-pill tier-3">
            <strong>Tier 3 (Regular Vrats & Parvas):</strong> All 24 Ekadashis (1), Pradosh Vrats (1), Sankashti Chaturthi (1), Purnima (1), Sankranti (1)
        </div>
        <div class="tier-pill tier-4">
            <strong>Tier 4 (Regional Observances):</strong> Onam, Pongal, Ugadi, Bihu, Gudi Padwa, Kali Puja (1 post)
        </div>
        <div class="tier-pill tier-5">
            <strong>Tier 5 (Skipped / Astronomical):</strong> Minor astronomical markers, eclipse days, administrative entries (0 posts)
        </div>
    </div>

    <!-- Quick Scope & Tier Counters -->
    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:15px; align-items:center; font-size:13px;">
        <span style="font-weight:600; color:#475569; margin-right:4px;">Scope Summary:</span>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'mrcb-festivals', 'scope' => $scope ], admin_url( 'admin.php' ) ) ); ?>" class="button button-small <?php echo ( $tier_filter === '' && $status_filter === '' ) ? 'button-primary' : ''; ?>">
            All (<?php echo esc_html( $scope_counts['total'] ); ?>)
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'mrcb-festivals', 'scope' => $scope, 'tier' => '1' ], admin_url( 'admin.php' ) ) ); ?>" class="button button-small <?php echo ( $tier_filter === '1' ) ? 'button-primary' : ''; ?>" style="border-left: 3px solid #d97706;">
            ⭐ Tier 1: Main (<?php echo esc_html( $scope_counts['tier_1'] ); ?>)
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'mrcb-festivals', 'scope' => $scope, 'tier' => '2' ], admin_url( 'admin.php' ) ) ); ?>" class="button button-small <?php echo ( $tier_filter === '2' ) ? 'button-primary' : ''; ?>" style="border-left: 3px solid #2563eb;">
            🎯 Tier 2: Secondary (<?php echo esc_html( $scope_counts['tier_2'] ); ?>)
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'mrcb-festivals', 'scope' => $scope, 'tier' => '3' ], admin_url( 'admin.php' ) ) ); ?>" class="button button-small <?php echo ( $tier_filter === '3' ) ? 'button-primary' : ''; ?>" style="border-left: 3px solid #059669;">
            📿 Tier 3: Vrats (<?php echo esc_html( $scope_counts['tier_3'] ); ?>)
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'mrcb-festivals', 'scope' => $scope, 'tier' => '4' ], admin_url( 'admin.php' ) ) ); ?>" class="button button-small <?php echo ( $tier_filter === '4' ) ? 'button-primary' : ''; ?>" style="border-left: 3px solid #9333ea;">
            🗺️ Tier 4: Regional (<?php echo esc_html( $scope_counts['tier_4'] ); ?>)
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'mrcb-festivals', 'scope' => $scope, 'tier' => '5' ], admin_url( 'admin.php' ) ) ); ?>" class="button button-small <?php echo ( $tier_filter === '5' ) ? 'button-primary' : ''; ?>" style="border-left: 3px solid #64748b;">
            ⏸️ Tier 5: Skipped (<?php echo esc_html( $scope_counts['tier_5'] ); ?>)
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'mrcb-festivals', 'scope' => $scope, 'status' => 'active' ], admin_url( 'admin.php' ) ) ); ?>" class="button button-small <?php echo ( $status_filter === 'active' ) ? 'button-primary' : ''; ?>" style="color:#059669; font-weight:600;">
            ✅ Active Coverage (<?php echo esc_html( $scope_counts['active'] ); ?>)
        </a>
    </div>

    <!-- Filter Form -->
    <div class="mrcb-toolbar">
        <form method="get" action="<?php echo admin_url( 'admin.php' ); ?>" class="mrcb-filter-form" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; width:100%;">
            <input type="hidden" name="page" value="mrcb-festivals" />

            <!-- Date Scope Selector (Default: upcoming 1 year) -->
            <label style="font-weight:600; color:#334155;">Time Horizon:</label>
            <select name="scope" style="font-weight:600; color:#1e40af;">
                <option value="upcoming_1_year" <?php selected( $scope, 'upcoming_1_year' ); ?>>📅 Upcoming 1 Year (Next 365 Days) [Recommended]</option>
                <option value="upcoming_30" <?php selected( $scope, 'upcoming_30' ); ?>>⚡ Next 30 Days</option>
                <option value="upcoming_90" <?php selected( $scope, 'upcoming_90' ); ?>>🗓️ Next 90 Days</option>
                <option value="year_2026" <?php selected( $scope, 'year_2026' ); ?>>Full Year 2026</option>
                <option value="year_2027" <?php selected( $scope, 'year_2027' ); ?>>Full Year 2027</option>
                <option value="year_2028" <?php selected( $scope, 'year_2028' ); ?>>Full Year 2028</option>
                <option value="all" <?php selected( $scope, 'all' ); ?>>All Dates (Full Archive)</option>
            </select>

            <!-- Tier Filter -->
            <select name="tier">
                <option value="">All Tiers</option>
                <option value="1" <?php selected( $tier_filter, '1' ); ?>>⭐ Tier 1 (Main - Mega)</option>
                <option value="2" <?php selected( $tier_filter, '2' ); ?>>🎯 Tier 2 (Secondary - Major)</option>
                <option value="3" <?php selected( $tier_filter, '3' ); ?>>📿 Tier 3 (Regular Vrat)</option>
                <option value="4" <?php selected( $tier_filter, '4' ); ?>>🗺️ Tier 4 (Regional)</option>
                <option value="5" <?php selected( $tier_filter, '5' ); ?>>⏸️ Tier 5 (Skipped)</option>
            </select>

            <!-- Status Filter -->
            <select name="status">
                <option value="">All Statuses</option>
                <option value="active" <?php selected( $status_filter, 'active' ); ?>>Active Coverage Only</option>
                <option value="skipped" <?php selected( $status_filter, 'skipped' ); ?>>Skipped Only</option>
            </select>

            <!-- Search -->
            <input type="search" name="s" placeholder="Search festival name..." value="<?php echo esc_attr( $search ); ?>" style="min-width: 180px;" />

            <!-- Per Page -->
            <select name="per_page">
                <option value="50" <?php selected( $per_page_raw, '50' ); ?>>50 / page</option>
                <option value="100" <?php selected( $per_page_raw, '100' ); ?>>100 / page</option>
                <option value="200" <?php selected( $per_page_raw, '200' ); ?>>200 / page</option>
                <option value="all" <?php selected( $per_page_raw, 'all' ); ?>>Show All (No Limit)</option>
            </select>

            <button type="submit" class="button button-primary">Filter</button>
            <?php if ( $scope !== 'upcoming_1_year' || ! empty( $tier_filter ) || ! empty( $status_filter ) || ! empty( $search ) || $per_page_raw !== '50' ) : ?>
                <a href="<?php echo admin_url( 'admin.php?page=mrcb-festivals' ); ?>" class="button">Reset</a>
            <?php endif; ?>

            <!-- Result Count Badge -->
            <span style="margin-left:auto; font-size:12px; color:#64748b;">
                Showing <strong><?php echo esc_html( count( $festivals ) ); ?></strong> of <strong><?php echo esc_html( $total_items ); ?></strong> festivals
            </span>
        </form>
    </div>

    <!-- Table -->
    <table class="wp-list-table widefat fixed striped mrcb-table">
        <thead>
            <tr>
                <th style="width: 140px;">Festival Date</th>
                <th>Festival Name (Hindi / English)</th>
                <th style="width: 170px;">Tier Level</th>
                <th style="width: 100px;">Post Count</th>
                <th style="width: 120px;">Coverage</th>
                <th style="width: 130px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $festivals ) ) : ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px;">
                        <em>No festivals matched your filter criteria for this time range.</em>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $festivals as $f ) : 
                    $f_ts = strtotime( $f['festival_date'] );
                    $days_away = (int) round( ( $f_ts - strtotime( $today ) ) / 86400 );
                ?>
                    <tr data-fest-id="<?php echo esc_attr( $f['id'] ); ?>">
                        <td>
                            <strong><?php echo esc_html( wp_date( 'd M Y', $f_ts ) ); ?></strong>
                            <br><small class="text-muted"><?php echo esc_html( wp_date( 'l', $f_ts ) ); ?></small>
                            <?php if ( $days_away === 0 ) : ?>
                                <br><span class="badge-timing today">Today</span>
                            <?php elseif ( $days_away === 1 ) : ?>
                                <br><span class="badge-timing tomorrow">Tomorrow</span>
                            <?php elseif ( $days_away > 1 && $days_away <= 10 ) : ?>
                                <br><span class="badge-timing tomorrow" style="background:#fef3c7; color:#b45309;">In <?php echo esc_html( $days_away ); ?> days</span>
                            <?php elseif ( $days_away < 0 ) : ?>
                                <br><span class="badge-timing past">Past</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="font-size: 15px; color:#1e293b;"><?php echo esc_html( $f['festival_name_hi'] ?: $f['festival_name_en'] ); ?></strong>
                            <?php if ( ! empty( $f['festival_name_en'] ) && $f['festival_name_en'] !== $f['festival_name_hi'] ) : ?>
                                <br><small class="text-muted" style="font-weight:500;"><?php echo esc_html( $f['festival_name_en'] ); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select class="fest-tier-select" data-id="<?php echo esc_attr( $f['id'] ); ?>" style="width:100%; font-size:12px;">
                                <option value="1" <?php selected( $f['tier'], 1 ); ?>>⭐ Tier 1 (Main - Mega)</option>
                                <option value="2" <?php selected( $f['tier'], 2 ); ?>>🎯 Tier 2 (Secondary - Major)</option>
                                <option value="3" <?php selected( $f['tier'], 3 ); ?>>📿 Tier 3 (Regular Vrat)</option>
                                <option value="4" <?php selected( $f['tier'], 4 ); ?>>🗺️ Tier 4 (Regional)</option>
                                <option value="5" <?php selected( $f['tier'], 5 ); ?>>⏸️ Tier 5 (Skip)</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="fest-count-input" data-id="<?php echo esc_attr( $f['id'] ); ?>" value="<?php echo esc_attr( $f['post_count'] ); ?>" min="0" max="20" style="width: 65px;" />
                        </td>
                        <td>
                            <select class="fest-status-select" data-id="<?php echo esc_attr( $f['id'] ); ?>" style="width:100%; font-size:12px;">
                                <option value="active" <?php selected( $f['coverage_status'], 'active' ); ?>>Active</option>
                                <option value="skipped" <?php selected( $f['coverage_status'], 'skipped' ); ?>>Skipped</option>
                            </select>
                        </td>
                        <td>
                            <div style="display:flex; gap:4px; align-items:center;">
                                <button type="button" class="button button-small btn-save-fest" data-id="<?php echo esc_attr( $f['id'] ); ?>" title="Save tier, count, and status">
                                    💾 Save
                                </button>
                                <?php if ( (int) $f['tier'] < 5 && $f['coverage_status'] === 'active' ) : ?>
                                    <button type="button" class="button button-small btn-plan-campaign" data-id="<?php echo esc_attr( $f['id'] ); ?>" title="Plan & Queue Campaign Posts according to Tier" style="background:#f0fdf4; border-color:#86efac; color:#166534; font-weight:600;">
                                        ⚡ Plan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <?php if ( $total_pages > 1 ) : ?>
        <div class="tablenav" style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; padding:8px 12px; background:#fff; border:1px solid #e2e8f0; border-radius:6px;">
            <div class="tablenav-pages" style="display:flex; align-items:center; gap:6px;">
                <span class="displaying-num" style="margin-right:10px;">
                    Showing <strong><?php echo esc_html( $offset + 1 ); ?>–<?php echo esc_html( min( $offset + $per_page, $total_items ) ); ?></strong> of <strong><?php echo esc_html( $total_items ); ?></strong> festivals
                </span>

                <?php if ( $paged > 1 ) : ?>
                    <a class="button button-small" href="<?php echo esc_url( mrcb_fest_page_url( 1 ) ); ?>">« First</a>
                    <a class="button button-small" href="<?php echo esc_url( mrcb_fest_page_url( $paged - 1 ) ); ?>">‹ Prev</a>
                <?php endif; ?>

                <span class="paging-input" style="font-size:13px; font-weight:600; margin:0 6px;">
                    Page <?php echo esc_html( $paged ); ?> of <?php echo esc_html( $total_pages ); ?>
                </span>

                <?php if ( $paged < $total_pages ) : ?>
                    <a class="button button-small" href="<?php echo esc_url( mrcb_fest_page_url( $paged + 1 ) ); ?>">Next ›</a>
                    <a class="button button-small" href="<?php echo esc_url( mrcb_fest_page_url( $total_pages ) ); ?>">Last »</a>
                <?php endif; ?>
            </div>

            <div>
                <small class="text-muted">Tip: Change "50 / page" to "100" or "All" in the filter above to view more at once.</small>
            </div>
        </div>
    <?php endif; ?>
</div>
