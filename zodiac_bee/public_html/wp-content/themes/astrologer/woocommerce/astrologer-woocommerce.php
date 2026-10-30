<?php
/**
 * astrologer equipment woocommerce support
 */
add_action( 'after_setup_theme', 'astrologer_add_woocommerce_support' );
function astrologer_add_woocommerce_support() {
    add_theme_support('woocommerce'); 
    add_theme_support('wc-product-gallery-zoom' );
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}   
/**
 * woocommerce remove breadcrumb
 */
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0); 

/**
 *woocommerce remove product anchor tag
 */
remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5); 

/**
 * woocommerce product title
 */ 
remove_action( 'woocommerce_single_product_summary','woocommerce_template_single_title');
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title');  
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);

add_action("woocommerce_shop_loop_item_title",'astrologer_woocommerce_template_loop_product_title',10); 
function astrologer_woocommerce_template_loop_product_title() { 
    global $woocommerce, $product;
    $currency = get_woocommerce_currency_symbol();
    $price = get_post_meta( get_the_ID(), '_regular_price', true);
    $sale = get_post_meta( get_the_ID(), '_sale_price', true);
    $variation_price = get_post_meta( get_the_ID(), '_price', true);
    
    // Displaying the formatted "Min" - "Max" price range

    echo '<h4 class="al-sub-heading"><a href="'.esc_url(get_the_permalink(get_the_ID())).'">'.get_the_title().'</a></h4>'; 
   $result = '';
   if(!empty($sale) || !empty($price)):
       
       $product_saleprice = '';
       if(!empty($sale)):
        $product_saleprice = $currency .''.$sale;
       endif;
       $product_price = '';
       if(!empty($price)):
        $product_price = $currency .''.$price;
       endif;
      
   $result .='<span class="al-price">'.esc_html($product_saleprice);
						if(!empty($sale)){
						  $result.=' <del>'.esc_html($product_price).'</del>';
					 	  $discount_f = ($price-$sale)/$price;
						  $discount = $discount_f*100;
					 	  $result.=' <span class="al-orange">('.round($discount,2).'% Off)</span>';
						}
						else{
						  $result.= esc_html($product_price); 
						}
	$result.='</span>';
	else:
	    $product_variationprice = '';
           if(!empty($variation_price)):
            $product_variationprice = $currency .''.$variation_price;
           endif;
	    $result .='<span class="al-price">'.$product_variationprice.'</span>';
	endif;	
      echo wp_kses($result, true);
            	
}

/**
 * Change number of products that are displayed per page (shop page)
 */
add_filter('loop_shop_per_page', 'astrologer_loop_shop_per_page', 20 );
function astrologer_loop_shop_per_page( $cols ) {
   $cols = 12;
   return $cols;
}

/**
 * Filter WooCommerce Search Field
 */
add_filter('get_product_search_form' , 'astrologer_custom_product_searchform' );
function astrologer_custom_product_searchform( $form ) {
    $form = '<form  class="woocommerce-product-search" method="get" id="searchform" action="' . esc_url( home_url( '/'  ) ) . '">
            <input type="text" value="' . get_search_query() . '" name="s" id="ws" placeholder="' . esc_attr__( 'Search', 'astrologer' ) . '" />
            <button type="submit" class="search-submit al-btn">
	        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
        	 viewBox="0 0 512.005 512.005" xml:space="preserve">
            <g>
            	<g>
        		<path d="M505.749,475.587l-145.6-145.6c28.203-34.837,45.184-79.104,45.184-127.317c0-111.744-90.923-202.667-202.667-202.667
        			S0,90.925,0,202.669s90.923,202.667,202.667,202.667c48.213,0,92.48-16.981,127.317-45.184l145.6,145.6
        			c4.16,4.16,9.621,6.251,15.083,6.251s10.923-2.091,15.083-6.251C514.091,497.411,514.091,483.928,505.749,475.587z
        			 M202.667,362.669c-88.235,0-160-71.765-160-160s71.765-160,160-160s160,71.765,160,160S290.901,362.669,202.667,362.669z"/>
        	</g>
        </g>
        </svg>
	 </button>
   <input type="hidden" name="post_type" value="'.esc_attr__('product','astrologer').'" />
</form>';

return $form;
}
/**
 * add to cart remove from shop page
 */
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10); 

/**
 * remove woocommerce catalog ordering
 */ 
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/**
 * Remove "Description" Heading Title @ WooCommerce Single Product Tabs
 */
add_filter( 'woocommerce_product_description_heading', '__return_null');

/**
 * Remove "Reviews"  Title @ WooCommerce Single Product Tabs
 */
add_filter('woocommerce_product_reviews_heading', '__return_empty_string');
/**
 * WooCommerce Register widget area.
 */
