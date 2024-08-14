<?php
/**
 * It is the Helper file.
 *
 * @package woorestaurant
 */

/**
 * Restraunt total sales function.
 */
function woorestaurant_total_sales() {

	global $wpdb;

	$order_totals = apply_filters(
		'woocommerce_reports_sales_overview_order_totals',
		// phpcs:ignore
		$wpdb->get_row("SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders FROM {$wpdb->posts} AS posts LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id WHERE meta.meta_key = '_order_total' AND posts.post_type = 'shop_order' AND posts.post_status IN ( '" . implode( "','", array( 'wc-completed', 'wc-processing', 'wc-on-hold' ) ) . "' )" )
	);

	return absint( $order_totals->total_sales );
}

add_action( 'admin_enqueue_scripts', 'woo_restaurant_admin_scripts' );
/**
 * To enqueue admin scripts.
 */
function woo_restaurant_admin_scripts() {
	$js_params = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
	wp_localize_script( 'jquery', 'woo_restaurant_ajax', $js_params );
	wp_enqueue_style( 'woo-restaurant', WOORESTAURANT_ASSETS . 'admin/css/style.css', '', '2.0.0' . time() );
	wp_enqueue_script( 'woo-restaurant', WOORESTAURANT_ASSETS . 'admin/js/admin.min.js', array( 'jquery' ), '2.0.0' . time() );
}

add_filter( 'manage_woorestaurant_scbd_posts_columns', 'woo_restaurant_edit_scbd_columns', 99 );
/**
 * To edit shortcode columns.
 *
 * @param array $columns it is having the columns.
 * @return array
 */
function woo_restaurant_edit_scbd_columns( $columns ) {
	unset( $columns['date'] );
	$columns['layout']    = esc_html__( 'Type', 'woorestaurant' );
	$columns['shortcode'] = esc_html__( 'Shortcode', 'woorestaurant' );
	$columns['date']      = esc_html__( 'Publish date', 'woorestaurant' );
	return $columns;
}

add_action( 'manage_woorestaurant_scbd_posts_custom_column', 'woorestaurant_scbd_custom_columns', 12 );
/**
 * To add custom columns.
 *
 * @param array $column it is having the columns.
 */
function woorestaurant_scbd_custom_columns( $column ) {
	global $post;
	switch ( $column ) {
		case 'layout':
			$sc_type           = get_post_meta( $post->ID, 'sc_type', true );
			$woo_restaurant_id = $post->ID;
			echo '<span class="layout">' . wp_kses_post( $sc_type ) . '</span>';
			break;
		case 'shortcode':
			$_shortcode = get_post_meta( $post->ID, '_shortcode', true );
			echo '<input type="text" readonly name="_shortcode" value="' . esc_attr( $_shortcode ) . '">';
			break;
	}
}

/**
 * To get id taxonomy columns.
 *
 * @param array $columns it is having the columns.
 * @return array
 */
function woo_restaurant_id_taxonomy_columns( $columns ) {
	$columns['cat_id'] = esc_html__( 'ID', 'woorestaurant' );

	return $columns;
}
add_filter( 'manage_edit-product_cat_columns', 'woo_restaurant_id_taxonomy_columns' );

/**
 * To get taxonomy columns content.
 *
 * @param int    $content it is having the term id.
 * @param string $column_name it is having the column name.
 * @param int    $term_id it is having the term id.
 * @return int
 */
function woo_restaurant_taxonomy_columns_content( $content, $column_name, $term_id ) {
	if ( 'cat_id' == $column_name ) {
		$content = $term_id;
	}
	return $content;
}
add_filter( 'manage_product_cat_custom_column', 'woo_restaurant_taxonomy_columns_content', 10, 3 );

add_action( 'wp_ajax_wooresfd_change_order_menu', 'wp_ajax_wooresfd_change_order_menu' );
/**
 * To change order menu using ajax.
 */
function wp_ajax_wooresfd_change_order_menu() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'order-menu' ) ) {
			return;
		}
	}

	if ( isset( $_POST['post_id'] ) || isset( $_POST['value'] ) ) {
		$post_id = wp_kses_post( wp_unslash( $_POST['post_id'] ) );
		$value   = wp_kses_post( wp_unslash( $_POST['value'] ) );
	}

	if ( '' == $value ) {
		$value = 0;
	}

	if ( isset( $post_id ) && 0 != $post_id ) {
		update_term_meta( $post_id, 'woo_restaurant_menu_order', esc_attr( $value ) );
	}
	die;
}

add_filter( 'manage_product_posts_columns', 'woore_edit_columns', 99 );
/**
 * Order column.
 *
 * @param array $columns it is having the names of the columns.
 * @return array
 */
function woore_edit_columns( $columns ) {
	$columns['woo_restaurant_order'] = esc_html__( 'CT Order', 'woorestaurant' );
	return $columns;
}

add_action( 'manage_product_posts_custom_column', 'woore_custom_columns', 12 );
/**
 * Custom columns.
 *
 * @param array $column it is having the column names.
 */
function woore_custom_columns( $column ) {
	global $post;
	switch ( $column ) {
		case 'woo_restaurant_order':
			$woore_order = get_post_meta( $post->ID, 'woo_restaurant_order', true );
			echo '<input type="number" style="max-width:50px" data-id="' . esc_attr( $post->ID ) . '" name="woo_restaurant_order" value="' . esc_attr( $woore_order ) . '">';
			break;
	}
}

add_action( 'wp_ajax_woo_restaurant_change_sort_food', 'woore_change_sort' );
/**
 * To change sort.
 */
