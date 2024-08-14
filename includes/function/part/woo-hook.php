<?php
/**
 * Woo hook file.
 *
 * @package woorestaurant
 */

add_filter(
	'woore_get_optionX',
	function ( $val, $option_key, $key ) {

		if ( 'woo_restaurant_ship_mode' == $key && 'woo_restaurant_shpping_options' == $option_key ) {
			return '';
		}
		return $val;
	},
	10,
	3
);

add_action( 'woocommerce_before_order_notes', 'woore_date_deli_field' );
/**
 * Add the field to the checkout.
 *
 * @param string $rq it is the require parameter.
 */
function woo_restaurant_ckselect_loc_html( $rq ) {
	$args = array(
		'hide_empty' => false,
		'parent'     => '0',
	);
	$args = apply_filters( 'woore_location_args', $args );
	// phpcs:ignore
	$terms = get_terms( 'woorestaurant_loc', $args );
	ob_start();
	$loc_selected = WC()->session->get( 'wr_userloc' );
	$user_log     = '';
	if ( '' == $loc_selected ) {
		$user_log     = WC()->session->get( '_user_deli_log' );
		$loc_selected = $user_log;
	}
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;}
	?>
	<div class="wrsf-loc-field ">
		<p class="form-row <?php echo 'req' == $rq ? 'validate-required' : ''; ?>">
			<label for="wooresfd_time_deli" class="">
				<?php
				esc_html_e( 'Locations ', 'woorestaurant' );
				echo 'req' == $rq ? '<abbr class="required" title="required">*</abbr>' : '';
				?>
				<small style="display: block;"><?php echo esc_html__( '(Please choose area you want to order)', 'woorestaurant' ); ?></small>
			</label>
			<span class="woocommerce-input-wrapper">
			<select class="wrsck-loc select" name="woo_restaurant_ck_loca">
				<?php
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					if ( '' != $loc_selected && '' == $user_log ) {
						$log_name = get_term_by( 'slug', $loc_selected, 'woorestaurant_loc' );
						if ( isset( $log_name->name ) && $log_name->name ) {
							$html = '<option value="' . esc_attr( $log_name->slug ) . '" selected >' . wp_kses_post( $log_name->name ) . '</option>';
						}
					} else {
						$html       = '<option value=""></option>';
						$count_stop = 5;
						foreach ( $terms as $term ) {
							$selected = $loc_selected == $term->slug ? 'selected' : '';
							if ( '' != $loc_selected && ( $loc_selected == $term->slug ) ) {
								if ( '' == $user_log ) {
									$html = '<option value="' . esc_attr( $term->slug ) . '" selected >' . wp_kses_post( $term->name ) . '</option>';
									break;
								} else {
									$html .= '<option value="' . esc_attr( $term->slug ) . '" selected >' . wp_kses_post( $term->name ) . '</option>';
								}
							} else {
								$html .= '<option value="' . esc_attr( $term->slug ) . '" >' . wp_kses_post( $term->name ) . '</option>';
								$html .= wooresfd_show_child_location( '', $term, $count_stop, $loc_selected, 'yes' );
							}
						}
					}
					echo esc_attr( $html );
				} // if have terms.
				?>
			</select>
			</span>
		</p>	
	</div>
	<?php
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}

/**
 * Woo dinein field.
 */
function woore_dinein_field() {
	$method = WC()->session->get( '_user_order_method' );
	if ( 'dinein' != $method ) {
		return;
	}
	$nbperson     = woo_restaurant_get_option( 'woo_restaurant_ck_nbperson', 'woo_restaurant_adv_dinein_options' );
	$max_nbperson = woo_restaurant_get_option( 'woo_restaurant_ck_maxperson', 'woo_restaurant_adv_dinein_options' );
	ob_start();
	?>
	<div class="wrsf-dine-field ">
		<?php
		do_action( 'woore_before_dinein_field' );
		if ( 'disable' != $nbperson ) {
			?>
			<p class="form-row <?php echo 'req' == $nbperson ? 'validate-required' : ''; ?>">
				<?php
				if ( '' == $max_nbperson || ! is_numeric( $max_nbperson ) ) {
					woocommerce_form_field(
						'wooresfd_person_dinein',
						array(
							'type'              => 'text',
							'required'          => 'req' == $nbperson ? true : false,
							'class'             => array( 'wrsfood-person-dinein form-row-wide' ),
							'label'             => esc_html__( 'Number of person', 'woorestaurant' ),
							'placeholder'       => esc_html__( 'Enter number', 'woorestaurant' ),
							'custom_attributes' => '',
						)
					);
				} else {
					$arr_nb   = array();
					$arr_nb[] = '';
					for ( $i = 1; $i <= $max_nbperson; $i++ ) {
						$arr_nb[ $i ] = $i;
					}
					$arr_nb = apply_filters( 'woore_nb_maxperson', $arr_nb );
					woocommerce_form_field(
						'wooresfd_person_dinein',
						array(
							'type'        => 'select',
							'required'    => 'req' == $nbperson ? true : false,
							'class'       => array( 'wrsfood-person-dinein form-row-wide' ),
							'label'       => esc_html__( 'Number of person', 'woorestaurant' ),
							'placeholder' => '',
							'options'     => $arr_nb,
							'default'     => '',
						)
					);
				}
				?>
			</p>
			<?php
		}
		do_action( 'woore_after_dinein_field' );
		?>
	</div>
	<?php
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}

/**
 * Woo date dellivery field.
 *
 * @param object $checkout it is the checkout object.
 */
