<?php
/**
 * It is the woo hook file.
 *
 * @package woorestaurant
 */

/**
 * Get options.
 *
 * @param int $id it is the id.
 */
function woo_restaurantget_options( $id ) {
	if ( '' == $id ) {
		$id = get_the_ID();
	}
	$global_op = array();
	// Check global option.
	$exclude_options = get_post_meta( $id, 'woores_exclude_options', true );
	if ( 'on' != $exclude_options ) {
		$cate               = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'slugs' ) );
		$args               = array(
			'post_type'        => 'woores_modifiers',
			'post_status'      => array( 'publish' ),
			'numberposts'      => -1,
			'suppress_filters' => true,
		);
		$args['meta_query'] = array(
			array(
				'key'     => 'woores_product_ids_arr',
				'value'   => $id,
				'compare' => '=',
			),
		);
		$args               = apply_filters( 'woores_option_by_cr_ids', $args );
		$glb_oids           = array();
		if ( isset( $args['meta_query'] ) ) {
			$glb_oids = get_posts( $args );
			$glb_oids = wp_list_pluck( $glb_oids, 'ID' );
			unset( $args['meta_query'] );
		}
		if ( ! empty( $cate ) && count( $cate ) > 0 ) {
			$args['tax_query'] = array(
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'slug',
					'terms'            => $cate,
					'operator'         => 'IN',
					'include_children' => false,
				),
			);
		}

		$glb_otqr = get_posts( $args );
		$glb_otqr = wp_list_pluck( $glb_otqr, 'ID' );
		$glb_otqr = array_merge( $glb_otqr, $glb_oids );
		if ( ! empty( $glb_otqr ) && count( $glb_otqr ) > 0 ) {
			foreach ( $glb_otqr as $op_item ) {
				$goptions  = get_post_meta( $op_item, 'woores_options', true );
				// phpcs:ignore
				$global_op = array_merge( $global_op, $goptions );
			}
			wp_reset_postdata();
		}
	}
	// include option.
	$include_options = get_post_meta( $id, 'woores_include_options', true );
	if ( '' != $include_options ) {
		$include_options = explode( ',', $include_options );
		foreach ( $include_options as $in_item ) {
			$goptions = get_post_meta( $in_item, 'woores_options', true );
			if ( is_array( $goptions ) && ! empty( $goptions ) ) {
				$global_op = array_merge( $global_op, $goptions );
			}
		}
	}
	if ( is_array( $global_op ) ) {
		$global_op = array_unique( $global_op, SORT_REGULAR );
	}
	$data_options = get_post_meta( $id, 'woores_options', true );
	if ( ! empty( $global_op ) ) {
		if ( '' == $data_options ) {
			$data_options = array();
		}
		$data_op_pos = get_post_meta( $id, 'woores_options_pos', true );
		$pos_glbop   = apply_filters( 'expoa_pos_global', $data_op_pos );
		if ( 'before' == $pos_glbop ) {
			$data_options = array_merge( $global_op, $data_options );
		} else {
			$data_options = array_merge( $data_options, $global_op );
		}
	}
	return apply_filters( 'woo_restaurantget_options', $data_options, $id );
}

/**
 * Add the field to add to cart form.
 */
