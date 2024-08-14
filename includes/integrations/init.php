<?php
/**
 * It is the init file.
 *
 * @package woorestaurant
 */

// Option addon.
$all_options = get_option( 'woo_restaurant_options' );
if ( ! isset( $all_options['woo_restaurant_disable_wrsoptions'] ) || 'yes' != $all_options['woo_restaurant_disable_wrsoptions'] ) {
	include plugin_dir_path( __FILE__ ) . 'product-options-addon/class-wr-woo-custom-option.php';
}
