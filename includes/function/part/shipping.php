<?php
/**
 * These functions are related to shipping.
 *
 * @package woorestaurant
 */

/**
 * To get shipping packages.
 *
 * @param array $value this is the value of cart.
 */
function woore_get_shipping_packages( $value ) {
	// Packages array for storing 'carts'.
	$packages                = array();
	$packages[0]['contents'] = WC()->cart->cart_contents;
	// $packages[0]['contents_cost']           = '5';
	$packages[0]['applied_coupons']          = WC()->session->applied_coupon;
	$packages[0]['destination']['country']   = '';
	$packages[0]['destination']['state']     = '';
	$packages[0]['destination']['postcode']  = $value['postcode'];
	$packages[0]['destination']['city']      = '';
	$packages[0]['destination']['address']   = '';
	$packages[0]['destination']['address_2'] = '';
	// $packages[0]['cart_subtotal']           = '5.03';
	return apply_filters( 'woocommerce_cart_shipping_packages', $packages );
}

/**
 * Get available packages.
 *
 * @param array $values this is the value of cart.
 */
function woore_get_shipping_packages_available( $values ) {
	$postcode       = woo_restaurant_get_option( 'woo_restaurant_ship_postcodes', 'woo_restaurant_shpping_options' );
	$delivery_zones = WC_Shipping_Zones::get_zones();
	if ( empty( $delivery_zones ) && '' == $postcode ) {
		return array( 'ok' );
	}
	$active_methods = array();
	if ( ! empty( $delivery_zones ) ) {
		global $woocommerce;
		$empty_cart = 0;
		if ( WC()->cart->is_empty() ) {
			$empty_cart = 1;
			$id_fac     = apply_filters( 'woore_fac_id_addtocart', '' );
			if ( '' == $id_fac || ! is_numeric( $id_fac ) ) {
				$args   = array(
					'post_type'      => 'product',
					'posts_per_page' => 1,
					'post_status'    => array( 'publish' ),
					'meta_query'     => array(
						array(
							'key'     => '_price',
							'value'   => 0,
							'compare' => '>',
							'type'    => 'NUMERIC',
						),
					),
				);
				$id_qrs = get_posts( $args );
				if ( ! empty( $id_qrs ) ) {
					foreach ( $id_qrs as $id_qr ) {
						$woocommerce->cart->add_to_cart( $id_qr->ID );
						break;
					}
				}
			} else {
				$woocommerce->cart->add_to_cart( $id_fac );
			}
		}
		WC()->shipping->calculate_shipping( woore_get_shipping_packages( $values ) );
		$shipping_methods = WC()->shipping->packages;
		foreach ( $shipping_methods[0]['rates'] as $id => $shipping_method ) {
			$active_methods[] = array(
				'id'       => $shipping_method->method_id,
				'type'     => $shipping_method->method_id,
				'provider' => $shipping_method->method_id,
				'name'     => $shipping_method->label,
				'price'    => number_format( $shipping_method->cost, 2, '.', '' ),
			);
		}
		if ( 1 == $empty_cart ) {
			$woocommerce->cart->empty_cart();
		}
	}
	if ( empty( $active_methods ) ) {
		if ( '' != $postcode ) {
			$postcode = str_replace( ' ', '', $postcode );
			$postcode = explode( ',', $postcode );
			if ( is_array( $postcode ) && isset( $postcode ) && '*' == $postcode[0] ) {
				return array( 'ok' );
			} elseif ( in_array( $values['postcode'], $postcode ) ) {
				return array( 'ok' );
			} else {
				$needle = '*';
				$ret    = array_filter(
					$postcode,
					function ( $var ) use ( $needle ) {
						// phpcs:ignore
						return strpos( $var, $needle ) !== false;
					}
				);
				if ( is_array( $ret ) && ! empty( $ret ) ) {
					foreach ( $ret as $itpc ) {
						$itpc = str_replace( '*', '', $itpc );
						// phpcs:ignore
						if ( strpos( $values['postcode'], $itpc ) !== false ) {
							return array( 'ok' );
						}
					}
				}
			}
		}
	}
	return apply_filters( 'woore_postcode_shipping_defpack', $active_methods, $values );
}
// Get distance.
$api = woo_restaurant_get_option( 'woo_restaurant_gg_api', 'woo_restaurant_shpping_options' );
/**
 * To get distance.
 *
 * @param string $from this is the from distance.
 * @param string $to this is the to distance.
 * @param string $km this is the distancein km.
 *
 * @return array
 */
