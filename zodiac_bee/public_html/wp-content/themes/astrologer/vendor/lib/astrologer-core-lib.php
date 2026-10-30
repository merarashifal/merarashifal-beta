<?php 
/**
 * astrologer core Features class
 * VERSION:1.0.0
 * @package astrologer
 */
Class Astrologer_setting {
	
	/**
     * Astrologer Top Header Settings
     */
	public function astrologer_top_header_settings(){
		
		$theme_option = '';
		if(function_exists('astrologer_theme_option_settings')):
		  $theme_option = astrologer_theme_option_settings();
		endif;
		
		$header_top_style = 'default';
		Switch($header_top_style):
		    case 'default':
			   get_template_part( 'vendor/header/astrologer-top-header', 'defult' );
			break;
			default:
			  get_template_part( 'vendor/header/astrologer-top-header', 'defult' );
		endswitch;
	}  
	/**
	 * Astrologer Header Settings.
	 */
	public function astrologer_header_settings(){
		$theme_option = '';
		if(function_exists('astrologer_theme_option_settings')):
		  $theme_option = astrologer_theme_option_settings();
		endif;
		
		$header_style = '';
		if(!empty($theme_option['header_style'])):
		  $header_style = $theme_option['header_style'];
		endif;
		switch ($header_style):
			case 'style-one':
			   get_template_part( 'vendor/header/astrologer-header', 'defulte' );
			break;
			case 'style-two':
			   get_template_part( 'vendor/header/astrologer-header', 'style1' );
			break;
			case 'style-tree':
			   get_template_part( 'vendor/header/astrologer-header', 'style2' );
			break;
			case 'style-four':
			   get_template_part( 'vendor/header/astrologer-header', 'style3' );
			break;
			default:
			   get_template_part( 'vendor/header/astrologer-header', 'defulte' );
		endswitch;
	} 
	/**
	 * Astrologer Breadcrumbs Settings
	 */
	public function astrologer_breadcrumbs_settings(){
		
		$theme_option = '';
		if(function_exists('astrologer_theme_option_settings')):
		  $theme_option = astrologer_theme_option_settings();
		endif;
		$page_breadcrumbs_settings = get_post_meta(get_the_ID(),'astrologer_page_breadcrumb',true);
		if(!empty($page_breadcrumbs_settings)):
		  $page_breadcrumbs_settings = get_post_meta(get_the_ID(),'astrologer_page_breadcrumb',true);
		endif;
		
		if($page_breadcrumbs_settings == true):
		   $page_breadcrumbs= get_post_meta(get_the_ID(),'astrologer_breadcrumb_on',true);
		   if(!empty($page_breadcrumbs)):
		    $page_breadcrumbs = get_post_meta(get_the_ID(),'astrologer_breadcrumb_on',true);
		   endif;
		   if($page_breadcrumbs == true):
		     get_template_part('vendor/lib/astrologer', 'breadcrumbs');
		   endif;
		else:
		    
			$global_breadcrumbs= '';
			if(!empty($theme_option['astrologer_global_breadcrumb'])):
			   $global_breadcrumbs = $theme_option['astrologer_global_breadcrumb'];
			else:
			   $global_breadcrumbs = true;
			endif;
			
			if($global_breadcrumbs == true):
				 get_template_part('vendor/lib/astrologer', 'breadcrumbs');
			endif;
		endif;
	} 
	
	/**
	 * Astrologer Footer Settings
	 */
	public function astrologer_footer_settings(){
		
		$theme_option = '';
		if(function_exists('astrologer_theme_option_settings')):
		  $theme_option = astrologer_theme_option_settings();
		endif;
		
		$footer_style = 'default';
	    switch($footer_style):
			case 'default':
			   get_template_part( 'vendor/footers/astrologer-footer', 'defult' );
			break; 
			default:
			   get_template_part( 'vendor/footers/astrologer-footer', 'defult' );
		endswitch;  
		
		/**
    	 * Astrologer Search Funtion Calling
    	 */
		if(function_exists('astrologercore_header_search')){
		    astrologercore_header_search();
		}
	}
	/**
	 * Astrologer 404 Page
	 */ 
	public function astrologer_404_setting($pageid){
	    
	    $theme_option = '';
		if(function_exists('astrologer_theme_option_settings')):
		  $theme_option = astrologer_theme_option_settings();
		endif;
        
        $error_404_title = '';
        if(!empty($theme_option['err_title'])):
           $error_404_title = $theme_option['err_title'];
        else:
           $error_404_title = esc_html__('','astrologer');
        endif;

        $error_404_desc = '';
        if(!empty($theme_option['error_404_desc'])):
          $error_404_desc = $theme_option['error_404_desc'];
        else:
          $error_404_desc = esc_html__("It looks like nothing was found at this location.","astrologer");
        endif; 
        $error_image_four_zero_four = '';
        if(!empty($theme_option['error_404_image']['url'])):
          $error_image_four_zero_four = $theme_option['error_404_image']['url'];
        else:
          $error_image_four_zero_four = get_template_directory_uri().'/assets/images/404.png';
        endif;
    ?>
	<div class="al-error-page-wrapper"> 
    	<div class="container">
	  	 <div class="row">
          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="al-error-info">
                <div class="al-error-img">
                    <?php 
                    if(!empty($error_image_four_zero_four)): ?>
                    <img class="parallax" src="<?php echo esc_url($error_image_four_zero_four); ?>" alt="<?php esc_attr_e('error 404','astrologer'); ?>" title="<?php esc_attr_e('404','astrologer'); ?>"/>
                    <?php 
                    endif; 
                    ?>
                </div>
                <div class="al-heading-wrap text-center">
                    <?php
                      if(!empty($error_404_title)):
                        echo '<h1 class="al-heading text-center">' . esc_html($error_404_title) . '</h1>';
                        endif; 
                        if(!empty($error_404_desc)):
                          echo '<h4>' . wp_kses($error_404_desc,true) . '</h4>';
                        endif;
                      ?>
                  </div>
              </div>  
            </div> 
          </div>
	 	</div>
   	</div> 
	<?php
	}
	
	public function astrologer_themeloader_setting(){
	    
	    $theme_option = '';
		if(function_exists('astrologer_theme_option_settings')){
		  $theme_option = astrologer_theme_option_settings();
		}
		$loader_switch = '';
        if(!empty($theme_option['loader_switch'])){
          $loader_switch = $theme_option['loader_switch'];
        }else{
          $loader_switch = esc_html__('on','astrologer');  
        }
        $loader_image = '';
        if(!empty($theme_option['page_loader']['url'])){
          $loader_image = $theme_option['page_loader']['url'];
        }else{
          $loader_image = get_template_directory_uri().'/assets/images/loader.png';
        } 
    	if($loader_switch == 1){
            if(!empty($loader_image)){
        ?>
        <div class="preloader">
           
          <div class="preloader-inner">
            <img src="<?php echo $loader_image; ?>" alt="<?php esc_attr_e('loading','astrologer'); ?>" />
          </div>
        </div>
        <?php  
          }
        }	
	}
	
} 