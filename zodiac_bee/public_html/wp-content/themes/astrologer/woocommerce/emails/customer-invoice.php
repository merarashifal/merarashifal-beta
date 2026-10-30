<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Customer invoice email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-invoice.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 9.8.0
 */

do_action( 'woocommerce_email_header', $email_heading, $email );

// Debugging: Print order ID
$order_id = $order->get_id();
echo '<pre>Order ID: ' . esc_html( $order_id ) . '</pre>';

// Get the QR code URL from order meta
$qr_code_url = get_post_meta($order->get_id(), '_qr_code_url', true);

// Debugging: Print QR code URL
echo '<pre>QR Code URL: ' . esc_url( $qr_code_url ) . '</pre>';
print_r("hello1");
?>
print_r("hello2");die;
<p><?php printf( __( 'You have received an invoice from %s. You can pay for this invoice using the link below:', 'woocommerce' ), get_bloginfo( 'name' ) ); ?></p>

<h2><?php printf( __( 'Order #%s', 'woocommerce' ), $order->get_order_number() ); ?></h2>

<?php if ( $qr_code_url ) : ?>
    <p><?php _e( 'Scan the QR code below to get more details about your order:', 'woocommerce' ); ?></p>
    <img src="<?php echo esc_url( $qr_code_url ); ?>" alt="<?php _e( 'QR Code', 'woocommerce' ); ?>" />
<?php endif; ?>

<?php

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_footer', $email );
?>