function woore_get_distance( $from, $to, $km ) {
	do_action( 'woore_before_caculate_distance', $from, $to, $km );
	$rs        = array();
	$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
	if ( 'postcode' == $ship_mode ) {
		if ( '' != $to ) {
			$data   = array(
				'country'  => '',
				'postcode' => $to,
			);
			$get_pk = woore_get_shipping_packages_available( $data );
			if ( is_array( $get_pk ) && ! empty( $get_pk ) ) {
				$rs['mes'] = '';
				WC()->session->set( '_user_postcode', $to );
				WC()->customer->set_shipping_postcode( $to );
			} else {
				$rs['mes'] = esc_html__( 'Your address are out of delivery zone, please change to carryout channel', 'woorestaurant' );
			}
		} else {
			$rs['distance'] = '0';
			$rs['mes']      = esc_html__( 'Please add your postcode', 'woorestaurant' );
		}
		return $rs;
	}
	$map_lang    = urlencode( apply_filters( 'woore_map_matrix_lang', 'en-EN' ) );
	$api         = woo_restaurant_get_option( 'woo_restaurant_gg_api', 'woo_restaurant_shpping_options' );
	$diskm       = woo_restaurant_get_option( 'woo_restaurant_restrict_km', 'woo_restaurant_shpping_options' );
	$distace_api = woo_restaurant_get_option( 'woo_restaurant_gg_distance_api', 'woo_restaurant_shpping_options' );
	if ( '' == $distace_api ) {
		$distace_api = $api;
	}
	if ( '' == $km ) {
		$km = $diskm;
	}
	if ( '' == $api || '99999' == $km ) {
		WC()->session->set( '_user_deli_adress', $to );
		if ( '' != $api ) {
			$data_address = file_get_contents( 'https://maps.googleapis.com/maps/api/geocode/json?key=' . esc_attr( $distace_api ) . '&address=' . urlencode( $to ) . "&language=$map_lang&sensor=true" );
			$data_address = json_decode( $data_address );
			if ( 'REQUEST_DENIED' == $data_address->status ) {
				$data_address = file_get_contents( 'https://maps.googleapis.com/maps/api/geocode/json?key=' . esc_attr( $api ) . '&address=' . urlencode( $to ) . "&language=$map_lang&sensor=true" );
				$data_address = json_decode( $data_address );
			}
			if ( isset( $data_address->results[0]->address_components ) ) {
				WC()->session->set( '_user_deli_adress_details', $data_address->results[0]->address_components );
			}
		}
		$rs['mes'] = '';
		return $rs;
	}
	$store_address = get_option( 'woocommerce_store_address', '' );
	$store_address = apply_filters( 'woore_default_store_address', $store_address );
	if ( '' == $from ) {
		$from = $store_address;
	}

	$from = urlencode( $from );
	$to   = urlencode( $to );

	$calcu_mode     = woo_restaurant_get_option( 'woo_restaurant_calcu_mode', 'woo_restaurant_shpping_options' );
	$calcu_mode     = '' != $calcu_mode ? $calcu_mode : 'driving';
	$mode_transport = apply_filters( 'woore_mode_transport_map_api', $calcu_mode );
	$data           = file_get_contents( 'https://maps.googleapis.com/maps/api/distancematrix/json?key=' . esc_attr( $distace_api ) . '&origins=' . $from . '&destinations=' . $to . "&language=$map_lang&sensor=false&mode=" . esc_attr( $mode_transport ) );
	$data           = json_decode( $data );

	if ( '' == $data ) {
		$curl_session = curl_init();
		curl_setopt( $curl_session, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/distancematrix/json?key=' . esc_attr( $distace_api ) . '&origins=' . $from . '&destinations=' . $to . "&language=$map_lang&sensor=false&mode=" . esc_attr( $mode_transport ) );
		curl_setopt( $curl_session, CURLOPT_BINARYTRANSFER, true );
		curl_setopt( $curl_session, CURLOPT_RETURNTRANSFER, true );
		$json_data = curl_exec( $curl_session );
		curl_close( $curl_session );
		$data = json_decode( $json_data );
	}

	WC()->session->set( '_user_deli_adress', '' );
	WC()->session->set( '_user_deli_adress_details', '' );
	if ( isset( $data->rows[0] ) ) {
		$time     = 0;
		$distance = 0;
		foreach ( $data->rows[0]->elements as $road ) {
			$time     += $road->duration->value;
			$distance += $road->distance->value;
		}
		if ( $distance <= '0' ) {
			$rs['distance'] = '0';
			$rs['mes']      = esc_html__( 'Could not calculate distance to your address, please re-check your address', 'woorestaurant' );
		} else {
			$distance = $distance / 1000;
			if ( '' != $km && $distance > $km ) {
				$rs['distance'] = $distance;
				$rs['limit']    = $km;
				$rs['mes']      = esc_html__( 'Your address are out of delivery zone, please change to carryout channel', 'woorestaurant' );
			} else {
				if ( isset( $data->destination_addresses[0] ) ) {
					$data_address = file_get_contents( 'https://maps.googleapis.com/maps/api/geocode/json?key=' . esc_attr( $distace_api ) . '&address=' . $to . "&language=$map_lang&sensor=true" );
					$data_address = json_decode( $data_address );
					if ( 'REQUEST_DENIED' == $data_address->status ) {
						$data_address = file_get_contents( 'https://maps.googleapis.com/maps/api/geocode/json?key=' . esc_attr( $api ) . '&address=' . urlencode( $to ) . "&language=$map_lang&sensor=true" );
						$data_address = json_decode( $data_address );
					}

					if ( isset( $data_address->results[0]->address_components ) ) {
						WC()->session->set( '_user_deli_adress_details', $data_address->results[0]->address_components );
					}
					WC()->session->set( '_user_deli_adress', $data->destination_addresses[0] );
				}
				WC()->session->set( '_user_distance', $distance );
				$rs['distance'] = $distance;
				$rs['mes']      = '';
			}
		}
	} else {
		$rs['distance'] = 'null';
		$rs['mes']      = isset( $data->error_message ) ? $data->error_message : '';
	}
	return $rs;
}

add_action( 'init', 'woore_clear_user_address', 1 );
/**
 * To clear user address.
 */
function woore_clear_user_address() {

	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! isset( WC()->session ) ) {
		return;
	}
	if ( isset( $_GET['change-address'] ) && '1' == $_GET['change-address'] ) {
		WC()->session->set( '_user_deli_adress', '' );
		WC()->session->set( '_user_deli_log', '' );
		WC()->session->set( '_user_postcode', '' );
		if ( woorestaurant_loc_field_html() == '' ) {
			WC()->session->set( '_user_order_method', '' );
		}
	}
	if ( isset( $_GET['change-method'] ) && ( 'delivery' == $_GET['change-method'] || 'takeaway' == $_GET['change-method'] || 'dinein' == $_GET['change-method'] ) ) {
		WC()->session->set( '_user_order_method', wp_kses_post( wp_unslash( $_GET['change-method'] ) ) );
	}
	if ( isset( $_GET['loc'] ) && '' == WC()->session->get( '_user_deli_log' ) ) {
		WC()->session->set( '_user_deli_log', wp_kses_post( wp_unslash( $_GET['loc'] ) ) );
	}
	if ( isset( $_GET['change-method'] ) && ( 'delivery' == $_GET['change-method'] || 'takeaway' == $_GET['change-method'] || 'dinein' == $_GET['change-method'] ) ) {

		$method_ship = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
		$dine_in     = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
		if ( 'takeaway' == $method_ship && 'yes' != $dine_in ) {
			WC()->session->set( '_user_order_method', 'takeaway' );
		} elseif ( 'delivery' == $method_ship && 'yes' != $dine_in ) {
			WC()->session->set( '_user_order_method', 'delivery' );
		} elseif ( '' == $method_ship && 'yes' == $dine_in ) {
			WC()->session->set( '_user_order_method', 'dinein' );
		} elseif ( '' == $method_ship && 'yes' != $dine_in ) {
			WC()->session->set( '_user_order_method', '' );
		}
	}
}

/**
 * Popup order method.
 *
 * @param array $loc it is the location value.
 * @param array $s_all it is s all variable.
 */
function woorestaurant_loc_field_html( $loc = false, $s_all = false ) {
	$location_en = woo_restaurant_get_option( 'woo_restaurant_multi_location', 'woo_restaurant_advanced_options' );

	if ( 'enable' != $location_en ) {
		return '';
	}

	$args = array(
		'hide_empty' => true,
		'parent'     => '0',
	);
	if ( 'yes' != woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
		$args['hide_empty'] = false;
	}
	$locations = isset( $loc ) && '' != $loc ? explode( ',', $loc ) : array();
	if ( ! empty( $locations ) && ! is_numeric( $locations[0] ) ) {
		$args['slug'] = $locations;
	} elseif ( ! empty( $locations ) ) {
		$args['include'] = $locations;
	}
	$args = apply_filters( 'woore_location_args', $args );
	if ( isset( $s_all ) && true == $s_all ) {
		$args['exclude'] = '';
	} else {
		$s_all = '';
	}

	// phpcs:ignore
	$terms = get_terms( 'woorestaurant_loc', $args );
	ob_start();
	$loc_selected = WC()->session->get( 'wr_userloc' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) { ?>
		<select class="wrs-ck-select wrsfd-choice-locate wrs-logreq" name="_location">
			<?php
			global $wp;
			$count_stop = 5;
			echo '<option disabled selected value>' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';

			foreach ( $terms as $term ) {
				$select_loc = '';
				if ( ! isset( $term->slug ) || empty( $term->slug ) ) {
					continue;
				}
				if ( '' != $term->slug && $loc_selected == $term->slug ) {
					$select_loc = ' selected="selected"';
				}
				echo '<option value="' . esc_attr( $term->slug ) . '" ' . esc_attr( $select_loc ) . '>' . wp_kses_post( $term->name ) . '</option>';
				echo esc_attr( wooresfd_show_child_location( '', $term, $count_stop, $loc_selected, 'yes', $s_all ) );
			}
			?>
		</select>
		<?php
	}
	$loca = ob_get_contents();
	ob_end_clean();
	return $loca;
}

/**
 * Popup delivery HTML.
 *
 * @param array $args it is the arguments.
 */
function woore_poup_delivery_type_html( $args ) {
	$enable_mtod = isset( $args['enable_mtod'] ) ? $args['enable_mtod'] : '';
	if ( 'no' == $enable_mtod ) {
		return;
	}
	global $locations;
	$locations  = isset( $args['locations'] ) ? $args['locations'] : '';
	$method     = WC()->session->get( '_user_order_method' );
	$user_log   = WC()->session->get( '_user_deli_log' );
	$user_addre = WC()->session->get( '_user_deli_adress' );
	$ship_mode  = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );

	if ( 'postcode' == $ship_mode ) {
		$user_addre = WC()->session->get( '_user_postcode' );
	}
	$loc_selected = WC()->session->get( 'wr_userloc' );
	if ( ( ( 'takeaway' == $method || 'dinein' == $method ) && ( '' == woorestaurant_loc_field_html( $locations ) || '' != $user_log ) ) ) {
		return;
	}

	if ( '' != $user_addre && 'yes' != woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) || ( '' != $user_addre && 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) && '' != $loc_selected ) ) {
		return;
	}
	if ( 'yes' != $enable_mtod ) {
		global $pu_del;
		if ( '' == $pu_del ) {
			$pu_del = 1;
		} else {
			return;
		}
	}

	woo_restaurant_template_plugin( 'order-method', 1 );
}

add_action( 'woore_before_shortcode_content', 'woore_poup_delivery_type_html', 12, 1 );
/**
 * Add shipping method to top of checkout field.
 */