function woore_change_sort() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'order-menu' ) ) {
			return;
		}
	}

	if ( isset( $_POST['post_id'] ) || isset( $_POST['value'] ) ) {
		$post_id = wp_kses_post( wp_unslash( $_POST['post_id'] ) );
		$value   = wp_kses_post( wp_unslash( $_POST['value'] ) );
	}

	if ( isset( $post_id ) && 0 != $post_id ) {
		update_post_meta( $post_id, 'woo_restaurant_order', esc_attr( str_replace( ' ', '', $value ) ) );
	}
	die;
}

add_filter( 'manage_shop_order_posts_columns', 'woore_edit_order_columns', 99 );
/**
 * Show delivery date column.
 *
 * @param array $columns it is having the names of the columns.
 * @return array
 */
function woore_edit_order_columns( $columns ) {
	$method_ship = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
	if ( '' != $method_ship ) {
		$columns['order-method'] = esc_html__( 'Order method', 'woorestaurant' );
	}
	$columns['date-delivery'] = esc_html__( 'Delivery time', 'woorestaurant' );
	$args                     = array(
		'hide_empty' => true,
		'parent'     => '0',
	);
	// phpcs:ignore
	$terms                    = get_terms( 'woorestaurant_loc', $args );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$columns['order-loc'] = esc_html__( 'Location', 'woorestaurant' );
	}
	return $columns;
}

add_action( 'manage_shop_order_posts_custom_column', 'woore_admin_order_delivery_columns', 12 );
/**
 * Admin order delivery columns.
 *
 * @param array $column it is having the name of the column.
 */
function woore_admin_order_delivery_columns( $column ) {
	global $post;
	switch ( $column ) {
		case 'order-method':
			$wooresf_id   = $post->ID;
			$order_method = get_post_meta( $wooresf_id, 'wooresfd_order_method', true );
			$order_method = 'takeaway' == $order_method ? esc_html__( 'Takeaway', 'woorestaurant' ) : ( 'dinein' == $order_method ? esc_html__( 'Dine-in', 'woorestaurant' ) : esc_html__( 'Delivery', 'woorestaurant' ) );
			echo '<span class="order-method">' . esc_attr( $order_method ) . '</span>';
			break;
		case 'date-delivery':
			$wooresf_id = $post->ID;
			echo '<span class="wooresf_id">' . esc_attr( get_post_meta( $wooresf_id, 'wooresfd_date_deli', true ) ) . ' ' . esc_attr( get_post_meta( $wooresf_id, 'wooresfd_time_deli', true ) ) . '</span>';
			break;
		case 'order-loc':
			$wooresf_id = $post->ID;
			$log_name   = get_term_by( 'slug', get_post_meta( $wooresf_id, 'woorestaurant_location', true ), 'woorestaurant_loc' );
			if ( isset( $log_name->name ) && $log_name->name ) {
				echo '<span class="order-loc">' . esc_attr( $log_name->name ) . '</span>';
			}
			break;
	}
}

add_action( 'manage_edit-shop_order_sortable_columns', 'woore_order_sortable_date_deli', 12 );
/**
 * Order sortable date delivery.
 *
 * @param array $columns it is having the name of the columns.
 * @return array
 */
function woore_order_sortable_date_deli( $columns ) {
	$columns['date-delivery'] = 'date_delivery';
	return $columns;
}

