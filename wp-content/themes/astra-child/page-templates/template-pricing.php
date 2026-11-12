<?php
/**
 * Template Name: MeraRashifal Pricing & Plans
 * Description: High-converting, compact Vedic astrology pricing page (Chat Credits -> Single Reports -> Report Bundles).
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="primary" class="site-main">
    <?php
    if (class_exists('MR_Pricing_Page')) {
        echo MR_Pricing_Page::render_shortcode();
    } else {
        echo do_shortcode('[mr_pricing]');
    }
    ?>
</main>
<?php
get_footer();