function woore_date_deli_field( $checkout ) {
	$autocolimit = woo_restaurant_get_option( 'woo_restaurant_autocomplete_limit', 'woo_restaurant_shpping_options' );
	$autocolimit = '' != str_replace( ' ', '', $autocolimit ) ? json_encode( explode( ',', $autocolimit ) ) : '';
	echo '<input type="hidden" name="woore_auto_limit" id="woore_auto_limit" value="' . esc_attr( $autocolimit ) . '">';
	$woo_restaurant_autocomplete_cko = woo_restaurant_get_option( 'woo_restaurant_autocomplete_cko', 'woo_restaurant_shpping_options' );
	echo '<input type="hidden" name="woore_dis_auto" id="woore_dis_auto" value="' . esc_attr( $woo_restaurant_autocomplete_cko ) . '">';
	// if disable fields.
	$check_ex = woore_if_check_product_notin_shipping();
	if ( false == $check_ex ) {
		return;
	}

	// Location select field.
	$loca_field = woo_restaurant_get_option( 'woo_restaurant_ck_loca', 'woo_restaurant_advanced_options' );
	if ( 'req' == $loca_field || 'op' == $loca_field ) {
		echo esc_attr( woo_restaurant_ckselect_loc_html( $loca_field ) );
	}
	echo esc_attr( woore_dinein_field() );
	// Delivery Date and time field.
	$rq_date = woo_restaurant_get_option( 'woo_restaurant_ck_date', 'woo_restaurant_advanced_options' );
	$rq_time = woo_restaurant_get_option( 'woo_restaurant_ck_time', 'woo_restaurant_advanced_options' );
	if ( 'disable' != $rq_date && 'disable' != $rq_time ) {
		$rq_date;
	} elseif ( 'disable' == $rq_date && 'disable' == $rq_time ) {
		return;
	}
	$text_datedel = woore_date_time_text( 'date' );
	$text_timedel = woore_date_time_text( 'time' );

	wp_enqueue_style( 'wrsf-date', WOORESTAURANT_ASSETS . 'js/jquery-timepicker/bootstrap-datepicker.css' );
	wp_enqueue_script( 'wrsf-date-js', WOORESTAURANT_ASSETS . 'js/jquery-timepicker/bootstrap-datepicker.js', array( 'jquery' ) );
	echo '<div class="wrsf-deli-field">';
	$date_before = woo_restaurant_get_option( 'woo_restaurant_ck_beforedate', 'woo_restaurant_advanced_options' );
	$cure_time   = strtotime( 'now' );
	$gmt_offset  = get_option( 'gmt_offset' );
	$menudate    = function_exists( 'woore_menuby_date_selected' ) ? woore_menuby_date_selected() : '';
	if ( '' != $menudate ) {
		woocommerce_form_field(
			'wooresfd_date_deli',
			array(
				'type'        => 'select',
				'required'    => 'no' == $rq_date ? false : true,
				'class'       => array( 'wrsfood-date-deli form-row-wide' ),
				'label'       => $text_datedel,
				'placeholder' => '',
				'options'     => array( $menudate => date_i18n( get_option( 'date_format' ), $menudate ) ),
				'default'     => '',
			),
			$checkout->get_value( 'wooresfd_date_deli' )
		);
	} elseif ( 'disable' != $rq_date ) {
		// woores_deli_time.
		$dis_date   = woo_restaurant_get_option( 'woo_restaurant_ck_disdate', 'woo_restaurant_advanced_options' );
		$dis_day    = woo_restaurant_get_option( 'woo_restaurant_ck_disday', 'woo_restaurant_advanced_options' );
		$enb_date   = woo_restaurant_get_option( 'woo_restaurant_ck_enadate', 'woo_restaurant_advanced_options' );
		$_date_type = woo_restaurant_get_option( 'woo_restaurant_dd_display', 'woo_restaurant_advanced_options' );

		if ( 'picker' != $_date_type ) {
			// phpcs:ignore
			date_default_timezone_set( 'UTC' );
			if ( '' != $date_before && is_numeric( $date_before ) ) {
				$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( "+$date_before day" ) );
			} elseif ( '' != $date_before && is_numeric( str_replace( 'm', '', $date_before ) ) ) {
				$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( '+' . str_replace( 'm', '', $date_before ) . ' minutes' ) );
			}
			if ( '' != $gmt_offset ) {
				$cure_time = floatval( $cure_time ) + ( $gmt_offset * 3600 );
			}
			$date      = strtotime( gmdate( 'Y-m-d', $cure_time ) );
			$maxl      = apply_filters( 'woore_number_delivery_date', 10 );
			$deli_date = array();
			if ( 'no' == $rq_date ) {
				$deli_date[] = '';
			}
			if ( is_array( $enb_date ) && count( $enb_date ) > 0 ) {
				foreach ( $enb_date as $enb_date_it ) {
					if ( $enb_date_it > $date ) {
						$date_fm                   = date_i18n( get_option( 'date_format' ), $enb_date_it );
						$deli_date[ $enb_date_it ] = $date_fm;
					}
				}
			} else {
				for ( $i = 0; $i <= $maxl; $i++ ) {
					$date_un    = strtotime( "+$i day", $date );
					$day_ofdate = gmdate( 'N', $date_un );
					if ( ( ! empty( $dis_day ) && 7 == count( $dis_day ) ) ) {
						break;
					}
					if ( ( ! empty( $dis_date ) && in_array( $date_un, $dis_date ) ) || ( ! empty( $dis_day ) && in_array( $day_ofdate, $dis_day ) ) ) {
						$maxl = $maxl++;
					} else {
						$date_fm               = date_i18n( get_option( 'date_format' ), $date_un );
						$deli_date[ $date_un ] = $date_fm;
					}
				}
			}
			woocommerce_form_field(
				'wooresfd_date_deli',
				array(
					'type'        => 'select',
					'required'    => 'no' == $rq_date ? false : true,
					'class'       => array( 'wrsfood-date-deli form-row-wide' ),
					'label'       => $text_datedel,
					'placeholder' => '',
					'options'     => $deli_date,
					'default'     => '',
				),
				$checkout->get_value( 'wooresfd_date_deli' )
			);
		} else {
			$date_fm                 = woo_restaurant_get_option( 'woo_restaurant_datepk_fm', 'woo_restaurant_advanced_options' );
			$ct_attr                 = array();
			$ct_attr['data-disday']  = '';
			$ct_attr['data-disdate'] = '';
			$ct_attr['data-fm']      = '';
			if ( is_array( $dis_day ) && count( $dis_day ) > 0 ) {
				$dis_day_st             = implode( ',', $dis_day );
				$ct_attr['data-disday'] = str_replace( '7', '0', $dis_day_st );
			}
			if ( 'dd-mm-yyyy' == $date_fm ) {
				$php_fm = 'd-m-Y';
			} else {
				$php_fm = 'm/d/Y';
			}
			$disable_book = '0';
			$dis_uni      = '';
			if ( '' != $date_before && is_numeric( $date_before ) ) {
				$dis_uni = apply_filters( 'wrst_disable_book_day', strtotime( "+$date_before day" ) );
			} elseif ( '' != $date_before && is_numeric( str_replace( 'm', '', $date_before ) ) ) {
				$dis_uni = apply_filters( 'wrst_disable_book_day', strtotime( '+' . str_replace( 'm', '', $date_before ) . ' minutes' ) );
			}
			if ( '' != $dis_uni ) {
				if ( '' != $gmt_offset ) {
					$dis_uni = $dis_uni + ( $gmt_offset * 3600 );
				}
				$disable_book = date_i18n( 'Y-m-d', $dis_uni );
			}
			$ct_attr['data-mindate'] = $disable_book;
			$tsl_fmonth              = array( esc_html__( 'January', 'woorestaurant' ), esc_html__( 'February', 'woorestaurant' ), esc_html__( 'March', 'woorestaurant' ), esc_html__( 'April', 'woorestaurant' ), esc_html__( 'May', 'woorestaurant' ), esc_html__( 'June', 'woorestaurant' ), esc_html__( 'July', 'woorestaurant' ), esc_html__( 'August', 'woorestaurant' ), esc_html__( 'September', 'woorestaurant' ), esc_html__( 'October', 'woorestaurant' ), esc_html__( 'November', 'woorestaurant' ), esc_html__( 'December', 'woorestaurant' ) );
			$ct_attr['data-fmon']    = str_replace( '\/', '/', json_encode( $tsl_fmonth ) );
			$tsl_smonth              = array( esc_html__( 'Jan', 'woorestaurant' ), esc_html__( 'Feb', 'woorestaurant' ), esc_html__( 'Mar', 'woorestaurant' ), esc_html__( 'Apr', 'woorestaurant' ), esc_html__( 'May', 'woorestaurant' ), esc_html__( 'Jun', 'woorestaurant' ), esc_html__( 'Jul', 'woorestaurant' ), esc_html__( 'Aug', 'woorestaurant' ), esc_html__( 'Sep', 'woorestaurant' ), esc_html__( 'Oct', 'woorestaurant' ), esc_html__( 'Nov', 'woorestaurant' ), esc_html__( 'December', 'woorestaurant' ) );
			$ct_attr['data-smon']    = str_replace( '\/', '/', json_encode( $tsl_smonth ) );
			$tsl_sday                = array( esc_html__( 'Su', 'woorestaurant' ), esc_html__( 'Mo', 'woorestaurant' ), esc_html__( 'Tu', 'woorestaurant' ), esc_html__( 'We', 'woorestaurant' ), esc_html__( 'Th', 'woorestaurant' ), esc_html__( 'Fr', 'woorestaurant' ), esc_html__( 'Sa', 'woorestaurant' ) );
			$ct_attr['data-sday']    = str_replace( '\/', '/', json_encode( $tsl_sday ) );

			$ct_attr['data-fiday'] = apply_filters( 'wrst_datepk_fday', 1 );
			if ( is_array( $dis_date ) && count( $dis_date ) > 0 ) {
				foreach ( $dis_date as $item ) {
					$arr_disdate[] = gmdate( $php_fm, $item );
				}
				$arr_disdate             = str_replace( '\/', '/', json_encode( $arr_disdate ) );
				$ct_attr['data-disdate'] = $arr_disdate;
			}
			$ct_attr['data-fm']  = $date_fm;
			$ct_attr['readonly'] = 'readonly';
			woocommerce_form_field(
				'wooresfd_date_deli',
				array(
					'type'              => 'text',
					'required'          => 'no' == $rq_date ? false : true,
					'class'             => array( 'wrsfood-date-deli form-row-wide' ),
					'label'             => $text_datedel,
					'placeholder'       => '',
					'custom_attributes' => $ct_attr,
				),
				$checkout->get_value( 'wooresfd_date_deli' )
			);
		}
	}
	$menutime = function_exists( 'woore_menuby_time_selected' ) ? woore_menuby_time_selected() : '';
	if ( '' != $menutime ) {
		$_user_menusl = woore_menuby_time_selected_details();
		$timefrom     = isset( $_user_menusl['time_from'] ) && '' != $_user_menusl['time_from'] ? $_user_menusl['time_from'] : '';
		$time_to      = isset( $_user_menusl['time_to'] ) && '' != $_user_menusl['time_to'] ? $_user_menusl['time_to'] : '';
		$name         = isset( $_user_menusl['mn_name'] ) && '' != $_user_menusl['mn_name'] ? $_user_menusl['mn_name'] : ( '' != $timefrom && '' != $time_to ? $timefrom . ' - ' . $time_to : ( '' != $timefrom ? $timefrom : ( '' != $time_to ? $time_to : '' ) ) );
		woocommerce_form_field(
			'wooresfd_time_deli',
			array(
				'type'        => 'select',
				'required'    => 'no' == $rq_date ? false : true,
				'class'       => array( 'wrsfood-time-deli wrsf-mn-timesl form-row-wide' ),
				'label'       => $text_timedel,
				'placeholder' => '',
				'options'     => array( $menutime => $name ),
				'default'     => '',
			),
			$checkout->get_value( 'wooresfd_time_deli' )
		);
	} elseif ( 'disable' != $rq_time ) {
		$array_time = array();
		$deli_time  = array();
		$array_time = woo_restaurant_get_option( 'woo_restaurant_ck_times', 'woo_restaurant_advanced_options' );
		$adv_timesl = woo_restaurant_get_option( 'woores_adv_timedeli', 'woo_restaurant_adv_timesl_options' );

		$_ftimesl = '';

		$method = WC()->session->get( '_user_order_method' );
		$method = '' != $method ? $method : 'delivery';
		if ( 'disable' == $rq_date ) {
			$date_deli = strtotime( gmdate( 'Y-m-d' ) );
		} else {
			$date_deli = '';}

		if ( '' != $date_deli && is_array( $adv_timesl ) && ! empty( $adv_timesl ) ) {
			$day_ofd  = gmdate( 'D', $date_deli );
			$user_log = WC()->session->get( '_user_deli_log' );
			foreach ( $adv_timesl as $it_timesl ) {
				$tsl_log = isset( $it_timesl['times_loc'] ) ? $it_timesl['times_loc'] : '';
				if ( isset( $it_timesl[ 'repeat_' . $day_ofd ] ) && 'on' == $it_timesl[ 'repeat_' . $day_ofd ] &&
					( ! isset( $it_timesl['deli_method'] )
						|| ( isset( $it_timesl['deli_method'] ) && '' == $it_timesl['deli_method'] )
						|| ( isset( $it_timesl['deli_method'] ) && $it_timesl['deli_method'] == $method )
					) && ( '' == $tsl_log || is_array( $tsl_log ) && in_array( $user_log, $tsl_log ) ) ) {
					$_ftimesl = isset( $it_timesl['woores_deli_time'] ) && is_array( $it_timesl['woores_deli_time'] ) ? $it_timesl['woores_deli_time'] : '';
					break;
				}
			}
		}
		$n_dl_time = '' != $_ftimesl ? $_ftimesl : woo_restaurant_get_option( 'woores_deli_time', 'woo_restaurant_advanced_options' );

		$disable_sl = woo_restaurant_get_option( 'woo_restaurant_disable_tslot', 'woo_restaurant_advanced_options' );

		if ( 'yes' == $disable_sl && is_array( $n_dl_time ) ) {
			foreach ( $n_dl_time as $key => $it_dl_time ) {
				if ( isset( $it_dl_time['disable-slot'] ) && '1' == $it_dl_time['disable-slot'] ) {
					unset( $n_dl_time[ $key ] );
				}
			}
		}
		if ( ! empty( $n_dl_time ) ) {
			$array_time = $n_dl_time;
		}
		if ( empty( $array_time ) ) {
			woocommerce_form_field(
				'wooresfd_time_deli',
				array(
					'type'        => 'text',
					'required'    => 'no' == $rq_time ? false : true,
					'class'       => array( 'wrsfood-time-deli form-row-wide' ),
					'label'       => $text_timedel,
					'placeholder' => '',
				),
				$checkout->get_value( 'wooresfd_time_deli' )
			);
		} else {
			if ( 'no' == $rq_time ) {
				$deli_time[] = '';
			}
			if ( ! empty( $n_dl_time ) ) {
				foreach ( $array_time as $time_option ) {
					$r_time = '';
					if ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] && isset( $time_option['end-time'] ) && '' != $time_option['end-time'] ) {
						$r_time = $time_option['start-time'] . ' - ' . $time_option['end-time'];
					} elseif ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] ) {
						$r_time = $time_option['start-time'];
					}
					$name               = isset( $time_option['name-ts'] ) && '' != $time_option['name-ts'] ? $time_option['name-ts'] : $r_time;
					$deli_time[ $name ] = $name;
				}
			} else {
				foreach ( $array_time as $time_option ) {
					$deli_time[ $time_option ] = $time_option;
				}
			}
			$time_attr              = array();
			$time_attr['data-time'] = json_encode( $n_dl_time );
			if ( '' != $date_before && is_numeric( str_replace( 'm', '', $date_before ) ) ) {
				$cure_time = strtotime( 'now' );
				if ( '' != $gmt_offset ) {
					$cure_time = $cure_time + ( $gmt_offset * 3600 );
				}
				$cure_time                = $cure_time + str_replace( 'm', '', $date_before ) * 60;
				$time_attr['data-crtime'] = $cure_time;
				$time_attr['data-date']   = strtotime( gmdate( 'Y-m-d', $cure_time ) );
			}
			woocommerce_form_field(
				'wooresfd_time_deli',
				array(
					'type'              => 'select',
					'required'          => 'no' == $rq_time ? false : true,
					'class'             => array( 'wrsfood-time-deli form-row-wide' ),
					'label'             => $text_timedel,
					'placeholder'       => '',
					'options'           => $deli_time,
					'default'           => '',
					'custom_attributes' => $time_attr,
				),
				$checkout->get_value( 'wooresfd_time_deli' )
			);
		}
	}

	echo '</div>';
}