if ( ! function_exists( 'woore_admin_filter_order_delivery' ) ) {
	/**
	 * Add filter order by delivery date.
	 *
	 * @param string $post_type it is having the name of the post type.
	 * @param string $which it is the which variable.
	 */
	function woore_admin_filter_order_delivery( $post_type, $which ) {
		if ( 'shop_order' == $post_type ) {
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-datetimepicker' );
			// Display filter HTML.
			// phpcs:ignore
			echo '<input type="text" class="date-picker" name="date_delivery" placeholder="' . esc_html__( 'Select delivery date', 'woorestaurant' ) . '" value="' . ( isset( $_GET['date_delivery'] ) ? ( $_GET['date_delivery'] ) : '' ) . '">';

			// Timeslot.
			$array_time   = array();
			$default_time = woo_restaurant_get_option( 'woores_deli_time', 'woo_restaurant_advanced_options' );
			$adv_timesl   = woo_restaurant_get_option( 'woores_adv_timedeli', 'woo_restaurant_adv_timesl_options' );
			if ( is_array( $adv_timesl ) && ! empty( $adv_timesl ) ) {
				foreach ( $adv_timesl as $it_timesl ) {
					foreach ( $it_timesl['woores_deli_time'] as $time_option ) {
						$r_time = '';
						if ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] && isset( $time_option['end-time'] ) && '' != $time_option['end-time'] ) {
							$r_time = $time_option['start-time'] . ' - ' . $time_option['end-time'];
						} elseif ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] ) {
							$r_time = $time_option['start-time'];
						}
						$name                = isset( $time_option['name-ts'] ) && '' != $time_option['name-ts'] ? $time_option['name-ts'] : $r_time;
						$array_time[ $name ] = $name;
					}
				}
			} elseif ( empty( $array_time ) && ! empty( $default_time ) ) {
				foreach ( $default_time as $time_option ) {
					$r_time = '';
					if ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] && isset( $time_option['end-time'] ) && '' != $time_option['end-time'] ) {
						$r_time = $time_option['start-time'] . ' - ' . $time_option['end-time'];
					} elseif ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] ) {
						$r_time = $time_option['start-time'];
					}
					$name                = isset( $time_option['name-ts'] ) && '' != $time_option['name-ts'] ? $time_option['name-ts'] : $r_time;
					$array_time[ $name ] = $name;
				}
			}
			if ( ! empty( $array_time ) ) {
				echo "<select name='time_slot' id='time_slot' class='postform'>";
				echo '<option value="">' . esc_html__( 'All Time slots', 'exthemes' ) . '</option>';
				foreach ( $array_time as $itemsl ) {
					echo '<option value="' . esc_attr( $itemsl ) . '">' . esc_attr( $itemsl ) . '</option>';
				}
				echo '</select>';
			}

			$method_ship = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
			$dine_in     = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
			if ( 'yes' == $dine_in && '' != $method_ship || 'both' == $method_ship ) {
				echo "<select name='method' id='method' class='postform'>";
				echo '<option value="">' . esc_html__( 'All Shipping methods', 'exthemes' ) . '</option>';
				if ( 'takeaway' != $method_ship ) {
					echo '<option value="delivery" ' . ( ( isset( $_GET['method'] ) && ( 'delivery' == $_GET['method'] ) ) ? ' selected="selected"' : '' ) . '>' . esc_html__( 'Delivery', 'woorestaurant' ) . '</option>';
				}
				if ( 'delivery' != $method_ship ) {
					echo '<option value="takeaway" ' . ( ( isset( $_GET['method'] ) && ( 'takeaway' == $_GET['method'] ) ) ? ' selected="selected"' : '' ) . '>' . esc_html__( 'Takeaway', 'woorestaurant' ) . '</option>';
				}
				if ( 'yes' == $dine_in ) {
					echo '<option value="dinein" ' . ( ( isset( $_GET['method'] ) && ( 'dinein' == $_GET['method'] ) ) ? ' selected="selected"' : '' ) . '>' . esc_html__( 'Dine-in', 'woorestaurant' ) . '</option>';
				}
				echo '</select>';
			}
			$args         = array(
				'hide_empty' => false,
				'parent'     => '0',
			);
			$loc_selected = isset( $_GET['floc'] ) ? wp_kses_post( wp_unslash( $_GET['floc'] ) ) : '';
			// phpcs:ignore
			$terms        = get_terms( 'woorestaurant_loc', $args );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) { ?>
				<select class="postform" name="floc">
					<?php
					$count_stop = 5;
					echo '<option value="">' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';
					foreach ( $terms as $term ) {
						$select_loc = '';
						if ( '' != $term->slug && $term->slug == $loc_selected ) {
							$select_loc = ' selected="selected"';
						}
						echo '<option value="' . esc_attr( $term->slug ) . '" ' . esc_attr( $select_loc ) . '>' . wp_kses_post( $term->name ) . '</option>';
						echo esc_attr( wooresfd_show_child_location( '', $term, $count_stop, $loc_selected, 'yes' ) );
					}
					?>
				</select>
				<?php
			}
		}
	}
	add_action( 'restrict_manage_posts', 'woore_admin_filter_order_delivery', 10, 2 );
}

add_action(
	'admin_init',
	function () {
		global $pagenow;
		// Check current admin page.
		$id = isset( $_GET['post'] ) ? wp_kses_post( wp_unslash( $_GET['post'] ) ) : '';
		if ( 'post.php' == $pagenow && '' != $id && ( in_array( get_post_type( $id ), array( 'shop_order', 'product' ) ) ) ) {
			$user         = wp_get_current_user();
			$loc_selected = get_the_author_meta( 'woore_mng_loc', $user->ID );
			if ( isset( $user->roles[0] ) && 'shop_manager' == $user->roles[0] && 'shop_order' == get_post_type( $id ) ) {
				$log_name = get_post_meta( $id, 'woorestaurant_location', true );
				if ( is_array( $loc_selected ) && ! empty( $loc_selected ) && ! in_array( $log_name, $loc_selected ) ) {
					wp_safe_redirect( admin_url( '/edit.php?post_type=' . get_post_type( $id ) ) );
					exit;
				}
			} elseif ( is_array( $loc_selected ) && ! empty( $loc_selected ) && ! has_term( $loc_selected, 'woorestaurant_loc', $id ) ) {
				wp_safe_redirect( admin_url( '/edit.php?post_type=' . get_post_type( $id ) ) );
				exit;
			}
		}
		if ( isset( $_GET['woore_uddel'] ) && 'yes' == $_GET['woore_uddel'] || isset( $_GET['page'] ) && 'woore_ocal_options' == $_GET['page'] ) {
			if ( 'updated' != get_option( '_woore_uddel' ) ) {
				update_option( '_woore_uddel', 'updated' );
				$orders   = wc_get_orders( array( 'numberposts' => -1 ) );
				$my_posts = $orders;
				foreach ( $my_posts as $post ) :
					$id       = $post->get_id();
					$datetime = get_post_meta( $id, 'wooresfd_datetime_deli_unix', true );
					$date     = get_post_meta( $id, 'wooresfd_date_deli_unix', true );
					if ( '' == $datetime ) {
						update_post_meta( $id, 'wooresfd_datetime_deli_unix', $date );
					}
				endforeach;
			}
		}
	}
);

