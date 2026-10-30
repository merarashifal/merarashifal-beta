<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package astrologer
 */
get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main">
    <?php
    /**
     * astrologer 404 content
     */
	$astrologer_setting = ''; 
	if(class_exists('Astrologer_setting')):
	   $astrologer_setting = new Astrologer_setting();
	   $astrologer_setting->astrologer_404_setting(get_the_ID());
	endif;  
	?>
    </main><!-- #main -->
</div><!-- #primary -->
<?php
get_footer();