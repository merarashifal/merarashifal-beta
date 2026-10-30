<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package astrologer
 */

get_header();

$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;

$page_sidebar_option = get_post_meta(get_the_ID(),'astrologer_page_sidebar',true);
if($page_sidebar_option == true):
    
    $sidebar_position = get_post_meta(get_the_ID(),'astrologer_sidebar_postion',true);
    if(!empty($sidebar_position)):
       $sidebar_position = get_post_meta(get_the_ID(),'astrologer_sidebar_postion',true); 
    else:
       if(! is_active_sidebar('left-sidebar') && ! is_active_sidebar('right-sidebar')):
           $sidebar_position = 'full';    
        elseif(is_active_sidebar('left-sidebar')):
           $sidebar_position = 'left';
        elseif(is_active_sidebar('right-sidebar')):
           $sidebar_position = 'right';
        endif;
    endif;
    
    if($sidebar_position == 'both'):
		$side_col = 'col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12';
		$main_col = 'col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12';
	elseif($sidebar_position == 'full'):
		$side_col = '';
		$main_col = 'col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12';
	else:
		$side_col = 'col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12';
		$main_col = 'col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12';
	endif;  
else:
    $sidebar_position = '';
	if(!empty($theme_option['sidebar_postion'])):
	    $sidebar_position = $theme_option['sidebar_postion'];
	else:
	    if(! is_active_sidebar('left-sidebar') && ! is_active_sidebar('right-sidebar')):
           $sidebar_position = 'full';    
        elseif(is_active_sidebar('left-sidebar')):
           $sidebar_position = 'left';
        elseif(is_active_sidebar('right-sidebar')):
           $sidebar_position = 'right';
        endif;
    endif;
	
	if($sidebar_position == 'both'):
		$side_col = 'col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12';
		$main_col = 'col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12';
	elseif($sidebar_position == 'full'):
		$side_col = '';
		$main_col = 'col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12';
	else:
		$side_col = 'col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12';
		$main_col = 'col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12';
	endif; 
endif;
?>
<main id="primary" class="site-main">
   <section class="al-main-page-container">
     <div class="container">
        <div class="row">
		<?php 
		if($sidebar_position=='left' || $sidebar_position=='both'):
		?>
		<div class="<?php echo esc_attr($side_col); ?>">
			<div class="al-sidebar-wrapper al-left-sidebar">
			<?php 
			if(is_active_sidebar('left-sidebar')):
			  dynamic_sidebar( 'left-sidebar' );
			endif;
			?>
			</div>                      
		</div>   
		<?php endif; ?> 
		<div class="<?php echo esc_attr($main_col); ?>">
			<div class="al-page-columns">           
			<?php
			while(have_posts()) : the_post();
				
				get_template_part( 'template-parts/content', 'page' );
		
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
		
			endwhile; // End of the loop.
			?>  
			</div>     
		</div>    
		<?php 
		if($sidebar_position=='right' || $sidebar_position=='both'):
		?>
		<div class="<?php echo esc_attr($side_col); ?>">
			<div class="al-sidebar-wrapper al-right-sidebar">
			 <?php 
			  if(is_active_sidebar('right-sidebar')):
			   dynamic_sidebar('right-sidebar');
			  endif;
			 ?> 
			</div>                      
		</div>  
		<?php endif; ?>		
		</div>
    </div>
  </section> 
</main><!-- #main -->
<?php
get_footer();  