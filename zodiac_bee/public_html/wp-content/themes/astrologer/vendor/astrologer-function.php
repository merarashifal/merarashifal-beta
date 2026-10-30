<?php
/**
 * Astrologer functions and definitions
 * @package astrologer
 */
  
/** 
 * Astrologer Theme Core features
 */
require get_template_directory() . '/vendor/lib/astrologer-core-lib.php';  
  
/** 
 * class tgm plugin activation file require 
 */
require get_template_directory() . '/vendor/theme-plugins/astrologer-plugin-activate-config.php';  

/**
 *  Aq Resizer file require 
 */
require get_template_directory() . '/vendor/lib/astrologer-aq-resizer.php'; 

/**
 * Astrologer Comments customization file require 
 */    
require get_template_directory() . '/vendor/lib/astrologer-comment.php';      
/**
 * Astrologer woocommerce
 */  
if(class_exists( 'woocommerce' )):
  require get_template_directory() . '/woocommerce/astrologer-woocommerce.php'; 
endif;
remove_action('shutdown', 'wp_ob_end_flush_all', 1);

/**
 * Astrologer Theme Option Settings
 */
 
if(!function_exists('astrologer_theme_option_settings')):
    function astrologer_theme_option_settings(){
	  global $astrologer_options;	
	  return $astrologer_options;
	}
endif;

/**
 * Astrologer Menu Editor Funcation
 */
if(!function_exists('astrologer_menu_editor')):
 
	function astrologer_menu_editor($args){
       
	   if(!current_user_can('manage_options')){
           return;
        }
        /* see wp-includes/nav-menu-template.php for available arguments */
        extract( $args );
         $link = $link_before
             . '<a href="' .admin_url( 'nav-menus.php' ) . '">'.$before.esc_html__('Add a menu','astrologer').$after.'</a>'
            . $link_after;
        /* We have a list */
        if ( FALSE !== stripos( $items_wrap, '<ul' )

			or FALSE !== stripos( $items_wrap, '<ol' )

		) {
           $link = "<li>$link</li>";
        }
        $output = sprintf( $items_wrap, $menu_id, $menu_class, $link );
        if ( ! empty ( $container ) ){
           $output  = "<$container class='$container_class' id='$container_id'>$output</$container>";
        }
        if ( $echo ){
           echo "$output";
        }
        return $output;
	} 
endif;

/**
 * add body in class
 */
add_filter( 'body_class','astrologer_core_body_classes' );
function astrologer_core_body_classes($classes) {
    
$astrologer_options = '';
    if(function_exists('astrologer_theme_option_settings')):
        $astrologer_options = astrologer_theme_option_settings();
endif;
$class_switch = '';
    if(!empty($astrologer_options['class_switch'])){
        $class_switch = $astrologer_options['class_switch'];
}
    
     if($class_switch == true) {
        $classes[] = 'astrologer-demo';
    }else{
        $classes[] = '';
    }
    //$user = wp_get_current_user();
    if ( is_user_logged_in() ) {
         $classes[] = 'astrologer-top-space';
    }else{
        $classes[] = '';
    }

     return $classes;
} 
/** 
 * Date Custome function
 */
if(!function_exists('astrologer_date_custome')){ 
    function astrologer_date_custome(){
        echo '<span>'.get_the_date( 'd' ).'</span>'.get_the_date( 'M' );
        
    }
}
/**
 *Theme Custom Css(internal)
*/
add_action( 'after_setup_theme', 'astrologer_in_css' );
function astrologer_in_css(){
	global $astrologer_internal_css;
	$astrologer_internal_css = array();
	return $astrologer_internal_css;
}

/**
 *Load Custom Css(internal)
*/
function astrologer_in_load_footer_scripts() {
	global $astrologer_internal_css;
	echo !empty($astrologer_internal_css) ? '<style>'.implode(' ', $astrologer_internal_css).'</style>' : '';
}
add_action( 'wp_footer', 'astrologer_in_load_footer_scripts' );


/**
 * code for archive count number breakt remove
 */
if(!function_exists('astrologercore_style_the_archive_count')){
function astrologercore_style_the_archive_count($links) {
    $links = str_replace('</a>&nbsp;(', '</a> <span class="al-archiveCount widget-list-count">', $links);
    $links = str_replace(')', '</span>', $links);
    return $links;
}
add_filter('get_archives_link', 'astrologercore_style_the_archive_count');
}
/**
 * code for category count number breakt remove
 */
if(!function_exists('astrologercore_categories_postcount_filter')){
function astrologercore_categories_postcount_filter ($variable) {
   $variable = str_replace('(', '<span class="al-post-count widget-list-count"> ', $variable);
   $variable = str_replace(')', ' </span>', $variable);
   return $variable;
}
add_filter('wp_list_categories','astrologercore_categories_postcount_filter');
}

/**
 * color option file
 */
//if(function_exists get_redux_options
if(class_exists( 'ReduxFramework')){
if(function_exists('astrologer_theme_option_settings')):
add_action('wp_head', 'my_custom_css');
function my_custom_css(){ 
    require_once( get_template_directory() . '/assets/css/color-picker-css.php' );
}
endif;
}
/**
 *Theme Custom css(internal)
*/
add_action( 'after_setup_theme', 'astrologer_css' );
function astrologer_css(){
	global $astrologer_internal_css;
	$astrologer_internal_css = array();
	return $astrologer_internal_css;
}
