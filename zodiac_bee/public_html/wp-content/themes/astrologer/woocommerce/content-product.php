<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
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
if($sidebar_position == 'full'):
  $contenr_class = 'col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12';
else:
  $contenr_class = 'col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12'; 
endif;
$hover_product_full_image = get_post_meta(get_the_ID(),'astrologer_product_image',true);
$hover_product_image = astrologer_aq_resize($hover_product_full_image,255,300,true);
$al_new_product = get_post_meta(get_the_ID(), 'astrologer_new_product', true);
$newaddclass = '';
if($al_new_product == 'new'):
  $newaddclass = 'al-new-product';
endif;  
?>
<div class="<?php echo esc_attr($contenr_class); ?>">
    <div class="al-product-thumb">
        <div class="al-product-image <?php echo esc_attr($newaddclass); ?>"> 
        <?php if($al_new_product == 'new'): ?>
          <span class="al-new-prod-tag"><?php echo esc_html__('New','astrologer'); ?></span>
        <?php
        endif;
    	/**
    	 * Hook: woocommerce_before_shop_loop_item.
    	 *
    	 * @hooked woocommerce_template_loop_product_link_open - 10
    	 */
    	do_action( 'woocommerce_before_shop_loop_item' );
        
    	/**
    	 * Hook: woocommerce_before_shop_loop_item_title.
    	 *
    	 * @hooked woocommerce_show_product_loop_sale_flash - 10
    	 * @hooked woocommerce_template_loop_product_thumbnail - 10
    	 */
    	do_action( 'woocommerce_before_shop_loop_item_title' );
    	if(!empty($hover_product_image)):
    	?>
    	<div class="al-overlay-img">
          <img src="<?php echo esc_url($hover_product_image); ?>" alt="<?php echo esc_attr__('product-img','astrologer'); ?>" class="img-fluid">
        </div>
        <?php 
        endif;
        ?>
        <div class="al-product-hover">
			<ul> 
            	<li><a href="?add_to_wishlist=<?php echo esc_attr(get_the_ID()); ?>" class="al-product-icon toltiped add_to_wishlist single_add_to_wishlist" title="<?php echo esc_attr('Wishlist','astrologer'); ?>" data-product-id="<?php echo esc_attr(get_the_ID()); ?>"  data-original-product-id="<?php echo esc_attr(get_the_ID()); ?>"  data-title="<?php esc_attr__('Add to wishlist','astrologer'); ?>">
            		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 16"><path class="cls-1" d="M849.5,4969a0.764,0.764,0,0,1-.385-0.11c-6.3-3.8-9.115-7.01-9.115-10.42a5.229,5.229,0,0,1,5.121-5.47,4.968,4.968,0,0,1,4.007,2.03,5.779,5.779,0,0,1,.372.52,5.779,5.779,0,0,1,.372-0.52,4.968,4.968,0,0,1,4.007-2.03,5.228,5.228,0,0,1,5.121,5.47c0,3.41-2.811,6.62-9.116,10.42A0.758,0.758,0,0,1,849.5,4969Zm-4.379-14.52a3.751,3.751,0,0,0-3.637,3.99,6.433,6.433,0,0,0,1.961,4.25,27.8,27.8,0,0,0,6.055,4.68,27.8,27.8,0,0,0,6.055-4.68,6.433,6.433,0,0,0,1.961-4.25,3.749,3.749,0,0,0-3.637-3.99,3.506,3.506,0,0,0-2.837,1.45,5.7,5.7,0,0,0-.834,1.51,0.743,0.743,0,0,1-1.417,0,5.589,5.589,0,0,0-.86-1.54A3.5,3.5,0,0,0,845.121,4954.48Z" transform="translate(-840 -4953)"/>
            		</svg>
            		</a>
            	</li>
            	<li>
            	<?php if($product = wc_get_product(get_the_ID())): ?>    
			    <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="al-product-icon toltiped" title="<?php echo esc_attr__('Cart','astrologer'); ?>" data-productid="<?php echo esc_attr(get_the_ID()); ?>">
            		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid"  viewBox="0 0 18 18">
            		  <path d="M18.000,6.047 L18.000,8.858 C18.000,9.247 17.685,9.561 17.297,9.561 L16.834,9.561 L16.538,12.198 C16.498,12.557 16.193,12.823 15.840,12.823 C15.814,12.823 15.787,12.822 15.761,12.818 C15.375,12.775 15.097,12.427 15.141,12.041 L15.507,8.780 C15.547,8.425 15.848,8.156 16.206,8.156 L16.594,8.156 L16.594,6.749 L1.406,6.749 L1.406,8.156 L13.394,8.156 C13.783,8.156 14.097,8.470 14.097,8.858 C14.097,9.247 13.783,9.561 13.394,9.561 L2.594,9.561 L3.288,15.354 C3.372,16.060 3.973,16.593 4.684,16.593 L13.372,16.593 C14.089,16.593 14.689,16.056 14.770,15.343 C14.813,14.958 15.161,14.680 15.547,14.723 C15.933,14.766 16.210,15.114 16.167,15.501 C16.007,16.925 14.805,17.999 13.372,17.999 L4.684,17.999 C3.261,17.999 2.061,16.933 1.891,15.521 L1.177,9.561 L0.703,9.561 C0.315,9.561 -0.000,9.247 -0.000,8.858 L-0.000,6.047 C-0.000,5.658 0.315,5.344 0.703,5.344 L3.197,5.344 L6.912,0.287 C7.142,-0.026 7.582,-0.094 7.895,0.136 C8.208,0.366 8.276,0.807 8.045,1.119 L4.942,5.344 L13.084,5.344 L9.980,1.119 C9.750,0.807 9.817,0.366 10.130,0.136 C10.443,-0.094 10.883,-0.027 11.113,0.287 L14.829,5.344 L17.297,5.344 C17.685,5.344 18.000,5.658 18.000,6.047 L18.000,6.047 ZM8.297,11.671 L8.297,14.484 C8.297,14.871 8.612,15.186 9.000,15.186 C9.388,15.186 9.703,14.871 9.703,14.484 L9.703,11.671 C9.703,11.283 9.388,10.968 9.000,10.968 C8.612,10.968 8.297,11.283 8.297,11.671 L8.297,11.671 ZM11.109,11.671 L11.109,14.484 C11.109,14.871 11.424,15.186 11.812,15.186 C12.201,15.186 12.516,14.871 12.516,14.484 L12.516,11.671 C12.516,11.283 12.201,10.968 11.812,10.968 C11.424,10.968 11.109,11.283 11.109,11.671 L11.109,11.671 ZM5.484,11.671 L5.484,14.484 C5.484,14.871 5.799,15.186 6.187,15.186 C6.576,15.186 6.890,14.871 6.890,14.484 L6.890,11.671 C6.890,11.283 6.576,10.968 6.187,10.968 C5.799,10.968 5.484,11.283 5.484,11.671 L5.484,11.671 Z" class="cls-1"/>
            		</svg>
            		<span><?php esc_html_e('Add To Cart', 'astrologer'); ?></span>
            		</a>
            	<?php endif; ?>
            	</li>
            	<li>
            		<a href="javascript:void(0)" class="al-product-icon toltiped yith-wcqv-button" data-product_id="<?php echo esc_attr(get_the_ID()); ?>" title="<?php echo esc_attr__('View','astrologer'); ?>">
            			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" viewBox="0 0 15 16.03">
            				<path d="M12.495,7.008 C12.472,7.833 12.249,8.928 11.684,9.910 C11.483,10.258 11.544,10.445 11.799,10.709 C12.740,11.679 13.663,12.670 14.581,13.666 C15.310,14.458 15.045,15.645 14.080,15.942 C13.550,16.106 13.126,15.887 12.755,15.488 C11.844,14.507 10.920,13.539 10.015,12.552 C9.799,12.317 9.647,12.252 9.340,12.431 C7.175,13.697 4.994,13.677 2.910,12.274 C0.871,10.901 -0.079,8.837 0.006,6.273 C0.109,3.165 2.412,0.521 5.354,0.066 C9.138,-0.519 12.513,2.610 12.495,7.008 ZM10.622,6.637 C10.616,4.092 8.648,1.986 6.275,1.985 C3.834,1.983 1.870,4.073 1.870,6.672 C1.870,9.230 3.853,11.330 6.262,11.325 C8.662,11.319 10.628,9.205 10.622,6.637 Z"></path>
            			</svg>
            		</a>
            	</li>
            </ul>
		 </div>
       </div>  
   	   <div class="al-product-info">
	    <?php 
	     /**
		 * Hook: woocommerce_after_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_rating - 5
		 * @hooked woocommerce_template_loop_price - 15
		 */
	        do_action('woocommerce_after_shop_loop_item_title');
		
		/**
		 * Hook: woocommerce_shop_loop_item_title.
		 *
		 * @hooked woocommerce_template_loop_product_title - 10
		 */
	    	do_action('woocommerce_shop_loop_item_title');
       
		/**
		 * Hook: woocommerce_after_shop_loop_item.
		 *
		 * @hooked woocommerce_template_loop_product_link_close - 5
		 * @hooked woocommerce_template_loop_add_to_cart - 10
		 */
	    	 do_action('woocommerce_after_shop_loop_item');
		 ?>
		</div>
	</div>
</div>