add_action( 'pre_get_posts', 'woore_admin_filter_delivery_qr', 101 );
if ( ! function_exists( 'woore_admin_filter_delivery_qr' ) ) {
	/**
	 * Admin filter delivery.
	 *
	 * @param object $query it is having the query.
	 */
	function woore_admin_filter_delivery_qr( $query ) {
		if ( isset( $_GET['post_type'] ) && 'shop_order' == $_GET['post_type'] && is_admin() ) {
			$meta_query_args = array();
			$method          = isset( $_GET['method'] ) ? wp_kses_post( wp_unslash( $_GET['method'] ) ) : '';
			if ( '' != $method ) {
				$meta_query_args['relation'] = 'AND';
				if ( 'delivery' != $method ) {
					$meta_query_args[] = array(
						'key'     => 'wooresfd_order_method',
						'value'   => $method,
						'compare' => '=',
					);
				} else {
					$meta_query_args[] = array(
						'relation' => 'OR',
						array(
							'key'     => 'wooresfd_order_method',
							'value'   => $method,
							'compare' => '=',
						),
						array(
							'key'     => 'wooresfd_order_method',
							'value'   => '',
							'compare' => 'NOT EXISTS',
						),
					);
				}
			}
			if ( isset( $_GET['date_delivery'] ) && '' != $_GET['date_delivery'] ) {
				$unix_tdl          = strtotime( wp_kses_post( wp_unslash( $_GET['date_delivery'] ) ) );
				$meta_query_args[] = array(
					'relation' => 'AND',
					array(
						'key'     => 'wooresfd_date_deli_unix',
						'value'   => $unix_tdl,
						'compare' => '>=',
					),
					array(
						'key'     => 'wooresfd_date_deli_unix',
						'value'   => ( $unix_tdl + 86399 ),
						'compare' => '<=',
					),
				);
			}
			if ( isset( $_GET['time_slot'] ) && '' != $_GET['time_slot'] ) {
				$meta_query_args[] = array(
					'key'     => 'wooresfd_time_deli',
					'value'   => wp_kses_post( wp_unslash( $_GET['time_slot'] ) ),
					'compare' => '=',
				);
			}
			$loc = isset( $_GET['floc'] ) ? wp_kses_post( wp_unslash( $_GET['floc'] ) ) : '';
			if ( '' != $loc ) {
				$meta_query_args['relation'] = 'AND';
				$args                        = array(
					'hide_empty' => false,
					'parent'     => '0',
				);
				// phpcs:ignore
				$terms                       = get_terms( 'woorestaurant_loc', $args );
				$locs_to_filter[] = $loc;
				foreach ( $terms as $term ) {
					if ( 0 === $term->parent && $term->slug == $loc ) {
						$args2 = array(
							'hide_empty' => false,
							'parent'     => $term->term_id,
						);
						// phpcs:ignore
						$child_terms = get_terms( 'woorestaurant_loc', $args2 );
						foreach ( $child_terms as $child_term ) {
							$locs_to_filter[] = $child_term->slug;
						}
					}
				}
				$meta_query_args[] = array(
					'key'     => 'woorestaurant_location',
					'value'   => $locs_to_filter,
					'compare' => 'IN',
				);
			}

			if ( ! empty( $meta_query_args ) ) {
				$query->set( 'meta_query', $meta_query_args );
			}
			if ( isset( $_GET['order'] ) && isset( $_GET['orderby'] ) && 'date_delivery' == $_GET['orderby'] ) {
				$query->set( 'order', wp_kses_post( wp_unslash( $_GET['order'] ) ) );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'meta_key', 'wooresfd_datetime_deli_unix' );
			}
		}
	}
}

add_filter( 'plugin_row_meta', 'woore_plugin_row_meta_link', 10, 2 );
/**
 * Plugin row meta link.
 *
 * @param array  $links it is havinf the links of the plugins.
 * @param string $file it is having the path of the file.
 * @return array
 */
function woore_plugin_row_meta_link( $links, $file ) {
	if ( 'woo-wooresf/woo-food.php' === $file || 'woo-restaurant/woo-restaurant.php' === $file ) {
		$row_meta = array(
			'support' => '<a href="https://woorestaurant.co.uk/support-portal/" target="_blank" title="">Support</a>',
			'doc'     => '<a href="https://woorestaurant.co.uk/docs/" target="_blank" title="">Plugin Documentation</a>',
		);
		return array_merge( $links, $row_meta );
	}

	return (array) $links;
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'woore_adm_display_date_deli', 10, 1 );
/**
 * Display field value on the order edit page.
 *
 * @param object $order it is the order object.
 */
