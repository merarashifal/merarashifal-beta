<?php
$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;
$site_logo = '';
if(!empty($theme_option['site_logo']['url'])):
 $site_logo = $theme_option['site_logo']['url'];	
endif;
$search_switch = '';
if(!empty($theme_option['search_switch'])):
   $search_switch = $theme_option['search_switch'];
endif;
$mobile_icon = '';
if(!empty($theme_option['mobile_icon']['url'])):
 $mobile_icon = $theme_option['mobile_icon']['url'];	
endif;
$top_header = '';
if(!empty($theme_option['top_header_switch'])):
 $top_header = $theme_option['top_header_switch'];	
endif;
$mobile_number = '';
if(!empty($theme_option['mobile_number'])):
 $mobile_number = $theme_option['mobile_number'];	
endif;
$email_icon = '';
if(!empty($theme_option['email_icon']['url'])):
 $email_icon = $theme_option['email_icon']['url'];	
endif;
$email = '';
if(!empty($theme_option['email'])):
 $email = $theme_option['email'];	
endif;
$clock_icon = '';
if(!empty($theme_option['clock_icon']['url'])):
 $clock_icon = $theme_option['clock_icon']['url'];	
endif;
$clock = '';
if(!empty($theme_option['clock'])):
 $clock = $theme_option['clock'];	
endif;
$page_title = '';
if(!empty($theme_option['page_title'])):
 $page_title = $theme_option['page_title'];	
endif;
$page_url = '';
if(!empty($theme_option['page_url'])):
 $page_url = $theme_option['page_url'];	
endif;
$mincart_switch = '';
if(!empty($theme_option['mincart_switch'])):
 $mincart_switch = $theme_option['mincart_switch'];	
endif;
$login_register_switch = '';
if(!empty($theme_option['login_register_switch'])):
 $login_register_switch = $theme_option['login_register_switch'];	
endif;

$facebook = '';
if(!empty($theme_option['astrologer_facebook'])):
 $facebook = $theme_option['astrologer_facebook'];	
endif;
$twitter = '';
if(!empty($theme_option['astrologer_twitter'])):
 $twitter = $theme_option['astrologer_twitter'];	
endif;
$instagram = '';
if(!empty($theme_option['astrologer_instagram'])):
 $instagram = $theme_option['astrologer_instagram'];	
endif;
$linkedIn = '';
if(!empty($theme_option['astrologer_linkedIn'])):
 $linkedIn = $theme_option['astrologer_linkedIn'];	
endif;
$Youtube = '';
if(!empty($theme_option['astrologer_youtube'])):
 $Youtube = $theme_option['astrologer_youtube'];	
endif;
$sticky_header_menu = '';
if(!empty($theme_option['sticky_header_menu'])):
   $sticky_header_menu = $theme_option['sticky_header_menu'];
endif;
$sticky_header = '';
if($sticky_header_menu == true):
    $sticky_header = 'al-sticky-header';
