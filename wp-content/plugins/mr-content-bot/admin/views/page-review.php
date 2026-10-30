<?php
/**
 * MeraRashifal Content Bot - Draft Review & Editor View
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$t_queue = MRCB_DB_Setup::table_queue();
$t_fest  = MRCB_DB_Setup::table_festivals();

$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
$item = $wpdb->get_row( $wpdb->prepare( "SELECT q.*, f.festival_name_hi, f.festival_name_en, f.tier FROM {$t_queue} q LEFT JOIN {$t_fest} f ON q.festival_id = f.id WHERE q.id = %d", $id ), ARRAY_A );

if ( ! $item ) {
    echo '<div class="notice notice-error"><p>Queue item not found.</p></div>';
    return;
}

$draft_markdown = ! empty( $item['admin_draft'] ) ? $item['admin_draft'] : ( $item['ai_draft_tagged'] ?: $item['ai_draft'] );
$draft_html     = MRCB_Writer::markdown_to_html( $draft_markdown );
$meta_title     = ! empty( $item['admin_meta_title'] ) ? $item['admin_meta_title'] : ( $item['ai_meta_title'] ?: $item['topic_title_hi'] );
$meta_desc      = ! empty( $item['admin_meta_desc'] ) ? $item['admin_meta_desc'] : $item['ai_meta_desc'];
$slug           = $item['wp_slug'] ?: sanitize_title( $item['focus_keyword'] );
?>

<div class="wrap mrcb-wrap mrcb-review-wrap">
    <div class="mrcb-review-header">
        <a href="<?php echo admin_url( 'admin.php?page=mrcb-queue' ); ?>" class="button">&larr; Back to Queue</a>
        <h2>Draft Review: <?php echo esc_html( $item['topic_title_hi'] ); ?></h2>
        <span class="mrcb-badge status-<?php echo esc_attr( $item['admin_status'] ); ?>">
            Status: <?php echo esc_html( ucwords( str_replace( '_', ' ', $item['admin_status'] ) ) ); ?>
        </span>
    </div>

    <div class="mrcb-review-container">
        <!-- Main Column: Editor & Preview -->
        <div class="mrcb-review-main">
            <div class="mrcb-card">
                <div class="mrcb-editor-nav">
                    <button type="button" class="mrcb-tab-btn active" data-tab="preview">👁️ Rendered Preview</button>
                    <button type="button" class="mrcb-tab-btn" data-tab="editor">✏️ Edit Text (Markdown)</button>
                </div>

                <!-- Tab 1: Rendered HTML Preview -->
                <div id="mrcb-tab-preview" class="mrcb-tab-content active">
                    <div class="mrcb-preview-body">
                        <h1 class="preview-h1"><?php echo esc_html( $meta_title ); ?></h1>
                        <div class="preview-content">
                            <?php echo $draft_html; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Raw Editor -->
                <div id="mrcb-tab-editor" class="mrcb-tab-content">
                    <p class="description">You can freely edit the text, paragraphs, or links below. When you click "Approve Draft", your edited version will be saved and scheduled.</p>
                    <textarea id="mrcb-draft-textarea" rows="28" class="large-text code"><?php echo esc_textarea( $draft_markdown ); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Right Column: SEO Meta & Workflow Actions -->
        <div class="mrcb-review-sidebar">
            <div class="mrcb-card">
                <h3>⚙️ Post Workflow Actions</h3>
                <p>Scheduled Publish Date:<br>
                    <strong>📅 <?php echo esc_html( wp_date( 'l, d F Y', strtotime( $item['scheduled_date'] ) ) ); ?></strong> at <strong>07:00 AM IST</strong>
                </p>

                <div class="mrcb-action-stack">
                    <button id="btn-save-approve" class="button button-primary button-hero" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        ✅ Approve Draft
                    </button>
                    <p class="text-muted" style="margin-top:4px; font-size:12px;">Approving locks in the post. It will automatically publish at 7:00 AM on scheduled date.</p>

                    <button id="btn-publish-direct" class="button button-secondary" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        🚀 Publish to Site Immediately
                    </button>

                    <button id="btn-review-regenerate" class="button" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        🔄 Regenerate with Gemini AI
                    </button>

                    <button id="btn-review-reject" class="button button-link-delete" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        ❌ Reject Draft
                    </button>
                </div>
            </div>

            <!-- SEO Metadata Card -->
            <div class="mrcb-card">
                <h3>🔍 Yoast SEO Metadata</h3>
                <div class="mrcb-field-group">
                    <label for="mrcb-meta-title"><strong>SEO Title:</strong></label>
                    <input type="text" id="mrcb-meta-title" class="widefat" value="<?php echo esc_attr( $meta_title ); ?>" />
                    <small class="text-muted">Target: &lt;60 characters</small>
                </div>

                <div class="mrcb-field-group">
                    <label for="mrcb-focus-keyword"><strong>Focus Keyword:</strong></label>
                    <input type="text" id="mrcb-focus-keyword" class="widefat" value="<?php echo esc_attr( $item['focus_keyword'] ); ?>" />
                </div>

                <div class="mrcb-field-group">
                    <label for="mrcb-slug"><strong>English URL Slug:</strong></label>
                    <input type="text" id="mrcb-slug" class="widefat" value="<?php echo esc_attr( $slug ); ?>" />
                    <small class="text-muted">Clean English URL without Hindi characters</small>
                </div>

                <div class="mrcb-field-group">
                    <label for="mrcb-meta-desc"><strong>Meta Description:</strong></label>
                    <textarea id="mrcb-meta-desc" rows="4" class="widefat"><?php echo esc_textarea( $meta_desc ); ?></textarea>
                    <small class="text-muted">Target: 150-160 characters</small>
                </div>

                <div class="mrcb-field-group">
                    <label><strong>Tags:</strong></label>
                    <input type="text" id="mrcb-tags" class="widefat" value="<?php echo esc_attr( $item['ai_tags'] ); ?>" />
                </div>
            </div>

            <!-- Context Info Card -->
            <div class="mrcb-card">
                <h3>ℹ️ Festival Context</h3>
                <p><strong>Festival:</strong> <?php echo esc_html( $item['festival_name_hi'] ?: 'Evergreen Filler' ); ?></p>
                <p><strong>Topic Angle:</strong> <code><?php echo esc_html( $item['topic_angle'] ); ?></code></p>
                <p><strong>Generation Attempts:</strong> <?php echo esc_html( $item['generation_attempts'] ); ?></p>
                <?php if ( ! empty( $item['last_generated_at'] ) ) : ?>
                    <p><strong>Generated At:</strong> <?php echo esc_html( $item['last_generated_at'] ); ?></p>
                <?php endif; ?>
                <?php if ( ! empty( $item['wp_post_id'] ) ) : ?>
                    <p><strong>WP Post ID:</strong> <a href="<?php echo get_permalink( $item['wp_post_id'] ); ?>" target="_blank">#<?php echo esc_html( $item['wp_post_id'] ); ?> (View Live)</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