function woo_restaurantdisplay_custom_field() {
	global $post, $cart_itemkey;
	$data_edit = '';
	if ( '' != $cart_itemkey ) {
		$cart = WC()->cart->get_cart();
		if ( isset( $cart[ $cart_itemkey ]['data_edit'] ) && '' != $cart[ $cart_itemkey ]['data_edit'] ) {
			$data_edit = $cart[ $cart_itemkey ]['data_edit'];
			echo '<input type="hidden" name="wrsf-up-cartitem" value="' . esc_attr( $cart_itemkey ) . '"/>';
		}
	}
	// Check for the custom field value.
	$data_options = woo_restaurantget_options( $post->ID );
	if ( is_array( $data_options ) && ! empty( $data_options ) ) {
		$i               = 0;
		$show_more       = apply_filters( 'woores_show_more_option_button', 0 );
		$cls             = '1' == $show_more ? 'woores-hide-options' : '';
		$accordion_style = apply_filters( 'woores_accordion_style', 0 );
		$cls             = '1' == $accordion_style ? 'woores-accordion-style' : '';
		$wrid            = 'wrot' . rand( 10000, 10000000000 );
		echo '<div class="woores-product-gr-options" id="' . esc_attr( $wrid ) . '">';
		$j          = 0;
		$logic_js   = '';
		$extralg_js = '';
		foreach ( $data_options as $item ) {
			$cls          = '1' == $accordion_style ? 'woores-accordion-style' : '';
			$display_type = isset( $item['_display_type'] ) ? $item['_display_type'] : '';
			if ( 'accor' == $display_type ) {
				$cls = 'woores-accordion-style';
			} elseif ( 'nor' == $display_type ) {
				$cls = '';
			}
			echo '<div class="woores-product-options ' . esc_attr( $cls ) . '">';
				$el_id      = isset( $item['_id'] ) && '' != $item['_id'] ? $item['_id'] : 'woores-id' . rand( 10000, 10000000000 );
				$el_id      = $el_id . '-' . $j;
				$type       = isset( $item['_type'] ) && '' != $item['_type'] ? $item['_type'] : 'checkbox';
				$required   = isset( $item['_required'] ) && '' != $item['_required'] ? 'wrs-required' : '';
				$min_req    = '';
				$max_req    = '';
				$required_m = '';
			if ( 'checkbox' == $type ) {
				$min_req = isset( $item['_min_op'] ) && '' != $item['_min_op'] ? $item['_min_op'] : '';
				if ( is_numeric( $min_req ) && $min_req > 0 ) {
					$required_m = ' wrs-required-min';
				}
				$max_req = isset( $item['_max_op'] ) && '' != $item['_max_op'] ? $item['_max_op'] : '';
				if ( is_numeric( $max_req ) && $max_req > 0 ) {
					$required_m .= ' wrs-required-max';
				}
			}
				$enb_logic = isset( $item['_enb_logic'] ) ? $item['_enb_logic'] : '';
				$plus_sign = apply_filters( 'woores_plus_sign_char', '+', $item );
			if ( 'on' == $enb_logic ) {
				$con_logic  = isset( $item['_con_logic'] ) ? $item['_con_logic'] : '';
				$logic_rule = isset( $item['_con_tlogic'] ) && 'hide' == $item['_con_tlogic'] ? 'fadeOut()' : 'fadeIn()';
				if ( is_array( $con_logic ) && ! empty( $con_logic ) ) {
					$log_option      = '';
					$extralog_option = '';
					$lg              = 0;
					foreach ( $con_logic as $key => $item_logic ) {
						++$lg;
						$cttype_rel = isset( $item_logic['type_rel'] ) && 'and' == $item_logic['type_rel'] ? '&&' : '||';
						$ctype_con  = isset( $item_logic['type_con'] ) && 'is_not' == $item_logic['type_con'] ? '!=' : '==';
						$ctype_op   = isset( $item_logic['type_op'] ) && '' != $item_logic['type_op'] && 'varia' != $item_logic['type_op'] ? $item_logic['type_op'] . '-' . $j/*$el_id*/ : '$wr_variation';
						$con_val    = isset( $item_logic['val'] ) ? $item_logic['val'] : '';
						$con_val    = explode( '-', $con_val );
						$con_val    = $con_val[0];
						if ( '' != $cttype_rel && '' != $ctype_con && '' != $ctype_op ) {
							if ( '$wr_variation' == $ctype_op ) {
								if ( count( $con_logic ) > 1 ) {
									if ( 1 == $lg ) {
										$log_option .= '$wr_variation ' . $ctype_con . ' "' . $con_val . '" ';
									} else {
										$log_option .= $cttype_rel . ' $wr_variation ' . $ctype_con . ' "' . $con_val . '" ';
									}

									if ( count( $con_logic ) == $lg ) {
										$log_option = 'if(' . $log_option . '){ jQuery("#' . $el_id . '")' . ( 'fadeOut()' == $logic_rule ? '.addClass("wrsf-offrq").css("display","none")' : '.removeClass("wrsf-offrq").css("display","block")' ) . ';}
											else{ jQuery("#' . $el_id . '")' . ( 'fadeOut()' == $logic_rule ? '.removeClass("wrsf-offrq").css("display","block")' : '.addClass("wrsf-offrq").css("display","none")' ) . ';}';
									}
								} else {
									$log_option = ' if($wr_variation ' . $ctype_con . ' "' . $con_val . '"){ jQuery("#' . $el_id . '")' . ( 'fadeOut()' == $logic_rule ? '.addClass("wrsf-offrq").css("display","none")' : '.removeClass("wrsf-offrq").css("display","block")' ) . ';}
											else{ jQuery("#' . $el_id . '")' . ( 'fadeOut()' == $logic_rule ? '.removeClass("wrsf-offrq").css("display","block")' : '.addClass("wrsf-offrq").css("display","none")' ) . ';}';
								}
							} else {
								$extralog_option = ' 
										if(jQuery("#' . $wrid . ' #' . $ctype_op . '").hasClass("wrs-checkbox")){
											var $value = [];
											jQuery.each(jQuery("#' . $wrid . ' #' . $ctype_op . ' input:checked"), function(){
								                $value.push(jQuery(this).val());
								            });
										}else if(jQuery("#' . $wrid . ' #' . $ctype_op . '").hasClass("wrs-textarea")){
											var $value = jQuery("#' . $wrid . ' #' . $wrid . ' #' . $ctype_op . ' textarea").val();
										}else if(jQuery("#' . $wrid . ' #' . $ctype_op . '").hasClass("wrs-radio")){
											var $value = jQuery("#' . $wrid . ' #' . $ctype_op . ' input:checked").val();
										}else if(jQuery("#' . $wrid . ' #' . $ctype_op . '").hasClass("wrs-select")){
											var $value = jQuery("#' . $wrid . ' #' . $ctype_op . ' select").val();
										}else{
											var $value = jQuery("#' . $wrid . ' #' . $ctype_op . ' input").val();
										}';
								if ( count( $con_logic ) > 1 ) {
									if ( 1 == $lg ) {
										$extralog_op = '( (jQuery.isArray($value) && jQuery.inArray("' . $con_val . '", $value) !== -1 ) ||  $value ' . $ctype_con . ' "' . $con_val . '") ';
									} else {
										$extralog_op .= $cttype_rel . ' ( (jQuery.isArray($value) && jQuery.inArray("' . $con_val . '", $value) !== -1 ) ||  $value ' . $ctype_con . ' "' . $con_val . '") ';
									}

									if ( count( $con_logic ) == $lg ) {
										$extralog_option .= '
												if(' . $extralog_op . '){ 
														jQuery("#' . $wrid . ' #' . $el_id . '")' . ( 'fadeOut()' == $logic_rule ? '.addClass("wrsf-offrq").css("display","none")' : '.removeClass("wrsf-offrq").css("display","block")' ) . ';}
													else{ jQuery("#' . $wrid . ' #' . $el_id . '")' . ( 'fadeOut()' == $logic_rule ? '.removeClass("wrsf-offrq").css("display","block")' : '.addClass("wrsf-offrq").css("display","none")' ) . ';
												}
												jQuery("#' . $wrid . ' #' . $el_id . ' .wrs-options").trigger("change");';
										$extralog_option .= '
											jQuery("body").on("change", "#' . $wrid . ' #' . $ctype_op . ' .wrs-options", function() {
												' . $extralog_option . '
												jQuery("#' . $wrid . ' .wrsf-offrq .wrs-options:not([type=radio]):not([type=checkbox])").val("");
												jQuery("#' . $wrid . ' .wrsf-offrq .wrs-options[type=radio], #' . $wrid . ' .wrsf-offrq .wrs-options[type=checkbox]").prop("checked", false);
											});
											';
									}
								} else {
									$extralog_option .= ' 
											if( (jQuery.isArray($value) && jQuery.inArray("' . $con_val . '", $value) !== -1 ) ||  $value ' . $ctype_con . ' "' . $con_val . '"){ jQuery("#' . $wrid . ' #' . $el_id . '")' . ( 'fadeOut()' == $logic_rule ? '.addClass("wrsf-offrq").css("display","none")' : '.removeClass("wrsf-offrq").css("display","block")' ) . ';}
												else{ jQuery("#' . $wrid . ' #' . $el_id . '")' . ( 'fadeOut()' == $logic_rule ? '.removeClass("wrsf-offrq").css("display","block")' : '.addClass("wrsf-offrq").css("display","none")' ) . ';
											}
											';
									$extralog_option .= '
										jQuery("body").on("change", "#' . $wrid . ' #' . $ctype_op . ' .wrs-options", function() {
											' . $extralog_option . '
											jQuery("#' . $wrid . ' #' . $el_id . '.wrsf-offrq .wrs-options").trigger("change");
											jQuery("#' . $wrid . ' .wrsf-offrq .wrs-options:not([type=radio]):not([type=checkbox])").val("");
											jQuery("#' . $wrid . ' .wrsf-offrq.wrs-select select").removeClass("woores-defed");
											jQuery("#' . $wrid . ' .wrsf-offrq .wrs-options[type=radio], #' . $wrid . ' .wrsf-offrq .wrs-options[type=checkbox]").prop("checked", false).removeClass("woores-defed");
											if(jQuery("#' . $wrid . ' .wrs-logic-on:not(.wrsf-offrq) .wrs-options[data-def=yes]").length > 0) {
												jQuery("#' . $wrid . ' .wrs-logic-on:not(.wrsf-offrq) .wrs-options[data-def=yes]:not(.woores-defed)").each(function(){
													jQuery(this).prop("checked", true).addClass("woores-defed").trigger("change");
												});
											}
											if(jQuery("#' . $wrid . ' .wrs-logic-on:not(.wrsf-offrq) .wrs-options option[data-def=yes]").length > 0) {
												jQuery("#' . $wrid . ' .wrs-logic-on.wrs-select:not(.wrsf-offrq) .wrs-options:not(.woores-defed) option[data-def=yes]").each(function(){
													jQuery(this).closest("select").val(jQuery(this).attr("value")).addClass("woores-defed").trigger("change");
												});
											}
										});
										';
								}
							}
						}
					}
					$logic_js   .= $log_option;
					$extralg_js .= $extralog_option;
				}
			}
			echo '<div class="wrrow-group wrs-' . esc_attr( $type ) . ' ' . esc_attr( $required ) . ' ' . esc_attr( $required_m ) . ' wrs-logic-' . esc_attr( $enb_logic ) . '" data-minsl="' . esc_attr( $min_req ) . '"  data-maxsl="' . esc_attr( $max_req ) . '" id="' . esc_attr( $el_id ) . '">';
			if ( isset( $item['_name'] ) && $item['_name'] ) {
				$price_tt = '';
				if ( 'text' == $type || 'textarea' == $type || 'quantity' == $type ) {
					$price_tt = isset( $item['_price'] ) && '' != $item['_price'] ? wc_price( woores_convert_number_decimal_comma( $item['_price'] ) ) : '';
					$price_tt = '' != $price_tt ? '<span> ' . $plus_sign . ' ' . wp_strip_all_tags( $price_tt ) . '</span>' : '';
				}
				echo '<span class="wooresf-label"><span class="woores-otitle">' . esc_attr( $item['_name'] ) . '</span> ' . esc_attr( $price_tt ) . '</span>';
			}
			echo '<div class="woores-container">';
			$options = isset( $item['_options'] ) ? $item['_options'] : '';
			if ( 'radio' == $type && ! empty( $options ) ) {
				foreach ( $options as $key => $value ) {
					$op_name = isset( $value['name'] ) ? $value['name'] : '';
					$dis_ck  = isset( $value['dis'] ) ? $value['dis'] : '';
					if ( is_array( $data_edit ) ) {
						$def_ck = isset( $data_edit[ $item['_id'] ][ $key ] ) ? 'yes' : '';
					} else {
						$def_ck = isset( $value['def'] ) && 'yes' != $dis_ck ? $value['def'] : '';
					}
					$op_val  = isset( $value['price'] ) ? woores_convert_number_decimal_comma( $value['price'] ) : '';
					$op_typ  = isset( $value['type'] ) ? $value['type'] : '';
					$op_name = '' != $op_val ? $op_name . ' ' . $plus_sign . ' ' . wc_price( $op_val ) : $op_name;
					$id_op   = 'raid-' . rand( 1, 10000 ) . '-' . $el_id . '-' . rand( 1, 10000 );
					echo '<span><input class="wrs-options" type="radio" name="wr_options_' . esc_attr( $i ) . '[]" id="' . esc_attr( $id_op ) . '" value="' . esc_attr( $key ) . '" data-price="' . esc_attr( $op_val ) . '" data-def="' . esc_attr( $def_ck ) . '" data-type="' . esc_attr( $op_typ ) . '" ' . checked( $def_ck, 'yes', false ) . ' ' . disabled( $dis_ck, 'yes', false ) . '><label for="' . esc_attr( $id_op ) . '">' . wp_kses_post( $op_name ) . '</label></span>';
				}
			} elseif ( 'select' == $type && ! empty( $options ) ) {
				echo '<select class="wrs-options" name="wr_options_' . esc_attr( $i ) . '[]">';
				echo '<option value="" data-price="">' . esc_html__( 'Select', 'product-options-addon' ) . '</option>';
				foreach ( $options as $key => $value ) {
					$op_name = isset( $value['name'] ) ? $value['name'] : '';
					$dis_ck  = isset( $value['dis'] ) ? $value['dis'] : '';
					if ( is_array( $data_edit ) ) {
						$def_ck = isset( $data_edit[ $item['_id'] ][ $key ] ) ? 'yes' : '';
					} else {
						$def_ck = isset( $value['def'] ) && 'yes' != $dis_ck ? $value['def'] : '';
					}
					$op_val  = isset( $value['price'] ) ? woores_convert_number_decimal_comma( $value['price'] ) : '';
					$op_typ  = isset( $value['type'] ) ? $value['type'] : '';
					$op_name = '' != $op_val ? $op_name . ' ' . $plus_sign . ' ' . wc_price( $op_val ) : $op_name;
					echo '<option value="' . esc_attr( $key ) . '" data-price="' . esc_attr( $op_val ) . '" data-type="' . esc_attr( $op_typ ) . '" data-def="' . esc_attr( $def_ck ) . '" ' . selected( $def_ck, 'yes', false ) . ' ' . disabled( $dis_ck, 'yes', false ) . '>' . wp_kses_post( $op_name ) . '</option>';
				}
				echo '<select>';
			} elseif ( 'text' == $type ) {
				$price_ta  = isset( $item['_price'] ) && '' != $item['_price'] ? woores_convert_number_decimal_comma( $item['_price'] ) : '';
				$price_typ = isset( $item['_price_type'] ) && '' != $item['_price_type'] ? $item['_price_type'] : '';
				$def       = '';
				if ( is_array( $data_edit ) && isset( $data_edit[ $item['_id'] ] ) ) {
					$def = $data_edit[ $item['_id'] ];
				}
				echo '<input class="wrs-options" value="' . esc_attr( $def ) . '" type="text" name="wr_options_' . esc_attr( $i ) . '" data-price="' . esc_attr( $price_ta ) . '" data-type="' . esc_attr( $price_typ ) . '"/>';
			} elseif ( 'quantity' == $type ) {
				$price_ta  = isset( $item['_price'] ) && '' != $item['_price'] ? woores_convert_number_decimal_comma( $item['_price'] ) : '';
				$price_typ = isset( $item['_price_type'] ) && '' != $item['_price_type'] ? $item['_price_type'] : '';
				$def       = '';
				if ( is_array( $data_edit ) && isset( $data_edit[ $item['_id'] ] ) ) {
					$def = $data_edit[ $item['_id'] ];
				}
				echo '<input class="wrs-options" value="' . esc_attr( $def ) . '" type="number" min="0" name="wr_options_' . esc_attr( $i ) . '" data-price="' . esc_attr( $price_ta ) . '" data-type="' . esc_attr( $price_typ ) . '" placeholder="0" />';
			} elseif ( 'textarea' == $type ) {
				$price_ta  = isset( $item['_price'] ) && '' != $item['_price'] ? woores_convert_number_decimal_comma( $item['_price'] ) : '';
				$price_typ = isset( $item['_price_type'] ) && '' != $item['_price_type'] ? $item['_price_type'] : '';
				$def       = '';
				if ( is_array( $data_edit ) && isset( $data_edit[ $item['_id'] ] ) ) {
					$def = $data_edit[ $item['_id'] ];
				}
				echo '<textarea class="wrs-options" name="wr_options_' . esc_attr( $i ) . '" data-price="' . esc_attr( $price_ta ) . '" data-type="' . esc_attr( $price_typ ) . '"/>' . esc_attr( $def ) . '</textarea>';
			} elseif ( ! empty( $options ) ) {
				foreach ( $options as $key => $value ) {
					$op_name = isset( $value['name'] ) ? $value['name'] : '';
					$dis_ck  = isset( $value['dis'] ) ? $value['dis'] : '';
					if ( is_array( $data_edit ) ) {
						$def_ck = isset( $data_edit[ $item['_id'] ][ $key ] ) ? 'yes' : '';
					} else {
						$def_ck = isset( $value['def'] ) && 'yes' != $dis_ck ? $value['def'] : '';
					}
					$op_val  = isset( $value['price'] ) ? woores_convert_number_decimal_comma( $value['price'] ) : '';
					$op_typ  = isset( $value['type'] ) ? $value['type'] : '';
					$op_name = '' != $op_val ? $op_name . ' ' . $plus_sign . ' ' . wc_price( $op_val ) : $op_name;
					$id_op   = 'ckid-' . rand( 1, 1000 ) . '-' . $el_id . '-' . rand( 1, 10000 );

					echo '<span><input class="wrs-options" type="checkbox" name="wr_options_' . esc_attr( $i ) . '[]" id="' . esc_attr( $id_op ) . '" value="' . esc_attr( $key ) . '" data-price="' . esc_attr( $op_val ) . '" data-def="' . esc_attr( $def_ck ) . '" data-type="' . esc_attr( $op_typ ) . '" ' . checked( $def_ck, 'yes', false ) . ' ' . disabled( $dis_ck, 'yes', false ) . '><label for="' . esc_attr( $id_op ) . '">' . wp_kses_post( $op_name ) . '</label></span>';
				}
			}
			if ( '' != $required ) {
				echo '<p class="wrs-required-message">' . esc_html__( 'This option is required', 'product-options-addon' ) . '</p>';
			}
			if ( 'checkbox' == $type && is_numeric( $min_req ) && $min_req > 0 ) {
				/* translators: %s is replaced with the min required */
				echo '<p class="wrs-required-min-message">' . sprintf( esc_html__( 'Please choose at least %s options.', 'woorestaurant' ), esc_attr( $min_req ) ) . '</p>';
			}
			if ( 'checkbox' == $type && is_numeric( $max_req ) && $max_req > 0 ) {
				/* translators: %s is replaced with the max required */
				echo '<p class="wrs-required-max-message">' . sprintf( esc_html__( 'You only can select max %s options.', 'woorestaurant' ), esc_attr( $max_req ) ) . '</p>';
			}
					echo '</div>
				</div>';
			++$i;
			echo '</div>';
		}
		if ( '' != $logic_js || '' != $extralg_js ) {
			echo '<script type="text/javascript">
				jQuery(document).ready(function() {
					var $wr_variation = jQuery("input.variation_id").val();
					if($wr_variation!="" && $wr_variation!=0){
						' . esc_attr( $logic_js ) . '
					}
					' . esc_attr( $extralg_js ) . '
					if(jQuery("#' . esc_attr( $wrid ) . ' .wrsf-offrq").length ){ 
						jQuery("#' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options:not([type=radio]):not([type=checkbox])").val("").trigger("change");
						jQuery("#' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options[type=radio], #' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options[type=checkbox]").prop("checked", false).trigger("change");
					}
				});
				jQuery( document ).on( "found_variation.first", function ( e, variation ) {
				});
				jQuery( ".variations_form" ).on( "woocommerce_variation_select_change", function () {
					setTimeout(function(){ 
						var $wr_variation = jQuery("input.variation_id").val();
						if($wr_variation=="" ){
							jQuery("#' . esc_attr( $wrid ) . ' .wrs-logic-on").fadeOut();
							jQuery("#' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options:not([type=radio]):not([type=checkbox])").val("").trigger("change");
							jQuery("#' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options[type=radio], #' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options[type=checkbox]").prop("checked", false).trigger("change");
						}
					}, 100);
				});	
				jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
					var $wr_variation = variation.variation_id;
					' . esc_attr( $logic_js ) . '
					jQuery("#' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options:not([type=radio]):not([type=checkbox])").val("").trigger("change");
					jQuery("#' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options[type=radio], #' . esc_attr( $wrid ) . ' .wrsf-offrq .wrs-options[type=checkbox]").prop("checked", false).trigger("change");
				});';
				do_action( 'woores_after_logicjs_op' );
			echo '	
			</script>';
		}
		do_action( 'woores_after_product_options' );
		echo '</div>';
		if ( '1' == $show_more ) {
			echo '<div class="woores-showmore"><span>' . esc_html__( 'Show extra options', 'product-options-addon' ) . '<span></div>';
		}
	}
}
add_action( 'woocommerce_before_add_to_cart_button', 'woo_restaurantdisplay_custom_field' );

