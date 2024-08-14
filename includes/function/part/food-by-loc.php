<?php
/**
 * This file is getting food by location.
 *
 * @package woorestaurant
 */

if ( ! function_exists( 'woore_query_by_menu_loca' ) ) {
	/**
	 * This is the query method.
	 *
	 * @param array $args it is having the argument of the query.
	 *
	 * @return array
	 */
	function woore_query_by_menu_loca( $args ) {
		$loc = WC()->session->get( 'wr_userloc' );
		if ( '' != $loc ) {
			if ( ! isset( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
				$args['tax_query'] = array();
			}
			$args['tax_query']['relation'] = 'AND';
			$args['tax_query'][]           =
				array(
					'taxonomy' => 'woorestaurant_loc',
					'field'    => 'slug',
					'terms'    => $loc,
				);
		}
		return $args;
	}
}
add_filter( 'woo_restaurant_query', 'woore_query_by_menu_loca', 21 );
add_filter( 'woore_ajax_query_args', 'woore_query_by_menu_loca', 21 );
add_filter( 'woore_ajax_filter_query_args', 'woore_query_by_menu_loca', 21 );
