/**
 * MeraRashifal Content Bot - Admin JavaScript Controller
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // 1. Tab Switching (Preview vs Markdown Editor)
        $('.mrcb-tab-btn').on('click', function() {
            var tabId = $(this).data('tab');
            $('.mrcb-tab-btn').removeClass('active');
            $(this).addClass('active');

            $('.mrcb-tab-content').removeClass('active');
            $('#mrcb-tab-' + tabId).addClass('active');
        });

        // 2. Approve Draft from Review Screen
        $('#btn-save-approve').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');
            var content = $('#mrcb-draft-textarea').val();
            var metaTitle = $('#mrcb-meta-title').val();
            var metaDesc = $('#mrcb-meta-desc').val();
            var slug = $('#mrcb-slug').val();

            $btn.prop('disabled', true).text('⏳ Approving...');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_approve_draft',
                nonce: mrcbConfig.nonce,
                id: id,
                content: content,
                meta_title: metaTitle,
                meta_desc: metaDesc,
                slug: slug
            }, function(res) {
                if (res.success) {
                    alert('✅ ' + res.data.message);
                    window.location.reload();
                } else {
                    alert('❌ ' + (res.data.message || 'Error approving draft.'));
                    $btn.prop('disabled', false).text('✅ Approve Draft');
                }
            });
        });

        // 3. Quick Approve from Queue Table
        $('.btn-quick-approve').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');

            if (!confirm('Approve this post as-is? It will be scheduled for 7:00 AM publish.')) {
                return;
            }

            $btn.prop('disabled', true).text('⏳');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_approve_draft',
                nonce: mrcbConfig.nonce,
                id: id
            }, function(res) {
                if (res.success) {
                    window.location.reload();
                } else {
                    alert(res.data.message || 'Failed to approve');
                    $btn.prop('disabled', false).text('✅');
                }
            });
        });

        // 4. Publish Immediately to Site
        $('#btn-publish-direct, .btn-publish-now').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');

            if (!confirm('Are you sure you want to publish this post live immediately to merarashifal.com?')) {
                return;
            }

            $btn.prop('disabled', true).text('⏳ Publishing...');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_publish_now',
                nonce: mrcbConfig.nonce,
                id: id
            }, function(res) {
                if (res.success) {
                    alert('🚀 ' + res.data.message + '\nPost URL: ' + res.data.url);
                    window.location.reload();
                } else {
                    alert('❌ ' + (res.data.message || 'Publishing failed.'));
                    $btn.prop('disabled', false).text('🚀 Publish');
                }
            });
        });

        // 5. Regenerate with Gemini AI
        $('#btn-review-regenerate, .btn-regenerate').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');

            if (!confirm('Regenerate this draft with Gemini AI? The current draft will be overwritten.')) {
                return;
            }

            $btn.prop('disabled', true).text('⏳ Writing (3-pass AI)...');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_regenerate_draft',
                nonce: mrcbConfig.nonce,
                id: id
            }, function(res) {
                if (res.success) {
                    alert('✨ ' + res.data.message);
                    window.location.reload();
                } else {
                    alert('❌ ' + (res.data.message || 'Regeneration failed.'));
                    $btn.prop('disabled', false).text('🔄');
                }
            });
        });

        // 6. Reject Draft
        $('#btn-review-reject').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');
            var notes = prompt('Reason for rejecting this draft (optional):');
            if (notes === null) return; // user cancelled

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_reject_draft',
                nonce: mrcbConfig.nonce,
                id: id,
                notes: notes
            }, function(res) {
                alert(res.data.message);
                window.location.reload();
            });
        });

        // 7. Trigger Day+10 Advance Cron Generation
        $('#mrcb-trigger-advance-cron').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            $btn.prop('disabled', true).text('⏳ Generating Day+10 Content...');

            // Get target date (10 days from today)
            var target = new Date();
            target.setDate(target.getDate() + 10);
            var dateStr = target.toISOString().split('T')[0];

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_generate_for_date',
                nonce: mrcbConfig.nonce,
                date: dateStr
            }, function(res) {
                if (res.success) {
                    alert('✨ Content successfully generated for ' + dateStr + '!');
                    window.location.reload();
                } else {
                    alert('❌ ' + (res.data.message || 'Generation failed.'));
                    $btn.prop('disabled', false).text('⚡ Generate for Day+10 Now');
                }
            });
        });

        // 8. Generate for Custom Date Picker
        $('#mrcb-btn-generate-date').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var dateStr = $('#mrcb-custom-date').val();
            if (!dateStr) {
                alert('Please select a valid date.');
                return;
            }

            $btn.prop('disabled', true).text('⏳ Generating...');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_generate_for_date',
                nonce: mrcbConfig.nonce,
                date: dateStr
            }, function(res) {
                if (res.success) {
                    alert('✨ Draft ready for ' + dateStr + '!');
                    window.location.reload();
                } else {
                    alert('❌ ' + (res.data.message || 'Failed to generate.'));
                    $btn.prop('disabled', false).text('🚀 Draft for Date');
                }
            });
        });

        // 9. Test Gemini API Connection
        $('#btn-test-connection').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $result = $('#mrcb-test-result');

            $btn.prop('disabled', true).text('Testing connection...');
            $result.html('<span style="color:#d97706;">Connecting to Google Gemini API...</span>');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_test_connection',
                nonce: mrcbConfig.nonce
            }, function(res) {
                $btn.prop('disabled', false).text('🔌 Test Gemini Connection Now');
                if (res.success) {
                    $result.html('<div style="background:#dcfce7; border:1px solid #86efac; color:#166534; padding:10px; border-radius:6px; margin-top:8px;"><strong>' + res.data.message + '</strong><br><small>Test probe response: ' + res.data.sample + '</small></div>');
                } else {
                    $result.html('<div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:10px; border-radius:6px; margin-top:8px;"><strong>' + res.data.message + '</strong></div>');
                }
            });
        });

        // 10. Save Festival Row Settings
        $('.btn-save-fest').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');
            var $row = $btn.closest('tr');
            var tier = $row.find('.fest-tier-select').val();
            var count = $row.find('.fest-count-input').val();
            var status = $row.find('.fest-status-select').val();

            $btn.prop('disabled', true).text('Saving...');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_toggle_festival',
                nonce: mrcbConfig.nonce,
                id: id,
                tier: tier,
                post_count: count,
                status: status
            }, function(res) {
                $btn.prop('disabled', false).text('💾 Saved!');
                setTimeout(function() { $btn.text('💾 Save'); }, 2000);
            });
        });

        // 10b. Sync Festivals from Master Calendar
        $('#btn-sync-festivals').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var originalText = $btn.html();

            $btn.prop('disabled', true).html('⏳ Syncing Calendar (1-Year)...');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_sync_festivals',
                nonce: mrcbConfig.nonce,
                scope: 'upcoming_1_year'
            }, function(res) {
                if (res.success) {
                    alert('✅ ' + res.data.message);
                    window.location.reload();
                } else {
                    alert('❌ ' + (res.data.message || 'Sync failed.'));
                    $btn.prop('disabled', false).html(originalText);
                }
            }).fail(function() {
                alert('❌ Request timed out or server error.');
                $btn.prop('disabled', false).html(originalText);
            });
        });

        // 10c. Plan Festival Campaign into Queue
        $('.btn-plan-campaign').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');
            var originalText = $btn.html();

            $btn.prop('disabled', true).html('⏳ Planning...');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_plan_campaign',
                nonce: mrcbConfig.nonce,
                festival_id: id
            }, function(res) {
                $btn.prop('disabled', false).html(originalText);
                if (res.success) {
                    if (confirm('✅ ' + res.data.message + '\n\nWould you like to open the Content Queue now to view and review these posts?')) {
                        window.location.href = 'admin.php?page=mrcb-queue';
                    }
                } else {
                    alert('❌ ' + (res.data.message || 'Failed to plan campaign.'));
                }
            }).fail(function() {
                alert('❌ Server error while planning campaign.');
                $btn.prop('disabled', false).html(originalText);
            });
        });

        // 11. Add New Filler Topic
        $('#mrcb-add-filler-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var title = $('#filler-title').val();
            var cat = $('#filler-cat').val();
            var kw = $('#filler-kw').val();
            var prio = $('#filler-prio').val();

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_save_filler',
                nonce: mrcbConfig.nonce,
                title_hi: title,
                category: cat,
                focus_keyword: kw,
                priority: prio
            }, function(res) {
                if (res.success) {
                    alert('Topic saved to Filler Bank!');
                    window.location.reload();
                } else {
                    alert(res.data.message || 'Failed to save');
                }
            });
        });

        // 12. Delete Filler Topic
        $('.btn-delete-filler').on('click', function(e) {
            e.preventDefault();
            if (!confirm('Delete this evergreen topic from the bank?')) {
                return;
            }
            var $btn = $(this);
            var id = $btn.data('id');

            $.post(mrcbConfig.ajax_url, {
                action: 'mrcb_delete_filler',
                nonce: mrcbConfig.nonce,
                id: id
            }, function(res) {
                if (res.success) {
                    $btn.closest('tr').fadeOut();
                } else {
                    alert('Failed to delete');
                }
            });
        });

    });

})(jQuery);