/**
 * Validate the text field.
 *
 * @param bool $passed it is the check.
 * @param int  $product_id it is the product id.
 * @param int  $quantity it is the quantity.
 * @param int  $variation_id it is the variation id.
 */
function woores_validate_custom_field( $passed, $product_id, $quantity, $variation_id = false ) {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'validate-field' ) ) {
			return;
		}
	}

	if ( isset( $_POST['wrsf-up-cartitem'] ) && '' != $_POST['wrsf-up-cartitem'] ) {
		WC()->cart->set_quantity( wp_kses_post( wp_unslash( $_POST['wrsf-up-cartitem'] ) ), 0 );
	}
	$vari_pro = false;
	if ( is_numeric( $variation_id ) && $variation_id > 0 ) {
		$variation  = wc_get_product( $variation_id );
		$product_id = $variation->get_parent_id();
		$vari_pro   = true;
	} elseif ( 'product_variation' == get_post_type( $product_id ) ) {
		$variation    = wc_get_product( $product_id );
		$variation_id = $variation->get_parent_id();
		$product_id   = $variation->get_parent_id();
		$vari_pro     = true;
	}
	$data_options = woo_restaurantget_options( $product_id );

	$msg = '';
	if ( is_array( $data_options ) && ! empty( $data_options ) ) {
		foreach ( $data_options as $key => $options ) {
			$rq        = isset( $options['_required'] ) ? $options['_required'] : '';
			// phpcs:ignore
			$data_exts = isset( $_POST[ 'wr_options_' . $key ] ) ? $_POST[ 'wr_options_' . $key ] : '';
			$type      = isset( $options['_type'] ) && '' != $options['_type'] ? $options['_type'] : 'checkbox';
			if ( ( 'checkbox' == $type || 'select' == $type || 'radio' == $type ) && ! empty( $data_exts ) && is_array( $data_exts ) ) {
				foreach ( $data_exts as $k => $opc ) {
					if ( isset( $options['_options'][ $opc ]['dis'] ) && ( 'yes' == $options['_options'][ $opc ]['dis'] ) ) {
						unset( $data_exts[ $k ] );
					}
				}
				$data_exts = array_values( $data_exts );
			}
			$min_req = 'checkbox' == $type && isset( $options['_min_op'] ) && '' != $options['_min_op'] ? $options['_min_op'] : 0;
			$max_req = 'checkbox' == $type && isset( $options['_max_op'] ) && '' != $options['_max_op'] ? $options['_max_op'] : 0;

			$enb_logic = isset( $options['_enb_logic'] ) ? $options['_enb_logic'] : '';
			if ( 'on' == $enb_logic ) {
				$tlogic  = isset( $options['_con_tlogic'] ) ? $options['_con_tlogic'] : '';
				$c_logic = isset( $options['_con_logic'] ) ? $options['_con_logic'] : '';
				if ( is_array( $c_logic ) && ! empty( $c_logic ) ) {
					$c_or     = array();
					$c_and    = array();
					$vali_con = true;
					foreach ( $c_logic as $key_lg => $c_lg_val ) {
						$c_val      = isset( $c_lg_val['val'] ) ? $c_lg_val['val'] : '';
						$c_val      = explode( '-', $c_val );
						$c_val      = $c_val[0];
						$c_type_con = isset( $c_lg_val['type_con'] ) ? $c_lg_val['type_con'] : '';
						$c_type_op  = isset( $c_lg_val['type_op'] ) ? $c_lg_val['type_op'] : '';
						if ( ( '' == $c_type_op || 'varia' == $c_type_op ) && true == $vari_pro ) {
							if ( 'is_not' == $c_type_con ) {
								if ( 'hide' == $tlogic && $c_val != $variation_id ) {
									$rq      = 'no';
									$min_req = 0;
									$max_req = 0;
									unset( $_POST[ 'wr_options_' . $key ] );
								} elseif ( '' == $tlogic && $c_val == $variation_id ) {
									$rq      = 'no';
									$min_req = 0;
									$max_req = 0;
									unset( $_POST[ 'wr_options_' . $key ] );
								}
							} elseif ( 'hide' == $tlogic && $c_val == $variation_id ) {
									$rq      = 'no';
									$min_req = 0;
									$max_req = 0;
									unset( $_POST[ 'wr_options_' . $key ] );
							} elseif ( '' == $tlogic ) {
								if ( $c_val != $variation_id ) {
									$vali_con = false;
								} else {
									$vali_con = true;
									break;
								}
							}
						} elseif ( '' != $c_type_op ) {
							$findk = '';
							if ( function_exists( 'array_column' ) ) {
								$findk = array_search( $c_type_op, array_column( $data_options, '_id' ) );
							} else {
								foreach ( $data_options as $keyfn => $optionfn ) {
									if ( $optionfn['_id'] === $c_type_op ) {
										$findk = $keyfn;
										break;
									}
								}
							}

							if ( 'is_not' == $c_type_con ) {
								if ( 'hide' == $tlogic && ( ! isset( $_POST[ 'wr_options_' . $findk ] ) || ( isset( $_POST[ 'wr_options_' . $findk ] ) && is_array( $_POST[ 'wr_options_' . $findk ] ) && ! in_array( $c_val, $_POST[ 'wr_options_' . $findk ] ) ) ) ) {
									$rq      = 'no';
									$min_req = 0;
									$max_req = 0;
									unset( $_POST[ 'wr_options_' . $key ] );
								} elseif ( '' == $tlogic && isset( $_POST[ 'wr_options_' . $findk ] ) && is_array( $_POST[ 'wr_options_' . $findk ] ) && in_array( $c_val, $_POST[ 'wr_options_' . $findk ] ) ) {
									$rq      = 'no';
									$min_req = 0;
									$max_req = 0;
									unset( $_POST[ 'wr_options_' . $key ] );
								}
							} elseif ( 'hide' == $tlogic && isset( $_POST[ 'wr_options_' . $findk ] ) && is_array( $_POST[ 'wr_options_' . $findk ] ) && in_array( $c_val, $_POST[ 'wr_options_' . $findk ] ) ) {
									$rq      = 'no';
									$min_req = 0;
									$max_req = 0;
									unset( $_POST[ 'wr_options_' . $key ] );
							} elseif ( '' == $tlogic ) {
								if ( ! isset( $_POST[ 'wr_options_' . $findk ] ) || ( isset( $_POST[ 'wr_options_' . $findk ] ) && is_array( $_POST[ 'wr_options_' . $findk ] ) && ! in_array( $c_val, $_POST[ 'wr_options_' . $findk ] ) ) ) {
									$vali_con = false;
								} else {
									$vali_con = true;
									break;
								}
							}
						}
					}
					if ( false == $vali_con ) {
						$rq      = 'no';
						$min_req = 0;
						$max_req = 0;
						unset( $_POST[ 'wr_options_' . $key ] );
					}
				}
			}
			$rq = apply_filters( 'woores_required_option', $rq, $options, $key );
			if ( is_array( $data_exts ) && 1 == count( $data_exts ) && '' == $data_exts[0] ) {
				$data_exts = '';
			}
			$c_item = ! empty( $data_exts ) && is_array( $data_exts ) ? count( $data_exts ) : 0;
			if ( ( 'yes' == $rq && ( '' == $data_exts || empty( $data_exts ) ) ) || ( $min_req > 0 && $min_req > $c_item ) || ( $max_req > 0 && $max_req < $c_item ) ) {
				$passed = false;
				wc_add_notice( __( 'Please re-check all required fields and try again', 'product-options-addon' ), 'error' );
				break;
			}
		}
	}
	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'woores_validate_custom_field', 10, 4 );


