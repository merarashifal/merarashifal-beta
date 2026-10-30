<?php
/**
 * astrologer comment form msg field below
 */
function astrologer_move_comment_form_below( $fields ) { 
    $comment_field = $fields['comment']; 
    unset( $fields['comment'] ); 
    $fields['comment'] = $comment_field; 
    return $fields; 
} 
add_filter( 'comment_form_fields', 'astrologer_move_comment_form_below' ); 
 
/**
 * astrologer comment list style
 */
function astrologer_comment_callback($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
	switch( $comment->comment_type ) :
	    case 'pingback' :
        case 'trackback' : ?>
            <li <?php comment_class(); ?> id="comment<?php comment_ID(); ?>">
            <div class="back-link"><?php comment_author_link(); ?></div>
        <?php break;
        default : 
		if ( 'div' === $args['style'] ) {
			$tag       = 'div';
			$add_below = 'comment';
		} else {
			$tag       = 'li';
			$add_below = 'div-comment';
		}
	?>

    <<?php echo esc_html($tag); ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID() ?>"><?php 
        if ( 'div' != $args['style'] ) { ?>
             <div id="div-comment-<?php comment_ID() ?>" class="comment-body al-comment-data">
                
        <?php } ?>
        <div class="comment-author"><?php 
			if ( $args['avatar_size'] != 0 ) {
				echo get_avatar( $comment, $args['avatar_size'] ); 
			} 
		?>
        </div>
        <?php 
        if ( $comment->comment_approved == '0' ) { ?>
			<em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.','astrologer'); ?></em><?php 
		} ?>
        <div class="comment-meta comment-info">	
            <div class="comment-head">
			   <h3><?php printf( __('<cite class="fn">%s</cite>', 'astrologer' ), get_comment_author_link() ); ?></h3>
                <p class="comment-date">
                    <a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ); ?>">
                        <?php
                        /* translators: 1: date, 2: time */
                        printf( 
                            
                            esc_html__('%1$s at %2$s', 'astrologer'), 

                            get_comment_date(),  

                            get_comment_time() 

                        );
                        ?>
                    </a>
                    <?php 
                    edit_comment_link( esc_html__('(Edit)', 'astrologer'), '  ', '' ); 
                    ?>
                </p>
               </div>
                <div class="comment-text">
                 <?php comment_text(); ?>
                </div>
                <?php 
                    comment_reply_link( 
						array_merge( 
							$args, 
							array( 
								'add_below' => $add_below, 
								'depth'     => $depth, 
                                'max_depth' => $args['max_depth'],
                                'reply_text' =>'<i class="fas fa-reply"></i>'.esc_html__('Reply','astrologer'),
                                'before' => '<div class="reply comment-reply">',
                                'after'=> '</div>'
							) 
						) 
					); 
					?>
			 </div>
			<?php 
            if ( 'div' != $args['style'] ) : ?>
			</div>
		 <?php 
		endif;
	break;
endswitch;
}