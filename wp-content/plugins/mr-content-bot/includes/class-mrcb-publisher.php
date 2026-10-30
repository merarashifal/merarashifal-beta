<?php
/**
 * MeraRashifal Content Bot - WordPress Publisher
 * Publishes admin-approved scheduled posts, applies Yoast SEO metadata, and updates internal link maps.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MRCB_Publisher {

    const CRON_HOOK_PUBLISH = 'mrcb_morning_publish_cron';

    /**
     * Initialize publisher hooks
     */
    public static function init() {
        add_action( self::CRON_HOOK_PUBLISH, [ __CLASS__, 'run_daily_publishing' ] );
    }

    /**
     * Register morning publish cron event (6:50 AM IST daily)
     */
    public static function register_cron_events() {
        if ( ! wp_next_scheduled( self::CRON_HOOK_PUBLISH ) ) {
            $tz_offset = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
            $time_650am_local = strtotime( 'tomorrow 06:50:00' );
            $timestamp_utc    = $time_650am_local - $tz_offset;

            wp_schedule_event( $timestamp_utc, 'daily', self::CRON_HOOK_PUBLISH );
        }
    }

    /**
     * Clear publish cron events
     */
    public static function clear_cron_events() {
        $timestamp = wp_next_scheduled( self::CRON_HOOK_PUBLISH );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::CRON_HOOK_PUBLISH );
        }
    }

    /**
     * Daily morning publishing handler
     */
    public static function run_daily_publishing() {
        global $wpdb;
        $t_queue = MRCB_DB_Setup::table_queue();
        $today = current_time( 'Y-m-d' );

        // Strictly check: admin_status = 'approved' AND wp_post_id IS NULL AND scheduled_date <= today
        $due_posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id FROM {$t_queue} 
                 WHERE admin_status = 'approved' 
                   AND wp_post_id IS NULL 
                   AND scheduled_date <= %s",
                $today
            ),
            ARRAY_A
        );

        $results = [];
        if ( ! empty( $due_posts ) ) {
            foreach ( $due_posts as $p ) {
                $results[] = self::publish_queue_item( $p['id'] );
            }
        }

        return $results;
    }

    /**
     * Publish a single approved queue item to WordPress
     */
    public static function publish_queue_item( $queue_id ) {
        global $wpdb;
        $t_queue = MRCB_DB_Setup::table_queue();
        $t_fest  = MRCB_DB_Setup::table_festivals();

        $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_queue} WHERE id = %d", $queue_id ), ARRAY_A );
        if ( ! $item ) {
            return [ 'success' => false, 'error' => 'Queue item not found.' ];
        }

        // STRICT GUARD: Must be approved by admin!
        if ( $item['admin_status'] !== 'approved' ) {
            return [ 'success' => false, 'error' => 'Post cannot be published without admin approval.' ];
        }

        // Content to use: admin edited version if present, else final AI tagged version
        $markdown_content = ! empty( $item['admin_draft'] ) ? $item['admin_draft'] : $item['ai_draft_tagged'];
        if ( empty( $markdown_content ) ) {
            return [ 'success' => false, 'error' => 'No content found in draft.' ];
        }

        $html_content = MRCB_Writer::markdown_to_html( $markdown_content );

        // Post Title
        $post_title = ! empty( $item['admin_meta_title'] ) ? $item['admin_meta_title'] : $item['topic_title_hi'];

        // Post Slug (clean English)
        $post_slug = ! empty( $item['wp_slug'] ) ? sanitize_title( $item['wp_slug'] ) : sanitize_title( $item['focus_keyword'] );
        if ( empty( $post_slug ) ) {
            $post_slug = 'festival-post-' . $item['scheduled_date'];
        }

        // Determine Categories
        $category_ids = self::resolve_categories( $item );

        // Determine Tags (Strictly English only)
        $raw_tags = array_filter( array_map( 'trim', explode( ',', $item['ai_tags'] ) ) );
        $tag_names = [];
        foreach ( $raw_tags as $t ) {
            $cleaned = preg_replace( '/[^a-zA-Z0-9\s\-]/', '', $t );
            $cleaned = strtolower( trim( preg_replace( '/\s+/', ' ', $cleaned ) ) );
            if ( ! empty( $cleaned ) && strlen( $cleaned ) >= 2 ) {
                $tag_names[] = $cleaned;
            }
        }
        $tag_names = array_unique( $tag_names );

        // Insert WordPress Post
        $post_data = [
            'post_title'    => wp_strip_all_tags( $post_title ),
            'post_name'     => $post_slug,
            'post_content'  => $html_content,
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_author'   => 1, // Default to site admin
            'post_category' => $category_ids,
            'tags_input'    => $tag_names,
            'post_date'     => current_time( 'mysql' ),
        ];

        $post_id = wp_insert_post( $post_data, true );
        if ( is_wp_error( $post_id ) ) {
            return [ 'success' => false, 'error' => $post_id->get_error_message() ];
        }

        // Auto-assign matching featured image thumbnail from media library
        self::assign_featured_image( $post_id, $item );

        // Bust snippet 45 transient cache so "Also Read" refreshes immediately
        delete_transient( 'related_posts_' . $post_id );

        // Apply Yoast SEO Postmeta
        $focus_kw  = $item['focus_keyword'] ?: '';
        $meta_desc = ! empty( $item['admin_meta_desc'] ) ? $item['admin_meta_desc'] : $item['ai_meta_desc'];
        $seo_title = ! empty( $item['admin_meta_title'] ) ? $item['admin_meta_title'] : $item['ai_meta_title'];

        if ( ! empty( $focus_kw ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_focuskw', sanitize_text_field( $focus_kw ) );
        }
        if ( ! empty( $seo_title ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_title', sanitize_text_field( $seo_title ) );
        }
        if ( ! empty( $meta_desc ) ) {
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', sanitize_text_field( $meta_desc ) );
        }

        // Update Queue record
        $published_at = current_time( 'mysql' );
        $wpdb->update(
            $t_queue,
            [
                'wp_post_id'      => $post_id,
                'wp_published_at' => $published_at,
                'admin_status'    => 'published',
            ],
            [ 'id' => $queue_id ],
            [ '%d', '%s', '%s' ],
            [ '%d' ]
        );

        // Update Link Map so future AI articles can link to this new post
        self::register_post_in_link_map( $post_id, $post_title, $focus_kw, $tag_names );

        return [
            'success'      => true,
            'queue_id'     => $queue_id,
            'wp_post_id'   => $post_id,
            'published_at' => $published_at,
            'url'          => get_permalink( $post_id )
        ];
    }

    /**
     * Resolve relevant categories for the post (Strictly existing categories only - NO auto-creation)
     */
    public static function resolve_categories( $item ) {
        $category_ids = [];

        // Check for general 'Festivals' category (slug: festivals)
        $festivals_cat = get_term_by( 'slug', 'festivals', 'category' );
        if ( ! $festivals_cat ) {
            $festivals_cat = get_term_by( 'name', 'Festivals', 'category' );
        }
        if ( $festivals_cat ) {
            $category_ids[] = (int) $festivals_cat->term_id;
        }

        // If it's a festival post, see if an existing specific category matches
        if ( $item['post_type'] === 'festival' && ! empty( $item['festival_id'] ) ) {
            global $wpdb;
            $t_fest = MRCB_DB_Setup::table_festivals();
            $fest = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_fest} WHERE id = %d", $item['festival_id'] ), ARRAY_A );

            if ( $fest && ! empty( $fest['festival_name_en'] ) ) {
                $en_slug = sanitize_title( $fest['festival_name_en'] );
                $specific_cat = get_term_by( 'slug', $en_slug, 'category' );
                if ( ! $specific_cat ) {
                    $specific_cat = get_term_by( 'name', $fest['festival_name_en'], 'category' );
                }
                if ( $specific_cat ) {
                    $category_ids[] = (int) $specific_cat->term_id;
                }
            }
        } elseif ( $item['post_type'] === 'filler' ) {
            // Check for existing Astrology or Vrat Katha categories
            $astro_cat = get_term_by( 'slug', 'astrology', 'category' );
            if ( $astro_cat ) {
                $category_ids[] = (int) $astro_cat->term_id;
            }
            $vrat_cat = get_term_by( 'slug', 'vrat-katha', 'category' );
            if ( $vrat_cat ) {
                $category_ids[] = (int) $vrat_cat->term_id;
            }
        }

        // Fallback: If no categories matched, use default category
        if ( empty( $category_ids ) ) {
            $default_cat = (int) get_option( 'default_category', 1 );
            $category_ids[] = $default_cat;
        }

        return array_unique( $category_ids );
    }

    /**
     * Register published post into mr_link_map
     */
    public static function register_post_in_link_map( $post_id, $title, $focus_kw, $tags = [] ) {
        global $wpdb;
        $t_link_map = MRCB_DB_Setup::table_link_map();

        $url = get_permalink( $post_id );
        if ( empty( $url ) ) {
            return;
        }

        $keywords_array = array_unique( array_filter( array_merge( [ $focus_kw ], $tags ) ) );
        $keywords_str   = implode( ', ', $keywords_array );

        $wpdb->insert(
            $t_link_map,
            [
                'url'         => $url,
                'title_hi'    => $title,
                'title_en'    => get_post_field( 'post_name', $post_id ),
                'link_type'   => 'post',
                'wp_post_id'  => $post_id,
                'keywords_hi' => $keywords_str,
                'keywords_en' => sanitize_title( $focus_kw ),
                'last_synced' => current_time( 'mysql' ),
                'is_active'   => 1,
            ],
            [ '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d' ]
        );
    }

    /**
     * Auto-assign matching featured image thumbnail from media library
     */
    public static function assign_featured_image( $post_id, $item ) {
        global $wpdb;

        $search_terms = [];
        if ( ! empty( $item['festival_id'] ) ) {
            $t_fest = MRCB_DB_Setup::table_festivals();
            $fest = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t_fest} WHERE id = %d", $item['festival_id'] ), ARRAY_A );
            if ( $fest && ! empty( $fest['festival_name_en'] ) ) {
                $search_terms[] = $fest['festival_name_en'];
                $parts = explode( ' ', $fest['festival_name_en'] );
                if ( ! empty( $parts[0] ) ) {
                    $search_terms[] = $parts[0];
                }
            }
        }

        $thumb_id = 0;
        foreach ( $search_terms as $term ) {
            $term = trim( $term );
            if ( strlen( $term ) < 3 ) {
                continue;
            }

            $attachments = get_posts( [
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'posts_per_page' => 1,
                's'              => $term,
            ] );

            if ( ! empty( $attachments ) ) {
                $thumb_id = $attachments[0]->ID;
                break;
            }
        }

        // Fallback: Pick a recent high-quality spiritual/festival image
        if ( ! $thumb_id ) {
            $fallback = get_posts( [
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'posts_per_page' => 1,
                'meta_key'       => '_wp_attachment_metadata',
                'orderby'        => 'ID',
                'order'          => 'DESC',
            ] );
            if ( ! empty( $fallback ) ) {
                $thumb_id = $fallback[0]->ID;
            }
        }

        if ( $thumb_id ) {
            set_post_thumbnail( $post_id, $thumb_id );
        }
    }
}
