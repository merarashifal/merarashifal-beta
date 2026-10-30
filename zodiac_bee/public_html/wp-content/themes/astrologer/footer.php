<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package astrologer
 */
if(class_exists('Astrologer_setting')):
	$theme_setting_option = new Astrologer_setting();
	$theme_setting_option->astrologer_footer_settings();
endif; 
?>
<?php wp_footer(); ?>
</body>
</html>
