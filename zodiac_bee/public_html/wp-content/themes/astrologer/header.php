<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package astrologer
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php 
  if(class_exists('Astrologer_setting')):
    $theme_setting_option = new Astrologer_setting();
    //Theme Loader
     $theme_setting_option->astrologer_themeloader_setting();
    //Header Settings
     $theme_setting_option->astrologer_header_settings();
	// Breadcrumbs Settings
	 $theme_setting_option->astrologer_breadcrumbs_settings();
  endif;  
?> 