add_action( 'woocommerce_checkout_process', 'woore_verify_date_deli_field' );
/**
 * Process the checkout.
 */
function woore_verify_date_deli_field() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'verify-date' ) ) {
			return;
		}
	}

	// Check if set, if its not set add an error.
	$rq_date    = woo_restaurant_get_option( 'woo_restaurant_ck_date', 'woo_restaurant_advanced_options' );
	$rq_time    = woo_restaurant_get_option( 'woo_restaurant_ck_time', 'woo_restaurant_advanced_options' );
	$loca_field = woo_restaurant_get_option( 'woo_restaurant_ck_loca', 'woo_restaurant_advanced_options' );
	$loc_sl     = isset( $_POST['woo_restaurant_ck_loca'] ) ? wp_kses_post( wp_unslash( $_POST['woo_restaurant_ck_loca'] ) ) : '';
	// check if do not apply field in special product.
	if ( 'disable' != $rq_date || 'disable' != $rq_time ) {
		$check_ex = woore_if_check_product_notin_shipping();
		if ( false == $check_ex ) {
			return;
		}
	} elseif ( 'disable' == $rq_date && 'disable' == $rq_time ) {
		// loc check required.
		if ( 'req' == $loca_field && '' == $loc_sl ) {
			wc_add_notice( esc_html__( 'Please select location you want to order', 'woorestaurant' ), 'error' );
		}
		return;
	}
	$text_datedel = woore_date_time_text( 'date' );
	$text_timedel = woore_date_time_text( 'time' );

	$date_deli = isset( $_POST['wooresfd_date_deli'] ) ? wp_kses_post( wp_unslash( $_POST['wooresfd_date_deli'] ) ) : '';
	if ( 'no' != $rq_date && 'disable' != $rq_date ) {
		if ( '' == $date_deli ) {
			/* translators: %s is replaced with the text date */
			wc_add_notice( sprintf( esc_html__( 'Please select %s', 'woorestaurant' ), $text_datedel ), 'error' );
		}
	}
	$_date_type  = woo_restaurant_get_option( 'woo_restaurant_dd_display', 'woo_restaurant_advanced_options' );
	$foodby_date = woo_restaurant_get_option( 'woo_restaurant_foodby_date' );
	if ( 'picker' == $_date_type && '' != $date_deli && 'yes' != $foodby_date ) {
		$date_deli = strtotime( $date_deli );
	}
	if ( 'disable' == $rq_date ) {
		$date_deli = strtotime( gmdate( 'Y-m-d' ) );
	}
	if ( '' != $date_deli ) {
		$enb_date = woo_restaurant_get_option( 'woo_restaurant_ck_enadate', 'woo_restaurant_advanced_options' );
		if ( is_array( $enb_date ) && count( $enb_date ) > 0 ) {
			if ( ! in_array( $date_deli, $enb_date ) ) {
				wc_add_notice( esc_html__( 'Error, please refresh page and try again', 'woorestaurant' ), 'error' );
			}
		}
		$dis_date   = woo_restaurant_get_option( 'woo_restaurant_ck_disdate', 'woo_restaurant_advanced_options' );
		$dis_day    = woo_restaurant_get_option( 'woo_restaurant_ck_disday', 'woo_restaurant_advanced_options' );
		$day_ofdate = gmdate( 'N', $date_deli );
		if ( ( is_array( $dis_date ) && in_array( $date_deli, $dis_date ) ) || ( ! empty( $dis_day ) && in_array( $day_ofdate, $dis_day ) ) ) {
			wc_add_notice( esc_html__( 'Error, please refresh page and try again', 'woorestaurant' ), 'error' );
		}
	}
	$time_deli = isset( $_POST['wooresfd_time_deli'] ) ? wp_kses_post( wp_unslash( $_POST['wooresfd_time_deli'] ) ) : '';
	do_action( 'woore_verify_date_time', $_POST );
	if ( 'disable' != $rq_time ) {
		if ( '0' == $time_deli ) {
			'' == $time_deli;
		}
		if ( 'no' != $rq_time && '' == $time_deli ) {
			/* translators: %s is replaced with the text time */
			wc_add_notice( sprintf( esc_html__( 'Please select %s', 'woorestaurant' ), $text_timedel ), 'error' );
		} elseif ( 'no' == $rq_time && '0' == $time_deli ) {
			$time_deli = '';
		}
		// check max order.
		if ( '' != $time_deli ) {
			woore_check_time_delivery_status( $_POST );
		}
	} elseif ( 'no' == $rq_time && '0' == $time_deli ) {
		$time_deli = '';
	}
	// loc check required.
	if ( 'req' == $loca_field && '' == $loc_sl ) {
		wc_add_notice( esc_html__( 'Please select location you want to order', 'woorestaurant' ), 'error' );
	}
	// verify time has expired.
	$method      = WC()->session->get( '_user_order_method' );
	$method      = '' != $method ? $method : 'delivery';
	$date_before = woo_restaurant_get_option( 'woo_restaurant_ck_beforedate', 'woo_restaurant_advanced_options' );
	if ( '' != $date_before && ( '' != $date_deli || '' != $time_deli ) ) {
		$check_time_exit = false;
		$_timeck         = '';
		$menutime        = function_exists( 'woore_menuby_time_selected' ) ? woore_menuby_time_selected() : '';
		if ( '' != $time_deli && ( 'yes' != $foodby_date || ( 'yes' == $foodby_date && '' == $menutime ) ) ) {
			// advanced slots.
			$adv_timesl = woo_restaurant_get_option( 'woores_adv_timedeli', 'woo_restaurant_adv_timesl_options' );
			$_ftimesl   = '';
			$user_log   = WC()->session->get( '_user_deli_log' );
			if ( isset( $_POST['woo_restaurant_ck_loca'] ) ) {
				$user_log = wp_kses_post( wp_unslash( $_POST['woo_restaurant_ck_loca'] ) );
			}
			if ( '' != $date_deli && is_array( $adv_timesl ) && ! empty( $adv_timesl ) ) {
				$day_ofd = gmdate( 'D', $date_deli );
				foreach ( $adv_timesl as $it_timesl ) {
					$tsl_log = isset( $it_timesl['times_loc'] ) ? $it_timesl['times_loc'] : '';
					if ( isset( $it_timesl[ 'repeat_' . $day_ofd ] ) && 'on' == $it_timesl[ 'repeat_' . $day_ofd ] &&
						(
							! isset( $it_timesl['deli_method'] )
							|| ( isset( $it_timesl['deli_method'] ) && '' == $it_timesl['deli_method'] )
							|| ( isset( $it_timesl['deli_method'] ) && $it_timesl['deli_method'] == $method )
						) && ( '' == $tsl_log || is_array( $tsl_log ) && in_array( $user_log, $tsl_log ) ) ) {
						$_ftimesl = isset( $it_timesl['woores_deli_time'] ) && is_array( $it_timesl['woores_deli_time'] ) ? $it_timesl['woores_deli_time'] : '';
						break;
					}
				}
			}
			$n_dl_time  = '' != $_ftimesl ? $_ftimesl : woo_restaurant_get_option( 'woores_deli_time', 'woo_restaurant_advanced_options' );
			$disable_sl = woo_restaurant_get_option( 'woo_restaurant_disable_tslot', 'woo_restaurant_advanced_options' );
			if ( 'yes' == $disable_sl ) {
				foreach ( $n_dl_time as $key => $it_dl_time ) {
					if ( isset( $it_dl_time['disable-slot'] ) && '1' == $it_dl_time['disable-slot'] ) {
						unset( $n_dl_time[ $key ] );
					}
				}
			}
			if ( ! is_array( $n_dl_time ) || empty( $n_dl_time ) ) {
				$check_time_exit = true;
			} else {
				foreach ( $n_dl_time as $time_option ) {
					$r_time = '';
					if ( '' != $time_option['start-time'] && '' != $time_option['end-time'] ) {
						$r_time = $time_option['start-time'] . ' - ' . $time_option['end-time'];
					} elseif ( '' != $time_option['start-time'] ) {
						$r_time = $time_option['start-time'];
					}
					$name = '' != $time_option['name-ts'] ? $time_option['name-ts'] : $r_time;
					if ( $time_deli == $name ) {
						WC()->session->set( '_st_timeslot', $time_option );
						$_time_base      = apply_filters( 'woore_timebase_to_check_delivery', $time_option['start-time'], $time_option );
						$_timeck         = $_time_base;
						$check_time_exit = true;
						break;
					}
				}
			}
		} else {
			$check_time_exit = true;
		}

		if ( false == $check_time_exit ) {
			wc_add_notice( esc_html__( 'Error, please refresh page and try again', 'woorestaurant' ), 'error' );
		} elseif ( '' != $_timeck ) {
			$date_deli = '' != $date_deli ? $date_deli : strtotime( gmdate( 'Y-m-d' ) );
			$_timeck   = explode( ':', $_timeck );
			$_timeck   = $_timeck[1] * 60 + $_timeck[0] * 3600;
			$cure_time = '';
			if ( is_numeric( $date_before ) ) {
				$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( "+$date_before day" ) );

			} elseif ( is_numeric( str_replace( 'm', '', $date_before ) ) ) {
				$cure_time = strtotime( 'now' );
				$cure_time = $cure_time + str_replace( 'm', '', $date_before ) * 60;
			}
			$gmt_offset = get_option( 'gmt_offset' );
			if ( '' != $gmt_offset ) {
				$cure_time = $cure_time + ( $gmt_offset * 3600 );
			}
			if ( ( $date_deli + $_timeck ) < $cure_time ) {
				wc_add_notice( esc_html__( 'Your time you have selected has closed, please try with different date or time', 'woorestaurant' ), 'error' );
			}
		} elseif ( '' != $date_deli ) {
			$date_deli = $date_deli + 86399;
			$cure_time = '';
			if ( is_numeric( $date_before ) ) {
				$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( "+$date_before day" ) );
			} elseif ( is_numeric( str_replace( 'm', '', $date_before ) ) ) {
				$cure_time  = strtotime( 'now' );
				$cure_time  = $cure_time + str_replace( 'm', '', $date_before ) * 60;
				$gmt_offset = get_option( 'gmt_offset' );
				if ( '' != $gmt_offset ) {
					$cure_time = $cure_time + ( $gmt_offset * 3600 );
				}
			}

			if ( $date_deli < $cure_time ) {
				wc_add_notice( esc_html__( 'Your time you have selected has closed, please try with different date' ), 'error' );
			}
		}
	}
}

