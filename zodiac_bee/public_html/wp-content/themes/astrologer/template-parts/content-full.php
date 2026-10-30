<?php
/**
 * Template part for displaying page content
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Miraculous
 */

?>
<article id="post-<?php the_ID(); ?>">
    <?php astrologer_post_thumbnail(); ?>
    <div class="ms_entry_content">   
		<?php
		the_content();
        wp_link_pages( array(
			'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'astrologer' ),
			'after'  => '</div>',
		) );
		?>
	</div><!-- .entry-content -->
    <?php if ( get_edit_post_link() ) : ?>
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
					get_the_title()
				),
				'<span class="edit-link">',
				'</span>'
			);
			?>
		</footer><!-- .entry-footer -->
	<?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->