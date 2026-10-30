<?php 
$theme_option = '';
if(function_exists('astrologer_theme_option_settings')):
  $theme_option = astrologer_theme_option_settings();
endif;
// print_r($theme_option);
$newsswitch = '';
if(!empty($theme_option['newsletter_switch'])):
    $newsswitch = $theme_option['newsletter_switch'];
endif;
$astro_footer = '';
if( is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3') || is_active_sidebar('footer-4') ):
else:
$astro_footer = 'al-foooter-spacer';
endif;
$bg_image = '';
if ( ! empty( $theme_option['footer_bg_image'] ) && ! empty( $theme_option['footer_bg_image']['url']) ):
	$bg_image = 'style='.'background-image:url(' . $theme_option['footer_bg_image']['url'] . ');';
endif;
?>
<footer <?php echo esc_attr($bg_image); ?> class="al-footer-wrapper default-footer <?php echo esc_attr($astro_footer);?>">
  <?php
   if( is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3') || is_active_sidebar('footer-4') ):
  ?>
  <div class="container">
   <?php 
    if($newsswitch == 1):
		if(function_exists('astrologer_footer_newsletter')):
			astrologer_footer_newsletter(); 
		endif;
	endif;
    ?>
   <div class="row">
		<div class="col-xl-3 col-lg-6 col-md-12 col-sm-12 col-12">
			<div class="al-footer-widgets">
			  <?php dynamic_sidebar('footer-1');  ?>
			</div>
		</div>
		<div class="col-xl-3 col-lg-6 col-md-12 col-sm-12 col-12">
			<div class="al-footer-widgets">
			  <?php dynamic_sidebar('footer-2');  ?>
			</div>
		</div>
		<div class="col-xl-3 col-lg-6 col-md-12 col-sm-12 col-12">
			<div class="al-footer-widgets">
			  <?php dynamic_sidebar('footer-3');  ?>
			</div>
		</div>
		<div class="col-xl-3 col-lg-6 col-md-12 col-sm-12 col-12">
			<div class="al-footer-widgets">
			  <?php dynamic_sidebar('footer-4');  ?>
			</div>
		</div>
	</div> 
  </div>
  <?php
  endif;
  ?>
  <!-- Bottom Footer start -->
	<div class="al-copyright-wrapper text-center">
		<!-- GO To Top -->
		<?php 
		
		$bottomtotop_switcher = '';
		if(!empty($theme_option['footer_bottomtotop_switcher'])):
		  $bottomtotop_switcher = $theme_option['footer_bottomtotop_switcher'];
		endif;
    	 if($theme_option['backtotop'] == 1) { 
        // Alignment classes
        $alignment_class = '';
        if($theme_option['bakToTop_align'] == 'left') {
            $alignment_class = 'align-left';
        } elseif($theme_option['bakToTop_align'] == 'right') {
            $alignment_class = 'align-right';
        } elseif($theme_option['bakToTop_align'] == 'center') {
            $alignment_class = 'align-center';
        }
    
        // Button Color
        $button_color = isset($theme_option['backtotop_color']) ? $theme_option['backtotop_color'] : '#5575e3';
    
        // Custom Image
        $custom_image = isset($theme_option['backtotop_custom']['url']) ? $theme_option['backtotop_custom']['url'] : '';
    ?>
       <a href="javascript:void(0);" id="scroll" class="back-to-top <?php echo $alignment_class; ?>" style="background-color: <?php echo esc_attr($button_color); ?>;">
    <?php if($custom_image) { ?>
        <img src="<?php echo esc_url($custom_image); ?>" alt="Back to Top" />
    <?php } else { ?>
        <svg xmlns:xlink="http://www.w3.org/1999/xlink" width="15px" height="24px">
            <path fill-rule="evenodd" fill="rgb(255, 255, 255)" d="M6.638,0.361 C6.638,0.361 6.637,0.361 6.637,0.362 L0.357,6.757 C-0.114,7.237 -0.112,8.012 0.361,8.489 C0.834,8.966 1.599,8.964 2.069,8.485 L6.285,4.191 L6.285,22.770 C6.285,23.446 6.826,23.994 7.493,23.994 C8.160,23.994 8.701,23.446 8.701,22.770 L8.701,4.191 L12.917,8.485 C13.387,8.964 14.152,8.966 14.625,8.489 C15.098,8.012 15.099,7.237 14.629,6.757 L8.349,0.362 C8.349,0.361 8.348,0.361 8.348,0.361 C7.876,-0.119 7.109,-0.117 6.638,0.361 L6.638,0.361 Z"/>
        </svg>
    <?php } ?>
</a>

    <?php } ?>       
		<div class="container">
			<div class="row">
				<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
				<p>
				<?php
				if(!empty($theme_option['footer_text'])):
				   echo esc_html($theme_option['footer_text']);
				else:
					/* translators: 1: Theme name, 2: Theme author. */
					printf( esc_html__( 'Theme: %1$s by %2$s.', 'astrologer' ), 'astrologer', '<a href="https://themeforest.net/user/kamleshyadav">kamleshyadav</a>' );
				endif
				?>
				</p>
				</div>
			</div>
		</div>
	</div> 
</footer>
