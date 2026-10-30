<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
 
$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;
$sidebar_position = '';
if(!empty($theme_option['woo_sidebar_postion'])):
  $sidebar_position = $theme_option['woo_sidebar_postion'];
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

$sidebar_class = '';
if($sidebar_position=='left' || $sidebar_position=='right' || $sidebar_position=='both'):
  $sidebar_class = 'al_shop_list_sidebar';
endif; 

echo '<div class="al-shop-wrapper top-spacer bottom-spacer '.esc_attr($sidebar_class).'"> 
     	<div class="container">
	     	<div class="row">';
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
             * Hook: woocommerce_before_main_content.
             *
             * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
             * @hooked woocommerce_breadcrumb - 20
             * @hooked WC_Structured_Data::generate_website_data() - 30
             */
            do_action( 'woocommerce_before_main_content' );
        ?>
        <?php
    	/**
    	 * Hook: woocommerce_archive_description.
    	 *
    	 * @hooked woocommerce_taxonomy_archive_description - 10
    	 * @hooked woocommerce_product_archive_description - 10
    	 */
    	do_action( 'woocommerce_archive_description' );
    	?>
    	
       <?php
       if(woocommerce_product_loop()){
        ?>
        <div class="al_product_filters">
        <?php
        /**
    	 * Hook: woocommerce_before_shop_loop.
    	 *
    	 * @hooked woocommerce_output_all_notices - 10
    	 * @hooked woocommerce_result_count - 20
    	 * @hooked woocommerce_catalog_ordering - 30
    	 */
    	 do_action( 'woocommerce_before_shop_loop' );
         ?>
         <div class="al-catalog-ordering">
            <?php woocommerce_catalog_ordering(); ?> 
         </div>
        </div> 
        <?php
	    woocommerce_product_loop_start();

    	if ( wc_get_loop_prop( 'total' ) ) {
    		while ( have_posts() ) {
    			the_post();
    
    			/**
    			 * Hook: woocommerce_shop_loop.
    			 *
    			 * @hooked WC_Structured_Data::generate_product_data() - 10
    			 */
    			do_action( 'woocommerce_shop_loop' );
    
    			wc_get_template_part( 'content', 'product' );
    		}
    	}

	   woocommerce_product_loop_end();
     
	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );
    } else {
    	/**
    	 * Hook: woocommerce_no_products_found.
    	 *
    	 * @hooked wc_no_products_found - 10
    	 */
    	do_action( 'woocommerce_no_products_found' );
    }
    ?>
    
    <?php
    /**
     * Hook: woocommerce_after_main_content.
     *
     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
     */
    do_action( 'woocommerce_after_main_content' );
    
    echo '</div>
        </div>';
    if($sidebar_position == 'right' || $sidebar_position=='both'):
        echo '<div class="'.esc_attr($side_col).'">
                <div class="al-sidebar-wrapper">';
                if(is_active_sidebar( 'wc-right-sidebar' ) ) : 
                    dynamic_sidebar('wc-right-sidebar');
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