/**
 * Check delivery status.
 *
 * @param array $data it is the array of data.
 * @param bool  $return it is the return value.
 */
function woore_check_time_delivery_status( $data, $return = false ) {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'delivery-status' ) ) {
			return;
		}
	}

	$date_deli = isset( $data['wooresfd_date_deli'] ) ? $data['wooresfd_date_deli'] : '';
	$rq_date   = woo_restaurant_get_option( 'woo_restaurant_ck_date', 'woo_restaurant_advanced_options' );
	if ( 'disable' == $rq_date ) {
		$date_deli = strtotime( gmdate( 'Y-m-d' ) );
	}
	$_date_type = woo_restaurant_get_option( 'woo_restaurant_dd_display', 'woo_restaurant_advanced_options' );
	if ( 'picker' == $_date_type && isset( $data['wooresfd_date_deli'] ) && ! is_numeric( $data['wooresfd_date_deli'] ) ) {
		$date_deli = strtotime( $data['wooresfd_date_deli'] );
		if ( '' == $date_deli ) {
			return;
		}
	}
	// advanced slots.
	$method     = WC()->session->get( '_user_order_method' );
	$method     = '' != $method ? $method : 'delivery';
	$adv_timesl = woo_restaurant_get_option( 'woores_adv_timedeli', 'woo_restaurant_adv_timesl_options' );
	$_ftimesl   = '';
	$tsl_method = '';
	if ( '' != $date_deli && is_array( $adv_timesl ) && ! empty( $adv_timesl ) ) {
		$day_ofd  = gmdate( 'D', $date_deli );
		$user_log = WC()->session->get( '_user_deli_log' );
		if ( isset( $_POST['woo_restaurant_ck_loca'] ) ) {
			$user_log = wp_kses_post( wp_unslash( $_POST['woo_restaurant_ck_loca'] ) );
		} elseif ( isset( $data['woo_restaurant_ck_loca'] ) ) {
			$user_log = $data['woo_restaurant_ck_loca'];
		}
		foreach ( $adv_timesl as $it_timesl ) {
			$tsl_log = isset( $it_timesl['times_loc'] ) ? $it_timesl['times_loc'] : '';
			if ( isset( $it_timesl[ 'repeat_' . $day_ofd ] ) && 'on' == $it_timesl[ 'repeat_' . $day_ofd ] &&
				(
					! isset( $it_timesl['deli_method'] )
					|| ( isset( $it_timesl['deli_method'] ) && '' == $it_timesl['deli_method'] )
					|| ( isset( $it_timesl['deli_method'] ) && $it_timesl['deli_method'] == $method )
				) && ( '' == $tsl_log || is_array( $tsl_log ) && in_array( $user_log, $tsl_log ) ) ) {
				$tsl_method = isset( $it_timesl['deli_method'] ) ? $it_timesl['deli_method'] : '';
				$_ftimesl   = isset( $it_timesl['woores_deli_time'] ) && is_array( $it_timesl['woores_deli_time'] ) ? $it_timesl['woores_deli_time'] : '';
				break;
			}
		}
	}
	$_time      = isset( $_ftimesl ) && is_array( $_ftimesl ) ? $_ftimesl : woo_restaurant_get_option( 'woores_deli_time', 'woo_restaurant_advanced_options' );
	$disable_sl = woo_restaurant_get_option( 'woo_restaurant_disable_tslot', 'woo_restaurant_advanced_options' );
	if ( 'yes' == $disable_sl ) {
		foreach ( $n_dl_time as $key => $it_dl_time ) {
			if ( isset( $it_dl_time['disable-slot'] ) && '1' == $it_dl_time['disable-slot'] ) {
				unset( $n_dl_time[ $key ] );
			}
		}
	}
	if ( ! empty( $_time ) && '' != $date_deli ) {
		foreach ( $_time as $key => $value ) {
			if ( ! isset( $value['end-time'] ) ) {
				$value['end-time'] = '';
			}
			$name = isset( $value['name-ts'] ) && '' != $value['name-ts'] ? $value['name-ts'] : ( $value['start-time'] . ' - ' . $value['end-time'] );
			if ( isset( $value['max-odts'] ) && is_numeric( $value['max-odts'] ) && $name == $data['wooresfd_time_deli'] ) {
				$locat    = isset( $data['woo_restaurant_ck_loca'] ) ? $data['woo_restaurant_ck_loca'] : '';
				$total_rs = woore_get_number_order_timeslot( $date_deli, $data['wooresfd_time_deli'], $locat, $method, $tsl_method, $_time );
				if ( $total_rs >= $value['max-odts'] ) {
					$text_datedel = woore_date_time_text( 'date' );
					$text_timedel = woore_date_time_text( 'time' );
					/* translators: %1$s is replaced with the text time, %2$s is replaced with the text date */
					$msg = sprintf( esc_html__( 'Sorry, the %1$s you have selected has full order, please try again with different  %2$s or time', 'woorestaurant' ), $text_timedel, $text_datedel );
					if ( isset( $return ) && true == $return ) {
						return $msg;
					} else {
						wc_add_notice( $msg, 'error' );
					}
				}
			}
		}
	}
}

/**
 * Get number order timeslot.
 *
 * @param string $date it is the date.
 * @param string $time it is the time.
 * @param string $loc it is the location.
 * @param string $method these are methods.
 * @param string $tsl_method these are tsl methods.
 * @param string $timeslots these are timeslots.
 */
function woore_get_number_order_timeslot( $date, $time, $loc, $method, $tsl_method, $timeslots ) {
	$status = wc_get_order_statuses();
	if ( isset( $status['wc-failed'] ) ) {
		unset( $status['wc-failed'] ); }
	if ( isset( $status['wc-refunded'] ) ) {
		unset( $status['wc-refunded'] ); }
	if ( isset( $status['wc-cancelled'] ) ) {
		unset( $status['wc-cancelled'] ); }
	$status        = apply_filters( 'woore_query_od_status', $status );
	$args          = array(
		'posts_per_page' => 1,
		'post_type'      => 'shop_order',
		'post_status'    => array_keys( $status ),
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => 'wooresfd_time_deli',
				'value'   => $time,
				'compare' => '=',
			),
			array(
				'key'     => 'wooresfd_date_deli_unix',
				'value'   => $date,
				'type'    => 'numeric',
				'compare' => '=',
			),
		),
	);
	$enable_adv_tl = woo_restaurant_get_option( 'woo_restaurant_adv_loca', 'woo_restaurant_advanced_options' );
	if ( 'enable' == $enable_adv_tl && '' != $loc ) {
		$args['meta_query'][] = array(
			'key'     => 'woorestaurant_location',
			'value'   => $loc,
			'compare' => '=',
		);
	}
	if ( '' != $tsl_method ) {
		$args['meta_query'][] = array(
			'key'     => 'wooresfd_order_method',
			'value'   => $method,
			'compare' => '=',
		);
	} elseif ( 'delivery' == $tsl_method ) {
		$args['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'     => 'wooresfd_order_method',
				'value'   => $method,
				'compare' => '=',
			),
			array(
				'key'     => 'wooresfd_order_method',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'wooresfd_order_method',
				'value'   => '',
				'compare' => '=',
			),
		);
	}
	$args     = apply_filters( 'woore_limit_order_qr_args', $args, $timeslots );
	$my_query = new WP_Query( $args );
	$total_rs = $my_query->found_posts;
	return $total_rs;
}

add_action( 'woocommerce_checkout_update_order_meta', 'woore_save_date_deli_field' );
/**
 * Update the order meta with field value.
 *
 * @param int $order_id it is the order id.
 */
function woore_save_date_deli_field( $order_id ) {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'variation-setting' ) ) {
			return;
		}
	}

	if ( ! empty( $_POST['wooresfd_date_deli'] ) ) {
		$_date_type  = woo_restaurant_get_option( 'woo_restaurant_dd_display', 'woo_restaurant_advanced_options' );
		$foodby_date = woo_restaurant_get_option( 'woo_restaurant_foodby_date' );
		if ( 'picker' != $_date_type || 'yes' == $foodby_date ) {
			update_post_meta( $order_id, 'wooresfd_date_deli', sanitize_text_field( date_i18n( get_option( 'date_format' ), wp_kses_post( wp_unslash( $_POST['wooresfd_date_deli'] ) ) ) ) );
			$deli_unix = sanitize_text_field( wp_kses_post( wp_unslash( $_POST['wooresfd_date_deli'] ) ) );
		} else {
			$date_dl = date_i18n( get_option( 'date_format' ), strtotime( wp_kses_post( wp_unslash( $_POST['wooresfd_date_deli'] ) ) ) );
			update_post_meta( $order_id, 'wooresfd_date_deli', sanitize_text_field( $date_dl ) );
			$deli_unix = strtotime( wp_kses_post( wp_unslash( $_POST['wooresfd_date_deli'] ) ) );
		}
	} else {
		$deli_unix = strtotime( gmdate( 'Y-m-d' ) );
	}
	update_post_meta( $order_id, 'wooresfd_date_deli_unix', $deli_unix );
	if ( isset( WC()->session ) ) {
		$timeslot = WC()->session->get( '_st_timeslot' );
		WC()->session->set( '_st_timeslot', '' );
		if ( isset( $timeslot['start-time'] ) && '' != $timeslot['start-time'] ) {
			$_timeck   = explode( ':', $timeslot['start-time'] );
			$_timeck   = $_timeck[1] * 60 + $_timeck[0] * 3600;
			$deli_unix = $deli_unix + $_timeck;
			update_post_meta( $order_id, 'wooresfd_datetime_deli_unix', $deli_unix );
		} else {
			update_post_meta( $order_id, 'wooresfd_datetime_deli_unix', $deli_unix );
		}
	} else {
		update_post_meta( $order_id, 'wooresfd_datetime_deli_unix', $deli_unix );
	}
	if ( ! empty( $_POST['wooresfd_time_deli'] ) ) {
		update_post_meta( $order_id, 'wooresfd_time_deli', sanitize_text_field( wp_kses_post( wp_unslash( $_POST['wooresfd_time_deli'] ) ) ) );
	}
	if ( ! empty( $_POST['woo_restaurant_ck_loca'] ) ) {
		update_post_meta( $order_id, 'woorestaurant_location', sanitize_text_field( wp_kses_post( wp_unslash( $_POST['woo_restaurant_ck_loca'] ) ) ) );
	}
	if ( ! empty( $_POST['wooresfd_person_dinein'] ) ) {
		update_post_meta( $order_id, 'wooresfd_person_dinein', sanitize_text_field( wp_kses_post( wp_unslash( $_POST['wooresfd_person_dinein'] ) ) ) );
	}
}