function woore_get_method_enable() {
	$method_ship = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
	$dine_in     = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
	$arr_methods = array();
	if ( 'takeaway' == $method_ship || 'delivery' == $method_ship ) {
		$arr_methods[] = $method_ship;
	} elseif ( 'both' == $method_ship ) {
		$arr_methods[] = 'delivery';
		$arr_methods[] = 'takeaway';
	}
	if ( 'yes' == $dine_in ) {
		$arr_methods[] = 'dinein';
	}
	return apply_filters( 'woore_arr_enable_method', $arr_methods );
}

add_action( 'woocommerce_before_checkout_form', 'woore_shipping_method_selectbox' );
/**
 * Shipping method selectbox.
 */
function woore_shipping_method_selectbox() {
	$arr_methods = woore_get_method_enable();
	if ( empty( $arr_methods ) ) {
		return;
	}
	$user_odmethod = WC()->session->get( '_user_order_method' );
	$cr_url        = wc_get_checkout_url();
	$cr_url        = apply_filters( 'woore_url_change_method', $cr_url );
	$check_ex      = woore_if_check_product_notin_shipping();
	if ( false == $check_ex ) {
		return;
	}
	if ( ! in_array( $user_odmethod, $arr_methods ) ) {
		$user_odmethod = $arr_methods[0];
		WC()->session->set( '_user_order_method', $user_odmethod );
	}
	$loc_selected = WC()->session->get( 'wr_userloc' );
	if ( '' != $loc_selected ) {
		$exclude_tk = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_adv_takeaway_options' );
		if ( is_array( $exclude_tk ) && in_array( $loc_selected, $exclude_tk ) ) {
			$key = array_search( 'takeaway', $arr_methods );
			if ( false !== $key ) {
				unset( $arr_methods[ $key ] );
			}
		}
		$exclude_di = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_adv_dinein_options' );
		if ( is_array( $exclude_di ) && in_array( $loc_selected, $exclude_di ) ) {
			$key_2 = array_search( 'dinein', $arr_methods );
			if ( false !== $key_2 ) {
				unset( $arr_methods[ $key_2 ] );
			}
		}
		$exclude_dl = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_advanced_options' );
		if ( is_array( $exclude_dl ) && in_array( $loc_selected, $exclude_dl ) ) {
			$key_3 = array_search( 'delivery', $arr_methods );
			if ( false !== $key_3 ) {
				unset( $arr_methods[ $key_3 ] );
			}
		}
	}

	?>
	<div class="wrsf-cksp-method wrsf-method-ct">
		<div class="wrsf-method-title">
			<?php if ( in_array( 'delivery', $arr_methods ) ) { ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'change-method' => 'delivery' ), $cr_url ) ); ?>" class="wrsf-order-deli 
									<?php
									if ( 'delivery' == $user_odmethod ) {
										?>
					at-method <?php } ?>">
					<?php esc_html_e( 'Delivery', 'woorestaurant' ); ?>
				</a>
				<?php
			}
			if ( in_array( 'takeaway', $arr_methods ) ) {
				?>
				<a href="<?php echo esc_url( add_query_arg( array( 'change-method' => 'takeaway' ), $cr_url ) ); ?>" class="wrsf-order-take 
									<?php
									if ( 'takeaway' == $user_odmethod ) {
										?>
					at-method <?php } ?>">
					<?php esc_html_e( 'Takeaway', 'woorestaurant' ); ?>
				</a>
				<?php
			}
			if ( in_array( 'dinein', $arr_methods ) ) {
				?>
				<a href="<?php echo esc_url( add_query_arg( array( 'change-method' => 'dinein' ), $cr_url ) ); ?>" class="wrsf-order-dinein 
									<?php
									if ( 'dinein' == $user_odmethod ) {
										?>
					at-method <?php } ?>">
					<?php esc_html_e( 'Dine-in', 'woorestaurant' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>
	<?php
}

add_action( 'wp_ajax_woore_check_distance', 'ajax_woore_check_distance' );
add_action( 'wp_ajax_nopriv_woore_check_distance', 'ajax_woore_check_distance' );
/**
 * To check distance through ajax.
 */
function ajax_woore_check_distance() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'check-distance' ) ) {
			return;
		}
	}

	if ( isset( $_POST['method'] ) ) {
		$method = wp_kses_post( wp_unslash( $_POST['method'] ) );
	}
	if ( isset( $_POST['log'] ) ) {
		$log = wp_kses_post( wp_unslash( $_POST['log'] ) );
	}
	WC()->session->set( '_user_deli_log', $log );
	WC()->session->set( '_user_order_method', $method );
	$output = array();
	if ( 'takeaway' == $method || 'dinein' == $method ) {
		$output['mes'] = '';
	} else {
		if ( isset( $_POST['address'] ) ) {
			$address = wp_kses_post( wp_unslash( $_POST['address'] ) );
		}
		$from  = '';
		$diskm = woo_restaurant_get_option( 'woo_restaurant_restrict_km', 'woo_restaurant_shpping_options' );
		if ( '' != $log ) {
			$term = get_term_by( 'slug', $log, 'woorestaurant_loc' );
			if ( isset( $term->term_id ) ) {
				$addres_log = get_term_meta( $term->term_id, 'wrsp_loc_address', true );
				if ( '' != $addres_log ) {
					$from = $addres_log;
				}
				$addres_km = get_term_meta( $term->term_id, 'wrsp_loc_diskm', true );
				if ( '' != $addres_km ) {
					$diskm = $addres_km;
				}
			}
		}
		$output = woore_get_distance( $from, $address, $diskm );
	}
	echo esc_attr( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}

add_action( 'woore_sidecart_after_content', 'woore_add_user_info_sidecart' );
/**
 * Add user info Sidecart.
 */
function woore_add_user_info_sidecart() {
	$method_ship = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
	$dine_in     = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
	if ( '' == $method_ship && 'yes' != $dine_in ) {
		return;
	}
	$user_odmethod = WC()->session->get( '_user_order_method' );
	global $wp;
	$cr_url     = home_url( $wp->request );
	$cr_url     = apply_filters( 'woore_current_link', $cr_url );
	$addres_log = '';
	if ( 'takeaway' == $user_odmethod || 'dinein' == $user_odmethod ) {
		$user_log = WC()->session->get( '_user_deli_log' );
		$url      = add_query_arg( array( 'change-address' => 1 ), $cr_url );
		if ( '' != $user_log ) {
			$term = get_term_by( 'slug', $user_log, 'woorestaurant_loc' );
			if ( isset( $term->term_id ) ) {
				$addres_log = get_term_meta( $term->term_id, 'wrsp_loc_address', true );
				if ( '' == $addres_log ) {
					$addres_log = $term->name;
				}
			}
		} else {
			$addres_log = get_option( 'woocommerce_store_address', '' );
			$addres_log = apply_filters( 'woore_default_store_address', $addres_log );
		}
		?>
		<div class="wrsf-user-dl-info">
			<?php if ( 'dinein' == $user_odmethod ) { ?>
				<span class="adrl-title"><?php esc_html_e( 'Dine-in at: ', 'woorestaurant' ); ?></span>
			<?php } else { ?>
				<span class="adrl-title"><?php esc_html_e( 'Carryout at: ', 'woorestaurant' ); ?></span>
			<?php } ?>
			<span class="adrl-info"><?php echo esc_attr( $addres_log ); ?></span>
			<span class="adrl-link"><a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( ' Change it ?', 'woorestaurant' ); ?></a></span>
		</div>
		<?php
	} else {
		$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
		if ( 'postcode' == $ship_mode ) {
			$user_address = WC()->session->get( '_user_postcode' );
		} else {
			$user_address = WC()->session->get( '_user_deli_adress' );
		}
		if ( '' != $user_address ) {
			$url = add_query_arg( array( 'change-address' => 1 ), $cr_url );
			?>
			<div class="wrsf-user-dl-info">
				<span class="adrl-title"><?php esc_html_e( 'Delivery to: ', 'woorestaurant' ); ?></span>
				<span class="adrl-info"><?php echo esc_attr( $user_address ); ?></span>
				<span class="adrl-link"><a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( ' Change it ?', 'woorestaurant' ); ?></a></span>
			</div>
			<?php
		}
	}
}

add_action( 'woocommerce_checkout_process', 'woore_verify_address_deli_field' );
/**
 * Verify address checkout.
 */
