<?php
/**
 * It is the functions file.
 *
 * @package woorestaurant
 */

/**
 * To include metadata file.
 */
require 'inc/metadata.php';

/**
 * To include global post type file.
 */
require 'inc/class-woores-global-op-posttype.php';

add_action( 'admin_enqueue_scripts', 'wooresop_admin_scripts' );
/**
 * To enqueue admin scripts.
 */
function wooresop_admin_scripts() {
	$js_params = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
	wp_localize_script( 'jquery', 'woo_restaurant_ajax', $js_params );
	wp_enqueue_style( 'woores-options', WR_WOO_OPTION_PATH . 'admin/css/style.css', '', '1.0' );
	wp_enqueue_script( 'woores-options', WR_WOO_OPTION_PATH . 'admin/js/admin.min.js', array( 'jquery' ), '1.0' . time() );
}

add_action( 'init', 'woores_update_data_options_by_ids' );
if ( ! function_exists( 'woores_update_data_options_by_ids' ) ) {
	/**
	 * Update data options by ids.
	 */
	function woores_update_data_options_by_ids() {
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			$update_woores = get_option( 'woores_update_ids' );
			if ( 'yes' != $update_woores ) {
				$my_posts = get_posts(
					array(
						'post_type'   => 'woores_modifiers',
						'numberposts' => -1,
					)
				);
				foreach ( $my_posts as $post ) :
					$ids_arr     = get_post_meta( $post->ID, 'woores_product_ids_arr', false );
					$product_ids = get_post_meta( $post->ID, 'woores_product_ids', true );
					if ( empty( $ids_arr ) && '' != $product_ids ) {
						$arr_ids = explode( ',', $product_ids );
						foreach ( $arr_ids as $key => $item ) {
							add_post_meta( $post->ID, 'woores_product_ids_arr', str_replace( ' ', '', $item ) );
						}
					}
				endforeach;
				update_option( 'woores_update_ids', 'yes' );
			}
		}
	}
}

add_filter( 'woocommerce_product_import_process_item_data', 'woores_unserialize_meta_in_import' );
/**
 * Unserialize meta in import.
 *
 * @param array $data it is the import data.
 */
function woores_unserialize_meta_in_import( $data ) {
	$unserialize_with_key = array( 'woores_options' );
	if ( isset( $data['meta_data'] ) ) {
		foreach ( $data['meta_data'] as $index => $meta ) {
			if ( in_array( $meta['key'], $unserialize_with_key ) ) {
				if ( '' != $meta['value'] ) {
					$data['meta_data'][ $index ]['value'] = unserialize( $data['meta_data'][ $index ]['value'] );
				}
			}
		}
	}
	return $data;
}

add_filter( 'woocommerce_product_export_meta_value', 'woores_woo_handle_export', 10, 4 );
/**
 * Woo handle export.
 *
 * @param string $value it is the value.
 * @param object $meta it is the meta.
 * @param object $product it is the product.
 * @param string $row it is the row.
 */
function woores_woo_handle_export( $value, $meta, $product, $row ) {
	if ( 'woores_options' == $meta->key ) {
		return serialize( $value );
	}
	return $value;
}