/**
 * Add the text field as item data to the cart object.
 *
 * @param array $cart_item_data it is the cart item data.
 * @param int   $product_id it is the product id.
 */
function woores_add_custom_field_item_data( $cart_item_data, $product_id ) {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'add-field' ) ) {
			return;
		}
	}

	$data_options = woo_restaurantget_options( $product_id );
	$c_options    = array();

	if ( is_array( $data_options ) && ! empty( $data_options ) ) {
		$data_edit = array();
		foreach ( $data_options as $key => $options ) {
			// phpcs:ignore
			$data_exts = isset( $_POST[ 'wr_options_' . $key ] ) ? $_POST[ 'wr_options_' . $key ] : '';
			if ( isset( $options['_type'] ) && ( 'text' == $options['_type'] || 'textarea' == $options['_type'] || 'quantity' == $options['_type'] ) ) {
				$price_op = isset( $options['_price'] ) ? woores_convert_number_decimal_comma( $options['_price'] ) : '';
				if ( '' != $data_exts ) {
					$type_price = isset( $options['_price_type'] ) ? $options['_price_type'] : '';
					if ( 'quantity' == $options['_type'] ) {
						$price_op = floatval( $price_op ) * $data_exts;
					}
					$c_options[]                  = array(
						'name'          => sanitize_text_field( $options['_name'] ),
						'value'         => $data_exts,
						'type_of_price' => $type_price,
						'price'         => floatval( $price_op ),
						'_type'         => $options['_type'],
						'_datas'        => $options,
					);
					$data_edit[ $options['_id'] ] = $data_exts;
				}
			} elseif ( is_array( $data_exts ) && ! empty( $data_exts ) ) {
				foreach ( $data_exts as $value ) {
					if ( '' != $value ) {
						$price_op                               = isset( $options['_options'][ $value ]['price'] ) ? woores_convert_number_decimal_comma( $options['_options'][ $value ]['price'] ) : '';
						$type_price                             = isset( $options['_options'][ $value ]['type'] ) ? $options['_options'][ $value ]['type'] : '';
						$c_options[]                            = array(
							'name'          => sanitize_text_field( $options['_name'] ),
							'value'         => $options['_options'][ $value ]['name'],
							'type_of_price' => $type_price,
							'price'         => floatval( $price_op ),
							'plu'           => sanitize_text_field( $options['_options'][ $value ]['plu'] ),

							'_type'         => isset( $options['_type'] ) ? $options['_type'] : '',
							'_datas'        => $options,
						);
						$data_edit[ $options['_id'] ][ $value ] = $value;
					}
				}
			}
			$cart_item_data['data_edit'] = $data_edit;
		}
		$cart_item_data['wrsoptions'] = $c_options;
	}
	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'woores_add_custom_field_item_data', 10, 2 );