function woore_verify_address_deli_field() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'verify-address' ) ) {
			return;
		}
	}

	$user_odmethod = WC()->session->get( '_user_order_method' );
	$ship_mode     = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
	if ( 'takeaway' == $user_odmethod || 'dinein' == $user_odmethod || 'postcode' == $ship_mode ) {
		return;
	}
	$check_ex = woore_if_check_product_notin_shipping();
	if ( false == $check_ex ) {
		return;
	}
	$loc_sl = isset( $_POST['woo_restaurant_ck_loca'] ) ? wp_kses_post( wp_unslash( $_POST['woo_restaurant_ck_loca'] ) ) : '';
	$to     = isset( $_POST['billing_address_1'] ) ? wp_kses_post( wp_unslash( $_POST['billing_address_1'] ) ) : '';
	if ( isset( $_POST['ship_to_different_address'] ) && '1' == $_POST['ship_to_different_address'] ) {
		$to = isset( $_POST['shipping_address_1'] ) ? wp_kses_post( wp_unslash( $_POST['shipping_address_1'] ) ) : $to;
	}
	$user_address = WC()->session->get( '_user_deli_adress' );
	if ( $user_address != $to ) {
		if ( isset( $_POST['ship_to_different_address'] ) && '1' == $_POST['ship_to_different_address'] ) {
			if ( isset( $_POST['shipping_city'] ) || isset( $_POST['shipping_country'] ) ) {
				$to = $to . ' ' . wp_kses_post( wp_unslash( $_POST['shipping_city'] ) ) . ' ' . woore_get_country_code( wp_kses_post( wp_unslash( $_POST['shipping_country'] ) ) );
			}
		} elseif ( isset( $_POST['billing_city'] ) || isset( $_POST['billing_country'] ) ) {
				$to = $to . ' ' . wp_kses_post( wp_unslash( $_POST['billing_city'] ) ) . ' ' . woore_get_country_code( wp_kses_post( wp_unslash( $_POST['billing_country'] ) ) );
		}
	}
	$diskm = '';
	$from  = '';
	if ( '' != $loc_sl ) {
		$term = get_term_by( 'slug', $loc_sl, 'woorestaurant_loc' );
		if ( isset( $term->term_id ) ) {
			$addres_log = get_term_meta( $term->term_id, 'wrsp_loc_address', true );
			if ( '' != $addres_log ) {
				$from = $addres_log;
			}
			$addres_km = get_term_meta( $term->term_id, 'wrsp_loc_diskm', true );
			if ( '' != $addres_km ) {
				$diskm = $addres_km;
			}
		}
	}
	$output = woore_get_distance( $from, $to, $diskm );
	if ( '' != $output['mes'] ) {
		wc_add_notice( $output['mes'], 'error' );
	}
}

add_action( 'woocommerce_cart_calculate_fees', 'wrsd_add_shipping_fee' );
/**
 * Add shipping fee.
 */
function wrsd_add_shipping_fee() {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	$check_ex = woore_if_check_product_notin_shipping();
	if ( false == $check_ex ) {
		return;
	}
	$user_odmethod = WC()->session->get( '_user_order_method' );
	if ( 'takeaway' == $user_odmethod || 'dinein' == $user_odmethod ) {
		return;
	}
	$fee           = woo_restaurant_get_option( 'woo_restaurant_ship_fee', 'woo_restaurant_shpping_options' );
	$free_shipping = woo_restaurant_get_option( 'woo_restaurant_ship_free', 'woo_restaurant_shpping_options' );
	$ship_mode     = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );

	global $woocommerce;
	// min by log.
	$loc_selected = woore_get_loc_selected();
	if ( '' != $loc_selected ) {
		$free_shipping_log = get_term_meta( $loc_selected, 'wrsp_loc_ship_free', true );
		$free_shipping     = '' != $free_shipping_log && is_numeric( $free_shipping_log ) ? $free_shipping_log : $free_shipping;
		$fee_log           = get_term_meta( $loc_selected, 'wrsp_loc_ship_fee', true );
		$fee               = '' != $fee_log && is_numeric( $fee_log ) ? $fee_log : $fee;
	}
	if ( 'postcode' != $ship_mode ) {
		$user_distance = WC()->session->get( '_user_distance' );
		if ( is_numeric( $user_distance ) && $user_distance > 0 ) {
			$adv_fee = woo_restaurant_get_option( 'wooresfd_adv_feekm', 'woo_restaurant_shpping_options' );
			if ( is_array( $adv_fee ) && ! empty( $adv_fee ) ) {
				usort(
					$adv_fee,
					function ( $a, $b ) {
						// anonymous function.
						return $a['km'] - $b['km'];
					}
				);
				foreach ( $adv_fee as $key => $item ) {
					if ( $user_distance <= $item['km'] ) {
						$fee           = isset( $item['fee'] ) && is_numeric( $item['fee'] ) ? $item['fee'] : '';
						$free_shipping = isset( $item['free'] ) && is_numeric( $item['free'] ) ? $item['free'] : $free_shipping;
						break;
					}
				}
			}
		}
	} else {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'shipping-options' ) ) {
				return;
			}
		}

		$adv_fee    = woo_restaurant_get_option( 'wooresfd_adv_feepos', 'woo_restaurant_shpping_options' );
		$pcode      = WC()->session->get( '_user_postcode' );
		$user_postc = isset( $_POST['postcode'] ) ? wp_kses_post( wp_unslash( $_POST['postcode'] ) ) : '';
		$user_postc = isset( $_POST['s_postcode'] ) && '' != $_POST['s_postcode'] ? wp_kses_post( wp_unslash( $_POST['s_postcode'] ) ) : $user_postc;
		if ( '' == $user_postc ) {
			$user_postc = $pcode;
		}
		if ( '' != $user_postc && is_array( $adv_fee ) && ! empty( $adv_fee ) ) {
			foreach ( $adv_fee as $key => $item ) {
				if ( $user_postc == $item['postcode'] ) {
					WC()->session->set( '_user_postcode', $user_postc );
					$fee           = isset( $item['fee'] ) && is_numeric( $item['fee'] ) ? $item['fee'] : '';
					$free_shipping = isset( $item['free'] ) && is_numeric( $item['free'] ) ? $item['free'] : $free_shipping;
					break;
				}
			}
		}
	}
	$fee = apply_filters( 'woore_shipping_fee_amount', $fee );
	if ( '' != $fee && is_numeric( $fee ) ) {
		$total = apply_filters( 'woore_total_cart_price_fee', WC()->cart->get_subtotal() );
		if ( '' != $free_shipping && is_numeric( $free_shipping ) && $total >= $free_shipping ) {
			$fee = 0;
		}
		$tax_fee = apply_filters( 'woore_shipping_fee_tax', true );
		$woocommerce->cart->add_fee( esc_html__( 'Shipping fee', 'woorestaurant' ), $fee, $tax_fee, '' );
	}
}

add_filter( 'woore_minimum_amount_required', 'wrsd_change_mini_mum_by_km' );
/**
 * To change mini number by km.
 *
 * @param array $minimum it is having the minimum value.
 *
 * @return array
 */
