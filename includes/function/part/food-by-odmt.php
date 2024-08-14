<?php
/**
 * This file is getting food by order method.
 *
 * @package woorestaurant
 */

add_action( 'init', 'woore_register_odmt_taxonomies' );
/**
 * Register Order Method Taxonomy.
 */
function woore_register_odmt_taxonomies() {
	$labels = array(
		'name'              => esc_html__( 'Order Method', 'woorestaurant' ),
		'singular_name'     => esc_html__( 'Order Method', 'woorestaurant' ),
		'search_items'      => esc_html__( 'Order Method', 'woorestaurant' ),
		'all_items'         => esc_html__( 'All Order Method', 'woorestaurant' ),
		'parent_item'       => esc_html__( 'Parent Order Method', 'woorestaurant' ),
		'parent_item_colon' => esc_html__( 'Parent Order Method:', 'woorestaurant' ),
		'edit_item'         => esc_html__( 'Edit Order Method', 'woorestaurant' ),
		'update_item'       => esc_html__( 'Update Order Method', 'woorestaurant' ),
		'add_new_item'      => esc_html__( 'Add New Order Method', 'woorestaurant' ),
		'menu_name'         => esc_html__( 'Order Method', 'woorestaurant' ),
	);
	$args   = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_menu'      => false,
		'public'            => false,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'order-method' ),
	);
	register_taxonomy( 'woore_odmethod', array( 'product' ), $args );
}
add_action(
	'pre_insert_term',
	function ( $term, $taxonomy ) {
		global $woore_fist_create;
		return ( 'woore_odmethod' === $taxonomy && true != $woore_fist_create )
		? new WP_Error( 'term_addition_blocked', esc_html__( 'You cannot add terms to this taxonomy', 'woorestaurant' ) )
		: $term;
	},
	0,
	2
);

if ( ! function_exists( 'woore_query_by_menu_odmt' ) ) {
	/**
	 * This is the query of order method.
	 *
	 * @param array $args this is the query argument.
	 *
	 * @return array
	 */
	function woore_query_by_menu_odmt( $args ) {
		$order_method = WC()->session->get( '_user_order_method' );
		if ( '' == $order_method ) {
			$order_method = 'delivery';
		}
		if ( '' != $order_method ) {
			if ( ! isset( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
				$args['tax_query'] = array();
			}
			$args['tax_query']['relation'] = 'AND';
			$args['tax_query'][]           =
				array(
					'taxonomy' => 'woore_odmethod',
					'field'    => 'slug',
					'terms'    => $order_method,
				);
		}
		return $args;
	}
}
add_filter( 'woo_restaurant_query', 'woore_query_by_menu_odmt' );
add_filter( 'woore_ajax_query_args', 'woore_query_by_menu_odmt' );
add_filter( 'woore_ajax_filter_query_args', 'woore_query_by_menu_odmt' );

add_action( 'woocommerce_checkout_process', 'woore_if_product_is_not_inmethod' );
/**
 * Check if product is not in method.
 */
function woore_if_product_is_not_inmethod() {
	$method = WC()->session->get( '_user_order_method' );
	$method = '' != $method ? $method : 'delivery';
	foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
		$product_id = $values['product_id'];
		if ( has_term( '', 'woore_odmethod', $product_id ) && ! has_term( $method, 'woore_odmethod', $product_id ) ) {
			$title = '' != $title ? $title . ', ' . get_the_title( $product_id ) : get_the_title( $product_id );
			WC()->cart->remove_cart_item( $cart_item_key );
		}
	}
	if ( '' != $title ) {
		/* translators: %s is replaced with the product title */
		$title = sprintf( esc_html__( 'The food "%s" has been removed because it does not exist in this order method, please refresh page and try again', 'woorestaurant' ), $title );
		wc_add_notice( $title, 'error' );
		return;
	}
}
add_action( 'admin_init', 'woore_force_create_tern_method', 1 );
/**
 * To force create term method.
 */
function woore_force_create_tern_method() {
	if ( current_user_can( 'manage_options' ) && isset( $_GET['create_mt'] ) && 'yes' == $_GET['create_mt'] ) {
		global $woore_fist_create;
		$woore_fist_create = true;
		$term              = term_exists( 'delivery', 'woore_odmethod' );
		if ( null == $term ) {
			wp_insert_term(
				'Delivery',
				'woore_odmethod',
				array(
					'slug'        => 'delivery',
					'description' => '',
				)
			);
		}
		$term_takeaway = term_exists( 'takeaway', 'woore_odmethod' );
		if ( null == $term_takeaway ) {
			wp_insert_term(
				'Takeaway',
				'woore_odmethod',
				array(
					'slug'        => 'takeaway',
					'description' => '',
				)
			);
		}
		$term_dinein = term_exists( 'dinein', 'woore_odmethod' );
		if ( null == $term_dinein ) {
			wp_insert_term(
				'Dinein',
				'woore_odmethod',
				array(
					'slug'        => 'dinein',
					'description' => '',
				)
			);
		}
		$woore_fist_create = false;
	}
}