function woore_adm_display_date_deli( $order ) {
	$text_datedel = woore_date_time_text( 'date', $order );
	$text_timedel = woore_date_time_text( 'time', $order );
	$order_method = get_post_meta( $order->get_id(), 'wooresfd_order_method', true );
	$method_ship  = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
	$dine_in      = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
	echo '<p class="woore-adm-odmethod"><strong>' . esc_html__( 'Order method', 'woorestaurant' ) . ':</strong> 
	<select name="woore_odmethod" class="postform" data-tdel="' . esc_html__( 'Delivery Date', 'woorestaurant' ) . '" data-ttk="' . esc_html__( 'Pickup Date', 'woorestaurant' ) . '" data-tdin="' . esc_html__( 'Date', 'woorestaurant' ) . '" data-ttdel="' . esc_html__( 'Delivery Time', 'woorestaurant' ) . '" data-tttk="' . esc_html__( 'Pickup Time', 'woorestaurant' ) . '" data-ttdin="' . esc_html__( 'Time', 'woorestaurant' ) . '">';
	echo '<option value="">' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';
	if ( 'takeaway' != $method_ship ) {
		echo '<option value="delivery" ' . ( 'delivery' == $order_method ? ' selected="selected"' : '' ) . '>' . esc_html__( 'Delivery', 'woorestaurant' ) . '</option>';
	}
	if ( 'delivery' != $method_ship ) {
		echo '<option value="takeaway" ' . ( 'takeaway' == $order_method ? ' selected="selected"' : '' ) . '>' . esc_html__( 'Takeaway', 'woorestaurant' ) . '</option>';
	}
	if ( 'yes' == $dine_in ) {
		echo '<option value="dinein" ' . ( 'dinein' == $order_method ? ' selected="selected"' : '' ) . '>' . esc_html__( 'Dine-in', 'woorestaurant' ) . '</option>';
	}
	if ( '' != $order_method && 'delivery' != $order_method && 'takeaway' != $order_method && 'dinein' != $order_method ) {
		echo '<option value="' . esc_attr( $order_method ) . '" selected="selected">' . esc_attr( $order_method ) . '</option>';
	}
	echo '</select></p>';
	$log_name = get_post_meta( $order->get_id(), 'woorestaurant_location', true );
	$args     = array(
		'hide_empty' => false,
		'parent'     => '0',
	);
	// phpcs:ignore
	$terms    = get_terms( 'woorestaurant_loc', $args );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		echo '<p><strong>' . esc_html__( 'Location', 'woorestaurant' ) . ':</strong> '
		?>
		<select class="postform" name="woore_odloc">
			<?php
			$count_stop = 5;
			echo '<option value="">' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';
			foreach ( $terms as $term ) {
				$select_loc = '';
				if ( '' != $term->slug && $term->slug == $log_name ) {
					$select_loc = ' selected="selected"';
				}
				echo '<option value="' . esc_attr( $term->slug ) . '" ' . esc_attr( $select_loc ) . '>' . wp_kses_post( $term->name ) . '</option>';
				echo esc_attr( wooresfd_show_child_location( '', $term, $count_stop, $log_name, 'yes' ) );
			}
			?>
		</select>
		<?php
	} elseif ( '' != $log_name ) {
		echo '<p><strong>' . esc_html__( 'Location', 'woorestaurant' ) . ':</strong> ' . esc_attr( $log_name ) . '</p>';
	}
	$_datedl = get_post_meta( $order->get_id(), 'wooresfd_date_deli_unix', true );
	echo '<p class="woore-adm-oddate" ><strong>' . esc_attr( $text_datedel ) . ':</strong> <input type="text" class="date-picker" name="woore_deldate" placeholder="' . esc_html__( 'Select delivery date', 'woorestaurant' ) . '" value="' . ( '' != $_datedl ? esc_attr( date_i18n( 'Y-m-d', $_datedl ) ) : '' ) . '"></p>';
	$_timedl = get_post_meta( $order->get_id(), 'wooresfd_time_deli', true );
	echo '<p class="woore-adm-odtime"><strong>' . esc_attr( $text_timedel ) . ':</strong> <input type="text" class="" name="woore_deltime" placeholder="' . esc_html__( 'Set delivery time', 'woorestaurant' ) . '" value="' . ( '' != $_timedl ? esc_attr( $_timedl ) : '' ) . '"></p>';
	$_nb_per = get_post_meta( $order->get_id(), 'wooresfd_person_dinein', true );
	echo '<p class="woore-di-person ' . ( 'dinein' != $order_method ? 'wrs-hidden' : '' ) . '"><strong>' . esc_html__( 'Number of person', 'woorestaurant' ) . ':</strong> <input type="text" class="" name="woore_diperson" placeholder="' . esc_html__( 'Set Number of person', 'woorestaurant' ) . '" value="' . ( '' != $_nb_per ? esc_attr( $_nb_per ) : '' ) . '"></p>';
}

add_action( 'save_post_shop_order', 'woore_custom_order_meta_data' );
/**
 * For saving the metabox data.
 *
 * @param int $post_id it is the post id.
 */
function woore_custom_order_meta_data( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_shop_order', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'order-data' ) ) {
			return;
		}
	}

	if ( isset( $_POST['woore_odmethod'] ) ) {
		update_post_meta( $post_id, 'wooresfd_order_method', wp_kses_post( wp_unslash( $_POST['woore_odmethod'] ) ) );
	}
	if ( isset( $_POST['woore_odloc'] ) ) {
		update_post_meta( $post_id, 'woorestaurant_location', wp_kses_post( wp_unslash( $_POST['woore_odloc'] ) ) );
	}
	if ( isset( $_POST['woore_deldate'] ) ) {
		$dunix = strtotime( wp_kses_post( wp_unslash( $_POST['woore_deldate'] ) ) );
		update_post_meta( $post_id, 'wooresfd_date_deli_unix', $dunix );
		if ( isset( $_POST['woore_deltime'] ) ) {
			$timedel = str_replace( ' ', '', wp_kses_post( wp_unslash( $_POST['woore_deltime'] ) ) );
			$timedel = explode( '-', $timedel );
			$start   = isset( $timedel[0] ) ? strtotime( $timedel[0] ) - strtotime( gmdate( 'Y-m-d' ) ) : 0;
			update_post_meta( $post_id, 'wooresfd_datetime_deli_unix', ( $start > 0 ? $dunix + $start : $dunix ) );
		} else {
			update_post_meta( $post_id, 'wooresfd_datetime_deli_unix', $dunix );
		}
		update_post_meta( $post_id, 'wooresfd_date_deli', sanitize_text_field( date_i18n( get_option( 'date_format' ), $dunix ) ) );
	}
	if ( isset( $_POST['woore_deltime'] ) ) {
		update_post_meta( $post_id, 'wooresfd_time_deli', wp_kses_post( wp_unslash( $_POST['woore_deltime'] ) ) );
	}
	if ( isset( $_POST['woore_odmethod'] ) && 'dinein' == $_POST['woore_odmethod'] && isset( $_POST['woore_diperson'] ) ) {
		update_post_meta( $post_id, 'wooresfd_person_dinein', wp_kses_post( wp_unslash( $_POST['woore_diperson'] ) ) );
	}
}

