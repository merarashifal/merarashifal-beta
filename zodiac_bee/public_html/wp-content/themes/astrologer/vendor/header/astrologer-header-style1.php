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
$login_register_switch = '';
if(!empty($theme_option['login_register_switch'])):
 $login_register_switch = $theme_option['login_register_switch'];	
endif;
$mincart_switch = '';
if(!empty($theme_option['mincart_switch'])):
 $mincart_switch = $theme_option['mincart_switch'];	
endif;
$sticky_header_menu = '';
if(!empty($theme_option['sticky_header_menu'])):
   $sticky_header_menu = $theme_option['sticky_header_menu'];
endif;
?>
<header class="al-header-wrapper al-header-style1">
    <!--Header Style One Top Header-->
    <?php if($top_header== '1'){ ?>
    <div class="al-top-header-style">
        <div class="container">
            <div class="row">
                <div class=al-top-header-inner>
                    <div class="top-header-l">
                        <div class="al-header-contact"> 
							 <?php if($top_header== '1'){ ?>
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
                    <div class="top-header-r">
                        <div class="al-header-top-controls">  
                            <ul>
                                <li>
                                    <?php if($login_register_switch == '1') { ?>
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
                                                        fill: #ff7010;
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
            						 <?php } 
            						 } ?>
                                </li>
                                <li>
                                    <?php if($mincart_switch == '1'){ 
                                        $minicart_path = get_template_directory_uri( dirname( __FILE__ ) ).'/assets/images/cart.svg';  
                                         if(is_checkout() || is_cart()):
                                    ?>
                                    <div class="al-cart-wrapper">
                                        <a href="<?php echo esc_url(wc_get_cart_url()); ?>">
                                        <div class="al-cart">
                                            <span class="al-cart-icon">
                                                <img src="<?php echo esc_url($minicart_path); ?>" alt="<?php echo esc_attr__('cart','astrologer'); ?>">
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
                                            $minicart_path = get_template_directory_uri( dirname( __FILE__ ) ).'/assets/images/cart.svg'; 
                                            ?>
                                            <span class="al-cart-icon">
                                                <img src="<?php echo esc_url($minicart_path); ?>" alt="<?php echo esc_attr__('cart','astrologer'); ?>">
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
                                  } ?>
                                    
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
     <?php } ?>
    <!--Header Main Style One -->
    <div class="al-header-style-one al-wrapper-sticky">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-5">
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
                <div class="col-xl-8 col-lg-8 col-md-8 col-sm-7 col-7">
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
                            </div>  
                        </div>
                    </div>
            </div> 
        </div> 
    </div>
</header>  