add_action( 'woocommerce_order_details_after_order_table_items', 'woore_display_date_deli_fe', 10, 1 );
/**
 * Display field value on thank you page.
 *
 * @param object $order it is the order object.
 */
function woore_display_date_deli_fe( $order ) {
	$text_datedel = woore_date_time_text( 'date', $order );
	$text_timedel = woore_date_time_text( 'time', $order );

	$order_method = get_post_meta( $order->get_id(), 'wooresfd_order_method', true );
	$order_method = 'takeaway' == $order_method ? esc_html__( 'Takeaway', 'woorestaurant' ) : ( 'dinein' == $order_method ? esc_html__( 'Dine-in', 'woorestaurant' ) : esc_html__( 'Delivery', 'woorestaurant' ) );
	if ( '' != get_post_meta( $order->get_id(), 'wooresfd_order_method', true ) ) {
		echo '
    <tr>
    	<th>' . esc_html__( 'Order method', 'woorestaurant' ) . '</th>
    	<td> ' . esc_attr( $order_method ) . '</td>
    </tr>';
	}
	if ( '' != get_post_meta( $order->get_id(), 'wooresfd_person_dinein', true ) ) {
		echo '
	    <tr>
	    	<th>' . esc_html__( 'Number of person', 'woorestaurant' ) . '</th>
	    	<td> ' . esc_attr( get_post_meta( $order->get_id(), 'wooresfd_person_dinein', true ) ) . '</td>
	    </tr>';
	}
	if ( '' != get_post_meta( $order->get_id(), 'wooresfd_date_deli', true ) ) {
		echo '
	    <tr>
	    	<th>' . esc_attr( $text_datedel ) . '</th>
	    	<td> ' . esc_attr( get_post_meta( $order->get_id(), 'wooresfd_date_deli', true ) ) . '</td>
	    </tr>';
	}
	if ( '' != get_post_meta( $order->get_id(), 'wooresfd_time_deli', true ) ) {
		echo '
	    <tr>
	    	<th>' . esc_attr( $text_timedel ) . '</th>
	    	<td> ' . esc_attr( get_post_meta( $order->get_id(), 'wooresfd_time_deli', true ) ) . '</td>
	    </tr>';
	}
	$log_name = get_term_by( 'slug', get_post_meta( $order->get_id(), 'woorestaurant_location', true ), 'woorestaurant_loc' );
	if ( isset( $log_name->name ) && $log_name->name ) {
		echo '
	    <tr>
	    	<th>' . esc_html__( 'Location', 'woorestaurant' ) . '</th>
	    	<td> ' . esc_attr( $log_name->name ) . '</td>
	    </tr>';
	}
}

add_action( 'woocommerce_email_after_order_table', 'woore_display_date_deli_em', 10, 1 );
/**
 * Display field value on email.
 *
 * @param object $order it is the object of the order.
 */
function woore_display_date_deli_em( $order ) {
	$text_align   = is_rtl() ? 'right' : 'left';
	$dv_date      = get_post_meta( $order->get_id(), 'wooresfd_date_deli', true );
	$dv_time      = get_post_meta( $order->get_id(), 'wooresfd_time_deli', true );
	$loc_ar       = get_post_meta( $order->get_id(), 'woorestaurant_location', true );
	$nbperson     = get_post_meta( $order->get_id(), 'wooresfd_person_dinein', true );
	$log_name     = get_term_by( 'slug', $loc_ar, 'woorestaurant_loc' );
	$order_method = get_post_meta( $order->get_id(), 'wooresfd_order_method', true );
	if ( '' == $order_method && '' == $dv_date && '' == $dv_time && ( ! isset( $log_name->name ) || '' == $log_name->name ) ) {
		return;
	}
	$text_datedel = woore_date_time_text( 'date', $order );
	$text_timedel = woore_date_time_text( 'time', $order );
	?>
	<div style="margin-bottom: 40px;">
		<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
			<?php
			$order_method = 'takeaway' == $order_method ? esc_html__( 'Takeaway', 'woorestaurant' ) : ( 'dinein' == $order_method ? esc_html__( 'Dine-in', 'woorestaurant' ) : ( 'delivery' == $order_method ? esc_html__( 'Delivery', 'woorestaurant' ) : '' ) );
			if ( '' != $order_method ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html__( 'Order method', 'woorestaurant' ); ?></th>
					<td class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
						<?php echo esc_attr( $order_method ); ?>
					</td>
				</tr>
				<?php
			}
			if ( '' != $nbperson ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html__( 'Number of person', 'woorestaurant' ); ?></th>
					<td class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_attr( $nbperson ); ?></td>
				</tr>
				<?php
			}
			if ( '' != $dv_date ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_attr( $text_datedel ); ?></th>
					<td class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_attr( $dv_date ); ?></td>
				</tr>
				<?php
			}
			if ( '' != $dv_time ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_attr( $text_timedel ); ?></th>
					<td class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_attr( $dv_time ); ?></td>
				</tr>
				<?php
			}

			if ( isset( $log_name->name ) && '' != $log_name->name ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html__( 'Location', 'woorestaurant' ); ?></th>
					<td class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_attr( $log_name->name ); ?></td>
				</tr>
			<?php } ?>
		</table>
	</div>
	<?php
}

add_action( 'woocommerce_checkout_process', 'woore_minimum_order_amount' );
add_action( 'woocommerce_before_cart', 'woore_minimum_order_amount' );
/**
 * Add minimum order amount.
 */
function woore_minimum_order_amount() {
	// check open closing time.
	$al_products = woo_restaurant_get_option( 'woo_restaurant_ign_op', 'woo_restaurant_advanced_options' );
	$enable_time = woo_restaurant_get_option( 'woo_restaurant_open_close', 'woo_restaurant_advanced_options' );
	$i           = 0;
	$j           = 0;
	$check_it    = false;
	if ( 'enable' == $enable_time && '' != $al_products ) {
		$al_products = explode( ',', $al_products );
		$msg_it      = '';
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			++$i;
			$id_cr = $cart_item['product_id'];
			if ( ! in_array( $id_cr, $al_products ) ) {
				++$j;
				/* translators: %s is replaced with the product id */
				$msg_it .= sprintf( esc_html__( 'The food "%s" ordering is now closed', 'woorestaurant' ), get_the_title( $id_cr ) );
			}
		}
		if ( ( $i != $j ) && $j > 0 ) {
			$check_it = true;
			if ( ! woore_check_open_close_time() ) {
				if ( is_cart() ) {
					wc_print_notice( $msg_it, 'error' );
				} else {
					wc_add_notice( $msg_it, 'error' );
				}
			}
		} elseif ( 0 == $j ) {
			$check_it = true;
		}
	}
	if ( ! woore_check_open_close_time() && true != $check_it ) {
		if ( is_cart() ) {
			wc_print_notice( wooresfd_open_closing_message( true ), 'error' );
		} else {
			wc_add_notice( wooresfd_open_closing_message( true ), 'error' );
		}
	} else {
		$check_ex = woore_if_check_product_notin_shipping();
		if ( false == $check_ex ) {
			return;
		}
		// Set this variable to specify a minimum order value.
		$minimum = woo_restaurant_get_option( 'woo_restaurant_ck_mini_amount', 'woo_restaurant_advanced_options' );
		// min by log.
		$loc_selected = woore_get_loc_selected();
		if ( '' != $loc_selected ) {
			$minimum_log = get_term_meta( $loc_selected, 'wrsp_loc_min_amount', true );
			if ( '' != $minimum_log && is_numeric( $minimum_log ) ) {
				$minimum = $minimum_log;
			}
		}

		$total = apply_filters( 'woore_total_cart_price', WC()->cart->get_subtotal() );
		$coup  = WC()->cart->get_applied_coupons();
		if ( is_array( $coup ) && count( $coup ) > 0 && is_numeric( $minimum ) && $minimum > 0 ) {
			foreach ( $coup as $itcp ) {
				$get_details = ( new WC_Coupon( $itcp ) );
				$discount    = $get_details->amount;
				if ( $discount > 0 ) {
					$minimum = $minimum - $discount;
				}
			}
		}
		$minimum = apply_filters( 'woore_minimum_amount_required', $minimum );
		if ( '' != $minimum && is_numeric( $minimum ) && $total < $minimum ) {
			if ( is_cart() ) {
				wc_print_notice(
					/* translators: %1$s is replaced with the total price, %2$s is replaced with the minimum price */
					sprintf( esc_html__( 'Your current order total is %1$s - you must have an order with a minimum of %2$s to place your order', 'woorestaurant' ), wc_price( $total ), wc_price( $minimum ) ),
					'error'
				);
			} else {
				wc_add_notice(
					/* translators: %1$s is replaced with the total price, %2$s is replaced with the minimum price */
					sprintf( esc_html__( 'Your current order total is %1$s - you must have an order with a minimum of %2$s to place your order', 'woorestaurant' ), wc_price( $total ), wc_price( $minimum ) ),
					'error'
				);
			}
		}
	}
}

add_action( 'woocommerce_widget_shopping_cart_before_buttons', 'woore_minimum_amount_sidecart', 999 );
/**
 * It is the minimum amount sidecart.
 */
