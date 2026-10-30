<?php
/***
 * Template Name:Full Width page
 * 
 * Astrology Full Width page Template
 * @package astrologer
 */
get_header();
?>
<main id="primary" class="site-main">
   <section class="al-main-page-container">
     <div class="container-fluid">
        <div class="row">
           <?php
        	while ( have_posts() ) : the_post();
        
        		get_template_part( 'template-parts/content', 'full' );
        
        	endwhile; // End of the loop.
	       ?> 
        </div>
    </div>
  </section> 
</main><!-- #main -->   
<?php
get_footer();