/**
 * To get enabled method.
 */
function woore_adm_get_method_enable() {
	$method_ship = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
	$dine_in     = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
	$arr_methods = array();
	if ( 'takeaway' == $method_ship || 'delivery' == $method_ship ) {
		$arr_methods[] = $method_ship;
	} elseif ( 'both' == $method_ship ) {
		$arr_methods[] = 'takeaway';
		$arr_methods[] = 'delivery';
	}
	if ( 'yes' == $dine_in ) {
		$arr_methods[] = 'dinein';
	}
	return apply_filters( 'woore_adm_arr_enable_method', $arr_methods );
}

add_action( 'show_user_profile', 'woore_user_manager_loc_profile_fields' );
add_action( 'edit_user_profile', 'woore_user_manager_loc_profile_fields' );
/**
 * Add user location role.
 *
 * @param object $user it is the user object.
 */
function woore_user_manager_loc_profile_fields( $user ) {
	if ( ! current_user_can( 'promote_user', $user->ID ) ) {
		return;
	}
	?>
	<h3><?php esc_html_e( 'Location manager', 'woorestaurant' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="woore_mng_loc"><?php esc_html_e( 'Locations', 'woorestaurant' ); ?></label></th>
			<td>
				<?php
				$args         = array(
					'hide_empty' => false,
				);
				$loc_selected = get_the_author_meta( 'woore_mng_loc', $user->ID );
				// phpcs:ignore
				$terms        = get_terms( 'woorestaurant_loc', $args );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					?>
					<select name="woore_mng_loc[]" multiple>
						<?php
						$count_stop = 5;
						echo '<option value="">' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';
						foreach ( $terms as $term ) {
							$select_loc = '';
							if ( '' != $term->slug && is_array( $loc_selected ) && in_array( $term->slug, $loc_selected ) ) {
								$select_loc = ' selected="selected"';
							}
							echo '<option value="' . esc_attr( $term->slug ) . '" ' . esc_attr( $select_loc ) . '>' . wp_kses_post( $term->name ) . '</option>';
						}
						?>
					</select>
					<?php
				}
				?>
				<p class="description"><?php esc_html_e( 'Select locations to allow this user can manage order and product, leave blank to allow this user can manage all orders and products', 'woorestaurant' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'personal_options_update', 'woore_save_manager_loc_profile_fields' );
add_action( 'edit_user_profile_update', 'woore_save_manager_loc_profile_fields' );

/**
 * Save manager profile fields.
 *
 * @param int $user_id it is the user id.
 */
function woore_save_manager_loc_profile_fields( $user_id ) {
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user_id ) ) {
		return;
	}

	if ( ! current_user_can( 'promote_user', $user_id ) ) {
		return;
	}

	if ( isset( $_POST['woore_mng_loc'] ) ) {
		update_user_meta( $user_id, 'woore_mng_loc', wp_kses_post( wp_unslash( $_POST['woore_mng_loc'] ) ) );
	}
}

add_action( 'pre_get_posts', 'woore_admin_manage_by_loc', 102 );
if ( ! function_exists( 'woore_admin_manage_by_loc' ) ) {
	/**
	 * Admin manage by Location.
	 *
	 * @param object $query it is the query.
	 */
	function woore_admin_manage_by_loc( $query ) {
		if ( ! is_admin() ) {
			return;
		}
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include ABSPATH . 'wp-includes/pluggable.php';
		}
		$user = wp_get_current_user();
		if ( is_admin() && isset( $user->roles[0] ) && 'shop_manager' == $user->roles[0] && in_array( $query->get( 'post_type' ), array( 'shop_order', 'product' ) ) ) {
			$loc_selected = get_the_author_meta( 'woore_mng_loc', $user->ID );
			if ( is_array( $loc_selected ) && ! empty( $loc_selected ) ) {
				if ( 'shop_order' == $query->get( 'post_type' ) ) {
					$meta_query_args['relation'] = 'AND';
					$meta_query_args[]           = array(
						'key'     => 'woorestaurant_location',
						'value'   => $loc_selected,
						'compare' => 'IN',
					);
					$query->set( 'meta_query', $meta_query_args );
				} else {
					$tax_query_args['relation'] = 'OR';
					$tax_query_args[]           = array(
						'taxonomy' => 'woorestaurant_loc',
						'field'    => 'slug',
						'terms'    => $loc_selected,
						'operator' => 'IN',
					);
					$query->set( 'tax_query', $tax_query_args );
				}
			}
		}
	}
}

add_action(
	'pre_insert_term',
	function ( $term, $taxonomy ) {
		$user         = wp_get_current_user();
		$loc_selected = get_the_author_meta( 'woore_mng_loc', $user->ID );
		if ( 'woorestaurant_loc' === $taxonomy && is_array( $loc_selected ) && ! empty( $loc_selected ) ) {
			return new WP_Error( 'term_addition_blocked', esc_html__( 'You cannot add terms to this taxonomy', 'woorestaurant' ) );
		}
		return $term;
	},
	0,
	2
);

