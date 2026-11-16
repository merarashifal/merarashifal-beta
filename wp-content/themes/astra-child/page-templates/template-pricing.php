<?php
/**
 * Template Name: MeraRashifal Pricing & Plans
 * Description: High-converting, compact Vedic astrology pricing page (Chat Credits -> Single Reports -> Report Bundles -> Subscriptions).
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<div class="mr-pricing-page-wrapper" style="width:100%; box-sizing:border-box;">
    <?php
    if (class_exists('MR_Pricing_Page')) {
        echo MR_Pricing_Page::render_shortcode();
    } else {
        echo do_shortcode('[mr_pricing]');
    }
    ?>
</div>
<?php
get_footer();