endif;
?>
<header class="al-header-wrapper al-wrapper-sticky al-header-style1 al-header-style2 <?php echo esc_attr($sticky_header); ?>">
    <!--Header Style One Top Header-->
	<?php if($top_header== '1'){  ?>
	
    <div class="al-top-header-style">
        <div class="container">
            <div class="row">
                <div class=al-top-header-inner>
                    <div class="top-header-l">
                        <div class="al-header-contact"> 
							 <?php if($top_header== '1'){ 
							    $clock_path = get_template_directory_uri( dirname( __FILE__ ) ).'/assets/images/clock.svg';  
							 ?>
							 
                            <ul>
                                <li>
                                     <a href="tel:<?php echo esc_attr($mobile_number); ?>">
                                        <span class="as-infoicon">
                                           <?php if(!empty($mobile_icon)): ?>
										   <img src="<?php echo esc_url($mobile_icon); ?>" alt="<?php echo esc_attr__('phone','astrologer') ; ?>">
										   <?php  endif; ?>
                                        </span>
                                        <?php 
										if(!empty($mobile_number)):
										   echo esc_html($mobile_number);
										 endif;
										?>
                                    </a>
                                </li>
                                <li>
                                    <a href="mailto:<?php echo esc_attr($email); ?>">
                                        <span class="as-infoicon">
                                            <?php if(!empty($email_icon)): ?>
										   <img src="<?php echo esc_url($email_icon); ?>" alt="<?php echo esc_attr__('email','astrologer') ; ?>">
										   <?php  endif; ?>
                                        </span>
                                        <?php 
										if(!empty($email)):
										   echo esc_html($email);
										 endif;
										?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <span class="as-infoicon">
										  <?php if(!empty($clock_icon)): ?>
										   <img src="<?php echo esc_url($clock_icon); ?>" alt="<?php echo esc_attr__('clock','astrologer') ; ?>">
										   <?php  endif; ?>
                                        </span>
                                        <?php 
										if(!empty($clock)):
										   echo esc_html($clock);
										 endif;
										?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                     <?php } ?>
                    <div class="top-header-r">
                        <div class="al-header-top-controls">  
                        
                            <ul class="al-header-social">
                             <?php 
									if(!empty($facebook)):
								?>
                                <li>
            						<a href="<?php echo esc_url($facebook); ?>" target="_blank">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 155.139 155.139" xml:space="preserve">
                                            <g>
                                            	<path id="f_1_" d="M89.584,155.139V84.378h23.742l3.562-27.585H89.584V39.184
                                            		c0-7.984,2.208-13.425,13.67-13.425l14.595-0.006V1.08C115.325,0.752,106.661,0,96.577,0C75.52,0,61.104,12.853,61.104,36.452
                                            		v20.341H37.29v27.585h23.814v70.761H89.584z"/>
                                            </g>
                                        </svg>
            						</a>
                                </li>
                                <?php 
        						endif;
								if(!empty($instagram)):
									?>
                                <li>
                                    
            						<a href="<?php echo esc_url($instagram); ?>" target="_blank">
                                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
                                        	<g>
                                        		<g>
                                        			<path d="M352,0H160C71.648,0,0,71.648,0,160v192c0,88.352,71.648,160,160,160h192c88.352,0,160-71.648,160-160V160
                                        				C512,71.648,440.352,0,352,0z M464,352c0,61.76-50.24,112-112,112H160c-61.76,0-112-50.24-112-112V160C48,98.24,98.24,48,160,48
                                        				h192c61.76,0,112,50.24,112,112V352z"/>
                                        		</g>
                                        	</g>
                                        	<g>
                                        		<g>
                                        			<path d="M256,128c-70.688,0-128,57.312-128,128s57.312,128,128,128s128-57.312,128-128S326.688,128,256,128z M256,336
                                        				c-44.096,0-80-35.904-80-80c0-44.128,35.904-80,80-80s80,35.872,80,80C336,300.096,300.096,336,256,336z"/>
                                        		</g>
                                        	</g>
                                        	<g>
                                        		<g>
                                        			<circle cx="393.6" cy="118.4" r="17.056"/>
                                        		</g>
                                        	</g>
                                        </svg>
            						</a>
                                </li>
                                <?php 
    						    endif;
    						    if(!empty($linkedIn)):
								?>
                                <li>
            						<a href="<?php echo esc_url($linkedIn); ?>" target="_blank">
                                        <svg viewBox="-21 -35 682.66669 682" xmlns="http://www.w3.org/2000/svg"><path d="m77.613281-.667969c-46.929687 0-77.613281 30.816407-77.613281 71.320313 0 39.609375 29.769531 71.304687 75.8125 71.304687h.890625c47.847656 0 77.625-31.695312 77.625-71.304687-.894531-40.503906-29.777344-71.320313-76.714844-71.320313zm0 0"/><path d="m8.109375 198.3125h137.195313v412.757812h-137.195313zm0 0"/><path d="m482.054688 188.625c-74.011719 0-123.640626 69.546875-123.640626 69.546875v-59.859375h-137.199218v412.757812h137.191406v-230.5c0-12.339843.894531-24.660156 4.519531-33.484374 9.917969-24.640626 32.488281-50.167969 70.390625-50.167969 49.644532 0 69.5 37.851562 69.5 93.339843v220.8125h137.183594v-236.667968c0-126.78125-67.6875-185.777344-157.945312-185.777344zm0 0"/></svg>
            						</a>
            						
                                </li>
                                <?php 
                                endif;
                                if(!empty($Youtube)):
                                ?>
                                 <li>
            						<a href="<?php echo esc_url($Youtube); ?>" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 512.002 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="m334.809 170.992-113.114-61.89c-6.504-3.559-14.191-3.426-20.566.351-6.379 3.781-10.184 10.461-10.184 17.875v122.719c0 7.379 3.782 14.047 10.118 17.832 3.308 1.976 6.976 2.969 10.652 2.969a20.74 20.74 0 0 0 9.848-2.504L334.68 207.52a20.772 20.772 0 0 0 10.937-18.223 20.763 20.763 0 0 0-10.808-18.305zm-113.86 63.617v-91.718l84.54 46.257zm0 0" opacity="1" data-original="#000000" class=""></path><path d="m508.234 91.527-.023-.234c-.434-4.121-4.75-40.777-22.57-59.422C465.043 9.941 441.69 7.277 430.46 6a135.45 135.45 0 0 1-2.543-.305l-.895-.093C359.336.68 257.113.008 256.09.004L256 0l-.09.004C254.887.008 152.664.68 84.367 5.602l-.902.093c-.727.098-1.528.188-2.399.29-11.101 1.28-34.203 3.949-54.859 26.671C9.234 51.102 4.328 86.973 3.824 91.004l-.058.523C3.613 93.242 0 134.067 0 175.051v38.312c0 40.985 3.613 81.809 3.766 83.528l.027.257c.434 4.055 4.746 40.04 22.484 58.692 19.368 21.195 43.856 24 57.028 25.508 2.082.238 3.875.441 5.097.656l1.184.164c39.082 3.719 161.617 5.55 166.812 5.625l.157.004.156-.004c1.023-.004 103.242-.676 170.93-5.598l.894-.093c.856-.114 1.817-.215 2.871-.325 11.04-1.172 34.016-3.605 54.387-26.02 16.973-18.448 21.883-54.32 22.383-58.347l.058-.523c.153-1.719 3.77-42.54 3.77-83.524v-38.312c-.004-40.985-3.617-81.805-3.77-83.524zm-26.238 121.836c0 37.934-3.312 77-3.625 80.586-1.273 9.88-6.45 32.574-14.719 41.563-12.75 14.027-25.847 15.418-35.41 16.43a164.16 164.16 0 0 0-3.195.359c-65.469 4.734-163.832 5.46-168.363 5.488-5.082-.074-125.825-1.922-163.715-5.441-1.942-.317-4.04-.559-6.25-.809-11.215-1.285-26.567-3.043-38.371-16.027l-.278-.297c-8.125-8.465-13.152-29.688-14.43-41.149-.238-2.71-3.636-42.238-3.636-80.703v-38.312c0-37.89 3.305-76.914 3.625-80.574 1.52-11.637 6.793-32.957 14.719-41.575 13.14-14.453 26.996-16.054 36.16-17.113.875-.102 1.691-.195 2.445-.293 66.422-4.758 165.492-5.465 169.047-5.492 3.555.023 102.59.734 168.422 5.492.808.102 1.691.203 2.64.313 9.426 1.074 23.672 2.699 36.747 16.644l.12.129c8.126 8.465 13.153 30.059 14.43 41.75.227 2.559 3.637 42.172 3.637 80.719zm0 0" opacity="1" data-original="#000000" class=""></path></g></svg>
            						</a>
            						
                                </li>
                                <?php 
                                endif;
                                
                                if(!empty($twitter)):
									?>
                                <li>
            						<a href="<?php echo esc_url($twitter); ?>" target="_blank">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/></svg>
            						</a>
            						
                                </li>
                                <?php 
            					endif;
								?>
                            </ul>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php }  ?>
    <!--Header Main Style One -->
    <div class="al-header-style-one al-wrapper-sticky">
        <div class="container">
            <div class="row">
                
                <div class="col-xl-3 col-lg-5 col-md-4 col-sm-5 col-5">
                    <!-- LOGO-->
                    <div class="al-logo">
                        <?php if(!empty($site_logo)): ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <img src="<?php echo esc_url($site_logo); ?>" alt="<?php echo esc_attr__('logo','astrologer'); ?>" />
                        </a> 
                        <?php else: ?>
                        <a class="al-logo-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo get_bloginfo('name'); ?></a>
                        <?php endif; ?> 
    				</div>
    			</div>
    			
                <div class="col-xl-6 col-lg-1 col-md-1 col-sm-1 col-1">
                        <div class="al-main-nav-wrapper">
                            <div class="al-main-navigation">
                                <?php
                                /** 
                                 * Astrologer Menu Toggale 
                                 */
                                wp_nav_menu(array(
                                    'theme_location' => 'astrologer-header-menu',
                                    'menu_id'        => 'astrologer-menu',
                                    'menu_class'=> 'menu', 
                                    'fallback_cb'=> 'astrologer_menu_editor'
                                  ) 
                                );   
                                ?>
                            </div>  
                        </div>
                </div>
                    
                <div class="col-xl-3 col-lg-6 col-md-7 col-sm-6 col-6">
                    <div class="al-main-nav-wrapper">
                        <div class="al-header-option">
                            <div class="al-menu-toggle">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            
                            <?php
                            /** 
                             * Astrologer Search Toggale 
                             */
                            if($search_switch == true):
                            ?> 
                            <div class="al-search-toggle">
                              <span class="al-search-btn">
                                <svg class="al-search-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" viewBox="0 0 15 16.031">
                                  <path d="M12.490,7.014 C12.467,7.837 12.244,8.933 11.679,9.915 C11.478,10.263 11.539,10.450 11.794,10.713 C12.735,11.684 13.658,12.675 14.576,13.671 C15.305,14.463 15.040,15.650 14.075,15.948 C13.545,16.111 13.121,15.892 12.750,15.492 C11.839,14.512 10.915,13.545 10.010,12.558 C9.794,12.321 9.642,12.257 9.335,12.436 C7.170,13.702 4.989,13.682 2.905,12.279 C0.866,10.906 -0.084,8.842 0.001,6.278 C0.104,3.170 2.407,0.526 5.349,0.071 C9.133,-0.514 12.508,2.615 12.490,7.014 ZM10.617,6.642 C10.611,4.097 8.643,1.991 6.270,1.989 C3.829,1.988 1.865,4.078 1.865,6.678 C1.865,9.235 3.848,11.336 6.257,11.330 C8.657,11.324 10.623,9.210 10.617,6.642 Z"/>
                                </svg>
                              </span>
                            </div>
                            <?php endif; ?> 
                            
                            
                              <?php if($mincart_switch == '1'){ 
                                        $minicart_path = get_template_directory_uri( dirname( __FILE__ ) ).'/assets/images/cart-shop.svg';  
                                         if(is_checkout() || is_cart()):
                                    ?>
                                    <div class="al-cart-wrapper">
                                        <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                                        <div class="al-cart">
                                            <span class="al-cart-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" width="18" height="18" viewBox="0 0 18 18">
                                              <defs>
                                                <style>
                                                  .cls-1 {
                                                    fill: #ff5050;
                                                    fill-rule: evenodd;
                                                  }
                                                </style>
                                              </defs>
                                              <path d="M18.000,6.047 L18.000,8.858 C18.000,9.247 17.685,9.561 17.297,9.561 L16.834,9.561 L16.538,12.198 C16.498,12.557 16.193,12.823 15.840,12.823 C15.814,12.823 15.787,12.822 15.761,12.818 C15.375,12.775 15.097,12.427 15.141,12.041 L15.507,8.780 C15.547,8.425 15.848,8.156 16.206,8.156 L16.594,8.156 L16.594,6.749 L1.406,6.749 L1.406,8.156 L13.394,8.156 C13.783,8.156 14.097,8.470 14.097,8.858 C14.097,9.247 13.783,9.561 13.394,9.561 L2.594,9.561 L3.288,15.354 C3.372,16.060 3.973,16.593 4.684,16.593 L13.372,16.593 C14.089,16.593 14.689,16.056 14.770,15.343 C14.813,14.958 15.161,14.680 15.547,14.723 C15.933,14.766 16.210,15.114 16.167,15.501 C16.007,16.925 14.805,17.999 13.372,17.999 L4.684,17.999 C3.261,17.999 2.061,16.933 1.891,15.521 L1.177,9.561 L0.703,9.561 C0.315,9.561 -0.000,9.247 -0.000,8.858 L-0.000,6.047 C-0.000,5.658 0.315,5.344 0.703,5.344 L3.197,5.344 L6.912,0.287 C7.142,-0.026 7.582,-0.094 7.895,0.136 C8.208,0.366 8.276,0.807 8.045,1.119 L4.942,5.344 L13.084,5.344 L9.980,1.119 C9.750,0.807 9.817,0.366 10.130,0.136 C10.443,-0.094 10.883,-0.027 11.113,0.287 L14.829,5.344 L17.297,5.344 C17.685,5.344 18.000,5.658 18.000,6.047 L18.000,6.047 ZM8.297,11.671 L8.297,14.484 C8.297,14.871 8.612,15.186 9.000,15.186 C9.388,15.186 9.703,14.871 9.703,14.484 L9.703,11.671 C9.703,11.283 9.388,10.968 9.000,10.968 C8.612,10.968 8.297,11.283 8.297,11.671 L8.297,11.671 ZM11.109,11.671 L11.109,14.484 C11.109,14.871 11.424,15.186 11.812,15.186 C12.201,15.186 12.516,14.871 12.516,14.484 L12.516,11.671 C12.516,11.283 12.201,10.968 11.812,10.968 C11.424,10.968 11.109,11.283 11.109,11.671 L11.109,11.671 ZM5.484,11.671 L5.484,14.484 C5.484,14.871 5.799,15.186 6.187,15.186 C6.576,15.186 6.890,14.871 6.890,14.484 L6.890,11.671 C6.890,11.283 6.576,10.968 6.187,10.968 C5.799,10.968 5.484,11.283 5.484,11.671 L5.484,11.671 Z" class="cls-1"/>
                                            </svg>
                                                <span class="al-cartnumber">
                                                    <?php 
                                                    $contents_count = '';
                                                    if(class_exists('WooCommerce')):
                                                    $contents_count = WC()->cart->get_cart_contents_count();
                                                    endif;
                                                    printf(esc_html__('%s', 'astrologer' ),$contents_count);
                                                    ?>
                                                </span>
                                			</span>
                                          </div>
                                        </a>
                                    </div>
                                    <?php
                                    else:
                                    ?>
                                    <div class="al-cart-wrapper">
                                        <div class="al-cart">
                                            <?php
                                            $minicart_path = get_template_directory_uri( dirname( __FILE__ ) ).'/assets/images/cart-shop.svg'; 
                                            ?>
                                            <span class="al-cart-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" width="18" height="18" viewBox="0 0 18 18">
                                                  <defs>
                                                    <style>
                                                      .cls-1 {
                                                        fill: #ff5050;
                                                        fill-rule: evenodd;
                                                      }
                                                    </style>
                                                  </defs>
                                                  <path d="M18.000,6.047 L18.000,8.858 C18.000,9.247 17.685,9.561 17.297,9.561 L16.834,9.561 L16.538,12.198 C16.498,12.557 16.193,12.823 15.840,12.823 C15.814,12.823 15.787,12.822 15.761,12.818 C15.375,12.775 15.097,12.427 15.141,12.041 L15.507,8.780 C15.547,8.425 15.848,8.156 16.206,8.156 L16.594,8.156 L16.594,6.749 L1.406,6.749 L1.406,8.156 L13.394,8.156 C13.783,8.156 14.097,8.470 14.097,8.858 C14.097,9.247 13.783,9.561 13.394,9.561 L2.594,9.561 L3.288,15.354 C3.372,16.060 3.973,16.593 4.684,16.593 L13.372,16.593 C14.089,16.593 14.689,16.056 14.770,15.343 C14.813,14.958 15.161,14.680 15.547,14.723 C15.933,14.766 16.210,15.114 16.167,15.501 C16.007,16.925 14.805,17.999 13.372,17.999 L4.684,17.999 C3.261,17.999 2.061,16.933 1.891,15.521 L1.177,9.561 L0.703,9.561 C0.315,9.561 -0.000,9.247 -0.000,8.858 L-0.000,6.047 C-0.000,5.658 0.315,5.344 0.703,5.344 L3.197,5.344 L6.912,0.287 C7.142,-0.026 7.582,-0.094 7.895,0.136 C8.208,0.366 8.276,0.807 8.045,1.119 L4.942,5.344 L13.084,5.344 L9.980,1.119 C9.750,0.807 9.817,0.366 10.130,0.136 C10.443,-0.094 10.883,-0.027 11.113,0.287 L14.829,5.344 L17.297,5.344 C17.685,5.344 18.000,5.658 18.000,6.047 L18.000,6.047 ZM8.297,11.671 L8.297,14.484 C8.297,14.871 8.612,15.186 9.000,15.186 C9.388,15.186 9.703,14.871 9.703,14.484 L9.703,11.671 C9.703,11.283 9.388,10.968 9.000,10.968 C8.612,10.968 8.297,11.283 8.297,11.671 L8.297,11.671 ZM11.109,11.671 L11.109,14.484 C11.109,14.871 11.424,15.186 11.812,15.186 C12.201,15.186 12.516,14.871 12.516,14.484 L12.516,11.671 C12.516,11.283 12.201,10.968 11.812,10.968 C11.424,10.968 11.109,11.283 11.109,11.671 L11.109,11.671 ZM5.484,11.671 L5.484,14.484 C5.484,14.871 5.799,15.186 6.187,15.186 C6.576,15.186 6.890,14.871 6.890,14.484 L6.890,11.671 C6.890,11.283 6.576,10.968 6.187,10.968 C5.799,10.968 5.484,11.283 5.484,11.671 L5.484,11.671 Z" class="cls-1"/>
                                                </svg>
                                                <span class="al-cartnumber">
                                                    <?php 
                                                    $contents_count = '';
                                                    if(class_exists('WooCommerce')):
                                                     $contents_count = WC()->cart->get_cart_contents_count();
                                                    endif;
                                                    printf(esc_html__('%s', 'astrologer' ),$contents_count);
                                                    ?>
                                                </span>
                                			</span>
                                        </div>
                                        <div class="al-cart-box">
                                			<div class="al-cart-list">
                                			  <?php 
                                			  if(class_exists('WooCommerce')):
                                				woocommerce_mini_cart();
                                			  endif;
                                			  ?>
                                			</div>
                                		</div>
                                    </div>  
                                    <?php
                                      endif;
                                    } 
                             if($login_register_switch == '1'){  ?>
                             <div class="al-user-profile">
        						<?php 
        						$logout_url = wp_logout_url(home_url());
        						if(is_user_logged_in()): 
        						 ?>
        						 <a href="<?php echo esc_url($logout_url); ?>">
        							 <span class="as-infoicon">
        							    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" width="20" height="20" viewBox="0 0 390 428">
                                          <defs>
                                            <style>
                                              .cls-1 {
                                                fill: #ff7010;
                                                fill-rule: evenodd;
                                              }
                                            </style>
                                          </defs>
                                          <path d="M195.000,428.000 C87.214,428.000 -0.000,340.654 -0.000,232.725 C-0.000,160.730 39.008,97.460 97.489,63.583 C103.475,60.117 111.135,62.165 114.598,68.159 C118.058,74.151 116.015,81.825 110.028,85.292 C59.212,114.729 25.043,169.764 25.043,232.725 C25.043,326.572 101.286,402.922 195.000,402.922 C288.714,402.922 364.957,326.572 364.957,232.725 C364.957,169.806 330.820,114.746 279.970,85.291 C273.983,81.823 271.940,74.151 275.401,68.158 C278.864,62.164 286.524,60.116 292.510,63.582 C350.965,97.445 390.001,160.700 390.001,232.725 C390.001,340.664 302.777,428.000 195.000,428.000 ZM227.239,70.864 L207.521,70.864 L207.521,290.745 C207.521,297.669 201.916,303.284 195.000,303.284 C188.084,303.284 182.478,297.669 182.478,290.745 L182.478,70.863 L162.760,70.863 C152.632,70.863 146.685,59.394 152.525,51.097 L184.765,5.303 C189.797,-1.830 200.313,-1.688 205.233,5.305 L237.473,51.100 C243.270,59.335 237.433,70.864 227.239,70.864 Z" class="cls-1"/>
                                        </svg>
        							</span>
        							<span class="as-logintext"><?php echo esc_html__('Logout','astrologer'); ?></span>
        						</a>
        						<?php else: ?>
        						 <a href="<?php echo esc_url($page_url); ?>">
        							<span class="as-infoicon">
            							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="xMidYMid" width="18" height="18" viewBox="0 0 18 18">
                                            <defs>
                                                <style>
                                                  .cls-1 {
                                                    fill:#ff7010;
                                                    fill-rule: evenodd;
                                                  }
                                                </style>
                                            </defs>
                                            <path d="M17.250,14.249 L14.250,14.249 L14.250,17.249 C14.250,17.664 13.915,17.999 13.500,17.999 C13.085,17.999 12.750,17.664 12.750,17.249 L12.750,14.249 L9.750,14.249 C9.335,14.249 9.000,13.914 9.000,13.499 C9.000,13.085 9.335,12.749 9.750,12.749 L12.750,12.749 L12.750,9.750 C12.750,9.336 13.085,8.999 13.500,8.999 C13.915,8.999 14.250,9.336 14.250,9.750 L14.250,12.749 L17.250,12.749 C17.665,12.749 18.000,13.085 18.000,13.499 C18.000,13.914 17.665,14.249 17.250,14.249 ZM7.500,10.499 C6.505,10.499 5.588,10.171 4.842,9.622 C2.863,10.604 1.500,12.645 1.500,14.999 L1.500,15.749 C1.500,16.163 1.836,16.499 2.250,16.499 L9.000,16.499 C9.415,16.499 9.750,16.836 9.750,17.249 C9.750,17.664 9.415,17.999 9.000,17.999 L2.250,17.999 C1.009,17.999 -0.000,16.990 -0.000,15.749 L-0.000,14.999 C-0.000,12.224 1.519,9.805 3.766,8.509 C3.283,7.791 3.000,6.928 3.000,5.999 L3.000,4.499 C3.000,2.018 5.018,-0.001 7.500,-0.001 C9.982,-0.001 12.000,2.018 12.000,4.499 L12.000,5.999 C12.000,8.481 9.982,10.499 7.500,10.499 ZM10.500,4.499 C10.500,2.845 9.154,1.499 7.500,1.499 C5.845,1.499 4.500,2.845 4.500,4.499 L4.500,5.999 C4.500,7.653 5.845,8.999 7.500,8.999 C9.154,8.999 10.500,7.653 10.500,5.999 L10.500,4.499 Z" class="cls-1"/>
                                        </svg>
        							</span>
        							<span class="as-logintext"><?php echo esc_html($page_title); ?></span>
        						 </a>
        						<?php endif; ?>
        					</div>
                            <?php } ?>
                        </div>  
                    </div>
                </div>
            </div> 
        </div> 
    </div>
</header>  