/**
 * Add custom field item data.
 *
 * @param array  $cart_item_data it is the cart item data.
 * @param object $product it is the product object.
 * @param object $order it is the order object.
 */
function woores_add_custom_field_item_data_again( $cart_item_data, $product, $order ) {
	remove_filter( 'woocommerce_add_to_cart_validation', 'woores_validate_custom_field', 10 );
	if ( isset( $product['item_meta']['_wrsoptions'] ) && is_array( $product['item_meta']['_wrsoptions'] ) ) {
		$cart_item_data['wrsoptions'] = $product['item_meta']['_wrsoptions'];
	}
	return $cart_item_data;
}
add_filter( 'woocommerce_order_again_cart_item_data', 'woores_add_custom_field_item_data_again', 11, 3 );

add_filter( 'woocommerce_add_cart_item', 'woore_update_total_price_item', 30, 1 );
/**
 * Update total price item.
 *
 * @param array $cart_item it is the cart item.
 */
function woore_update_total_price_item( $cart_item ) {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'total-price' ) ) {
			return;
		}
	}

	if ( isset( $cart_item['wrsoptions'] ) && is_array( $cart_item['wrsoptions'] ) ) {
		$price = (float) $cart_item['data']->get_price( 'edit' );
		$qty   = $cart_item['quantity'];
		if ( isset( $_POST['action'] ) && isset( $_POST['key'] ) && 'woore_update_quantity' == $_POST['action'] && $_POST['key'] == $cart_item['key'] ) {
			if ( isset( $_POST['quantity'] ) ) {
				$qty = wp_kses_post( wp_unslash( $_POST['quantity'] ) );
			}
		}
		foreach ( $cart_item['wrsoptions'] as $option ) {
			if ( $option['price'] ) {
				if ( 'fixed' == $option['type_of_price'] ) {
					$price += (float) $option['price'] / $qty;
				} else {
					$price += (float) $option['price'];
				}
			}
		}
		$cart_item['data']->set_price( $price );
	}
	return $cart_item;
}

