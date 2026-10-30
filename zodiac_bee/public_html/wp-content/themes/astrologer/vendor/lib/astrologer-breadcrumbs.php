<?php
$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;

$breadimg = '';
if(!empty($theme_option['breadcrumb_image']['url'])):
    $breadimg = 'style=background:url('.esc_url($theme_option['breadcrumb_image']['url']).');background-repeat:no-repeat;background-size:100%;';
endif;
if($theme_option['astrologer_global_breadcrumb']==1):
?>
<div class="al-breadcrumb-wrapper text-center" <?php if(!empty($theme_option['breadcrumb_image']['url'])): echo esc_html($breadimg); endif; ?>>
	<div class="container">
		<div class="row">
			<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
				<div class="al-breadcrumb-container">
					<?php 
					echo '<h1>';
						if(is_home() && have_posts()) {
						  esc_html_e('Blog','astrologer');
						}
						if(is_page()){
							while(have_posts()) { the_post();
							   the_title();
							}
						}
						if( class_exists("woocommerce") && is_product() && is_single()){
							the_title();
						}
						elseif(is_single()){ 
							  the_title();
						}
						if(class_exists( 'WooCommerce' ) ){
							if(is_shop()){
								esc_html_e('Shop','astrologer');
							}
							else{
								if(is_archive()){
									the_archive_title();
								}
							}
						}
						else{
							if(is_archive()){
							  the_archive_title();
							}
						}
						if(is_search()){
							printf( esc_html__( 'Search Results for: %s', 'astrologer' ), '<span>'.get_search_query().'</span>' );
						}
						if(is_404()){
							esc_html_e('404 Not Found','astrologer');
						}
					echo '</h1>';
					?> 
					<ul> 
					<?php
					if (is_category() || is_tag()) {
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						echo '<li>'. get_the_archive_title() .'</li>';
					}
					# Daily archive
					elseif (is_day()) {
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						echo '<li>'. get_the_time('F jS, Y') .'</li>';
					}
					# Monthly archive
					elseif (is_month()) {
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						echo '<li>'. get_the_time('F, Y') .'</li>';
					}
					# Yearly archive
					elseif (is_year()) {
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						echo '<li>'. get_the_time('Y') .'</li>';
					}
					# Author archive
					elseif (is_author()) {
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						echo '<li>'.is_author().'<li>';
					}
					# Paged archive
					elseif (is_paged()) {
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
					}
					elseif( class_exists("woocommerce") && is_product() && is_single()){
						global $post;
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						if(class_exists("woocommerce") && is_product() && is_single()){
							echo '<li>'. esc_html('Shop','astrologer') .'</li>';
						}
						echo "<li>". get_the_title() ."</li>";   
					}
					# Single post
					/*
					elseif (is_single()) {
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
							echo '<li>'. esc_html('Blog','astrologer') .'</li>';
						echo "<li>". get_the_title() ."</li>";
					}
					*/
                    elseif (is_single()) {
                        $post_type = get_post_type();
                        if ($post_type === 'mep_events') {
                            echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
                            echo '<li>'. esc_html('Events','astrologer') .'</li>'; // Changed to 'Events' instead of 'Blog'
                            echo '<li>'. esc_html(get_the_title()) .'</li>';
                        } else {
                            echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
                            echo '<li>'. esc_html('Blog','astrologer') .'</li>'; // Default to 'Blog' for other post types
                            echo '<li>'. esc_html(get_the_title()) .'</li>';
                        }
                    }


					# Single page

					elseif (!is_front_page() && is_home()) {
						global $post;
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						echo '<li>'. esc_html('Blog','astrologer') .'</li>';   
					}	

					elseif (is_page()) {
						global $post;
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						
						echo "<li>". get_the_title() ."</li>";   
					}
					elseif( class_exists("woocommerce") && is_shop()){
						global $post;
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__('Home','astrologer').'</a></li>';
						echo '<li>'. esc_html('Shop','astrologer') .'</li>'; 
					}
					else {
						echo '<li><a href="'.esc_url(home_url()).'">'.esc_html__("Home", "astrologer").'</a></li>';
					} ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div> 
<?php

endif;