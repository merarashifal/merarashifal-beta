<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package astrologer
 */

get_header();

$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;

$post_sidebar_option = get_post_meta(get_the_ID(),'astrologer_post_sidebar',true);

if($post_sidebar_option == true):
    
    $sidebar_position = get_post_meta(get_the_ID(),'astrologer_sidebar_postion',true);
    if(empty($sidebar_position) && $post_sidebar_option == true):
        $sidebar_position = 'right';
    else:
        if(!empty($sidebar_position)):
    	   $sidebar_position = get_post_meta(get_the_ID(),'astrologer_sidebar_postion',true);
    	else:
    	  if(! is_active_sidebar('left-sidebar') && ! is_active_sidebar('right-sidebar')):
                $sidebar_position = 'full';   
            elseif(is_active_sidebar('right-sidebar') && is_active_sidebar('left-sidebar')):
                $sidebar_position = 'both'; 
            elseif(is_active_sidebar('left-sidebar')):
                $sidebar_position = 'left';
            elseif(is_active_sidebar('right-sidebar')):
                $sidebar_position = 'right';
            endif;
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
	    if(!is_active_sidebar('left-sidebar') && ! is_active_sidebar('right-sidebar')):
            $sidebar_position = 'full';   
        elseif(is_active_sidebar('right-sidebar') && is_active_sidebar('left-sidebar')):
            $sidebar_position = 'both'; 
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
<section class="al-single-blog">
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
                <div class="al-blog-columns">     
                <?php
				while ( have_posts() ) : the_post();
					
                    get_template_part( 'template-parts/content', get_post_type());
                    
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
				 dynamic_sidebar( 'right-sidebar' );
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