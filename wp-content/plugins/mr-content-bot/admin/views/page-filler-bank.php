<?php
/**
 * MeraRashifal Content Bot - Filler Bank View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$t_filler = MRCB_DB_Setup::table_filler();

$cat_filter = isset( $_GET['cat'] ) ? sanitize_text_field( $_GET['cat'] ) : '';
$where = '1=1';
if ( ! empty( $cat_filter ) ) {
    $where .= $wpdb->prepare( " AND category = %s", $cat_filter );
}

$topics = $wpdb->get_results( "SELECT * FROM {$t_filler} WHERE {$where} ORDER BY priority ASC, id DESC", ARRAY_A );
?>

<div class="wrap mrcb-wrap">
    <div class="mrcb-header">
        <div class="mrcb-header-title">
            <h1>📚 Evergreen Filler Bank</h1>
            <p class="mrcb-subtitle">A pool of high-intent astrological, vastu, and devotional topics that the bot drafts whenever there is no active festival scheduled.</p>
        </div>
    </div>

    <div class="mrcb-review-container">
        <!-- Main Column: List of Fillers -->
        <div class="mrcb-review-main">
            <div class="mrcb-toolbar" style="margin-bottom: 15px;">
                <ul class="subsubsub">
                    <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-filler-bank' ); ?>" class="<?php echo empty( $cat_filter ) ? 'current' : ''; ?>">All Topics</a> |</li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-filler-bank&cat=puja_basics' ); ?>" class="<?php echo $cat_filter === 'puja_basics' ? 'current' : ''; ?>">Puja Basics</a> |</li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-filler-bank&cat=vastu' ); ?>" class="<?php echo $cat_filter === 'vastu' ? 'current' : ''; ?>">Vastu</a> |</li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-filler-bank&cat=astrology' ); ?>" class="<?php echo $cat_filter === 'astrology' ? 'current' : ''; ?>">Astrology</a> |</li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-filler-bank&cat=mythology' ); ?>" class="<?php echo $cat_filter === 'mythology' ? 'current' : ''; ?>">Mythology</a> |</li>
                    <li><a href="<?php echo admin_url( 'admin.php?page=mrcb-filler-bank&cat=sacred_items' ); ?>" class="<?php echo $cat_filter === 'sacred_items' ? 'current' : ''; ?>">Sacred Items</a></li>
                </ul>
            </div>

            <table class="wp-list-table widefat fixed striped mrcb-table">
                <thead>
                    <tr>
                        <th>Hindi Topic Title</th>
                        <th style="width: 120px;">Category</th>
                        <th style="width: 150px;">Focus Keyword</th>
                        <th style="width: 80px;">Priority</th>
                        <th style="width: 110px;">Times Used</th>
                        <th style="width: 90px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $topics ) ) : ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">No topics found in this category.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $topics as $t ) : ?>
                            <tr data-filler-id="<?php echo esc_attr( $t['id'] ); ?>">
                                <td>
                                    <strong><?php echo esc_html( $t['topic_title_hi'] ); ?></strong>
                                </td>
                                <td>
                                    <code><?php echo esc_html( $t['category'] ); ?></code>
                                </td>
                                <td>
                                    <small><?php echo esc_html( $t['focus_keyword'] ?: '—' ); ?></small>
                                </td>
                                <td>
                                    <span class="mrcb-badge priority-<?php echo esc_attr( $t['priority'] ); ?>">
                                        P-<?php echo esc_html( $t['priority'] ); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html( $t['times_used'] ); ?> times
                                    <?php if ( ! empty( $t['last_used_date'] ) ) : ?>
                                        <br><small class="text-muted"><?php echo esc_html( $t['last_used_date'] ); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="button button-small button-link-delete btn-delete-filler" data-id="<?php echo esc_attr( $t['id'] ); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Sidebar: Add New Topic Form -->
        <div class="mrcb-review-sidebar">
            <div class="mrcb-card">
                <h3>➕ Add New Evergreen Topic</h3>
                <form id="mrcb-add-filler-form">
                    <div class="mrcb-field-group">
                        <label for="filler-title"><strong>Hindi Topic Title:</strong></label>
                        <input type="text" id="filler-title" name="title_hi" class="widefat" placeholder="उदा: घर में तुलसी का पौधा सूखने पर क्या करें?" required />
                    </div>

                    <div class="mrcb-field-group">
                        <label for="filler-cat"><strong>Category:</strong></label>
                        <select id="filler-cat" name="category" class="widefat">
                            <option value="puja_basics">Puja Basics (पूजा विधि)</option>
                            <option value="vastu">Vastu Shastra (वास्तु शास्त्र)</option>
                            <option value="astrology">Astrology & Remedies (ज्योतिष व उपाय)</option>
                            <option value="sacred_items">Sacred Items & Gems (रत्न व रुद्राक्ष)</option>
                            <option value="mythology">Mythology & Katha (पौराणिक कथाएं)</option>
                            <option value="lifestyle">Daily Lifestyle (दिनचर्या व नियम)</option>
                        </select>
                    </div>

                    <div class="mrcb-field-group">
                        <label for="filler-kw"><strong>Focus Keyword:</strong></label>
                        <input type="text" id="filler-kw" name="focus_keyword" class="widefat" placeholder="उदा: tulsi ka paudha sukhne par kya kare" />
                    </div>

                    <div class="mrcb-field-group">
                        <label for="filler-prio"><strong>Priority (1 = Highest, 10 = Lowest):</strong></label>
                        <input type="number" id="filler-prio" name="priority" value="3" min="1" max="10" class="widefat" />
                    </div>

                    <button type="submit" class="button button-primary" style="margin-top: 10px; width: 100%;">
                        💾 Save Topic to Bank
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