add_filter(
	'get_terms_args',
	function ( $query, $taxonomies ) {
		if ( is_admin() && isset( $taxonomies[0] ) && 'woorestaurant_loc' == $taxonomies[0] ) {
			$user         = wp_get_current_user();
			$loc_selected = get_the_author_meta( 'woore_mng_loc', $user->ID );
			if ( is_array( $loc_selected ) && ! empty( $loc_selected ) ) {
				$query['slug'] = $loc_selected;
			}
		}
		return $query;
	},
	0,
	2
);

add_filter( 'woorestaurant_loc_row_actions', 'woore_remove_delete_link_term', 10, 2 );
/**
 * To remove delete link term.
 *
 * @param array  $actions it is having the actions.
 * @param object $post it is the post object.
 */
function woore_remove_delete_link_term( $actions, $post ) {
	$user         = wp_get_current_user();
	$loc_selected = get_the_author_meta( 'woore_mng_loc', $user->ID );
	if ( is_array( $loc_selected ) && ! empty( $loc_selected ) ) {
		unset( $actions['delete'] );
	}
	return $actions;
}

add_action( 'admin_init', 'woore_set_object_terms_terms', 1 );
/**
 * Set object terms.
 */
function woore_set_object_terms_terms() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'object-terms' ) ) {
			return;
		}
	}

	$post_id = isset( $_POST['post_ID'] ) ? wp_kses_post( wp_unslash( $_POST['post_ID'] ) ) : '';
	if ( '' == $post_id || 'product' !== get_post_type( $post_id ) ) {
		return;
	}
	$user         = wp_get_current_user();
	$loc_selected = get_the_author_meta( 'woore_mng_loc', $user->ID );
	if ( is_array( $loc_selected ) && ! empty( $loc_selected ) ) {
		$term_obj_list = wp_get_post_terms( $post_id, 'woorestaurant_loc' );
		$terms_arr     = wp_list_pluck( $term_obj_list, 'term_id' );
		print_r( $terms_arr );
		$_POST['tax_input']['woorestaurant_loc'] = $terms_arr;
	}
}

/**
 * Menu head.
 *
 * @param string $text it is the text value.
 */
