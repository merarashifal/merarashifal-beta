<?php 

$astrologer_options = '';
    if(function_exists('astrologer_theme_option_settings')):
        $astrologer_options = astrologer_theme_option_settings();
endif;
if (!empty($astrologer_options)) {
$theme_bg_color = '';
if(!empty($astrologer_options['theme_bg_color'])):
 $theme_bg_color = $astrologer_options['theme_bg_color'];	
endif;
$main_bg = $astrologer_options['theme_bg_color'];
$global_color = $astrologer_options['body_font_color'];
$primary = $astrologer_options['solid_primary_color'];
$primary_light_color = $astrologer_options['primary_light_color'];
$secondary_color = $astrologer_options['secondary_color'];
$global_title_color = $astrologer_options['body_title_color'];
$main_title_size = $astrologer_options['body_heading_size'];
$global_font_size = $astrologer_options['body_font_size'];
$gradient_color1 = $astrologer_options['grad_color_one'];
$gradient_color2 = $astrologer_options['grad_color_two'];
$top_header_bg = $astrologer_options['top_header_bg'];
$top_header_color = $astrologer_options['top_header_color']; 
$header_bg_color = $astrologer_options['header_bg_color'];
$footer_bg_color = $astrologer_options['footer_bg_color'];
$input_field_bg_color = $astrologer_options['input_field_bg_color'];
$copyright_bg_color = $astrologer_options['copyright_bg_color'];
$alternate_bg = $astrologer_options['alternate_bg'];
$button_hover_color = $astrologer_options['button_hover_color'];
$button_border_radius = $astrologer_options['button_border_radius'];
}


?>

<style type="text/css">
:root {
    --al-main-bg: <?php echo esc_html($main_bg);?>;
    --al-font-color: <?php echo esc_html($global_color);?>;
    --al-title-color: <?php echo esc_html($global_title_color);?>;
    --al-primary-color: <?php echo esc_html($primary);?>;
    --al-primary-light: <?php echo esc_html($primary_light_color);?>; 
    --al-dark: <?php echo esc_html($secondary_color);?>;
    --al-title-size: <?php echo esc_html($main_title_size);?>;
    --al-font-size: <?php echo esc_html($global_font_size);?>;
    --al-gradient-color1: <?php echo esc_html($gradient_color1);?>;
    --al-gradient-color2: <?php echo esc_html($gradient_color2);?>;
    --top-header-bg: <?php echo esc_html($top_header_bg);?>;
    --top-header-color: <?php echo esc_html($top_header_color);?>;
    --al-header-bg-color: <?php echo esc_html($header_bg_color);?>;
    --al-footer-bg: <?php echo esc_html($footer_bg_color);?>;
    --al-input-bg: <?php echo esc_html($input_field_bg_color);?>;
    --al-copyright-bg: <?php echo esc_html($copyright_bg_color);?>;
    --al-alternate-bg: <?php echo esc_html($alternate_bg);?>;
     --al-btn-hover-bg: <?php echo esc_html($button_hover_color);?>;
     --al-btn-radius: <?php echo esc_html($button_border_radius);?>;

}
</style>