function wrsd_change_mini_mum_by_km( $minimum ) {
	$user_odmethod = WC()->session->get( '_user_order_method' );
	if ( 'takeaway' == $user_odmethod || 'dinein' == $user_odmethod ) {
		return $minimum;
	}
	$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
	if ( 'postcode' != $ship_mode ) {
		$user_distance = WC()->session->get( '_user_distance' );
		if ( is_numeric( $user_distance ) && $user_distance > 0 ) {
			$adv_fee = woo_restaurant_get_option( 'wooresfd_adv_feekm', 'woo_restaurant_shpping_options' );
			if ( is_array( $adv_fee ) && ! empty( $adv_fee ) ) {
				usort(
					$adv_fee,
					function ( $a, $b ) {
						// anonymous function.
						return $a['km'] - $b['km'];
					}
				);
				foreach ( $adv_fee as $key => $item ) {
					if ( $user_distance <= $item['km'] ) {
						$minimum = isset( $item['min_amount'] ) && is_numeric( $item['min_amount'] ) ? $item['min_amount'] : $minimum;
						break;
					}
				}
			}
		}
	} else {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'shipping-options' ) ) {
				return;
			}
		}

		$adv_fee    = woo_restaurant_get_option( 'wooresfd_adv_feepos', 'woo_restaurant_shpping_options' );
		$pcode      = WC()->session->get( '_user_postcode' );
		$user_postc = isset( $_POST['postcode'] ) ? wp_kses_post( wp_unslash( $_POST['postcode'] ) ) : '';
		$user_postc = isset( $_POST['s_postcode'] ) && '' != $_POST['s_postcode'] ? wp_kses_post( wp_unslash( $_POST['s_postcode'] ) ) : $user_postc;
		if ( '' == $user_postc ) {
			$user_postc = $pcode;
		}
		if ( '' != $user_postc && is_array( $adv_fee ) && ! empty( $adv_fee ) ) {
			foreach ( $adv_fee as $key => $item ) {
				if ( $user_postc == $item['postcode'] ) {
					$minimum = isset( $item['min_amount'] ) && is_numeric( $item['min_amount'] ) ? $item['min_amount'] : $minimum;
					break;
				}
			}
		}
	}
	return $minimum;
}

add_filter( 'woore_minimum_amount_required', 'wrsd_change_mini_mum_order_method' );
/**
 * To change minimum order number.
 *
 * @param string $minimum it is the minimum number.
 *
 * @return string
 */
function wrsd_change_mini_mum_order_method( $minimum ) {
	$user_odmethod = WC()->session->get( '_user_order_method' );
	$minimum_mt    = '';
	if ( 'takeaway' == $user_odmethod ) {
		$minimum_mt = woo_restaurant_get_option( 'woo_restaurant_ck_mini_amount', 'woo_restaurant_adv_takeaway_options' );
	} elseif ( 'dinein' == $user_odmethod ) {
		$minimum_mt = woo_restaurant_get_option( 'woo_restaurant_ck_mini_amount', 'woo_restaurant_adv_dinein_options' );
	}

	return '' != $minimum_mt ? $minimum_mt : $minimum;
}

add_action( 'woocommerce_widget_shopping_cart_before_buttons', 'woore_minimum_amount_free_deli_sidecart', 999 );
/**
 * Display shipping free.
 *
 * @param string $return it is the return value.
 */
function woore_minimum_amount_free_deli_sidecart( $return = false ) {
	$user_odmethod = WC()->session->get( '_user_order_method' );
	if ( 'takeaway' == $user_odmethod || 'dinein' == $user_odmethod ) {
		return;
	}
	$free_shipping = woo_restaurant_get_option( 'woo_restaurant_ship_free', 'woo_restaurant_shpping_options' );
	$fee           = woo_restaurant_get_option( 'woo_restaurant_ship_fee', 'woo_restaurant_shpping_options' );
	$total         = apply_filters( 'woore_total_cart_price_fee', WC()->cart->get_subtotal() );
	// min by log.
	$loc_selected = woore_get_loc_selected();
	if ( '' != $loc_selected ) {
		$free_shipping_log = get_term_meta( $loc_selected, 'wrsp_loc_ship_free', true );
		$free_shipping     = '' != $free_shipping_log && is_numeric( $free_shipping_log ) ? $free_shipping_log : $free_shipping;
		$fee_log           = get_term_meta( $loc_selected, 'wrsp_loc_ship_fee', true );
		$fee               = '' != $fee_log && is_numeric( $fee_log ) ? $fee_log : $fee;
	}
	$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
	if ( 'postcode' != $ship_mode ) {
		$user_distance = WC()->session->get( '_user_distance' );
		if ( is_numeric( $user_distance ) && $user_distance > 0 ) {
			$adv_fee = woo_restaurant_get_option( 'wooresfd_adv_feekm', 'woo_restaurant_shpping_options' );
			if ( is_array( $adv_fee ) && ! empty( $adv_fee ) ) {
				usort(
					$adv_fee,
					function ( $a, $b ) {
						// anonymous function.
						return $a['km'] - $b['km'];
					}
				);
				foreach ( $adv_fee as $key => $item ) {
					if ( $user_distance <= $item['km'] ) {
						$fee           = isset( $item['fee'] ) ? $item['fee'] : '';
						$free_shipping = isset( $item['free'] ) && is_numeric( $item['free'] ) ? $item['free'] : $free_shipping;
						break;
					}
				}
				if ( 0 == $fee ) {
					$free_shipping = 0;
				}
			}
		}
	} else {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'shipping-option' ) ) {
				return;
			}
		}

		$adv_fee    = woo_restaurant_get_option( 'wooresfd_adv_feepos', 'woo_restaurant_shpping_options' );
		$pcode      = WC()->session->get( '_user_postcode' );
		$user_postc = isset( $_POST['postcode'] ) ? wp_kses_post( wp_unslash( $_POST['postcode'] ) ) : '';
		$user_postc = isset( $_POST['s_postcode'] ) && '' != $_POST['s_postcode'] ? wp_kses_post( wp_unslash( $_POST['s_postcode'] ) ) : $user_postc;
		if ( '' == $user_postc ) {
			$user_postc = $pcode;
		}
		if ( '' != $user_postc && is_array( $adv_fee ) && ! empty( $adv_fee ) ) {
			foreach ( $adv_fee as $key => $item ) {
				if ( $user_postc == $item['postcode'] ) {
					WC()->session->set( '_user_postcode', $user_postc );
					$fee           = isset( $item['fee'] ) && is_numeric( $item['fee'] ) ? $item['fee'] : '';
					$free_shipping = isset( $item['free'] ) && is_numeric( $item['free'] ) ? $item['free'] : $free_shipping;
					break;
				}
			}
			if ( 0 == $fee ) {
				$free_shipping = 0;
			}
		}
	}
	$html = '';
	if ( '' != $fee && '' != $free_shipping && is_numeric( $fee ) && is_numeric( $free_shipping ) && $total < $free_shipping ) {
		$nbom_displ = apply_filters( 'woore_free_ship_value_message', wc_price( $free_shipping - $total ), $free_shipping );
		/* translators: %s is replaced with the number display */
		$html = sprintf( esc_html__( 'Order %s amount more to get free delivery', 'woorestaurant' ), $nbom_displ );
	}
	if ( '' != $html && isset( $return ) && true == $return ) {
		return $html;
	} elseif ( '' != $html ) {
		echo '<p class="wrsf-mini-amount wrsf-min-free-ship wrsf-warning">' . esc_attr( $html ) . '</p>';
	}
}

add_action( 'wp_ajax_woore_update_shipping_fee', 'ajax_woore_update_shipping_fee' );
add_action( 'wp_ajax_nopriv_woore_update_shipping_fee', 'ajax_woore_update_shipping_fee' );
/**
 * If change loc.
 */
function ajax_woore_update_shipping_fee() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'shipping-fee' ) ) {
			return;
		}
	}

	if ( isset( $_POST['loc'] ) ) {
		$loc = wp_kses_post( wp_unslash( $_POST['loc'] ) );
	}
	WC()->session->set( '_user_deli_log', $loc );
	ajax_woore_update_shipping_fee_bykm();
}

add_action( 'wp_ajax_woore_update_shipping_fee_bykm', 'ajax_woore_update_shipping_fee_bykm' );
add_action( 'wp_ajax_nopriv_woore_update_shipping_fee_bykm', 'ajax_woore_update_shipping_fee_bykm' );
/**
 * To update shipping fee.
 */
function ajax_woore_update_shipping_fee_bykm() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'shipping-fee' ) ) {
			return;
		}
	}

	$adv_fee = woo_restaurant_get_option( 'wooresfd_adv_feekm', 'woo_restaurant_shpping_options' );
	if ( ! is_array( $adv_fee ) || empty( $adv_fee ) ) {
		echo esc_attr( str_replace( '\/', '/', json_encode( array( 'result' => 'unc' ) ) ) );
		die;
	}
	if ( isset( $_POST['address'] ) || isset( $_POST['city'] ) || isset( $_POST['loc'] ) || isset( $_POST['country'] ) ) {
		$address = wp_kses_post( wp_unslash( $_POST['address'] ) );
		$city    = wp_kses_post( wp_unslash( $_POST['city'] ) );
		$log     = wp_kses_post( wp_unslash( $_POST['loc'] ) );
		$country = wp_kses_post( wp_unslash( $_POST['country'] ) );
	}
	$to   = $address . ' ' . $city . ' ' . woore_get_country_code( $country );
	$from = '';
	if ( '' != $log ) {
		$term = get_term_by( 'slug', $log, 'woorestaurant_loc' );
		if ( isset( $term->term_id ) ) {
			$addres_log = get_term_meta( $term->term_id, 'wrsp_loc_address', true );
			if ( '' != $addres_log ) {
				$from = $addres_log;
			}
		}
	}
	$output = woore_get_distance( $from, $to, '' );
	echo esc_attr( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}

