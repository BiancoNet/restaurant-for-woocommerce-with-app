<?php
/**
 * It is the functions file.
 *
 * @package woorestaurant
 */

/**
 * To include woo hook.
 */
require plugin_dir_path( __FILE__ ) . 'woo-hook.php';

/**
 * It is the woo text domain.
 */
function woores_text_domain() {
	$textdomain = 'product-options-addon';
	if ( class_exists( 'wr_WOOFood' ) ) {
		$textdomain = 'woorestaurant';
	}
	return $textdomain;
}

/**
 * To convert number decimal comma.
 *
 * @param int $number it is the number.
 */
function woores_convert_number_decimal_comma( $number ) {
	if ( '' == $number ) {
		return;
	}
	if ( ',' == get_option( 'woocommerce_price_decimal_sep' ) ) {
		$number = floatval( str_replace( ',', '.', $number ) );
		// phpcs:ignore
	} elseif ( get_option( 'woocommerce_price_decimal_sep' ) == '.' && strpos( $number, ',' ) !== false ) {
		$number = floatval( str_replace( ',', '.', str_replace( '.', '', $number ) ) );
	}
	return $number;
}

/**
 * To price display.
 *
 * @param int $number it is the price.
 */
function woores_price_display( $number ) {
	if ( '' == $number ) {
		return;
	}
	if ( function_exists( 'wmc_get_price' ) ) {
		$number = wmc_get_price( $number );
	}
	return $number;
}