function woore_minimum_amount_sidecart() {
	// check open closing time.
	$al_products = woo_restaurant_get_option( 'woo_restaurant_ign_op', 'woo_restaurant_advanced_options' );
	$enable_time = woo_restaurant_get_option( 'woo_restaurant_open_close', 'woo_restaurant_advanced_options' );
	$i           = 0;
	$j           = 0;
	$check_it    = false;
	if ( 'enable' == $enable_time && '' != $al_products ) {
		$al_products = explode( ',', $al_products );
		$msg_it      = '';
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			++$i;
			$id_cr = $cart_item['product_id'];
			if ( ! in_array( $id_cr, $al_products ) ) {
				++$j;
				/* translators: %s is replaced with the product id */
				$msg_it .= '<p class="wrsf-mini-amount wrsf-opcs-warning wrsf-warning">' . sprintf( esc_html__( 'The food "%s"  ordering is now closed', 'woorestaurant' ), get_the_title( $id_cr ) ) . '</p>';
			}
		}
		if ( ( $i != $j ) && $j > 0 ) {
			echo esc_attr( $msg_it );
			$check_it = true;
		} elseif ( 0 == $j ) {
			$check_it = true;
		}
	}
	if ( ! woore_check_open_close_time() && true != $check_it ) {
		echo '<p class="wrsf-mini-amount wrsf-opcs-warning wrsf-warning">' . esc_attr( wooresfd_open_closing_message( true ) ) . '</p>';
	} else {
		$minimum = woo_restaurant_get_option( 'woo_restaurant_ck_mini_amount', 'woo_restaurant_advanced_options' );
		// min by log.
		$loc_selected = WC()->session->get( 'wr_userloc' );
		$user_log     = '';
		if ( '' == $loc_selected ) {
			$user_log     = WC()->session->get( '_user_deli_log' );
			$loc_selected = $user_log;
		}
		if ( '' != $loc_selected ) {
			$term = get_term_by( 'slug', $loc_selected, 'woorestaurant_loc' );
			if ( isset( $term->term_id ) ) {
				$minimum_log = get_term_meta( $term->term_id, 'wrsp_loc_min_amount', true );
				if ( '' != $minimum_log && is_numeric( $minimum_log ) ) {
					$minimum = $minimum_log;
				}
			}
		}

		$total = apply_filters( 'woore_total_cart_price', WC()->cart->get_subtotal() );
		$coup  = WC()->cart->get_applied_coupons();
		if ( is_array( $coup ) && count( $coup ) > 0 && is_numeric( $minimum ) && $minimum > 0 ) {
			foreach ( $coup as $itcp ) {
				$get_details = ( new WC_Coupon( $itcp ) );
				$discount    = $get_details->amount;
				if ( $discount > 0 ) {
					$minimum = $minimum - $discount;
				}
			}
		}
		$minimum = apply_filters( 'woore_minimum_amount_required', $minimum );
		if ( '' != $minimum && is_numeric( $minimum ) && $total < $minimum ) {
			/* translators: %1$s is replaced with the total price, %2$s is replaced with the minimum price */
			echo '<p class="wrsf-mini-amount wrsf-min-required wrsf-warning">' . sprintf( esc_html__( 'Your current order total is %1$s - you must have an order with a minimum of %2$s to place your order', 'woorestaurant' ), esc_attr( wc_price( $total ) ), esc_attr( wc_price( $minimum ) ) ) . '</p>';
		}
	}
}

add_action( 'wp_ajax_woore_time_delivery_status', 'ajax_woore_time_delivery_status' );
add_action( 'wp_ajax_nopriv_woore_time_delivery_status', 'ajax_woore_time_delivery_status' );
/**
 * Ajax check delivery time available or not.
 */
function ajax_woore_time_delivery_status() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'variation-setting' ) ) {
			return;
		}
	}

	$data                           = array();
	$data['wooresfd_date_deli']     = isset( $_POST['date'] ) && '' != $_POST['date'] && is_numeric( $_POST['date'] ) ? wp_kses_post( wp_unslash( $_POST['date'] ) ) : strtotime( gmdate( 'Y-m-d' ) );
	$data['wooresfd_time_deli']     = isset( $_POST['time'] ) ? wp_kses_post( wp_unslash( $_POST['time'] ) ) : '';
	$data['woo_restaurant_ck_loca'] = isset( $_POST['loc'] ) ? wp_kses_post( wp_unslash( $_POST['loc'] ) ) : '';
	$html_timesl                    = '';
	$html                           = woore_check_time_delivery_status( $data, true );
	if ( '' != $html ) {
		$html = '<p class="wrsf-time-stt">' . $html . '</p>';
	}
	$update_your_order = false;
	$method            = WC()->session->get( '_user_order_method' );
	$method            = '' != $method ? $method : 'delivery';
	$ship_bytime       = woo_restaurant_get_option( 'woo_restaurant_shipfee_bytime', 'woo_restaurant_shpping_options' );
	if ( 'delivery' == $method && 'yes' == $ship_bytime ) {
		$update_your_order = true;
	}
	$output = array(
		'html_content'  => $html,
		'html_timesl'   => $html_timesl,
		'refresh_order' => $update_your_order,
	);
	echo esc_attr( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}

add_action( 'wp_ajax_woore_time_delivery_slots', 'ajax_woore_time_delivery_slots' );
add_action( 'wp_ajax_nopriv_woore_time_delivery_slots', 'ajax_woore_time_delivery_slots' );
/**
 * It is the time delivery slot.
 */
function ajax_woore_time_delivery_slots() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'variation-setting' ) ) {
			return;
		}
	}

	$data                       = array();
	$data['wooresfd_date_deli'] = isset( $_POST['date'] ) && '' != $_POST['date'] && is_numeric( $_POST['date'] ) ? wp_kses_post( wp_unslash( $_POST['date'] ) ) : strtotime( gmdate( 'Y-m-d' ) );
	$adv_timesl                 = woo_restaurant_get_option( 'woores_adv_timedeli', 'woo_restaurant_adv_timesl_options' );
	$html_timesl                = '';
	$def_timesl                 = woo_restaurant_get_option( 'woores_deli_time', 'woo_restaurant_advanced_options' );

	$cure_time   = strtotime( 'now' );
	$date_before = woo_restaurant_get_option( 'woo_restaurant_ck_beforedate', 'woo_restaurant_advanced_options' );
	if ( is_numeric( $date_before ) ) {
		$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( "+$date_before day" ) );

	} elseif ( is_numeric( str_replace( 'm', '', $date_before ) ) ) {
		$cure_time = $cure_time + str_replace( 'm', '', $date_before ) * 60;
	}
	$gmt_offset = get_option( 'gmt_offset' );
	if ( '' != $gmt_offset ) {
		$cure_time = $cure_time + ( $gmt_offset * 3600 );
	}
	$disable_sl = woo_restaurant_get_option( 'woo_restaurant_disable_tslot', 'woo_restaurant_advanced_options' );
	$user_log   = isset( $_POST['loc'] ) ? wp_kses_post( wp_unslash( $_POST['loc'] ) ) : '';
	$method     = WC()->session->get( '_user_order_method' );
	$method     = '' != $method ? $method : 'delivery';
	if ( is_array( $def_timesl ) && ! empty( $def_timesl ) ) {
		$html_timesl .= '<select name="wooresfd_time_deli" id="wooresfd_time_deli" class="select " data-time="' . json_encode( $def_timesl ) . '" data-crtime="' . esc_attr( $cure_time ) . '" data-date="' . strtotime( gmdate( 'Y-m-d', $cure_time ) ) . '" data-placeholder="">';
		foreach ( $def_timesl as $time_option ) {
			if ( 'yes' == $disable_sl && isset( $time_option['disable-slot'] ) && '1' == $time_option['disable-slot'] ) {
				$disable_sl;
			} else {
				$r_time = '';
				if ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] && '' != $time_option['end-time'] ) {
					$r_time = $time_option['start-time'] . ' - ' . $time_option['end-time'];
				} elseif ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] ) {
					$r_time = $time_option['start-time'];
				}
				$name       = isset( $time_option['name-ts'] ) && '' != $time_option['name-ts'] ? $time_option['name-ts'] : $r_time;
				$disable    = '';
				$_time_base = apply_filters( 'woore_timebase_to_check_delivery', $time_option['start-time'], $time_option );
				if ( '' != $_time_base ) {
					$_timeck = $_time_base;
					$_timeck = explode( ':', $_timeck );
					$_timeck = $_timeck[1] * 60 + $_timeck[0] * 3600;
					if ( ( $data['wooresfd_date_deli'] + $_timeck ) < $cure_time ) {
						$disable = 'disabled="disabled"';
					}
				}
				$maxsl = isset( $time_option['max-odts'] ) && is_numeric( $time_option['max-odts'] ) ? $time_option['max-odts'] : '';
				if ( 'disabled="disabled"' != $disable && '' != $maxsl ) {
					$total_rs = woore_get_number_order_timeslot( $data['wooresfd_date_deli'], $name, $user_log, $method, '', $time_option );
					if ( $total_rs >= $maxsl ) {
						$disable = 'disabled="disabled"';
					}
				}
				$html_timesl .= '<option value="' . esc_attr( $name ) . '" ' . $disable . '>' . $name . '</option>';
			}
		}
		$html_timesl .= '</select>';
	} else {
		$html_timesl = '<input type="text" class="input-text " name="wooresfd_time_deli" id="wooresfd_time_deli" placeholder="" value="">';
	}
	if ( is_array( $adv_timesl ) && ! empty( $adv_timesl ) ) {
		$day_ofd = gmdate( 'D', $data['wooresfd_date_deli'] );

		foreach ( $adv_timesl as $it_timesl ) {
			$tsl_log = isset( $it_timesl['times_loc'] ) ? $it_timesl['times_loc'] : '';
			if ( isset( $it_timesl[ 'repeat_' . $day_ofd ] ) && 'on' == $it_timesl[ 'repeat_' . $day_ofd ] &&
				(
					! isset( $it_timesl['deli_method'] )
					|| ( isset( $it_timesl['deli_method'] ) && '' == $it_timesl['deli_method'] )
					|| ( isset( $it_timesl['deli_method'] ) && $it_timesl['deli_method'] == $method )
				) && ( '' == $tsl_log || is_array( $tsl_log ) && ! empty( $tsl_log ) && in_array( $user_log, $tsl_log ) ) ) {
				$tsl_method   = isset( $it_timesl['deli_method'] ) ? $it_timesl['deli_method'] : '';
				$html_timesl  = '';
				$html_timesl .= '<select name="wooresfd_time_deli" id="wooresfd_time_deli" class="select " data-time="' . json_encode( $def_timesl ) . '" data-crtime="' . esc_attr( $cure_time ) . '" data-date="' . strtotime( gmdate( 'Y-m-d', $cure_time ) ) . '" data-placeholder="">';
				if ( isset( $it_timesl['woores_deli_time'] ) && is_array( $it_timesl['woores_deli_time'] ) ) {
					$def_timesl = $it_timesl['woores_deli_time'];
					foreach ( $it_timesl['woores_deli_time'] as $time_option ) {
						if ( 'yes' == $disable_sl && isset( $time_option['disable-slot'] ) && '1' == $time_option['disable-slot'] ) {
							$disable_sl;
						} else {
							$r_time = '';
							if ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] && isset( $time_option['end-time'] ) && '' != $time_option['end-time'] ) {
								$r_time = $time_option['start-time'] . ' - ' . $time_option['end-time'];
							} elseif ( isset( $time_option['start-time'] ) && '' != $time_option['start-time'] ) {
								$r_time = $time_option['start-time'];
							}
							$name       = isset( $time_option['name-ts'] ) && '' != $time_option['name-ts'] ? $time_option['name-ts'] : $r_time;
							$_time_base = apply_filters( 'woore_timebase_to_check_delivery', $time_option['start-time'], $time_option );
							$disable    = '';
							if ( '' != $_time_base ) {
								$_timeck = $_time_base;
								$_timeck = explode( ':', $_timeck );
								$_timeck = $_timeck[1] * 60 + $_timeck[0] * 3600;
								if ( '' != $time_option['start-time'] && ( $data['wooresfd_date_deli'] + $_timeck ) < $cure_time ) {
									$disable = 'disabled="disabled"';
								}
							}
							$maxsl = isset( $time_option['max-odts'] ) && is_numeric( $time_option['max-odts'] ) ? $time_option['max-odts'] : '';
							if ( 'disabled="disabled"' != $disable && '' != $maxsl ) {
								$total_rs = woore_get_number_order_timeslot( $data['wooresfd_date_deli'], $name, $user_log, $method, $tsl_method, $time_option );
								if ( $total_rs >= $maxsl ) {
									$disable = 'disabled="disabled"';
								}
							}
							$html_timesl .= '<option value="' . esc_attr( $name ) . '" ' . $disable . '>' . $name . '</option>';
						}
					}
				} else {
					$html_timesl .= '<option value="">' . esc_html__( 'No time slot available for selection', 'woorestaurant' ) . '</option>';
				}
				$html_timesl .= '</select>';
				break;
			}
		}
	}
	$output = array(
		'html_timesl' => $html_timesl,
		'data_time'   => json_encode( $def_timesl ),
	);
	echo esc_attr( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}