add_filter( 'woocommerce_get_cart_item_from_session', 'woore_update_total_from_session', 20, 2 );
/**
 * Update total from session.
 *
 * @param array  $cart_item it is the cart item array.
 * @param string $values it is having values.
 */
function woore_update_total_from_session( $cart_item, $values ) {
	if ( isset( $cart_item['wrsoptions'] ) && is_array( $cart_item['wrsoptions'] ) ) {
		$cart_item = woore_update_total_price_item( $cart_item );
	}
	return $cart_item;
}

add_filter( 'woocommerce_get_item_data', 'woore_show_option_in_cart', 11, 2 );
/**
 * Show option in cart.
 *
 * @param array $other_data it is the other data.
 * @param array $cart_item it is the cart item.
 */
function woore_show_option_in_cart( $other_data, $cart_item ) {
	if ( isset( $cart_item['wrsoptions'] ) && is_array( $cart_item['wrsoptions'] ) ) {
		$show_sgline = apply_filters( 'woore_show_options_single_line', 'no' );
		if ( 'yes' != $show_sgline ) {
			foreach ( $cart_item['wrsoptions'] as $option ) {
				$char_j = ' + ';
				if ( isset( $option['_type'] ) && 'quantity' == $option['_type'] ) {
					$char_j = ' x ';}
				$char_j = apply_filters( 'woores_plus_sign_char', $char_j, $option );
				if ( isset( $option['_type'] ) && 'quantity' == $option['_type'] ) {
					$price_s = isset( $option['price'] ) && '' != $option['price'] ? $option['value'] . $char_j . wc_price( woores_price_display( $option['price'] ) / $option['value'] ) : $option['value'];
				} else {
					$price_s = isset( $option['price'] ) && '' != $option['price'] ? $option['value'] . $char_j . wc_price( woores_price_display( $option['price'] ) ) : $option['value'];
				}
				$price_s      = apply_filters( 'woores_price_show_inorder', $price_s, $option, $cart_item );
				$name_opt     = apply_filters( 'woores_oname_show_inorder', $option['name'], $option, $cart_item );
				$other_data[] = array(
					'name'  => $name_opt,
					'value' => $price_s,
				);
			}
		} else {
			$grouped_types = array();
			foreach ( $cart_item['wrsoptions'] as $type ) {
				$grouped_types[ $type['name'] ][] = $type;
			}
			foreach ( $grouped_types as $key => $option_tp ) {
				if ( is_array( $option_tp ) ) {
					$price_a = '';
					$i       = 0;
					foreach ( $option_tp as $option_it ) {
						++$i;
						$name   = $option_it['name'];
						$char_j = ' + ';
						if ( isset( $option_it['_type'] ) && 'quantity' == $option_it['_type'] ) {
							$char_j = ' x ';
						}
						$char_j   = apply_filters( 'woores_plus_sign_char', $char_j, $option_it );
						$price_s  = isset( $option_it['price'] ) && '' != $option_it['price'] ? $option_it['value'] . $char_j . wc_price( woores_price_display( $option_it['price'] ) ) : $option_it['value'];
						$price_s  = apply_filters( 'woores_price_show_inorder', $price_s, $option_it, $cart_item );
						$price_a .= $price_s;
						if ( $i > 0 && $i < count( $option_tp ) ) {
							$price_a .= ', ';
						}
					}
					$name_opt     = apply_filters( 'woores_oname_show_inorder', $option_it['name'], $option_tp, $cart_item );
					$other_data[] = array(
						'name'  => $name_opt,
						'value' => $price_a,
					);
				}
			}
		}
	}
	return $other_data;
}