add_action( 'woocommerce_before_cart', 'woore_minimum_amount_fee_deli' );
/**
 * Minimum amount fee.
 */
function woore_minimum_amount_fee_deli() {
	$user_odmethod = WC()->session->get( '_user_order_method' );
	if ( 'takeaway' == $user_odmethod ) {
		return;
	}
	$prnotice = woore_minimum_amount_free_deli_sidecart( true );
	if ( '' != $prnotice ) {
		wc_print_notice( $prnotice, 'error' );
	}
}

add_action( 'woocommerce_checkout_update_order_meta', 'woore_save_order_method_field' );
/**
 * Save method value.
 *
 * @param int $order_id it is the order id.
 */
function woore_save_order_method_field( $order_id ) {
	$user_odmethod = WC()->session->get( '_user_order_method' );
	update_post_meta( $order_id, 'wooresfd_order_method', $user_odmethod );
}

add_filter( 'woocommerce_default_address_fields', 'woore_unrequired_address_field' );
/**
 * Unrequired address field.
 *
 * @param array $address_fields it is having the address fields.
 *
 * @return array
 */
function woore_unrequired_address_field( $address_fields ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! isset( WC()->session ) ) {
		return $address_fields;
	}
	$user_odmethod = WC()->session->get( '_user_order_method' );
	if ( 'takeaway' == $user_odmethod ) {
		$address_fields['address_1']['required'] = false;
	}

	return $address_fields;
}

add_filter( 'default_checkout_billing_country', 'woore_change_default_checkout_country' );
add_filter( 'default_checkout_billing_city', 'woore_change_default_checkout_city' );
add_filter( 'default_checkout_billing_address_1', 'woore_change_default_checkout_address_1' );
add_filter( 'default_checkout_billing_postcode', 'woore_change_default_checkout_billing_postcode' );
/**
 * Change default checkout country.
 *
 * @param array $contr it is having the country.
 *
 * @return array
 */
function woore_change_default_checkout_country( $contr ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! isset( WC()->session ) ) {
		return $contr;
	}
	$user_address         = WC()->session->get( '_user_deli_adress' );
	$user_details_address = WC()->session->get( '_user_deli_adress_details' );
	if ( '' != $user_details_address && is_array( $user_details_address ) ) {
		foreach ( $user_details_address as $address_component ) {
			if ( in_array( 'country', $address_component->types ) ) {
				$country = $address_component->short_name;
				return $country;
				break;
			}
		}
	}
	if ( '' != $user_address ) {
		$user_address = explode( ',', $user_address );
		$contr        = end( $user_address );
		$contr        = woore_get_country_code( trim( $contr ) );
	}
	return $contr; // country code.
}

/**
 * Change default checkout state.
 */
function woore_change_default_checkout_state() {
	return 'US'; // state code.
}

/**
 * Change default checkout city.
 *
 * @param array $city it is the default city.
 *
 * @return array
 */
function woore_change_default_checkout_city( $city ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! isset( WC()->session ) ) {
		return $city;
	}
	$user_address         = WC()->session->get( '_user_deli_adress' );
	$user_details_address = WC()->session->get( '_user_deli_adress_details' );
	if ( '' != $user_details_address && is_array( $user_details_address ) ) {
		foreach ( $user_details_address as $address_component ) {
			if ( in_array( 'locality', $address_component->types ) ) {
				$name = $address_component->long_name;
				return $name;
				break;
			}
		}
	}
	if ( '' != $user_address ) {
		$user_address = explode( ',', $user_address );
		$count_ar     = count( $user_address );
		if ( $count_ar > 1 ) {
			$city = $user_address[ $count_ar - 2 ];
		}
	}
	return $city; // state code.
}

/**
 * Change default checkout to address 1.
 *
 * @param string $street it is the street name.
 *
 * @return string
 */
function woore_change_default_checkout_address_1( $street ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! isset( WC()->session ) ) {
		return $street;
	}
	$user_address         = WC()->session->get( '_user_deli_adress' );
	$user_details_address = WC()->session->get( '_user_deli_adress_details' );
	if ( '' != $user_details_address && is_array( $user_details_address ) ) {
		$name = '';
		foreach ( $user_details_address as $address_component ) {
			if ( in_array( 'street_number', $address_component->types ) ) {
				$name .= $address_component->long_name;
			} elseif ( in_array( 'route', $address_component->types ) ) {
				$name .= ' ' . $address_component->long_name;
			} elseif ( in_array( 'neighborhood', $address_component->types ) ) {
				$name .= ' ' . $address_component->long_name;
			}
		}
		return $name;
	}
	if ( '' != $user_address ) {
		$user_address = explode( ',', $user_address );
		$count_ar     = count( $user_address );
		if ( $count_ar > 3 ) {
			$street = '';
			for ( $i = 0; $i < ( $count_ar - 2 ); $i++ ) {
				$street .= ' ' . $user_address[ $i ];
			}
		}
	}
	return $street; // state code.
}

/**
 * Change default checkout billing postcode.
 *
 * @param int $code it is the postcode.
 *
 * @return int
 */
function woore_change_default_checkout_billing_postcode( $code ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ! isset( WC()->session ) ) {
		return $code;
	}
	$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
	if ( 'postcode' == $ship_mode ) {
		$code = WC()->session->get( '_user_postcode' );
		return $code;
	}
	$user_details_address = WC()->session->get( '_user_deli_adress_details' );
	if ( '' != $user_details_address && is_array( $user_details_address ) ) {
		foreach ( $user_details_address as $address_component ) {
			if ( in_array( 'postal_code', $address_component->types ) ) {
				$name = $address_component->long_name;
				return $name;
				break;
			}
			if ( isset( $address_component->types ) && 'postal_code' == $address_component->types[0] ) {
				$name = $address_component->long_name;
				return $name;
				break;
			}
		}
	}
	return $code; // state code.
}

/**
 * Get country code.
 *
 * @param string $name it is the name of the country.
 *
 * @return string
 */
function woore_get_country_code( $name ) {
	$countrycodes = array(
		'AF' => 'Afghanistan',
		'AX' => 'Åland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia and Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CD' => 'Zaire',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Côte D\'Ivoire',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard Island and Mcdonald Islands',
		'VA' => 'Vatican City State',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran, Islamic Republic of',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'KENYA',
		'KI' => 'Kiribati',
		'KP' => 'Korea, Democratic People\'s Republic of',
		'KR' => 'Korea, Republic of',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Lao People\'s Democratic Republic',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libyan Arab Jamahiriya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macao',
		'MK' => 'Macedonia, the Former Yugoslav Republic of',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia, Federated States of',
		'MD' => 'Moldova, Republic of',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PS' => 'Palestinian Territory, Occupied',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Réunion',
		'RO' => 'Romania',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent and the Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'RS' => 'Serbia',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syrian Arab Republic',
		'TW' => 'Taiwan, Province of China',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania, United Republic of',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'VG' => 'Virgin Islands, British',
		'VI' => 'Virgin Islands, U.S.',
		'WF' => 'Wallis and Futuna',
		'EH' => 'Western Sahara',
		'YE' => 'Yemen',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe',
	);
	$code         = array_search( $name, $countrycodes );
	return $code;
}

// add_filter( 'option_woo_restaurant_advanced_options', 'woore_change_disable_takeaway_day', 88, 1 );.
/**
 * Example change day of takeaway.
 *
 * @param array $default it is having takeaway days.
 *
 * @return array
 */
