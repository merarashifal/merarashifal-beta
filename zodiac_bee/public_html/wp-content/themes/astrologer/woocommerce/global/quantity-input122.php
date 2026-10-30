<?php
/**
 * Product quantity inputs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/quantity-input.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;
$labelledby = '';
if ( $max_value && $min_value === $max_value ) {
?>
<div class="quantity hidden">
	<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
</div> 
<?php
} else {
   /* translators: %s: Quantity. */
	$label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'astrologer' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'astrologer' );
	?>
	<div class="product-qyt-wrapper">
	    <div class="product-qyt">
            <div class="quantity">
                <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( 'Quantity', 'astrologer' ); ?></label>
                <button type="button" class="qty_button minus toltiped" title="<?php echo esc_attr__('Decrease','astrologer'); ?>">
                    <?php echo esc_html('-','astrologer');  ?>
                </button>
                <input
                    type="number"
                    placeholder="0"
                    id="<?php echo esc_attr( $input_id ); ?>"
                    class="input-text qty text"
                    step="<?php echo esc_attr( $step ); ?>"
                    
                    min="<?php echo esc_attr( $min_value ); ?>"
                    
                    max="<?php echo esc_attr( 0 < $max_value ? $max_value : '100' ); ?>"
                    
                    name="<?php echo esc_attr( $input_name ); ?>"
                    value="<?php echo esc_attr($input_value); ?>"
                    
                    title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'astrologer' ); ?>"
                    inputmode="<?php echo esc_attr( $inputmode ); ?>"
                   />
                <button type="button" class="qty_button plus toltiped" title="<?php echo esc_attr__('Increase','astrologer'); ?>">
                     <?php echo esc_html('+','astrologer');  ?>
                </button>  
            </div>
        </div>
    </div>
<?php
}