add_action( 'woocommerce_after_cart_item_name', 'woore_edit_extra_options', 12, 2 );
/**
 * To edit extra options.
 *
 * @param array $cart_item it is the cart items.
 * @param array $cart_item_key it is the cart item key.
 */
function woore_edit_extra_options( $cart_item, $cart_item_key ) {
	if ( isset( $cart_item['wrsoptions'] ) && ! empty( $cart_item['wrsoptions'] ) ) {
		$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
		echo '<a class="wrsf-edit-options" data-key="' . esc_attr( $cart_item_key ) . '" data-id_food="' . esc_attr( $product_id ) . '" href="javascript:;">' . esc_html__( 'Edit Options', 'woorestaurant' ) . '</a>';
	}
}

add_filter( 'woocommerce_widget_cart_item_quantity', 'woore_minicart_edit_extra_options', 99, 3 );
/**
 * Minicart edit extra options.
 *
 * @param html  $html it is the html.
 * @param array $cart_item it is the cart items.
 * @param array $cart_item_key it is the cart item key.
 */
function woore_minicart_edit_extra_options( $html, $cart_item, $cart_item_key ) {
	if ( isset( $cart_item['wrsoptions'] ) && ! empty( $cart_item['wrsoptions'] ) ) {
		$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
		$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
		return '<a class="wrsf-edit-options" data-key="' . esc_attr( $cart_item_key ) . '" data-id_food="' . esc_attr( $product_id ) . '" href="javascript:;">' . esc_html__( 'Edit Options', 'woorestaurant' ) . '</a>' . $html;
	}
	return $html;
}

add_action( 'woocommerce_before_cart', 'woore_edit_product_option_on_popup' );
/**
 * Edit product option on popup.
 */
function woore_edit_product_option_on_popup() {
	echo '<div class="wrs-hidden">' . do_shortcode( '[woores_view_ordbutton product_id="-1" cart_enable="no"]' ) . '</div>';
}

add_filter( 'woocommerce_quantity_input_args', 'woores_defaut_qty', 99, 2 );
/**
 * Default Quantity.
 *
 * @param array  $args it is the argument.
 * @param object $product it is the product object.
 */
function woores_defaut_qty( $args, $product ) {
	global $cart_itemkey;
	if ( '' != $cart_itemkey ) {
		$cart = WC()->cart->get_cart();
		if ( isset( $cart[ $cart_itemkey ] ) ) {
			$args['input_value'] = $cart[ $cart_itemkey ]['quantity'];
		}
	}
	return $args;
}

add_filter( 'woocommerce_product_get_default_attributes', 'woores_defaut_variation_attribute', 10, 2 );
/**
 * Default variation attribute.
 *
 * @param array  $default_attributes it is the default attributes.
 * @param object $product it is the product object.
 */
function woores_defaut_variation_attribute( $default_attributes, $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return $default_attributes;
	}
	global $cart_itemkey;
	if ( '' != $cart_itemkey ) {
		$cart = WC()->cart->get_cart();
		if ( isset( $cart[ $cart_itemkey ] ) ) {
			$item_data  = $cart[ $cart_itemkey ]['data'];
			$attributes = $item_data->get_attributes();
			if ( is_array( $attributes ) && ! empty( $attributes ) ) {
				$default_attributes = $attributes;
			}
		}
	}
	return $default_attributes;
}

