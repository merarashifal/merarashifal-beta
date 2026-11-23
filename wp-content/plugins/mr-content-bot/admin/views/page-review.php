<?php
/**
 * MeraRashifal Content Bot - Draft Review & Visual Editor View
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

// Ensure localhost URLs are thoroughly scrubbed
$draft_markdown = ! empty( $item['admin_draft'] ) ? $item['admin_draft'] : ( $item['ai_draft_tagged'] ?: $item['ai_draft'] );
$draft_markdown = class_exists( 'MRCB_Writer' ) ? MRCB_Writer::clean_localhost_urls( $draft_markdown ) : $draft_markdown;
$draft_html     = class_exists( 'MRCB_Writer' ) ? MRCB_Writer::markdown_to_html( $draft_markdown ) : nl2br( $draft_markdown );
$draft_html     = class_exists( 'MRCB_Writer' ) ? MRCB_Writer::clean_localhost_urls( $draft_html ) : $draft_html;

$post_title = ! empty( $item['topic_title_hi'] ) ? $item['topic_title_hi'] : ( $item['admin_meta_title'] ?: $item['ai_meta_title'] );
$meta_title = ! empty( $item['admin_meta_title'] ) ? $item['admin_meta_title'] : ( $item['ai_meta_title'] ?: $post_title );
$meta_desc  = ! empty( $item['admin_meta_desc'] ) ? $item['admin_meta_desc'] : $item['ai_meta_desc'];
$slug       = $item['wp_slug'] ?: sanitize_title( $item['focus_keyword'] );
$tags       = ! empty( $item['ai_tags'] ) ? $item['ai_tags'] : '';
?>

<div class="wrap mrcb-wrap mrcb-review-wrap">
    <div class="mrcb-review-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <a href="<?php echo admin_url( 'admin.php?page=mrcb-queue' ); ?>" class="button">&larr; Back to Queue</a>
        <div style="display:flex; align-items:center; gap:10px;">
            <span class="mrcb-badge status-<?php echo esc_attr( $item['admin_status'] ); ?>">
                Status: <?php echo esc_html( ucwords( str_replace( '_', ' ', $item['admin_status'] ) ) ); ?>
            </span>
            <button id="btn-save-draft" class="button button-secondary" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                💾 Save Changes
            </button>
        </div>
    </div>

    <!-- Article Headline / Title Editor Box -->
    <div class="mrcb-card" style="margin-bottom: 20px; padding: 16px 20px; background: #fff; border-left: 4px solid #2563eb;">
        <label for="mrcb-post-title" style="font-weight: 700; font-size: 14px; color: #1e293b; display: block; margin-bottom: 6px;">
            📌 Article Headline / Post Title (Hindi):
        </label>
        <input type="text" id="mrcb-post-title" class="widefat" value="<?php echo esc_attr( $post_title ); ?>" style="font-size: 18px; font-weight: 700; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; line-height: 1.4; color:#0f172a;" placeholder="Enter post headline..." />
        <small class="text-muted" style="display:block; margin-top:5px;">This will be the main title of the published article on MeraRashifal.</small>
    </div>

    <div class="mrcb-review-container">
        <!-- Main Column: Editor & Preview -->
        <div class="mrcb-review-main">
            <div class="mrcb-card">
                <div class="mrcb-editor-nav">
                    <button type="button" class="mrcb-tab-btn active" data-tab="editor">✍️ Visual Rich Editor</button>
                    <button type="button" class="mrcb-tab-btn" data-tab="preview">👁️ Live Preview</button>
                    <button type="button" class="mrcb-tab-btn" data-tab="markdown">📝 Raw Markdown</button>
                </div>

                <!-- Tab 1: WordPress Visual Rich Text Editor -->
                <div id="mrcb-tab-editor" class="mrcb-tab-content active">
                    <?php if ( empty( trim( strip_tags( $draft_html ) ) ) ) : ?>
                        <div style="margin-bottom: 15px; padding: 14px 18px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px; font-size: 14px; color: #92400e; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>⚠️ This planned topic does not have a draft yet.</strong><br>
                                Click the button to generate the complete 3-pass article with Gemini AI.
                            </div>
                            <button type="button" class="button button-primary btn-regenerate" data-id="<?php echo esc_attr( $item['id'] ); ?>" style="font-weight: 600;">
                                ✍️ Generate Draft Now
                            </button>
                        </div>
                    <?php else : ?>
                        <div style="margin-bottom: 12px; color: #475569; font-size: 13px;">
                            💡 <strong>Visual Editor:</strong> You can edit paragraphs, headings, bullet points, and hyperlinks directly below.
                        </div>
                    <?php endif; ?>
                    <?php
                    wp_editor(
                        $draft_html,
                        'mrcb_content_editor',
                        [
                            'textarea_name' => 'mrcb_content_editor',
                            'textarea_rows' => 28,
                            'media_buttons' => true,
                            'tinymce'       => [
                                'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,link,unlink,undo,redo,fullscreen',
                            ],
                            'quicktags'     => true,
                        ]
                    );
                    ?>
                </div>

                <!-- Tab 2: Rendered Live Preview -->
                <div id="mrcb-tab-preview" class="mrcb-tab-content">
                    <div class="mrcb-preview-body">
                        <h1 class="preview-h1" id="mrcb-preview-title"><?php echo esc_html( $post_title ); ?></h1>
                        <div class="preview-content" id="mrcb-preview-content">
                            <?php echo $draft_html; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Raw Markdown -->
                <div id="mrcb-tab-markdown" class="mrcb-tab-content">
                    <p class="description">Raw markdown source for developers or quick copy-pasting:</p>
                    <textarea id="mrcb-draft-textarea" rows="28" class="large-text code"><?php echo esc_textarea( $draft_markdown ); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Right Column: SEO Meta & Workflow Actions -->
        <div class="mrcb-review-sidebar">
            <div class="mrcb-card">
                <h3>⚙️ Schedule & Publish Actions</h3>
                
                <div class="mrcb-field-group" style="margin-bottom: 12px;">
                    <label for="mrcb-sched-date"><strong>📅 Scheduled Publish Date:</strong></label>
                    <input type="date" id="mrcb-sched-date" class="widefat" value="<?php echo esc_attr( $item['scheduled_date'] ); ?>" style="padding: 6px 10px; margin-top: 4px;" />
                </div>

                <div class="mrcb-field-group" style="margin-bottom: 15px;">
                    <label for="mrcb-sched-time"><strong>⏰ Publish Time Slot:</strong></label>
                    <select id="mrcb-sched-time" class="widefat" style="margin-top: 4px; padding: 6px 10px;">
                        <option value="07:00:00" <?php selected( $item['publish_time'], '07:00:00' ); ?>>⏰ 07:00 AM (Slot 1 - Primary Event)</option>
                        <option value="09:00:00" <?php selected( $item['publish_time'], '09:00:00' ); ?>>⏰ 09:00 AM (Slot 2 - Secondary / 2-Hour Gap)</option>
                        <option value="11:00:00" <?php selected( $item['publish_time'], '11:00:00' ); ?>>⏰ 11:00 AM</option>
                        <option value="17:00:00" <?php selected( $item['publish_time'], '17:00:00' ); ?>>⏰ 05:00 PM</option>
                    </select>
                </div>

                <?php if ( ! empty( $item['wp_post_id'] ) ) : ?>
                    <div style="background:#e0f2fe; border-left:4px solid #0284c7; padding:10px 12px; margin-bottom:15px; border-radius:4px; font-size:13px; line-height: 1.5;">
                        <strong style="color:#0369a1;">📌 Scheduled in WordPress:</strong><br>
                        Post ID: <code>#<?php echo esc_html( $item['wp_post_id'] ); ?></code> &bull; 
                        Status: <strong><?php echo esc_html( get_post_status( $item['wp_post_id'] ) ); ?></strong><br>
                        <a href="<?php echo admin_url( 'post.php?post=' . $item['wp_post_id'] . '&action=edit' ); ?>" target="_blank" style="font-weight:600; text-decoration:underline;">✏️ Edit in WordPress Posts &rarr;</a><br>
                        <a href="<?php echo admin_url( 'edit.php?post_status=future' ); ?>" target="_blank" style="font-weight:600; text-decoration:underline;">📋 View Posts &gt; Scheduled &rarr;</a>
                    </div>
                <?php endif; ?>

                <div class="mrcb-action-stack">
                    <button id="btn-save-draft" class="button button-secondary" style="width: 100%; margin-bottom: 8px; font-weight: 600; padding: 6px 12px;" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        💾 Save Changes (Draft)
                    </button>

                    <button id="btn-save-approve" class="button button-primary button-hero" style="width: 100%; margin-bottom: 8px;" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        <?php echo ! empty( $item['wp_post_id'] ) ? '💾 Update Scheduled Post in WP' : '✅ Approve & Schedule in WordPress'; ?>
                    </button>
                    <p class="text-muted" style="margin-top:2px; margin-bottom: 12px; font-size:12px;">
                        <?php echo ! empty( $item['wp_post_id'] )
                            ? 'Updates content, SEO metadata, and schedule of WordPress Post #' . $item['wp_post_id'] . '.'
                            : 'Immediately creates and schedules this post in WordPress under <strong>Posts &gt; Scheduled</strong>.'; ?>
                    </p>

                    <button id="btn-publish-direct" class="button button-secondary" style="width: 100%; margin-bottom: 8px;" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        🚀 Publish Live Immediately
                    </button>

                    <button id="btn-review-regenerate" class="button <?php echo empty( trim( strip_tags( $draft_html ) ) ) ? 'button-primary' : ''; ?>" style="width: 100%; margin-bottom: 8px;" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        <?php echo empty( trim( strip_tags( $draft_html ) ) ) ? '✍️ Generate Draft with Gemini AI' : '🔄 Regenerate with Gemini AI'; ?>
                    </button>

                    <button id="btn-review-reject" class="button button-link-delete" style="width: 100%; margin-top: 4px;" data-id="<?php echo esc_attr( $item['id'] ); ?>">
                        ❌ Reject Draft
                    </button>
                </div>
            </div>

            <!-- SEO Metadata Card -->
            <div class="mrcb-card">
                <h3>🔍 Yoast SEO Metadata</h3>
                <div class="mrcb-field-group">
                    <label for="mrcb-focus-keyword"><strong>Focus Keyword:</strong></label>
                    <input type="text" id="mrcb-focus-keyword" class="widefat" value="<?php echo esc_attr( $item['focus_keyword'] ); ?>" />
                    <small class="text-muted">Target Hindi keyphrase</small>
                </div>

                <div class="mrcb-field-group">
                    <label for="mrcb-meta-title"><strong>SEO Title:</strong> (<span id="mrcb-meta-title-count">0</span> / 60)</label>
                    <input type="text" id="mrcb-meta-title" class="widefat" value="<?php echo esc_attr( $meta_title ); ?>" />
                    <small class="text-muted">Must start with Focus Keyword & stay under 60 characters</small>
                </div>

                <div class="mrcb-field-group">
                    <label for="mrcb-slug"><strong>English URL Slug:</strong></label>
                    <input type="text" id="mrcb-slug" class="widefat" value="<?php echo esc_attr( $slug ); ?>" />
                    <small class="text-muted">Clean English URL slug</small>
                </div>

                <div class="mrcb-field-group">
                    <label for="mrcb-meta-desc"><strong>Meta Description:</strong> (<span id="mrcb-meta-desc-count">0</span> / 154)</label>
                    <textarea id="mrcb-meta-desc" rows="4" class="widefat"><?php echo esc_textarea( $meta_desc ); ?></textarea>
                    <small class="text-muted">Strict Yoast safe limit: 120-154 characters</small>
                </div>

                <div class="mrcb-field-group">
                    <label for="mrcb-tags"><strong>Tags (English):</strong></label>
                    <input type="text" id="mrcb-tags" class="widefat" value="<?php echo esc_attr( $tags ); ?>" />
                </div>
            </div>

            <!-- Context Info Card -->
            <div class="mrcb-card">
                <h3>ℹ️ Topic Context</h3>
                <p><strong>Type:</strong> <?php echo $item['post_type'] === 'festival' ? 'Festival / Vrat' : 'Evergreen Guide'; ?></p>
                <?php if ( ! empty( $item['festival_name_hi'] ) ) : ?>
                    <p><strong>Festival:</strong> <?php echo esc_html( $item['festival_name_hi'] ); ?></p>
                <?php endif; ?>
                <p><strong>Topic Angle:</strong> <code><?php echo esc_html( $item['topic_angle'] ); ?></code></p>
                <p><strong>Attempts:</strong> <?php echo esc_html( $item['generation_attempts'] ); ?></p>
                <?php if ( ! empty( $item['last_generated_at'] ) ) : ?>
                    <p><strong>Generated:</strong> <?php echo esc_html( $item['last_generated_at'] ); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
