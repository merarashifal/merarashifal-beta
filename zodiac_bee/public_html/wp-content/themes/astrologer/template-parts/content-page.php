<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package astrologer
 */
?> 
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="al-details-wrapper">
        <?php if(the_post_thumbnail()): ?>
		<div class="al-single-imgae-wrap">
			<div class="al-single-imgae">
			   <?php astrologer_post_thumbnail(); ?>
			</div>	
		</div>	
		<?php endif; ?>
		<!-- entry-content -->
		<div class="entry-content al-single-data">
    		<?php
    		the_content();
    	    wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'astrologer' ).'<span class="page-number">',
						'after'  => '</span></div>',
					) );
    		?>
    	</div><!-- .entry-content -->
    	<?php if ( get_edit_post_link() ) : ?>
			<div class="edit-btn-read-more-wrap">
        		<footer class="entry-footer">
        			<?php
        			edit_post_link(
        				sprintf(
        					wp_kses(
        						/* translators: %s: Name of current post. Only visible to screen readers */
        						__( 'Edit <span class="screen-reader-text">%s</span>', 'astrologer' ),
        						array(
        							'span' => array(
        								'class' => array(),
        							),
        						)
        					),
        					wp_kses_post( get_the_title() )
        				),
        				'<span class="edit-link">',
        				'</span>'
        			);
        			?>
        		</footer><!-- .entry-footer -->
        		</div>
    	    <?php endif; ?>
			<!-- .entry-footer -->
	</div>
</article><!-- #post-<?php the_ID(); ?> -->