/**
 * Add option to order object.
 *
 * @param object $item it is the item object.
 * @param array  $cart_item_key it is the cart item key.
 * @param array  $values it is the values.
 * @param object $order it is the order object.
 */
function woore_add_options_to_order( $item, $cart_item_key, $values, $order ) {
	if ( isset( $values['wrsoptions'] ) && is_array( $values['wrsoptions'] ) ) {
		$show_sgline = apply_filters( 'woore_show_options_single_line', 'no' );
		if ( 'yes' != $show_sgline ) {
			foreach ( $values['wrsoptions'] as $option ) {
				$char_j = '+';
				if ( isset( $option['_type'] ) && 'quantity' == $option['_type'] ) {
					$char_j = 'x';
				}
				$char_j = apply_filters( 'woores_plus_sign_char', $char_j, $option );
				$name   = isset( $option['price'] ) && '' != $option['price'] ? strip_tags( $option['name'] . ' (' . $char_j . wc_price( woores_price_display( $option['price'] ) ) . ')' ) : $option['name'];
				$name   = apply_filters( 'woores_name_show_inorder', $name, $option, $values );
				$ovalue = apply_filters( 'woores_ovalue_show_inorder', $option['value'], $option, $values );
				$item->add_meta_data( $name, $option['value'] );
			}
		} else {
			$grouped_types = array();
			foreach ( $values['wrsoptions'] as $type ) {
				$grouped_types[ $type['name'] ][] = $type;
			}
			foreach ( $grouped_types as $key => $option_tp ) {
				if ( is_array( $option_tp ) ) {
					$price_a = '';
					$i       = 0;
					foreach ( $option_tp as $option_it ) {
						++$i;
						$name   = $option_it['name'];
						$char_j = ' + ';
						if ( isset( $option_it['_type'] ) && 'quantity' == $option_it['_type'] ) {
							$char_j = ' x ';
						}
						$char_j   = apply_filters( 'woores_plus_sign_char', $char_j, $option );
						$price_s  = isset( $option_it['price'] ) && '' != $option_it['price'] ? $option_it['value'] . $char_j . wc_price( ( $option_it['price'] ) ) : $option_it['value'];
						$price_s  = apply_filters( 'woores_price_show_inorder', $price_s, $option_it, $values );
						$price_a .= $price_s;
						if ( $i > 0 && $i < count( $option_tp ) ) {
							$price_a .= ', ';
						}
					}
					$name_opt = apply_filters( 'woores_oname_show_inorder', $option_it['name'], $option_it, $values );
					$item->add_meta_data( $option_it['name'], $price_a );
				}
			}
		}
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'woore_add_options_to_order', 10, 4 );

add_action( 'woocommerce_new_order_item', 'woore_add_options_order_item_meta', 10, 2 );
/**
 * Add options order item meta.
 *
 * @param int    $item_id it is the item id.
 * @param object $item it is the item object.
 */
function woore_add_options_order_item_meta( $item_id, $item ) {
	if ( is_object( $item ) && isset( $item->legacy_values ) ) {
		$values = $item->legacy_values;
		if ( isset( $values['wrsoptions'] ) && ! empty( $values['wrsoptions'] ) ) {
			wc_add_order_item_meta( $item_id, '_wrsoptions', $values['wrsoptions'] );
		}
	}
}

/**
 * Register order status deliver.
 */
function register_order_status_deliverect() {
	register_post_status(
		'wc-preparing',
		array(
			'label'                     => __( 'Preparing', 'text-domain' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			/* translators: %s is replaced with the label count */
			'label_count'               => _n_noop( 'Preparing <span class="count">(%s)</span>', 'Preparing <span class="count">(%s)</span>', 'text-domain' ),
		)
	);
	register_post_status(
		'wc-prepared',
		array(
			'label'                     => __( 'Prepared', 'text-domain' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			/* translators: %s is replaced with the label count */
			'label_count'               => _n_noop( 'Prepared<span class="count">(%s)</span>', 'Prepared <span class="count">(%s)</span>', 'text-domain' ),
		)
	);

	register_post_status(
		'wc-ready-to-pickup',
		array(
			'label'                     => __( 'Ready to pickup', 'text-domain' ),
			'public'                    => true,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			/* translators: %s is replaced with the label count */
			'label_count'               => _n_noop( 'Ready to pickup <span class="count">(%s)</span>', 'Ready to pickup <span class="count">(%s)</span>', 'text-domain' ),
		)
	);
}
add_action( 'init', 'register_order_status_deliverect' );

/**
 * Add custom order bulk statuses.
 *
 * @param array $order_statuses it is having order statuses.
 */
function deliverect_add_custom_order_statuses_to_bulk_actions( $order_statuses ) {
	$new_order_statuses = array();
	foreach ( $order_statuses as $key => $status ) {
		$new_order_statuses[ $key ] = $status;
	}
	$new_order_statuses['wc-ready-to-pickup'] = _x( 'Ready to pickup', 'Ready to pickup', 'text_domain' );
	$new_order_statuses['wc-preparing']       = _x( 'Preparing', 'Preparing', 'text_domain' );
	$new_order_statuses['wc-prepared']        = _x( 'Prepared', 'Prepared', 'text_domain' );
	return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'deliverect_add_custom_order_statuses_to_bulk_actions' );

/**
 * Include custom order status to reports.
 *
 * @param array $actions it is having actions.
 */
function deliverect_include_custom_order_status_to_reports( $actions ) {
	$new_actions = array();

	foreach ( $actions as $key => $action ) {
		$new_actions[ $key ] = $action;
		if ( 'mark_processing' === $key ) {
			$key;
		}
	}
	$new_actions['mark_ready-to-pickup'] = __( 'Move to ready for pickup', 'woocommerce' );
	$new_actions['mark_preparing']       = __( 'Move to Preparing', 'woocommerce' );
	$new_actions['mark_prepared']        = __( 'Move to Prepared', 'woocommerce' );

	return $new_actions;
}
add_filter( 'bulk_actions-edit-shop_order', 'deliverect_include_custom_order_status_to_reports', 20, 1 );

/**
 * Deliver woocommerce oroder ready move statuses.
 *
 * @param array $statuses it is having statuses.
 */
function deliverect_woocommerce_order_is_ready_to_move_statuses( $statuses ) {
	$statuses[] = 'ready-to-pickup';
	$statuses[] = 'preparing';
	$statuses[] = 'prepared';
	return $statuses;
}
add_filter( 'woocommerce_order_is_paid_statuses', 'deliverect_woocommerce_order_is_ready_to_move_statuses' );

