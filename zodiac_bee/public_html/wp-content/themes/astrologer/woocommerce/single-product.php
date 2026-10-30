<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' );

$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;
 
$sidebar_position = '';
if(!empty($theme_option['woo_single_sidebar_postion'])):
  $sidebar_position = $theme_option['woo_single_sidebar_postion'];
else:
    if(! is_active_sidebar('wc-right-sidebar') && ! is_active_sidebar('wc-left-sidebar')):
        $sidebar_position = 'full';   
    elseif(is_active_sidebar('wc-right-sidebar') && is_active_sidebar('wc-left-sidebar')):
        $sidebar_position = 'both'; 
    elseif(is_active_sidebar('wc-left-sidebar')):
        $sidebar_position = 'left';
    elseif(is_active_sidebar('wc-right-sidebar')):
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

echo '<div class="al-single-product-page"> 
   	  <div class="container">
     	<div class="row"> ';
	   
        if($sidebar_position=='left' || $sidebar_position=='both'):
     	     echo '<div class="'.esc_attr($side_col).'">
     	           <div class="al-sidebar-wrapper">';
    	     	    if(is_active_sidebar('wc-left-sidebar')):
    				  dynamic_sidebar( 'wc-left-sidebar' );
    				endif; 
     	     echo '</div>
     	        </div>';    
        endif;
    echo '<div class="'.esc_attr($main_col).'">
    	       <div class="al-blog-columns">';     
    /**
	 * woocommerce_before_main_content hook.
	 *
	 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
	 * @hooked woocommerce_breadcrumb - 20
	 */
	do_action( 'woocommerce_before_main_content' );
    ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php wc_get_template_part( 'content', 'single-product' ); ?>

	<?php endwhile; // end of the loop. ?>

	<?php
	/**
	 * woocommerce_after_main_content hook.
	 *
	 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	do_action( 'woocommerce_after_main_content' );

    /**
	 * woocommerce_sidebar hook.
	 *
	 * @hooked woocommerce_get_sidebar - 10
	 */
	do_action( 'woocommerce_sidebar' );
	
    echo '</div>
    </div>';
    if($sidebar_position == 'right' || $sidebar_position=='both'):
        echo '<div class="'.esc_attr($side_col).'">
                <div class="al-sidebar-wrapper">';
                if(is_active_sidebar( 'wc-right-sidebar' ) ) : 
                    dynamic_sidebar( 'wc-right-sidebar' );
				else:
				    get_sidebar();
				    /**
                     * Hook: woocommerce_sidebar.
                     *
                     * @hooked woocommerce_get_sidebar - 10
                     * 
                     */
                    do_action( 'woocommerce_sidebar' );
				endif;
        echo '</div> 
           </div>'; 
    endif;
    echo  '</div>
       </div>
    </div>';
get_footer( 'shop' );
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */  