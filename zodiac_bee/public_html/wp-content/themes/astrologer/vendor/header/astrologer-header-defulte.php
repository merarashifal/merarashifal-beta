<?php
$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;
$site_logo = '';
if(!empty($theme_option['site_logo']['url'])):
 $site_logo = $theme_option['site_logo']['url'];	
endif;
$mobile_logo = '';
if(!empty($theme_option['mobile_logo']['url'])):
 $mobile_logo = $theme_option['mobile_logo']['url'];	
endif;
$top_header_swich = '';
if(!empty($theme_option['top_header_switch'])):
   $top_header_swich = $theme_option['top_header_switch'];
endif;
$search_switch = '';
if(!empty($theme_option['search_switch'])):
   $search_switch = $theme_option['search_switch'];
endif;
?>
<header class="al-header-wrapper al-wrapper-sticky">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-3 al-logo-wrap">
                <!--Desktop LOGO-->
                <div class="al-logo al-desktop-logo">
                    <?php if(!empty($site_logo)): ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <img src="<?php echo esc_url($site_logo); ?>" alt="<?php echo esc_attr__('logo','astrologer'); ?>" />
                    </a> 
                    <?php else: ?>
                    <a class="al-logo-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo get_bloginfo('name'); ?></a>
                    <?php endif; ?> 
				</div>
				<!--MOBILE LOGO-->
				<div class="al-logo al-mobile-logo">
    				<?php if(!empty($mobile_logo)): ?>
        				<a href="<?php echo esc_url(home_url('/')); ?>">
        				    <img src="<?php echo esc_url($mobile_logo); ?>" alt="<?php echo esc_attr__('logo','astrologer'); ?>" />
        				</a> 
        		    <?php else: ?>
        			    <a class="al-logo-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo substr(get_bloginfo( 'name' ),0,2); ?></a>
        			<?php endif; ?> 
    			</div>
			</div>
            <div class="col-xl-9 col-lg-9 col-md-8 col-sm-6 col-9 al-menu-wrap">
                    <div class="al-main-nav-wrapper">
                       <?php  if($top_header_swich == true): ?>
                        <?php if(is_active_sidebar('al-top-header') || is_active_sidebar('al-top-header-2')): ?>
                            <div class="al-top-header">
                                <?php
                                    if(class_exists('Astrologer_setting')):
                                       $theme_setting_option = new Astrologer_setting();
                                       //Top Header Settings 	 
                                	   $theme_setting_option->astrologer_top_header_settings();
                                    endif; 
                                endif;
                                ?>
                            </div>
                        <?php endif; ?>
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
</header>  