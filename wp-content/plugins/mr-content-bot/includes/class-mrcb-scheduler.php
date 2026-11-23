<?php
/**
 * MeraRashifal Content Bot - Scheduling Intelligence Engine
 * Auto-plans 10-day advance festival campaigns and evergreen filler topics
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MRCB_Scheduler {

    const CRON_HOOK_NIGHTLY = 'mrcb_nightly_generation_cron';

    /**
     * Initialize scheduler hooks
     */
    public static function init() {
        add_action( self::CRON_HOOK_NIGHTLY, [ __CLASS__, 'run_nightly_generation' ] );
    }

    /**
     * Register cron events
     */
    public static function register_cron_events() {
        if ( ! wp_next_scheduled( self::CRON_HOOK_NIGHTLY ) ) {
            // Schedule nightly at 2:00 AM IST (20:30 UTC previous day)
            $tz_offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
            $time_2am_local = strtotime( 'tomorrow 02:00:00' );
            $timestamp_utc  = $time_2am_local - $tz_offset;

            wp_schedule_event( $timestamp_utc, 'daily', self::CRON_HOOK_NIGHTLY );
        }
    }

    /**
     * Clear cron events on deactivation
     */
    public static function clear_cron_events() {
        $timestamp = wp_next_scheduled( self::CRON_HOOK_NIGHTLY );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::CRON_HOOK_NIGHTLY );
        }
    }

    /**
     * Main nightly routine: cleans expired posts and guarantees complete 14-day schedule coverage
     */
    public static function run_nightly_generation() {
        if ( defined( 'DISABLE_GEMINI_AUTO_GENERATION' ) && DISABLE_GEMINI_AUTO_GENERATION ) {
            return false;
        }
        $advance_days = defined( 'MRCB_ADVANCE_DAYS' ) ? (int) MRCB_ADVANCE_DAYS : 14;
        self::cleanup_expired_posts();
        return self::ensure_daily_schedule_coverage( $advance_days, true );
    }

    /**
     * Clean up unapproved drafts whose scheduled date has expired.
     * Stale past festival drafts have no relevance and are removed from the queue.
     */
    public static function cleanup_expired_posts() {
        global $wpdb;
        $t_queue = MRCB_DB_Setup::table_queue();
        $today   = current_time( 'Y-m-d' );

        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$t_queue} 
                 WHERE scheduled_date < %s 
                   AND (admin_status IN ('pending_review', 'rejected') OR (admin_status = 'approved' AND (wp_post_id IS NULL OR wp_post_id = 0)))",
                $today
            )
        );

        if ( class_exists( 'MRCB_DB_Setup' ) ) {
            MRCB_DB_Setup::sanitize_stored_links_and_queue();
        }

        return (int) $deleted;
    }

    /**
     * Find next available time slot for a date (07:00:00 or 09:00:00)
     * Enforces maximum 2 posts per day with a 2-hour gap.
     */
    public static function get_next_available_slot( $preferred_date, $min_date = null, $max_date = null ) {
        global $wpdb;
        $t_queue = MRCB_DB_Setup::table_queue();

        $today = current_time( 'Y-m-d' );
        if ( ! $min_date || $min_date < $today ) {
            $min_date = $today;
        }

        $target_date = $preferred_date;
        if ( $target_date < $min_date ) {
            $target_date = $min_date;
        }
        if ( $max_date && $target_date > $max_date ) {
            $target_date = $max_date;
        }

        $date_cursor = $target_date;
        $attempts = 0;

        while ( $attempts < 20 ) {
            $existing = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, publish_time FROM {$t_queue} 
                     WHERE scheduled_date = %s AND admin_status != 'skipped' 
                     ORDER BY publish_time ASC",
                    $date_cursor
                ),
                ARRAY_A
            );

            $cnt = count( $existing );
            if ( $cnt === 0 ) {
                return [ 'date' => $date_cursor, 'time' => '07:00:00', 'slot' => 1 ];
            } elseif ( $cnt === 1 ) {
                return [ 'date' => $date_cursor, 'time' => '09:00:00', 'slot' => 2 ];
            }

            // Both slots (07:00 and 09:00) occupied on this date; try adjacent dates
            $date_cursor = wp_date( 'Y-m-d', strtotime( '+1 day', strtotime( $date_cursor ) ) );
            if ( $max_date && $date_cursor > $max_date ) {
                $date_cursor = wp_date( 'Y-m-d', strtotime( '-1 day', strtotime( $target_date ) ) );
                if ( $date_cursor < $min_date ) {
                    break;
                }
            }
            $attempts++;
        }

        return [ 'date' => $preferred_date, 'time' => '09:00:00', 'slot' => 2 ];
    }

    /**
     * Ensure daily schedule coverage across a multi-day horizon (default 14 days)
     * Rules:
     * 1. Every calendar day has AT LEAST 1 post (Slot 1: 07:00:00).
     * 2. If 2 festivals occur on that day, Slot 2 is scheduled at 09:00:00 (2-hour gap).
     * 3. Days with no festivals are populated with evergreen topics from Filler Bank.
     */
    public static function ensure_daily_schedule_coverage( $days_ahead = 14, $auto_generate = false ) {
        global $wpdb;
        $t_fest  = MRCB_DB_Setup::table_festivals();
        $t_queue = MRCB_DB_Setup::table_queue();

        self::cleanup_expired_posts();

        $today = current_time( 'Y-m-d' );
        $today_ts = strtotime( $today );
        $end_date = wp_date( 'Y-m-d', strtotime( "+{$days_ahead} days", $today_ts ) );

        // 1. Fetch all active festivals within coverage horizon
        $festivals = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$t_fest} 
                 WHERE coverage_status = 'active'
                   AND tier < 5
                   AND festival_date >= %s 
                   AND festival_date <= %s
                 ORDER BY festival_date ASC, tier ASC",
                $today,
                $end_date
            ),
            ARRAY_A
        );

        $festivals_by_date = [];
        foreach ( $festivals as $f ) {
            $festivals_by_date[ $f['festival_date'] ][] = $f;
        }

        // 2. Schedule festival posts for the festivals occurring on these dates
        foreach ( $festivals_by_date as $f_date => $f_list ) {
            foreach ( $f_list as $f ) {
                // Check if this festival already has a post scheduled on this date
                $has_post = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM {$t_queue} 
                         WHERE festival_id = %d AND scheduled_date = %s AND admin_status != 'skipped'",
                        $f['id'],
                        $f_date
                    )
                );
                if ( $has_post ) {
                    continue;
                }

                $slot = self::get_next_available_slot( $f_date, $today, $f_date );
                // Only place on the festival day if slots are available (slot 1 = 07:00, slot 2 = 09:00)
                if ( $slot['date'] !== $f_date ) {
                    continue; // Day already reached maximum 2 posts
                }

                $year = wp_date( 'Y', strtotime( $f_date ) );
                $fest_name_hi = $f['festival_name_hi'] ?: $f['festival_name_en'];
                $clean_kw = class_exists( 'MRCB_Writer' )
                    ? MRCB_Writer::sanitize_focus_keyword( "{$fest_name_hi} {$year}", $fest_name_hi )
                    : "{$fest_name_hi} {$year}";

                $title_hi = ( $slot['slot'] === 1 )
                    ? "{$fest_name_hi} {$year}: आज का शुभ मुहूर्त, संपूर्ण पूजन विधि और आरती"
                    : "{$fest_name_hi} {$year}: पौराणिक व्रत कथा, महत्व और विशेष उपाय";

                $wpdb->insert(
                    $t_queue,
                    [
                        'festival_id'       => (int) $f['id'],
                        'post_type'         => 'festival',
                        'topic_title_hi'    => $title_hi,
                        'topic_angle'       => ( $slot['slot'] === 1 ) ? 'muhurat' : 'katha',
                        'focus_keyword'     => $clean_kw,
                        'word_count_target' => 1000,
                        'scheduled_date'    => $f_date,
                        'publish_time'      => $slot['time'],
                        'admin_status'      => 'pending_review',
                    ]
                );
                $new_id = $wpdb->insert_id;

                if ( $auto_generate && class_exists( 'MRCB_Writer' ) ) {
                    MRCB_Writer::generate_for_queue_item( $new_id );
                }
            }
        }

        // 3. Ensure EVERY single day from today to end_date has at least 1 post scheduled
        for ( $i = 0; $i <= $days_ahead; $i++ ) {
            $cur_date = wp_date( 'Y-m-d', strtotime( "+{$i} days", $today_ts ) );

            $existing_count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$t_queue} 
                     WHERE scheduled_date = %s AND admin_status != 'skipped'",
                    $cur_date
                )
            );

            if ( $existing_count === 0 ) {
                // Empty date: schedule an evergreen topic from the filler bank at 07:00:00
                $filler = self::pick_filler_topic( $cur_date );
                $clean_kw = class_exists( 'MRCB_Writer' )
                    ? MRCB_Writer::sanitize_focus_keyword( $filler['focus_keyword'] ?: '', '', $filler['topic_title_hi'] )
                    : $filler['focus_keyword'];

                $wpdb->insert(
                    $t_queue,
                    [
                        'festival_id'       => null,
                        'post_type'         => 'filler',
                        'topic_title_hi'    => $filler['topic_title_hi'],
                        'topic_angle'       => $filler['topic_angle'] ?: 'guide',
                        'focus_keyword'     => $clean_kw,
                        'word_count_target' => 950,
                        'scheduled_date'    => $cur_date,
                        'publish_time'      => '07:00:00', // Slot 1: 7:00 AM
                        'admin_status'      => 'pending_review',
                    ]
                );
                $new_id = $wpdb->insert_id;

                if ( $auto_generate && class_exists( 'MRCB_Writer' ) ) {
                    MRCB_Writer::generate_for_queue_item( $new_id );
                }
            }

            // Enforce timing slots on all posts for this date:
            // First post is always 07:00:00; second post (if present) is always 09:00:00 (2h gap)
            $posts_on_date = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, publish_time FROM {$t_queue} 
                     WHERE scheduled_date = %s AND admin_status != 'skipped' 
                     ORDER BY publish_time ASC, id ASC",
                    $cur_date
                ),
                ARRAY_A
            );

            if ( ! empty( $posts_on_date ) ) {
                if ( $posts_on_date[0]['publish_time'] !== '07:00:00' ) {
                    $wpdb->update( $t_queue, [ 'publish_time' => '07:00:00' ], [ 'id' => $posts_on_date[0]['id'] ] );
                }
                if ( isset( $posts_on_date[1] ) && $posts_on_date[1]['publish_time'] !== '09:00:00' ) {
                    $wpdb->update( $t_queue, [ 'publish_time' => '09:00:00' ], [ 'id' => $posts_on_date[1]['id'] ] );
                }
            }
        }

        return true;
    }

    /**
     * Ensure content exists in queue for a given target date
     */
    public static function ensure_content_for_date( $target_date, $force_regenerate = false ) {
        global $wpdb;
        $t_queue = MRCB_DB_Setup::table_queue();

        // Check if content already exists
        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_queue} WHERE scheduled_date = %s", $target_date ), ARRAY_A );
        if ( $existing && ! $force_regenerate ) {
            return [ 'status' => 'exists', 'queue_id' => $existing['id'] ];
        }

        // Plan content: festival or filler
        $planned = self::plan_topic_for_date( $target_date );
        $slot    = self::get_next_available_slot( $target_date );

        $queue_id = 0;
        if ( $existing && $force_regenerate ) {
            $queue_id = $existing['id'];
            $wpdb->update(
                $t_queue,
                [
                    'festival_id'       => $planned['festival_id'],
                    'post_type'         => $planned['post_type'],
                    'topic_title_hi'    => $planned['topic_title_hi'],
                    'topic_angle'       => $planned['topic_angle'],
                    'focus_keyword'     => $planned['focus_keyword'],
                    'admin_status'      => 'pending_review',
                    'last_generated_at' => current_time( 'mysql' ),
                ],
                [ 'id' => $queue_id ]
            );
        } else {
            $wpdb->insert(
                $t_queue,
                [
                    'festival_id'       => $planned['festival_id'],
                    'post_type'         => $planned['post_type'],
                    'topic_title_hi'    => $planned['topic_title_hi'],
                    'topic_angle'       => $planned['topic_angle'],
                    'focus_keyword'     => $planned['focus_keyword'],
                    'word_count_target' => 950,
                    'scheduled_date'    => $target_date,
                    'publish_time'      => $slot['time'],
                    'admin_status'      => 'pending_review',
                ],
                [ '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' ]
            );
            $queue_id = $wpdb->insert_id;
        }

        // Trigger AI Generation immediately so draft is prepared in advance
        if ( class_exists( 'MRCB_Writer' ) ) {
            $gen_result = MRCB_Writer::generate_for_queue_item( $queue_id );
            return [
                'status'    => 'generated',
                'queue_id'  => $queue_id,
                'planned'   => $planned,
                'ai_result' => $gen_result
            ];
        }

        return [ 'status' => 'queued_only', 'queue_id' => $queue_id, 'planned' => $planned ];
    }

    /**
     * Determine best topic for a given date
     */
    public static function plan_topic_for_date( $target_date ) {
        global $wpdb;
        $t_fest  = MRCB_DB_Setup::table_festivals();
        $t_queue = MRCB_DB_Setup::table_queue();

        $target_ts = strtotime( $target_date );
        $year = wp_date( 'Y', $target_ts );

        // 1. Check if any campaign post is already scheduled for this date in queue
        $existing_queued = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$t_queue} WHERE scheduled_date = %s",
                $target_date
            ),
            ARRAY_A
        );
        if ( $existing_queued ) {
            return [
                'festival_id'    => $existing_queued['festival_id'],
                'post_type'      => $existing_queued['post_type'],
                'topic_title_hi' => $existing_queued['topic_title_hi'],
                'topic_angle'    => $existing_queued['topic_angle'],
                'focus_keyword'  => $existing_queued['focus_keyword'],
            ];
        }

        // 2. Check for Active Festivals within window [target_date, target_date + 12 days]
        $festivals = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$t_fest} 
                 WHERE coverage_status = 'active'
                   AND tier < 5
                   AND festival_date >= %s 
                   AND festival_date <= DATE_ADD(%s, INTERVAL 12 DAY)
                 ORDER BY tier ASC, festival_date ASC",
                $target_date,
                $target_date
            ),
            ARRAY_A
        );

        if ( ! empty( $festivals ) ) {
            // Plan full campaign for this festival if not planned already
            $fest = $festivals[0];
            $campaign_res = self::plan_campaign_for_festival( (int) $fest['id'], false );
            if ( $campaign_res['success'] ) {
                // Find post assigned to target_date or closest date
                $assigned_post = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM {$t_queue} 
                         WHERE festival_id = %d AND scheduled_date = %s",
                        $fest['id'],
                        $target_date
                    ),
                    ARRAY_A
                );
                if ( $assigned_post ) {
                    return [
                        'festival_id'    => (int) $fest['id'],
                        'post_type'      => 'festival',
                        'topic_title_hi' => $assigned_post['topic_title_hi'],
                        'topic_angle'    => $assigned_post['topic_angle'],
                        'focus_keyword'  => $assigned_post['focus_keyword'],
                    ];
                }
            }
        }

        // 3. If no festival nearby or no matching post, pick evergreen filler from Filler Bank
        return self::pick_filler_topic( $target_date );
    }

    /**
     * Plan and queue complete multi-post campaign for a festival based on Tier and Pillars
     */
    public static function plan_campaign_for_festival( $festival_id, $auto_generate_drafts = false ) {
        global $wpdb;
        $t_fest   = MRCB_DB_Setup::table_festivals();
        $t_queue  = MRCB_DB_Setup::table_queue();
        $t_series = MRCB_DB_Setup::table_series();

        $fest = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_fest} WHERE id = %d", $festival_id ), ARRAY_A );
        if ( ! $fest ) {
            return [ 'success' => false, 'error' => 'Festival not found.' ];
        }

        $fest_name_hi = ! empty( $fest['festival_name_hi'] ) ? $fest['festival_name_hi'] : $fest['festival_name_en'];
        $fest_date    = $fest['festival_date'];
        $tier         = (int) $fest['tier'];
        $post_count   = max( 1, (int) $fest['post_count'] );
        $year         = wp_date( 'Y', strtotime( $fest_date ) );
        $today        = current_time( 'Y-m-d' );

        // If tier is 5 (skip), do not plan
        if ( $tier >= 5 ) {
            return [ 'success' => false, 'error' => 'Festival is marked as Tier 5 (Skipped).' ];
        }

        // Build the post blueprint according to 4-Pillar Tier architecture
        $posts_blueprint = self::get_campaign_blueprint( $fest_name_hi, $fest_date, $tier, $post_count, $year );

        // Create or get series
        $series_name = "{$fest_name_hi} {$year} Campaign";
        $series_id   = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t_series} WHERE festival_id = %d", $festival_id ) );
        if ( ! $series_id ) {
            $wpdb->insert( $t_series, [
                'festival_id' => $festival_id,
                'series_name' => $series_name,
                'total_posts' => count( $posts_blueprint ),
                'status'      => 'planned',
            ] );
            $series_id = $wpdb->insert_id;
        } else {
            $wpdb->update( $t_series, [ 'total_posts' => count( $posts_blueprint ) ], [ 'id' => $series_id ] );
        }

        $queued_ids = [];

        foreach ( $posts_blueprint as $idx => $p ) {
            $pos = $idx + 1;
            // Check if item already exists for this festival & series position
            $existing = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id, admin_status FROM {$t_queue} WHERE festival_id = %d AND series_position = %d",
                    $festival_id,
                    $pos
                ),
                ARRAY_A
            );

            if ( $existing ) {
                // Update topic details if not yet published
                if ( in_array( $existing['admin_status'], [ 'pending_review', 'rejected' ], true ) ) {
                    $wpdb->update(
                        $t_queue,
                        [
                            'topic_title_hi'    => $p['title_hi'],
                            'topic_angle'       => $p['angle'],
                            'focus_keyword'     => $p['focus_keyword'],
                            'word_count_target' => $p['word_count'],
                            'scheduled_date'    => $p['scheduled_date'],
                        ],
                        [ 'id' => $existing['id'] ]
                    );
                }
                $qid = $existing['id'];
            } else {
                $slot = self::get_next_available_slot( $p['scheduled_date'], $today, $fest_date );
                $wpdb->insert(
                    $t_queue,
                    [
                        'series_id'         => $series_id,
                        'festival_id'       => $festival_id,
                        'post_type'         => 'festival',
                        'topic_title_hi'    => $p['title_hi'],
                        'topic_angle'       => $p['angle'],
                        'focus_keyword'     => $p['focus_keyword'],
                        'word_count_target' => $p['word_count'],
                        'series_position'   => $pos,
                        'scheduled_date'    => $slot['date'],
                        'publish_time'      => $slot['time'],
                        'admin_status'      => 'pending_review',
                    ]
                );
                $qid = $wpdb->insert_id;
            }

            $queued_ids[] = $qid;

            // Optionally generate draft if requested
            if ( $auto_generate_drafts && class_exists( 'MRCB_Writer' ) ) {
                MRCB_Writer::generate_for_queue_item( $qid );
            }
        }

        return [
            'success'    => true,
            'festival'   => $fest_name_hi,
            'series_id'  => $series_id,
            'queue_ids'  => $queued_ids,
            'post_count' => count( $posts_blueprint ),
            'blueprint'  => $posts_blueprint,
        ];
    }

    /**
     * Build the 4-Pillar Content Blueprint based on Tier and Post Count
     * Pillars:
     * 1. Sacred Lore & Origin (Katha)
     * 2. Deity Significance, Mantras & Top Temples
     * 3. Tithi, Shubh Muhurat, Samagri & Puja Vidhi
     * 4. Astrological Blessings, Kundali Doshas/Dashas & Rashi Remedies (MeraRashifal USP)
     */
    public static function get_campaign_blueprint( $fest_name_hi, $fest_date, $tier, $post_count, $year ) {
        $fest_ts = strtotime( $fest_date );
        $blueprint = [];

        if ( $tier === 1 ) {
            // Tier 1: Main Festivals (Mega Campaigns: 3 to 5+ posts)
            if ( $post_count >= 5 ) {
                // 5-Post Mega Sequence (e.g. Diwali, Holi)
                $blueprint = [
                    [
                        'lead_days'      => 10,
                        'angle'          => 'tier1_muhurat_date',
                        'title_hi'       => "{$fest_name_hi} {$year} कब है? जानें सही तिथि, शुभ मुहूर्त, स्थिर लग्न और चौघड़िया मुहूर्त",
                        'focus_keyword'  => "{$fest_name_hi} {$year}",
                        'word_count'     => 1200,
                    ],
                    [
                        'lead_days'      => 7,
                        'angle'          => 'tier1_zodiac_remedies',
                        'title_hi'       => "{$fest_name_hi} पर 12 राशियों के लिए अचूक महाउपाय: धन वृद्धि और दरिद्रता निवारण के सरल उपाय",
                        'focus_keyword'  => "{$fest_name_hi} उपाय",
                        'word_count'     => 1300,
                    ],
                    [
                        'lead_days'      => 4,
                        'angle'          => 'tier1_kundali_dosha',
                        'title_hi'       => "{$fest_name_hi} की रात्रि किन जातकों को अवश्य करनी चाहिए पूजा? कुंडली के शनि, राहु और कालसर्प दोष से मुक्ति के उपाय",
                        'focus_keyword'  => "{$fest_name_hi} ग्रह शांति",
                        'word_count'     => 1200,
                    ],
                    [
                        'lead_days'      => 2,
                        'angle'          => 'tier1_deity_temples',
                        'title_hi'       => "{$fest_name_hi} पर करें इन सिद्ध मंदिरों के दर्शन: इष्टदेव का अलौकिक स्वरूप और दिव्य बीज मंत्र",
                        'focus_keyword'  => "{$fest_name_hi} मंदिर",
                        'word_count'     => 1200,
                    ],
                    [
                        'lead_days'      => 0, // Day of festival
                        'angle'          => 'tier1_vidhi_katha_aarti',
                        'title_hi'       => "{$fest_name_hi} {$year}: आज का पावन पर्व, संपूर्ण पूजन विधि, आवश्यक सामग्री लिस्ट, व्रत कथा और आरती",
                        'focus_keyword'  => "{$fest_name_hi} पूजन विधि",
                        'word_count'     => 1500,
                    ],
                ];
            } elseif ( $post_count === 4 ) {
                // 4-Post Sequence (e.g. Maha Shivratri, Janmashtami)
                $blueprint = [
                    [
                        'lead_days'      => 7,
                        'angle'          => 'tier1_muhurat_date',
                        'title_hi'       => "{$fest_name_hi} {$year} कब है? जानें सही तारीख, चार प्रहर की पूजा का शुभ मुहूर्त और पारण समय",
                        'focus_keyword'  => "{$fest_name_hi} {$year}",
                        'word_count'     => 1200,
                    ],
                    [
                        'lead_days'      => 4,
                        'angle'          => 'tier1_kundali_dosha',
                        'title_hi'       => "{$fest_name_hi} पर राशि अनुसार करें ये अचूक उपाय: कालसर्प, साढ़ेसाती और पितृ दोष से मिलेगी मुक्ति",
                        'focus_keyword'  => "{$fest_name_hi} उपाय",
                        'word_count'     => 1300,
                    ],
                    [
                        'lead_days'      => 2,
                        'angle'          => 'tier1_deity_temples',
                        'title_hi'       => "{$fest_name_hi} पर भारत के इन सिद्ध धामों और प्रमुख मंदिरों का आध्यात्मिक महत्व और स्तुति",
                        'focus_keyword'  => "{$fest_name_hi} मंदिर",
                        'word_count'     => 1200,
                    ],
                    [
                        'lead_days'      => 0, // Day of festival
                        'angle'          => 'tier1_vidhi_katha_aarti',
                        'title_hi'       => "{$fest_name_hi} {$year}: आज का पावन पर्व, व्रत कथा, अभिषेक व पूजन विधि, सामग्री और आरती",
                        'focus_keyword'  => "{$fest_name_hi} पूजन विधि",
                        'word_count'     => 1400,
                    ],
                ];
            } else {
                // 3-Post Sequence (e.g. Karwa Chauth, Chhath Puja, Ram Navami, Makar Sankranti)
                $blueprint = [
                    [
                        'lead_days'      => 5,
                        'angle'          => 'tier1_muhurat_date',
                        'title_hi'       => "{$fest_name_hi} {$year} कब है? जानें सही तिथि, पूजा का शुभ मुहूर्त, अर्घ्य समय और पूजन सामग्री",
                        'focus_keyword'  => "{$fest_name_hi} {$year}",
                        'word_count'     => 1200,
                    ],
                    [
                        'lead_days'      => 2,
                        'angle'          => 'tier1_kundali_dosha',
                        'title_hi'       => "{$fest_name_hi} पर राशि अनुसार करें ये विशेष उपाय: वैवाहिक सुख, सौभाग्य और ग्रह शांति के अचूक उपाय",
                        'focus_keyword'  => "{$fest_name_hi} उपाय",
                        'word_count'     => 1300,
                    ],
                    [
                        'lead_days'      => 0, // Day of festival
                        'angle'          => 'tier1_vidhi_katha_aarti',
                        'title_hi'       => "{$fest_name_hi} {$year}: आज का पावन पर्व, संपूर्ण पौराणिक व्रत कथा, पूजन विधि और आरती",
                        'focus_keyword'  => "{$fest_name_hi} पूजन विधि",
                        'word_count'     => 1400,
                    ],
                ];
            }
        } elseif ( $tier === 2 ) {
            // Tier 2: Secondary Festivals (Major Campaigns: 2 Posts Formula)
            $blueprint = [
                [
                    'lead_days'      => 3,
                    'angle'          => 'tier2_practical_astrology',
                    'title_hi'       => "{$fest_name_hi} {$year}: शुभ मुहूर्त, पूजन सामग्री, आवश्यक विधि और कुंडली दोष शांति के उपाय",
                    'focus_keyword'  => "{$fest_name_hi} {$year}",
                    'word_count'     => 1300,
                ],
                [
                    'lead_days'      => 0, // Day of festival
                    'angle'          => 'tier2_spiritual_katha',
                    'title_hi'       => "{$fest_name_hi} पावन व्रत कथा: पौराणिक इतिहास, इष्टदेव का स्वरूप और आज के विशेष उपाय",
                    'focus_keyword'  => "{$fest_name_hi} व्रत कथा",
                    'word_count'     => 1300,
                ],
            ];
        } elseif ( $tier === 4 ) {
            // Tier 4: Regional Observances (1 Post Cultural + Jyotish Composite)
            $blueprint = [
                [
                    'lead_days'      => 0, // Day of festival
                    'angle'          => 'tier4_regional_composite',
                    'title_hi'       => "{$fest_name_hi} {$year}: क्षेत्रीय परंपराएं, आज का शुभ मुहूर्त, लोककथा और नव संवत्सर का ज्योतिषीय प्रभाव",
                    'focus_keyword'  => "{$fest_name_hi} {$year}",
                    'word_count'     => 1300,
                ],
            ];
        } else {
            // Tier 3 (and fallback): Regular Vrats & Observances (1 Post All-in-One Master Architecture)
            $blueprint = [
                [
                    'lead_days'      => 0, // Day of festival
                    'angle'          => 'tier3_master_all_in_one',
                    'title_hi'       => "{$fest_name_hi} {$year}: आज की तिथि, शुभ मुहूर्त, पूजा विधि, संपूर्ण व्रत कथा और राशि अनुसार उपाय",
                    'focus_keyword'  => "{$fest_name_hi} {$year}",
                    'word_count'     => 1500,
                ],
            ];
        }

        // Assign scheduled_date based on lead_days relative to festival_date
        $today_ts = strtotime( current_time( 'Y-m-d' ) );
        foreach ( $blueprint as &$b ) {
            $lead = (int) $b['lead_days'];
            $target_ts = strtotime( "-{$lead} days", $fest_ts );
            // If lead date is already past or today, schedule for today or tomorrow
            if ( $target_ts < $today_ts ) {
                $target_ts = $today_ts;
            }
            $b['scheduled_date'] = wp_date( 'Y-m-d', $target_ts );

            if ( class_exists( 'MRCB_Writer' ) ) {
                $b['focus_keyword'] = MRCB_Writer::sanitize_focus_keyword( $b['focus_keyword'], $fest_name_hi, $b['title_hi'] );
            }
        }

        return $blueprint;
    }

    /**
     * Pick a filler topic from mr_filler_bank
     */
    public static function pick_filler_topic( $target_date ) {
        global $wpdb;
        $t_filler = MRCB_DB_Setup::table_filler();

        $month = (int) wp_date( 'n', strtotime( $target_date ) );

        // Pick top priority active filler not used in last 14 days
        $fillers = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$t_filler} 
                 WHERE is_active = 1 
                   AND (last_used_date IS NULL OR last_used_date < DATE_SUB(%s, INTERVAL 14 DAY))
                 ORDER BY priority ASC, times_used ASC, RAND() 
                 LIMIT 5",
                $target_date
            ),
            ARRAY_A
        );

        $selected = null;
        if ( ! empty( $fillers ) ) {
            foreach ( $fillers as $f ) {
                if ( ! empty( $f['seasonal_months'] ) ) {
                    $valid_months = array_map( 'intval', explode( ',', $f['seasonal_months'] ) );
                    if ( ! in_array( $month, $valid_months, true ) ) {
                        continue;
                    }
                }
                $selected = $f;
                break;
            }
            if ( ! $selected ) {
                $selected = $fillers[0];
            }
        }

        if ( $selected ) {
            // Update usage stats
            $wpdb->update(
                $t_filler,
                [
                    'times_used'     => (int) $selected['times_used'] + 1,
                    'last_used_date' => $target_date,
                ],
                [ 'id' => $selected['id'] ],
                [ '%d', '%s' ],
                [ '%d' ]
            );

            return [
                'festival_id'    => null,
                'post_type'      => 'filler',
                'topic_title_hi' => $selected['topic_title_hi'],
                'topic_angle'    => $selected['topic_angle'] ?: 'guide',
                'focus_keyword'  => $selected['focus_keyword'] ?: '',
            ];
        }

        // Ultimate fallback if filler bank is empty
        return [
            'festival_id'    => null,
            'post_type'      => 'filler',
            'topic_title_hi' => 'सनातन धर्म में दैनिक पूजा और दीप प्रज्वलन के चमत्कारी आध्यात्मिक नियम',
            'topic_angle'    => 'guide',
            'focus_keyword'  => 'daily puja niyam deepak jalane ke niyam',
        ];
    }
}
