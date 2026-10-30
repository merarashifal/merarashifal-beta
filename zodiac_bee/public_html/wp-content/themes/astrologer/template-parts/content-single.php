<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package astrologer
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="al-blog-details">
	    <?php 
		$sl_dataclass = '';
	    if(has_post_thumbnail()): 	
		?>
		<div class="al-blog-single-imgae">
    		<?php 
    	astrologer_post_thumbnail();
    		?>
    		<span class="al-blog-date">
                <?php echo esc_html(get_the_date()); ?>
            </span>
		</div>	
		<?php endif; ?>
		<div class="al-blog-single-data">
			<?php 
			$sl_dataclass = 'al-date-with-thumb';
			?>
			<header class="entry-header al-blog-head">
			 <div class="al-post-meta <?php echo esc_attr($sl_dataclass); ?>">
				<a href="<?php echo esc_url(get_permalink()); ?>" class="al-blog-authore">
						 <span class="al-text-icon">
						   <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" viewBox="0 0 15 15">
                              <path d="M12.821,9.696 C12.001,8.879 11.026,8.274 9.962,7.908 C11.114,7.117 11.867,5.787 11.850,4.286 C11.823,1.899 9.838,-0.032 7.443,-0.001 C5.071,0.030 3.151,1.963 3.151,4.335 C3.151,5.816 3.899,7.126 5.039,7.908 C3.975,8.274 3.000,8.879 2.180,9.696 C0.910,10.962 0.150,12.600 0.003,14.363 C-0.026,14.705 0.242,15.000 0.586,15.000 L0.591,15.000 C0.897,15.000 1.149,14.764 1.175,14.460 C1.450,11.222 4.182,8.671 7.500,8.671 C10.819,8.671 13.550,11.222 13.826,14.460 C13.852,14.764 14.104,15.000 14.410,15.000 L14.414,15.000 C14.758,15.000 15.027,14.705 14.998,14.363 C14.850,12.600 14.090,10.962 12.821,9.696 ZM4.328,4.440 C4.268,2.613 5.772,1.113 7.606,1.173 C9.269,1.227 10.619,2.572 10.673,4.230 C10.732,6.058 9.228,7.557 7.395,7.498 C5.732,7.444 4.382,6.098 4.328,4.440 Z"></path>
                            </svg>
						  </span>
						  <?php esc_html_e('By ','astrologer'); ?>
						  <?php echo get_the_author(); ?>
					</a> 
					<a href="<?php echo esc_url(get_comments_link(get_the_ID())); ?>" class="al-comment">
						<span class="al-text-icon">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" viewBox="0 0 20 16">
									<path d="M19.208,11.276 C18.679,12.068 17.953,12.736 17.032,13.282 C17.106,13.464 17.182,13.630 17.260,13.781 C17.339,13.934 17.432,14.079 17.539,14.220 C17.647,14.360 17.731,14.470 17.790,14.549 C17.850,14.629 17.947,14.740 18.080,14.884 C18.214,15.028 18.300,15.122 18.337,15.168 C18.345,15.176 18.359,15.193 18.382,15.219 C18.404,15.245 18.421,15.265 18.432,15.276 C18.443,15.287 18.458,15.307 18.476,15.333 C18.495,15.359 18.508,15.380 18.516,15.395 L18.543,15.451 C18.543,15.451 18.551,15.474 18.566,15.520 C18.580,15.565 18.582,15.590 18.571,15.594 C18.560,15.597 18.557,15.622 18.560,15.667 C18.538,15.773 18.489,15.856 18.415,15.917 C18.341,15.978 18.258,16.004 18.169,15.997 C17.798,15.944 17.477,15.883 17.210,15.815 C16.064,15.512 15.030,15.027 14.107,14.360 C13.437,14.481 12.783,14.543 12.143,14.543 C10.126,14.543 8.370,14.043 6.875,13.043 C7.306,13.073 7.634,13.088 7.857,13.088 C9.055,13.088 10.205,12.918 11.305,12.577 C12.406,12.236 13.389,11.747 14.252,11.111 C15.182,10.413 15.897,9.611 16.395,8.703 C16.894,7.794 17.143,6.831 17.143,5.817 C17.143,5.233 17.057,4.658 16.886,4.089 C17.846,4.627 18.605,5.301 19.163,6.111 C19.721,6.922 20.000,7.794 20.000,8.725 C20.000,9.634 19.736,10.484 19.208,11.276 ZM11.802,10.856 C10.594,11.375 9.278,11.634 7.857,11.633 C7.217,11.633 6.562,11.573 5.893,11.452 C4.970,12.118 3.936,12.603 2.790,12.906 C2.522,12.974 2.203,13.035 1.831,13.088 L1.797,13.088 C1.715,13.088 1.639,13.058 1.568,12.997 C1.498,12.937 1.455,12.857 1.439,12.759 C1.432,12.736 1.429,12.711 1.429,12.685 C1.429,12.659 1.431,12.634 1.434,12.611 C1.438,12.588 1.445,12.565 1.456,12.543 L1.484,12.486 C1.484,12.486 1.498,12.466 1.524,12.424 C1.549,12.383 1.565,12.363 1.568,12.367 C1.572,12.371 1.589,12.352 1.619,12.311 C1.648,12.269 1.663,12.252 1.663,12.259 C1.700,12.214 1.786,12.119 1.920,11.975 C2.053,11.831 2.151,11.720 2.210,11.640 C2.270,11.561 2.353,11.451 2.461,11.310 C2.569,11.170 2.662,11.024 2.740,10.873 C2.818,10.722 2.894,10.555 2.969,10.373 C2.046,9.828 1.321,9.158 0.793,8.362 C0.264,7.567 0.000,6.718 0.000,5.817 C0.000,4.764 0.350,3.791 1.050,2.897 C1.749,2.004 2.703,1.297 3.912,0.778 C5.121,0.259 6.436,-0.001 7.857,-0.001 C9.279,-0.001 10.594,0.259 11.802,0.778 C13.012,1.297 13.966,2.003 14.665,2.897 C15.364,3.791 15.714,4.764 15.714,5.817 C15.714,6.870 15.364,7.844 14.665,8.737 C13.966,9.631 13.012,10.337 11.802,10.856 ZM13.410,3.647 C12.826,2.973 12.039,2.439 11.049,2.044 C10.059,1.651 8.996,1.454 7.857,1.454 C6.719,1.454 5.655,1.651 4.665,2.044 C3.676,2.439 2.889,2.973 2.305,3.647 C1.721,4.320 1.429,5.044 1.429,5.817 C1.429,6.438 1.626,7.036 2.020,7.612 C2.415,8.188 2.969,8.688 3.683,9.112 L4.766,9.748 L4.375,10.702 C4.628,10.551 4.859,10.403 5.067,10.259 L5.558,9.907 L6.150,10.021 C6.730,10.126 7.300,10.179 7.857,10.179 C8.996,10.179 10.059,9.983 11.049,9.589 C12.039,9.196 12.826,8.661 13.410,7.987 C13.994,7.313 14.286,6.590 14.286,5.817 C14.286,5.044 13.994,4.320 13.410,3.647 Z"></path>
								</svg>
						</span>
						<?php 
						if(get_comments_number(get_the_ID()) == 1):
						   echo get_comments_number(get_the_ID());  
						   esc_html_e(' Comment','astrologer');
						else:
						   echo get_comments_number(get_the_ID());  
						   esc_html_e(' Comments','astrologer');  
						endif;
						?>
					</a>
				</div>
			</header>
			<div class="entry-content">
				<?php
					the_content( sprintf(
						wp_kses(
							/* translators: %s: Name of current post. Only visible to screen readers */
							__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'astrologer' ),
							array(
								'span' => array(
									'class' => array(),
								),
							)
						),
						get_the_title()
					) );

					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'astrologer' ).'<span class="page-number">',
						'after'  => '</span></div>',
					) );
				?>
			</div>
		</div>	
		<!-- Authore Info -->
		<div class="al-categories-tags">
		    <div class="w-100 mb-3 mt-3">
		        <?php astrologer_categories_tags(); ?>
		    </div>
		</div>
		<!-- About Admin section start-->
		<?php 
        $admin_title = get_post_meta(get_the_ID(),'astrologer_admin_title',true);
        $admin_desc = get_post_meta(get_the_ID(),'astrologer_admin_desc',true);
        $admin_img = get_post_meta(get_the_ID(),'astrologer_admin_image',true);
        if(!empty($admin_img) && !empty($admin_desc) && !empty($admin_title)):
        ?>
		<div class="al-blog-authore-box">
	        <div class="al-blog-authore-img">
	        	<img src="<?php echo esc_url($admin_img['url']); ?>" alt="<?php echo esc_attr__('about admin','astrologer'); ?>" class="img-responsive">
	        </div>
        	<div class="al-blog-authore-info">
        		<h5><?php echo esc_html($admin_title) ?></h5>
        		<p><?php echo esc_html($admin_desc) ?></p>  
        	</div>
        </div>
        <?php 
        endif;
        ?>
        <!-- About Admin section End-->
		<div class="w-100">
		<?php
		if(astrologer_entry_footer()): ?>
		<div class="edit-btn-read-more-wrap">
			<!-- .entry-footer -->
			<footer class="entry-footer">
			    <div class="al-tag-cat">
				  <?php astrologer_entry_footer(); ?>
				</div>
			</footer>
		</div>
		<?php endif; ?>
		</div>
	</div>
</article>