function woore_change_disable_takeaway_day( $default ) {
	if ( ! isset( WC()->session ) || is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return $default;
	}
	$user_odmethod = WC()->session->get( '_user_order_method' );
	if ( 'takeaway' == $user_odmethod ) {
		$default['woo_restaurant_ck_disday'] = '';
		$default['woo_restaurant_ck_disday'] = array(
			// '0' => '1', // Disable Monday.
			// '0' => '2', // Disable Tuesday.
			// '0' => '3', // Disable Wednesday.
			// '0' => '4', // Disable Thursday.
			// '0' => '5', // Disable Friday.
			'0' => '7', // Disable Sunday.
		);
	}
	return $default;
}
if ( ! function_exists( 'woore_hide_fields_takeaway' ) ) {
	// add_filter( 'woocommerce_checkout_fields' , 'woore_hide_fields_takeaway' );.
	/**
	 * To hide takeaway fields.
	 *
	 * @param array $fields it is having takeaway fields.
	 *
	 * @return array
	 */
	function woore_hide_fields_takeaway( $fields ) {
		if ( ! isset( WC()->session ) || is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return $fields;
		}
		$user_odmethod = WC()->session->get( '_user_order_method' );
		if ( 'takeaway' == $user_odmethod ) {
			unset( $fields['billing']['billing_address_1'] );
			unset( $fields['billing']['billing_address_2'] );
			unset( $fields['billing']['billing_city'] );
			unset( $fields['billing']['billing_state'] );
			unset( $fields['billing']['billing_postcode'] );
			unset( $fields['billing']['billing_company'] );
		}
		return $fields;
	}
}

if ( ! function_exists( 'woore_hide_def_address_field_by_sp' ) ) {
	add_filter( 'woocommerce_checkout_fields', 'woore_hide_def_address_field_by_sp', 99999 );
	/**
	 * Hide default address fields.
	 *
	 * @param array $fields it is having takeaway fields.
	 *
	 * @return array
	 */
	function woore_hide_def_address_field_by_sp( $fields ) {
		if ( ! isset( WC()->session ) || is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return $fields;
		}
		$user_odmethod = WC()->session->get( '_user_order_method' );
		$disaddr       = woo_restaurant_get_option( 'woo_restaurant_ck_disaddr', 'woo_restaurant_adv_takeaway_options' );
		$disaddr_di    = woo_restaurant_get_option( 'woo_restaurant_ck_disaddr', 'woo_restaurant_adv_dinein_options' );
		if ( ( 'takeaway' == $user_odmethod && 'yes' == $disaddr ) || ( 'dinein' == $user_odmethod && 'yes' == $disaddr_di ) ) {
			unset( $fields['billing']['billing_address_1'] );
			unset( $fields['billing']['billing_address_2'] );
			unset( $fields['billing']['billing_city'] );
			unset( $fields['billing']['billing_state'] );
			unset( $fields['billing']['billing_postcode'] );
			unset( $fields['billing']['billing_company'] );
			unset( $fields['billing']['billing_country'] );
		}
		return $fields;
	}
}

add_filter( 'woore_get_option', 'woore_change_settings_if_pickup', 10, 3 );
/**
 * Change setting for pickup.
 *
 * @param string $val it is having the settings value.
 * @param string $option_key it is having the option key.
 * @param string $key it is the key.
 *
 * @return string
 */
function woore_change_settings_if_pickup( $val, $option_key, $key ) {
	if ( ! isset( WC()->session ) || is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return $val;
	}
	$method = WC()->session->get( '_user_order_method' );
	if ( 'takeaway' == $method ) {
		if ( 'woo_restaurant_advanced_options' == $option_key && 'woo_restaurant_ck_beforedate' == $key ) {
			$pickup_val = woo_restaurant_get_option( 'woo_restaurant_ck_beforedate', 'woo_restaurant_adv_takeaway_options' );
			if ( '' != $pickup_val ) {
				return $pickup_val;
			}
		} elseif ( 'woo_restaurant_advanced_options' == $option_key && 'woo_restaurant_ck_disdate' == $key ) {
			$pickup_val = woo_restaurant_get_option( 'woo_restaurant_ck_disdate', 'woo_restaurant_adv_takeaway_options' );
			if ( is_array( $pickup_val ) && ! empty( $pickup_val ) ) {
				return $pickup_val;
			}
		} elseif ( 'woo_restaurant_advanced_options' == $option_key && 'woo_restaurant_ck_enadate' == $key ) {
			$pickup_val = woo_restaurant_get_option( 'woo_restaurant_ck_enadate', 'woo_restaurant_adv_takeaway_options' );
			if ( is_array( $pickup_val ) && ! empty( $pickup_val ) ) {
				return $pickup_val;
			}
		} elseif ( 'woo_restaurant_advanced_options' == $option_key && 'woo_restaurant_ck_disday' == $key ) {
			$pickup_val = woo_restaurant_get_option( 'woo_restaurant_ck_disday', 'woo_restaurant_adv_takeaway_options' );
			if ( is_array( $pickup_val ) && ! empty( $pickup_val ) ) {
				return $pickup_val;
			}
		}
	} elseif ( 'dinein' == $method ) {
		if ( 'woo_restaurant_advanced_options' == $option_key && 'woo_restaurant_ck_beforedate' == $key ) {
			$pickup_val = woo_restaurant_get_option( 'woo_restaurant_ck_beforedate', 'woo_restaurant_adv_dinein_options' );
			if ( '' != $pickup_val ) {
				return $pickup_val;
			}
		} elseif ( 'woo_restaurant_advanced_options' == $option_key && 'woo_restaurant_ck_disdate' == $key ) {
			$pickup_val = woo_restaurant_get_option( 'woo_restaurant_ck_disdate', 'woo_restaurant_adv_dinein_options' );
			if ( is_array( $pickup_val ) && ! empty( $pickup_val ) ) {
				return $pickup_val;
			}
		} elseif ( 'woo_restaurant_advanced_options' == $option_key && 'woo_restaurant_ck_enadate' == $key ) {
			$pickup_val = woo_restaurant_get_option( 'woo_restaurant_ck_enadate', 'woo_restaurant_adv_dinein_options' );
			if ( is_array( $pickup_val ) && ! empty( $pickup_val ) ) {
				return $pickup_val;
			}
		} elseif ( 'woo_restaurant_advanced_options' == $option_key && 'woo_restaurant_ck_disday' == $key ) {
			$pickup_val = woo_restaurant_get_option( 'woo_restaurant_ck_disday', 'woo_restaurant_adv_dinein_options' );
			if ( is_array( $pickup_val ) && ! empty( $pickup_val ) ) {
				return $pickup_val;
			}
		}
	}
	if ( 'woo_restaurant_shpping_options' == $option_key && 'wooresfd_adv_feekm' == $key ) {
		$loc_selected = woore_get_loc_selected();
		if ( '' != $loc_selected ) {
			$loc_shipkm = woo_restaurant_get_option( 'wooresfd_km_loc', 'woo_restaurant_shpping_options' );
			if ( 'yes' == $loc_shipkm ) {
				$fee_lockm = get_term_meta( $loc_selected, 'wooresfd_adv_feekm', true );
				if ( is_array( $fee_lockm ) && ! empty( $fee_lockm ) ) {
					$val = $fee_lockm;
				}
			}
		}
	} elseif ( 'woo_restaurant_shpping_options' == $option_key && 'woo_restaurant_ship_postcodes' == $key ) {
		$loc_selected = woore_get_loc_selected();
		if ( '' != $loc_selected ) {
			$postcodes_loc = get_term_meta( $loc_selected, 'wrsp_loc_ship_postcodes', true );
			if ( '' != $postcodes_loc ) {
				$val = $postcodes_loc;
			}
		}
	}

	return $val;
}