add_filter( 'woocommerce_email_recipient_new_order', 'woore_change_email_recipient', 10, 2 );
/**
 * Send email loc.
 *
 * @param string $recipient it is the recipient email.
 * @param object $order it is the order object.
 */
function woore_change_email_recipient( $recipient, $order ) {
	$mail = '';
	if ( is_object( $order ) && method_exists( $order, 'get_id' ) && '' != get_post_meta( $order->get_id(), 'woorestaurant_location', true ) ) {
		$term = get_term_by( 'slug', $order->get_meta( 'woorestaurant_location' ), 'woorestaurant_loc' );
		if ( $term->term_id ) {
			$mail = get_term_meta( $term->term_id, 'wrsp_loc_email', true );
		}
	}
	if ( '' != $mail ) {
		$recipient = $mail;
	}
	return $recipient;
}

add_action( 'woocommerce_before_add_to_cart_quantity', 'woore_update_live_total_price', 32 );
/**
 * Live total.
 */
function woore_update_live_total_price() {
	$enable_livetotal = woo_restaurant_get_option( 'woo_restaurant_enable_livetotal', 'woo_restaurant_options' );
	if ( 'yes' == $enable_livetotal ) {
		woo_restaurant_template_plugin( 'live-total', 1 );
	}
}

add_action( 'woocommerce_single_product_summary', 'woore_food_meta_information_html' );
/**
 * Metdata.
 *
 * @param int $id_food it is the id of the post.
 */
function woore_food_meta_information_html( $id_food = false ) {
	if ( ! isset( $id_food ) || '' == $id_food ) {
		$id_food = get_the_ID();
	}
	$protein = get_post_meta( $id_food, 'woo_restaurant_protein', true );
	$calo    = get_post_meta( $id_food, 'woo_restaurant_calo', true );
	$choles  = get_post_meta( $id_food, 'woo_restaurant_choles', true );
	$fibel   = get_post_meta( $id_food, 'woo_restaurant_fibel', true );
	$sodium  = get_post_meta( $id_food, 'woo_restaurant_sodium', true );
	$carbo   = get_post_meta( $id_food, 'woo_restaurant_carbo', true );
	$fat     = get_post_meta( $id_food, 'woo_restaurant_fat', true );

	$custom_data = get_post_meta( $id_food, 'woo_restaurant_custom_data_gr', true );
	?>
	<div class="wooresfd_nutrition">
		<ul>
			<?php if ( '' != $protein ) { ?>
				<li>
					<span><?php esc_html_e( 'Protein', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $protein ); ?>
				</li>
			<?php } if ( '' != $calo ) { ?>
				<li><span><?php esc_html_e( 'Calories', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $calo ); ?></li>
			<?php } if ( '' != $choles ) { ?>
				<li><span><?php esc_html_e( 'Cholesterol', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $choles ); ?></li>
			<?php } if ( '' != $fibel ) { ?>
				<li><span><?php esc_html_e( 'Dietary fibre', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $fibel ); ?></li>
			<?php } if ( '' != $sodium ) { ?>
				<li><span><?php esc_html_e( 'Sodium', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $sodium ); ?></li>
			<?php } if ( '' != $carbo ) { ?>
				<li><span><?php esc_html_e( 'Carbohydrates', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $carbo ); ?></li>
			<?php } if ( '' != $fat ) { ?>
				<li><span><?php esc_html_e( 'Fat total', 'woorestaurant' ); ?></span><?php echo wp_kses_post( $fat ); ?></li>
				<?php
			}
			if ( '' != $custom_data ) {
				foreach ( $custom_data as $data_it ) {
					?>
					<li><span><?php echo wp_kses_post( $data_it['_name'] ); ?></span><?php echo wp_kses_post( $data_it['_value'] ); ?></li>
					<?php
				}
			}
			?>
			<div class="wooresfd_clearfix"></div>
		</ul>
	</div>
	<?php
}

add_filter( 'woocommerce_widget_cart_item_quantity', 'woore_add_minicart_quantity_fields', 10, 3 );
/**
 * Add minicart quantity fields.
 *
 * @param html   $html it is the quantity field html.
 * @param array  $cart_item it is having the cart item data.
 * @param string $cart_item_key it is the cart item key.
 */
function woore_add_minicart_quantity_fields( $html, $cart_item, $cart_item_key ) {
	$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $cart_item['data'] ), $cart_item, $cart_item_key );
	$_product      = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	if ( ! $_product->is_sold_individually() ) {
		$input_args   = array( 'input_value' => $cart_item['quantity'] );
		$product_id   = $_product->get_id();
		$woore_varmin = '';
		$woore_varmax = '';
		if ( $_product->get_parent_id() ) {
			$woore_varmin = get_post_meta( $_product->get_parent_id(), 'woore_minquantity', true );
			$woore_varmax = get_post_meta( $_product->get_parent_id(), 'woore_maxquantity', true );
		}
		$woore_minquantity = '' != $woore_varmin ? $woore_varmin : get_post_meta( $product_id, 'woore_minquantity', true );
		$woore_maxquantity = '' != $woore_varmax ? $woore_varmax : get_post_meta( $product_id, 'woore_maxquantity', true );
		if ( '' != $woore_minquantity && $woore_minquantity > 0 ) {
			// phpcs:ignore
			$input_args['min_value'] = $woore_minquantity;
		}
		if ( '' != $woore_maxquantity && $woore_maxquantity > 0 ) {
			// phpcs:ignore
			$input_args['max_value'] = $woore_maxquantity;
		}
		if ( $_product->managing_stock() && ! $_product->backorders_allowed() ) {
			$stock                   = $_product->get_stock_quantity();
			// phpcs:ignore
			$input_args['max_value'] = min( $stock, $input_args['max_value'] );
		}

		add_filter(
			'woocommerce_quantity_input_classes',
			function ( $arr ) {
				$arr[] = 'wrr_input_text';
				return $arr;
			}
		);

		$html = '	<div class="wrs-hidden">
		<div class="wrr_quantity wrr_d_flex wrr_align_items_center">
                        <div class="wrr_input_group" data-cart_key="' . esc_attr( $cart_item_key ) . '" data-quatity="' . $cart_item['quantity'] . '"> 
                        ' . woocommerce_quantity_input( $input_args, $cart_item['data'], false ) . '	 
                          <span class="inc-qty wrr_minus wrr_minus_2"  id="wrsminus_ticket"><img src="https://enteraddon.com/restrofood-demo/wp-content/plugins/restrofood/assets/img/icon/minus1.svg"></span>
                          <span class="inc-qty wrr_plus wrr_plus_2"  id="explus_ticket"><img src="https://enteraddon.com/restrofood-demo/wp-content/plugins/restrofood/assets/img/icon/plus1.svg"></span>
                        </div>
                    </div>
                     
                </div>' . $html;
	}
	return $html;
}

add_action( 'wp_ajax_woore_update_quantity', 'ajax_woore_update_quantity' );
add_action( 'wp_ajax_nopriv_woore_update_quantity', 'ajax_woore_update_quantity' );
/**
 * Ajax update quantity.
 */
function ajax_woore_update_quantity() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'variation-setting' ) ) {
			return;
		}
	}

	if ( isset( $_POST['key'] ) || isset( $_POST['quantity'] ) ) {
		$key   = wp_kses_post( wp_unslash( $_POST['key'] ) );
		$value = wp_kses_post( wp_unslash( $_POST['quantity'] ) );
	}
	global $woocommerce;
	$values            = WC()->cart->get_cart_item( $key );
	$passed_validation = apply_filters( 'woocommerce_update_cart_validation', true, $key, $values, $value );
	if ( false == $passed_validation ) {
		$notice = wc_print_notices( true );
		if ( '' != $notice ) {
			$data = array(
				'error'   => true,
				'message' => '<div class="wrsfd-out-notice">' . $notice . '</div>',
			);
			// phpcs:ignore
			echo ( wp_send_json( $data ) );
			wc_clear_notices();
			wp_die();
		}
	}
	WC()->cart->set_quantity( $key, $value );
	echo '1';
	wp_die();
}

add_filter( 'woocommerce_add_to_cart_validation', 'woore_validate_food_in_loc', 4, 4 );
/**
 * To validate food in loc.
 *
 * @param bool $passed it is the passed check.
 * @param int  $product_id it is the product id.
 * @param int  $quantity it is the quantity.
 * @param int  $variation_id it is the variation id.
 */