function woores_menu_head( $text ) {
	?>

	<div class="wpc-admin-header">
		<div class="wpc-admin-header-logo">
			<div class="wpc-logo-wrap">
				<img src="<?php echo esc_attr( WOORESTAURANT_ASSETS ) . 'admin/img/logo.jpg'; ?>" alt="logo">
			</div>
			<h1 class="wpc-settings-title wpc-header-title"><?php echo esc_attr( $text ); ?></h1>
		</div>
		<div class="wpc-admin-menu-wrap">
			<ul>
				<li><a href="<?php echo esc_html( admin_url( '/edit.php?post_type=woores_modifiers' ) ); ?>">Modifiers</a></li>
				<li><a href="<?php echo esc_html( admin_url( '/edit.php?post_type=woorestaurant_scbd' ) ); ?>">Menu</a></li>
				<li><a href="<?php echo esc_html( admin_url( '/edit-tags.php?taxonomy=woorestaurant_loc&post_type=woorestaurant_scbd' ) ); ?>">Locations</a></li>
				<li><a href="<?php echo esc_html( admin_url( '/admin.php?page=woo_restaurant_options' ) ); ?>">Settings</a></li>
			</ul>
		</div>
		<div class="wpc-header-btn-wrap">
			<div class="wpc-admin-notify-wrap">
				<div class="wpc-admin-btn-wrap">
					<svg width="26" height="25" viewBox="0 0 26 25" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M19.2106 0H6.57897C2.7895 0 0.263184 2.52632 0.263184 6.31579V13.8947C0.263184 17.6842 2.7895 20.2105 6.57897 20.2105V22.9011C6.57897 23.9116 7.70318 24.5179 8.53687 23.9495L14.1579 20.2105H19.2106C23 20.2105 25.5263 17.6842 25.5263 13.8947V6.31579C25.5263 2.52632 23 0 19.2106 0ZM12.8948 15.3726C12.3642 15.3726 11.9474 14.9432 11.9474 14.4253C11.9474 13.9074 12.3642 13.4779 12.8948 13.4779C13.4253 13.4779 13.8421 13.9074 13.8421 14.4253C13.8421 14.9432 13.4253 15.3726 12.8948 15.3726ZM14.4863 10.1305C13.9937 10.4589 13.8421 10.6737 13.8421 11.0274V11.2926C13.8421 11.8105 13.4127 12.24 12.8948 12.24C12.3769 12.24 11.9474 11.8105 11.9474 11.2926V11.0274C11.9474 9.56211 13.0211 8.84211 13.4253 8.56421C13.8927 8.24842 14.0442 8.03368 14.0442 7.70526C14.0442 7.07368 13.5263 6.55579 12.8948 6.55579C12.2632 6.55579 11.7453 7.07368 11.7453 7.70526C11.7453 8.22316 11.3158 8.65263 10.7979 8.65263C10.28 8.65263 9.85055 8.22316 9.85055 7.70526C9.85055 6.02526 11.2148 4.66105 12.8948 4.66105C14.5748 4.66105 15.939 6.02526 15.939 7.70526C15.939 9.14526 14.8779 9.86526 14.4863 10.1305Z" fill="#2F4858"></path>
					</svg>
					<div class="dropdown">
						<div class="list-item">
							<a href="#support" target="_blank">
								<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M10.0482 4.37109H4.30125C4.06778 4.37109 3.84329 4.38008 3.62778 4.40704C1.21225 4.6137 0 6.04238 0 8.6751V12.2693C0 15.8634 1.43674 16.5733 4.30125 16.5733H4.66044C4.85799 16.5733 5.1184 16.708 5.23514 16.8608L6.3127 18.2985C6.78862 18.9364 7.56087 18.9364 8.03679 18.2985L9.11435 16.8608C9.24904 16.6811 9.46456 16.5733 9.68905 16.5733H10.0482C12.6793 16.5733 14.107 15.3692 14.3136 12.9432C14.3405 12.7275 14.3495 12.5029 14.3495 12.2693V8.6751C14.3495 5.80876 12.9127 4.37109 10.0482 4.37109ZM4.04084 11.5594C3.53798 11.5594 3.14288 11.1551 3.14288 10.6609C3.14288 10.1667 3.54696 9.76233 4.04084 9.76233C4.53473 9.76233 4.93881 10.1667 4.93881 10.6609C4.93881 11.1551 4.53473 11.5594 4.04084 11.5594ZM7.17474 11.5594C6.67188 11.5594 6.27678 11.1551 6.27678 10.6609C6.27678 10.1667 6.68086 9.76233 7.17474 9.76233C7.66862 9.76233 8.07271 10.1667 8.07271 10.6609C8.07271 11.1551 7.6776 11.5594 7.17474 11.5594ZM10.3176 11.5594C9.81476 11.5594 9.41966 11.1551 9.41966 10.6609C9.41966 10.1667 9.82374 9.76233 10.3176 9.76233C10.8115 9.76233 11.2156 10.1667 11.2156 10.6609C11.2156 11.1551 10.8115 11.5594 10.3176 11.5594Z" fill="#E3106E"></path>
									<path d="M17.9423 5.08086V8.67502C17.9423 10.4721 17.3855 11.6941 16.272 12.368C16.0026 12.5298 15.6884 12.3141 15.6884 11.9996L15.6973 8.67502C15.6973 5.08086 13.641 3.0232 10.0491 3.0232L4.58048 3.03219C4.26619 3.03219 4.05067 2.7177 4.21231 2.44814C4.88578 1.33395 6.10702 0.776855 7.89398 0.776855H13.641C16.5055 0.776855 17.9423 2.21452 17.9423 5.08086Z" fill="#E3106E"></path>
								</svg>
								<span>Need Help?</span>
							</a>
							<a href="#documentation" target="_blank">
								<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M16.1896 7.57803H13.5902C11.4586 7.57803 9.72274 5.84103 9.72274 3.70803V1.10703C9.72274 0.612031 9.318 0.207031 8.82332 0.207031H5.00977C2.23956 0.207031 0 2.00703 0 5.22003V13.194C0 16.407 2.23956 18.207 5.00977 18.207H12.0792C14.8494 18.207 17.089 16.407 17.089 13.194V8.47803C17.089 7.98303 16.6843 7.57803 16.1896 7.57803ZM8.09478 14.382H4.4971C4.12834 14.382 3.82254 14.076 3.82254 13.707C3.82254 13.338 4.12834 13.032 4.4971 13.032H8.09478C8.46355 13.032 8.76935 13.338 8.76935 13.707C8.76935 14.076 8.46355 14.382 8.09478 14.382ZM9.89363 10.782H4.4971C4.12834 10.782 3.82254 10.476 3.82254 10.107C3.82254 9.73803 4.12834 9.43203 4.4971 9.43203H9.89363C10.2624 9.43203 10.5682 9.73803 10.5682 10.107C10.5682 10.476 10.2624 10.782 9.89363 10.782Z" fill="#E3106E"></path>
								</svg>
								<span>Documentation</span>

							</a>
							<a href="#ideas" target="_blank">
								<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M13.5902 7.57803H16.1896C16.6843 7.57803 17.089 7.98303 17.089 8.47803V13.194C17.089 16.407 14.8494 18.207 12.0792 18.207H5.00977C2.23956 18.207 0 16.407 0 13.194V5.22003C0 2.00703 2.23956 0.207031 5.00977 0.207031H8.82332C9.318 0.207031 9.72274 0.612031 9.72274 1.10703V3.70803C9.72274 5.84103 11.4586 7.57803 13.5902 7.57803ZM11.9613 0.396012C11.5926 0.0270125 10.954 0.279013 10.954 0.792013V3.93301C10.954 5.24701 12.0693 6.33601 13.4274 6.33601C14.2818 6.34501 15.4689 6.34501 16.4852 6.34501H16.4854C16.998 6.34501 17.2679 5.74201 16.9081 5.38201C16.4894 4.96018 15.9637 4.42927 15.3988 3.85888L15.3932 3.85325L15.3913 3.85133L15.3905 3.8505L15.3902 3.85016C14.2096 2.65803 12.86 1.29526 11.9613 0.396012ZM3.0145 12.0732C3.0145 11.7456 3.28007 11.48 3.60768 11.48H5.32132V9.76639C5.32132 9.43879 5.58689 9.17321 5.9145 9.17321C6.2421 9.17321 6.50768 9.43879 6.50768 9.76639V11.48H8.22131C8.54892 11.48 8.8145 11.7456 8.8145 12.0732C8.8145 12.4008 8.54892 12.6664 8.22131 12.6664H6.50768V14.38C6.50768 14.7076 6.2421 14.9732 5.9145 14.9732C5.58689 14.9732 5.32132 14.7076 5.32132 14.38V12.6664H3.60768C3.28007 12.6664 3.0145 12.4008 3.0145 12.0732Z" fill="#E3106E"></path>
								</svg>
								<span>Feature Request</span>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