if ( ! function_exists( 'woore_hide_df_shipping_when_takeaway_dinein' ) ) {
	add_filter( 'woocommerce_product_needs_shipping', 'woore_hide_df_shipping_when_takeaway_dinein', 10, 2 );
	/**
	 * Remove shipping option when takeaway or dinein.
	 *
	 * @param array $return it is the return value.
	 * @param array $data it is the data.
	 *
	 * @return array
	 */
	function woore_hide_df_shipping_when_takeaway_dinein( $return, $data ) {
		if ( ! isset( WC()->session ) || is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return $return;
		}

		$check_ex = woore_if_check_product_in_shipping();
		if ( true == $check_ex ) {
			return $return;
		}

		$user_odmethod = WC()->session->get( '_user_order_method' );
		if ( 'takeaway' == $user_odmethod || 'dinein' == $user_odmethod ) {
			return false;
		}
		return $return;
	}

	// add_filter( 'woocommerce_cart_needs_shipping', 'woore_disable_df_shipping_when_takeaway_dinein', 10 );.
	/**
	 * Disable shipping on takeaway and dinein.
	 *
	 * @param array $return it is the value.
	 *
	 * @return array
	 */
	function woore_disable_df_shipping_when_takeaway_dinein( $return ) {
		if ( ! isset( WC()->session ) || is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return $return;
		}
		$check_ex = woore_if_check_product_in_shipping();
		if ( true == $check_ex ) {
			return $return;
		}
		$user_odmethod = WC()->session->get( '_user_order_method' );
		if ( 'takeaway' == $user_odmethod || 'dinein' == $user_odmethod ) {
			return false;
		}
		return $return;
	}
}

add_filter( 'woore_shipping_fee_amount', 'woore_fee_by_timeslot' );
/**
 * Fees by timeslot.
 *
 * @param int $fee it is the fee amount.
 */
function woore_fee_by_timeslot( $fee ) {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'fee-timeslot' ) ) {
			return;
		}
	}

	if ( isset( $_POST['post_data'] ) && '' != $_POST['post_data'] ) {
		parse_str( wp_kses_post( wp_unslash( $_POST['post_data'] ) ), $data );
		$timeslot = isset( $data['wooresfd_time_deli'] ) && '' != $data['wooresfd_time_deli'] ? $data['wooresfd_time_deli'] : '';
		if ( '' != $timeslot ) {
			$_timeck = array();
			// advanced slots.
			$rq_date     = woo_restaurant_get_option( 'woo_restaurant_ck_date', 'woo_restaurant_advanced_options' );
			$adv_timesl  = woo_restaurant_get_option( 'wooresfd_adv_timedeli', 'woo_restaurant_adv_timesl_options' );
			$_ftimesl    = '';
			$user_log    = isset( $data['woo_restaurant_ck_loca'] ) ? $data['woo_restaurant_ck_loca'] : '';
			$date_deli   = isset( $data['wooresfd_date_deli'] ) ? $data['wooresfd_date_deli'] : '';
			$_date_type  = woo_restaurant_get_option( 'woo_restaurant_dd_display', 'woo_restaurant_advanced_options' );
			$foodby_date = woo_restaurant_get_option( 'woo_restaurant_foodby_date' );
			if ( 'picker' == $_date_type && '' != $date_deli && 'yes' != $foodby_date ) {
				$date_deli = strtotime( $date_deli );
			}
			if ( 'disable' == $rq_date ) {
				$date_deli = strtotime( gmdate( 'Y-m-d' ) );
			}
			$method = 'delivery';
			if ( '' != $date_deli && is_array( $adv_timesl ) && ! empty( $adv_timesl ) ) {
				$day_ofd = gmdate( 'D', $date_deli );
				foreach ( $adv_timesl as $it_timesl ) {
					$tsl_log = isset( $it_timesl['times_loc'] ) ? $it_timesl['times_loc'] : '';
					if (
						isset( $it_timesl[ 'repeat_' . $day_ofd ] ) && 'on' == $it_timesl[ 'repeat_' . $day_ofd ] &&
						(
							! isset( $it_timesl['deli_method'] )
							|| ( isset( $it_timesl['deli_method'] ) && '' == $it_timesl['deli_method'] )
							|| ( isset( $it_timesl['deli_method'] ) && $it_timesl['deli_method'] == $method )
						) && ( '' == $tsl_log || is_array( $tsl_log ) && in_array( $user_log, $tsl_log ) )
					) {
						$_ftimesl = isset( $it_timesl['wooresfd_deli_time'] ) && is_array( $it_timesl['wooresfd_deli_time'] ) ? $it_timesl['wooresfd_deli_time'] : '';
						break;
					}
				}
			}
			$n_dl_time = '' != $_ftimesl ? $_ftimesl : woo_restaurant_get_option( 'wooresfd_deli_time', 'woo_restaurant_advanced_options' );
			if ( is_array( $n_dl_time ) && ! empty( $n_dl_time ) ) {
				foreach ( $n_dl_time as $time_option ) {
					$r_time = '';
					if ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] && isset( $time_option['end-time'] ) && '' != $time_option['end-time'] ) {
						$r_time = $time_option['start-time'] . ' - ' . $time_option['end-time'];
					} elseif ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] ) {
						$r_time = $time_option['start-time'];
					}
					$name = isset( $time_option['name-ts'] ) && '' != $time_option['name-ts'] ? $time_option['name-ts'] : $r_time;
					if ( $timeslot == $name ) {
						$_time_base = apply_filters( 'woore_timebase_to_check_delivery', ( isset( $time_option['start-time'] ) ? $time_option['start-time'] : '' ), $time_option );
						$_timeck    = $time_option;
						break;
					}
				}
			}
			if ( ! empty( $_timeck ) && isset( $_timeck['ship-fee'] ) && $_timeck >= 0 ) {
				return $_timeck['ship-fee'];
			}
		}
	}
	return $fee;
}

add_filter( 'woore_location_args', 'woore_location_by_method', 10 );
/**
 * To get location by method.
 *
 * @param array $args it is having the arguments.
 *
 * @return array
 */
function woore_location_by_method( $args ) {
	if ( ! isset( WC()->session ) || is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return $args;
	}
	$user_odmethod = WC()->session->get( '_user_order_method' );
	if ( 'takeaway' == $user_odmethod ) {
		$exclude = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_adv_takeaway_options' );
	} elseif ( 'dinein' == $user_odmethod ) {
		$exclude = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_adv_dinein_options' );
	} else {
		$exclude = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_advanced_options' );
	}
	$ids_to_exclude = array();
	if ( is_array( $exclude ) && ! empty( $exclude ) ) {
		// phpcs:ignore
		$get_terms_to_exclude = get_terms(
			'woorestaurant_loc',
			array(
				'fields'     => 'ids',
				'slug'       => $exclude,
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $get_terms_to_exclude ) && count( $get_terms_to_exclude ) > 0 ) {
			$ids_to_exclude = $get_terms_to_exclude;
		}
	}
	$args['exclude'] = $ids_to_exclude;
	return $args;
}

add_action( 'woocommerce_checkout_process', 'woore_verify_location_by_order_mt' );
/**
 * To verify location by order date.
 */
function woore_verify_location_by_order_mt() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'verify-location' ) ) {
			return;
		}
	}

	$user_odmethod = WC()->session->get( '_user_order_method' );
	$user_odmethod = '' != $user_odmethod ? $user_odmethod : 'delivery';
	if ( 'takeaway' == $user_odmethod ) {
		$exclude = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_adv_takeaway_options' );
	} elseif ( 'dinein' == $user_odmethod ) {
		$exclude = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_adv_dinein_options' );
	} else {
		$exclude = woo_restaurant_get_option( 'woo_restaurant_adv_dislog', 'woo_restaurant_advanced_options' );
	}
	if ( is_array( $exclude ) && ! empty( $exclude ) ) {
		$user_log = WC()->session->get( '_user_deli_log' );
		if ( isset( $_POST['woo_restaurant_ck_loca'] ) ) {
			$user_log = wp_kses_post( wp_unslash( $_POST['woo_restaurant_ck_loca'] ) );
		}
		if ( in_array( $user_log, $exclude ) ) {
			$user_odmethod = 'takeaway' == $user_odmethod ? esc_html__( 'Takeaway', 'woorestaurant' ) : ( 'dinein' == $user_odmethod ? esc_html__( 'Dine-in', 'woorestaurant' ) : esc_html__( 'Delivery', 'woorestaurant' ) );
			/* translators: %s is replaced with the user method */
			wc_add_notice( sprintf( esc_html__( 'Sorry the location you have selected is not available for %s please try with other order methods', 'woorestaurant' ), $user_odmethod ), 'error' );
		}
	}
}