function woore_validate_food_in_loc( $passed, $product_id, $quantity, $variation_id = false ) {
	if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
		if ( is_numeric( $variation_id ) && $variation_id > 0 ) {
			$variation  = wc_get_product( $variation_id );
			$product_id = $variation->get_parent_id();
		} elseif ( 'product_variation' == get_post_type( $product_id ) ) {
			$variation    = wc_get_product( $product_id );
			$variation_id = $variation->get_parent_id();
			$product_id   = $variation->get_parent_id();
		}
		$loc_selected = WC()->session->get( 'wr_userloc' );

		if ( has_term( '', 'woorestaurant_loc', $product_id ) && ! has_term( $loc_selected, 'woorestaurant_loc', $product_id ) ) {
			$passed = false;
			wc_add_notice( esc_html__( 'Something went wrong, please try again', 'woorestaurant' ), 'error' );
		}
	}
	return $passed;
}

add_action( 'woocommerce_checkout_process', 'woore_if_product_is_not_inlocation_orpostcodes' );
/**
 * If product is not inlocation.
 */
function woore_if_product_is_not_inlocation_orpostcodes() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'product-location' ) ) {
			return;
		}
	}

	$method = WC()->session->get( '_user_order_method' );
	$method = '' != $method ? $method : 'delivery';
	if ( 'delivery' == $method ) {
		$delivery_zones = WC_Shipping_Zones::get_zones();
		if ( is_array( $delivery_zones ) && empty( $delivery_zones ) || '' == $delivery_zones ) {
			$postcode  = woo_restaurant_get_option( 'woo_restaurant_ship_postcodes', 'woo_restaurant_shpping_options' );
			$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
			if ( 'postcode' == $ship_mode && '' != $postcode ) {
				$postcode   = str_replace( ' ', '', $postcode );
				$postcode   = explode( ',', $postcode );
				$user_postc = isset( $_POST['billing_postcode'] ) ? wp_kses_post( wp_unslash( $_POST['billing_postcode'] ) ) : '';
				if ( isset( $_POST['ship_to_different_address'] ) && '1' == $_POST['ship_to_different_address'] ) {
					$user_postc = isset( $_POST['shipping_postcode'] ) ? wp_kses_post( wp_unslash( $_POST['shipping_postcode'] ) ) : '';
				}
				if ( '' != $user_postc ) {
					$valid = false;
					if ( is_array( $postcode ) && isset( $postcode ) && '*' == $postcode[0] ) {
						$valid = true;
					} elseif ( in_array( $user_postc, $postcode ) ) {
						$valid = true;
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
								if ( strpos( $user_postc, $itpc ) !== false ) {
									$valid = true;
									break;
								}
							}
						}
					}
					if ( false == $valid ) {
						$title = sprintf( esc_html__( 'Your address are out of delivery zone, please change to carryout channel', 'woorestaurant' ), $title );
						wc_add_notice( $title, 'error' );
						return;
					}
				}
			}
		}
	}
	if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
		$title        = '';
		$loc_selected = isset( $_POST['woo_restaurant_ck_loca'] ) ? wp_kses_post( wp_unslash( $_POST['woo_restaurant_ck_loca'] ) ) : WC()->session->get( 'wr_userloc' );
		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			$product_id = $values['product_id'];
			if ( has_term( '', 'woorestaurant_loc', $product_id ) && ! has_term( $loc_selected, 'woorestaurant_loc', $product_id ) ) {
				$title = '' != $title ? $title . ', ' . get_the_title( $product_id ) : get_the_title( $product_id );
				WC()->cart->remove_cart_item( $cart_item_key );
			}
		}
		if ( '' != $title ) {
			/* translators: %s is replaced with the title */
			$title = sprintf( esc_html__( 'The food "%s" has been removed because it does not exist in this location, please refresh page and try again', 'woorestaurant' ), $title );
			wc_add_notice( $title, 'error' );
			return;
		}
	}
}

add_action( 'woocommerce_cart_calculate_fees', 'woore_dine_in_surcharge' );
/**
 * Dine in surcharge.
 */
function woore_dine_in_surcharge() {
	global $woocommerce;
	if ( ! isset( WC()->session ) || is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;}
	$user_odmethod = WC()->session->get( '_user_order_method' );
	if ( 'dinein' != $user_odmethod && 'takeaway' != $user_odmethod ) {
		return;
	}
	$tax_fee     = apply_filters( 'woore_surcharge_fee_tax', false );
	$total_cart  = WC()->cart->get_cart_contents_total();
	$al_products = woo_restaurant_get_option( 'woo_restaurant_ign_deli', 'woo_restaurant_advanced_options' );
	$al_cats     = woo_restaurant_get_option( 'woo_restaurant_igncat_deli', 'woo_restaurant_advanced_options' );
	if ( '' != $al_products || ( is_array( $al_cats ) && ! empty( $al_cats ) ) ) {
		$al_products = '' != $al_products ? explode( ',', $al_products ) : array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$id_cr = $cart_item['product_id'];
			if ( is_array( $al_cats ) && ! empty( $al_cats ) ) {
				if ( in_array( $id_cr, $al_products ) || has_term( $al_cats, 'product_cat', $id_cr ) ) {
					$line_subtotal = wc_format_decimal( $cart_item['line_subtotal'] );
					$total_cart    = $total_cart - $line_subtotal;
				}
			} elseif ( ! empty( $al_products ) && in_array( $id_cr, $al_products ) ) {
					$line_subtotal = wc_format_decimal( $cart_item['line_subtotal'] );
					$total_cart    = $total_cart - $line_subtotal;
			}
		}
	}
	if ( $total_cart <= 0 ) {
		return;
	}
	if ( 'dinein' == $user_odmethod ) {
		$surcharge = woo_restaurant_get_option( 'woo_restaurant_dinein_sur', 'woo_restaurant_adv_dinein_options' );
		$lablel    = esc_html__( 'Dine-in Surcharge', 'woorestaurant' );
		if ( '' != $surcharge && is_numeric( $surcharge ) ) {
			if ( $surcharge < 0 ) {
				$lablel = esc_html__( 'Dine-in Discount', 'woorestaurant' );}
			$woocommerce->cart->add_fee( $lablel, $surcharge, $tax_fee, '' );
		} elseif ( '' != $surcharge && is_numeric( str_replace( '%', '', $surcharge ) ) ) {
			if ( $surcharge < 0 ) {
				$lablel = esc_html__( 'Dine-in Discount', 'woorestaurant' );}
			$fee = $total_cart * ( str_replace( '%', '', $surcharge ) / 100 );
			$woocommerce->cart->add_fee( $lablel, $fee, $tax_fee, '' );
		}
	} elseif ( 'takeaway' == $user_odmethod ) {
		$surcharge = woo_restaurant_get_option( 'woo_restaurant_takeaway_sur', 'woo_restaurant_adv_takeaway_options' );
		$lablel    = esc_html__( 'Takeaway Surcharge', 'woorestaurant' );
		if ( '' != $surcharge && is_numeric( $surcharge ) ) {
			if ( $surcharge < 0 ) {
				$lablel = esc_html__( 'Takeaway Discount', 'woorestaurant' );}
			$woocommerce->cart->add_fee( $lablel, $surcharge, $tax_fee, '' );
		} elseif ( '' != $surcharge && is_numeric( str_replace( '%', '', $surcharge ) ) ) {
			if ( $surcharge < 0 ) {
				$lablel = esc_html__( 'Takeaway Discount', 'woorestaurant' );}
			$fee = $total_cart * ( str_replace( '%', '', $surcharge ) / 100 );
			$woocommerce->cart->add_fee( $lablel, $fee, $tax_fee, '' );
		}
	}
}

add_action( 'woore_modal_after_price', 'woore_add_review_tab' );
/**
 * Add review form to popup.
 *
 * @param int $id_food it is the food id.
 */
function woore_add_review_tab( $id_food ) {
	if ( comments_open( $id_food ) ) {
		?>
		<div class="wrsf-md-tabs">
			<span class="wrsf-tab wrsf-tab-current" data-control="wrsf-md-details"><?php esc_html_e( 'Details', 'woorestaurant' ); ?></span>
			<span class="wrsf-tab" data-control="wrsf-reviews"><?php esc_html_e( 'Reviews', 'woorestaurant' ); ?></span>
		</div> 
		<?php
	}
}

add_action( 'woore_modal_after_content', 'woore_add_review_form' );
/**
 * Add review form.
 */
function woore_add_review_form() {
	woo_restaurant_template_plugin( 'review', 1 );
}

/**
 * Ajax post rating.
 *
 * @param int    $comment_id it is the comment id.
 * @param string $comment_status it is the comment status.
 */
function woore_ajax_comments( $comment_id, $comment_status ) {
	if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && 'xmlhttprequest' == strtolower( wp_kses_post( wp_unslash( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) ) ) {
		// If AJAX Request Then.
		switch ( $comment_status ) {
			case '0':
				// notify moderator of unapproved comment.
				wp_notify_moderator( $comment_id );
				echo '<p>' . esc_html__( 'Your review is awaiting approval', 'woorestaurant' ) . '</p>';
				break;
			case '1': // Approved comment.
				$commentdata = get_comment( $comment_id, ARRAY_A );
				wp_notify_postauthor( $comment_id );
				echo '<p>' . esc_html__( 'Thanks for your rating!', 'woorestaurant' ) . '</p>';
				break;
			default:
				echo '<p>' . esc_html__( 'Oops something went wrong!', 'woorestaurant' ) . '</p>';
				break;
		}
		exit;
	}
}
add_action( 'comment_post', 'woore_ajax_comments', 20, 2 );

add_action( 'woocommerce_mini_cart_contents', 'woore_show_cross_sells', 1 );
/**
 * Add cross sells side cart.
 */
function woore_show_cross_sells() {
	$limit       = 2;
	$columns     = 2;
	$orderby     = 'rand';
	$order       = 'desc';
	$cross_sells = WC()->cart->get_cross_sells();
	if ( is_array( $cross_sells ) && ! empty( $cross_sells ) ) {
		shuffle( $cross_sells );
		$cross_sells = apply_filters( 'woore_cross_sells_arr', $cross_sells );
		$cross_sells = implode( ',', $cross_sells );
		global $modal_html, $pu_del;
		$modal_html = 'on';
		$pu_del     = '1';
		echo '<div class="wrsf-cart-cross-sells wrs-load-hidden">
		<h3>' . esc_html__( 'You may be interested in...', 'woorestaurant' ) . '</h3>
		' . do_shortcode( '[woores_view_carousel style="3" cart_enable="no"  count="999" slidesshow="1" ids="' . $cross_sells . '" autoplay="no" loading_effect="1" infinite="yes" number_excerpt="0"]' ) . '</div>';
		$modal_html = '';
		$pu_del     = '';
	}
}