add_action( 'widgets_init', 'astrologer_woocommerce_widgets_init' );
function astrologer_woocommerce_widgets_init() {
	
	register_sidebar(
		array(
			'name'          => esc_html__( 'WooCommerce Right Sidebar', 'astrologer' ),
			'id'            => 'wc-right-sidebar',
			'description'   => esc_html__( 'Add Right widgets here.', 'astrologer' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__( 'WooCommerce Left Sidebar', 'astrologer' ),
			'id'            => 'wc-left-sidebar',
			'description'   => esc_html__( 'Add Right widgets here.', 'astrologer' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}

/**
 * WooCommerc Product Function
 */ 
 if(!function_exists('astrologer_content_products')){
  function astrologer_content_products(){
    echo"hello testing";  
    global $product;
    $rating_count = $product->get_rating_count();
    $review_count = $product->get_review_count();
    $average = $product->get_average_rating();
    $result = '';
    if(has_post_thumbnail(get_the_ID())):
      $attachment_full_url = wp_get_attachment_url(get_post_thumbnail_id(get_the_ID()), 'full');
      $attachment_url = astrologer_aq_resize($attachment_full_url,255,300,true);
	endif;
    $hover_product_full_image = get_post_meta(get_the_ID(),'astrologer_product_image',true);
    $hover_product_image = astrologer_aq_resize($hover_product_full_image,255,300,true);
    $al_new_product = get_post_meta(get_the_ID(), 'astrologer_new_product', true);
    $newaddclass = '';
    if($al_new_product == 'new'):
      $newaddclass = 'al-new-product';
    endif;  
    $result .= '<div class="al-product-thumb">
                <div class="al-product-image '.esc_attr($newaddclass).'">';
    if($al_new_product == 'new'):
    $result .= '<span class="al-new-prod-tag">'.esc_html__('New','astrologer').'</span>';
    endif;
    if($product->get_sale_price()):
    $result .= '<span class="onsale">'.esc_html__('Sale!','astrologer').'</span>';
    endif;
    $result .= '<a href="'.esc_url(get_the_permalink()).'">
    				      <img src="'.esc_url($attachment_url).'" alt="'.esc_attr__('product-img','astrologer').'" class="img-fluid attachment-woocommerce_thumbnail size-woocommerce_thumbnail">
                        </a>';
                if(!empty($hover_product_image)):
                    $result .= '<div class="al-overlay-img">
                                <img src="'.esc_url($hover_product_image).'" alt="'.esc_attr__('product-img','astrologer').'" class="img-fluid">
                               </div>';  
                endif; 
    $result .= '</div> 
		        <div class="al-product-info">
                 <div class="al-product-hover">
               <ul> 
                    <li><a href="?add_to_wishlist='.esc_attr(get_the_ID()).'" class="al-product-icon toltiped add_to_wishlist single_add_to_wishlist" title="'.esc_attr('Wishlist','astrologer') .'" data-product-id="'.esc_attr(get_the_ID()).'"  data-original-product-id="'.esc_attr(get_the_ID()).'"  data-title="'.esc_attr__('Add to wishlist','astrologer').'">
            	    	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 16"><path class="cls-1" d="M849.5,4969a0.764,0.764,0,0,1-.385-0.11c-6.3-3.8-9.115-7.01-9.115-10.42a5.229,5.229,0,0,1,5.121-5.47,4.968,4.968,0,0,1,4.007,2.03,5.779,5.779,0,0,1,.372.52,5.779,5.779,0,0,1,.372-0.52,4.968,4.968,0,0,1,4.007-2.03,5.228,5.228,0,0,1,5.121,5.47c0,3.41-2.811,6.62-9.116,10.42A0.758,0.758,0,0,1,849.5,4969Zm-4.379-14.52a3.751,3.751,0,0,0-3.637,3.99,6.433,6.433,0,0,0,1.961,4.25,27.8,27.8,0,0,0,6.055,4.68,27.8,27.8,0,0,0,6.055-4.68,6.433,6.433,0,0,0,1.961-4.25,3.749,3.749,0,0,0-3.637-3.99,3.506,3.506,0,0,0-2.837,1.45,5.7,5.7,0,0,0-.834,1.51,0.743,0.743,0,0,1-1.417,0,5.589,5.589,0,0,0-.86-1.54A3.5,3.5,0,0,0,845.121,4954.48Z" transform="translate(-840 -4953)"/>
            	    	</svg>
            	    	</a>
                    </li>
                	<li>'; 
                		if($product = wc_get_product(get_the_ID())): 
						$result.=' <a href="'. esc_url($product->add_to_cart_url()) .'" class="al-product-icon toltiped" title="'.esc_attr__('Add To Cart','astrologer').'" data-productid="' . esc_attr(get_the_ID()). '">
                		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid"  viewBox="0 0 18 18">
                			<path d="M18.000,6.047 L18.000,8.858 C18.000,9.247 17.685,9.561 17.297,9.561 L16.834,9.561 L16.538,12.198 C16.498,12.557 16.193,12.823 15.840,12.823 C15.814,12.823 15.787,12.822 15.761,12.818 C15.375,12.775 15.097,12.427 15.141,12.041 L15.507,8.780 C15.547,8.425 15.848,8.156 16.206,8.156 L16.594,8.156 L16.594,6.749 L1.406,6.749 L1.406,8.156 L13.394,8.156 C13.783,8.156 14.097,8.470 14.097,8.858 C14.097,9.247 13.783,9.561 13.394,9.561 L2.594,9.561 L3.288,15.354 C3.372,16.060 3.973,16.593 4.684,16.593 L13.372,16.593 C14.089,16.593 14.689,16.056 14.770,15.343 C14.813,14.958 15.161,14.680 15.547,14.723 C15.933,14.766 16.210,15.114 16.167,15.501 C16.007,16.925 14.805,17.999 13.372,17.999 L4.684,17.999 C3.261,17.999 2.061,16.933 1.891,15.521 L1.177,9.561 L0.703,9.561 C0.315,9.561 -0.000,9.247 -0.000,8.858 L-0.000,6.047 C-0.000,5.658 0.315,5.344 0.703,5.344 L3.197,5.344 L6.912,0.287 C7.142,-0.026 7.582,-0.094 7.895,0.136 C8.208,0.366 8.276,0.807 8.045,1.119 L4.942,5.344 L13.084,5.344 L9.980,1.119 C9.750,0.807 9.817,0.366 10.130,0.136 C10.443,-0.094 10.883,-0.027 11.113,0.287 L14.829,5.344 L17.297,5.344 C17.685,5.344 18.000,5.658 18.000,6.047 L18.000,6.047 ZM8.297,11.671 L8.297,14.484 C8.297,14.871 8.612,15.186 9.000,15.186 C9.388,15.186 9.703,14.871 9.703,14.484 L9.703,11.671 C9.703,11.283 9.388,10.968 9.000,10.968 C8.612,10.968 8.297,11.283 8.297,11.671 L8.297,11.671 ZM11.109,11.671 L11.109,14.484 C11.109,14.871 11.424,15.186 11.812,15.186 C12.201,15.186 12.516,14.871 12.516,14.484 L12.516,11.671 C12.516,11.283 12.201,10.968 11.812,10.968 C11.424,10.968 11.109,11.283 11.109,11.671 L11.109,11.671 ZM5.484,11.671 L5.484,14.484 C5.484,14.871 5.799,15.186 6.187,15.186 C6.576,15.186 6.890,14.871 6.890,14.484 L6.890,11.671 C6.890,11.283 6.576,10.968 6.187,10.968 C5.799,10.968 5.484,11.283 5.484,11.671 L5.484,11.671 Z" class="cls-1"/>
                	</svg>
                	<span>'.esc_html__('Add To Cart','astrologer').'</span>
                	</a>';
                	endif; 
                	$result.='</li>
                	<li>
                		<a href="javascript:void(0)" class="al-product-icon toltiped" title="'.esc_attr__('View','astrologer').'">
                			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" viewBox="0 0 15 16.03">
                				<path d="M12.495,7.008 C12.472,7.833 12.249,8.928 11.684,9.910 C11.483,10.258 11.544,10.445 11.799,10.709 C12.740,11.679 13.663,12.670 14.581,13.666 C15.310,14.458 15.045,15.645 14.080,15.942 C13.550,16.106 13.126,15.887 12.755,15.488 C11.844,14.507 10.920,13.539 10.015,12.552 C9.799,12.317 9.647,12.252 9.340,12.431 C7.175,13.697 4.994,13.677 2.910,12.274 C0.871,10.901 -0.079,8.837 0.006,6.273 C0.109,3.165 2.412,0.521 5.354,0.066 C9.138,-0.519 12.513,2.610 12.495,7.008 ZM10.622,6.637 C10.616,4.092 8.648,1.986 6.275,1.985 C3.834,1.983 1.870,4.073 1.870,6.672 C1.870,9.230 3.853,11.330 6.262,11.325 C8.662,11.319 10.628,9.205 10.622,6.637 Z"></path>
                			</svg>
                		</a>
                	</li>
                </ul>
            </div>
            <h2 class="al-product-title">
                <a href="'.esc_url(get_permalink()).'">'.
                   get_the_title()
               .'</a>
            </h2>';
            if($rating_count >= 0 ) :
              $result .= wc_get_rating_html($average, $rating_count);
            endif;
            
        $result .= '<span class="price">';
                if($product->get_price_html()):
                  $result .= $product->get_price_html(); 
                endif;
    $result .= '</span>	
        </div>  
    </div>';
    return $result;
    }
 }
/**
 * woocommerce inline style remove
 */
 add_action( 'wp_print_styles', function(){
	wp_style_add_data( 'woocommerce-inline', 'after', '' );
});

add_filter('woocommerce_product_get_rating_html', 'astrologer_get_rating_html',5,2);
function astrologer_get_rating_html($rating_html, $rating) {
  if ( $rating > 0 ) {
	$title = sprintf( __( 'Rated %s out of 5', 'astrologer' ), $rating );
  } else {
	$title = esc_html__('Not yet rated','astrologer');
	$rating = 0;
  }

  $rating_html  = '<div class="star-rating" title="' .esc_attr($title). '">';
  $rating_html .= '<span style="width:' . ( ( $rating / 5 ) * 100 ) . '%"><strong class="rating">' . $rating . '</strong> ' . __( 'out of 5', 'astrologer' ) . '</span>';
  $rating_html .= '</div>';
  return $rating_html;
} 
