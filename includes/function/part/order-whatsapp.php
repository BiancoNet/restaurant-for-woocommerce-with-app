<?php
/**
 * Order whatsapp file.
 *
 * @package woorestaurant
 */

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_setting_whatsapp', 24 );
/**
 * Tip setting.
 */
function woo_restaurant_register_setting_whatsapp() {
	$args = array(
		'id'           => 'woo_restaurant_whatsapp',
		'menu_title'   => '',
		'object_types' => array( 'options-page' ),
		'option_key'   => 'woo_restaurant_whatsapp_options',
		'parent_slug'  => 'woo_restaurant_advanced_options',
		'tab_group'    => 'woo_restaurant_options',
		'capability'   => 'manage_woocommerce',
		'srz_parent'   => 'woo_restaurant_advanced_options',
		'tab_title'    => esc_html__( 'Order On whatsapp', 'woorestaurant' ),
	);
	// 'tab_group' property is supported in > 2.4.0.
	if ( version_compare( WRMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'woo_restaurant_options_display_with_tabs';
	}
	$whatsapp_options = new_wrmb2_box( $args );
	$whatsapp_options->add_field(
		array(
			'name'       => esc_html__( 'Whatsapp number', 'woorestaurant' ),
			'desc'       => esc_html__( 'Enter Whatsapp number in International format, example: +12124567890', 'woorestaurant' ),
			'id'         => 'woo_restaurant_whatsapp_nb',
			'type'       => 'text',
			'escape_cb'  => '',
			'before_row' => 'woore_ot_add_adv_time_html',
		)
	);

	$whatsapp_options->add_field(
		array(
			'name'      => esc_html__( 'Message title', 'woorestaurant' ),
			'desc'      => esc_html__( 'Enter Message title', 'woorestaurant' ),
			'id'        => 'woo_restaurant_whatsapp_mes',
			'type'      => 'text',
			'escape_cb' => '',
		)
	);
	$whatsapp_options->add_field(
		array(
			'name'             => esc_html__( 'Whatsapp number by location', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enable whatsapp number setting by location', 'woorestaurant' ),
			'id'               => 'woo_restaurant_whatsapp_loc',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
			'escape_cb'        => '',
		)
	);
}

add_action( 'woore_admin_adv_settings_tab_html', 'woore_adv_whatsapp_settings_tab_html', 31, 2 );
/**
 * WhatsApp tab setting HTML.
 *
 * @param html   $html it is the html of the tab.
 * @param string $tab it is the tab name.
 *
 * @return html
 */
function woore_adv_whatsapp_settings_tab_html( $html, $tab ) {
	$html .= ' | <a href="?page=woo_restaurant_whatsapp_options" class="' . ( 'woo_restaurant_whatsapp_options' == $tab ? 'current' : '' ) . '">' . esc_html__( 'Order On whatsapp', 'woorestaurant' ) . '</a>';
	return $html;
}

add_action( 'wrmb2_admin_init', 'woore_whatsapp_meta_loc' );
/**
 * WhatsApp metalog.
 */
function woore_whatsapp_meta_loc() {
	$whatsapp_loc = woo_restaurant_get_option( 'woo_restaurant_whatsapp_loc', 'woo_restaurant_whatsapp_options' );
	if ( 'yes' !== $whatsapp_loc ) {
		return;
	}
	$prefix = 'wrsp_loc_';
	/**
	 * Metabox to add fields to categories and tags.
	 */
	$woore_log_meta = new_wrmb2_box(
		array(
			'id'               => $prefix . 'data',
			'title'            => esc_html__( 'Category Metabox', 'woorestaurant' ), // Doesn't output for term boxes.
			'object_types'     => array( 'term' ), // Tells WRMB2 to use term_meta vs post_meta.
			'taxonomies'       => array( 'woorestaurant_loc' ), // Tells WRMB2 which taxonomies should have these fields.
			'new_term_section' => true, // Will display in the "Add New Category" section.
		)
	);
	$woore_log_meta->add_field(
		array(
			'name' => esc_html__( 'Whatsapp number', 'woorestaurant' ),
			'id'   => $prefix . 'whatsapp_nb',
			'desc' => esc_html__( 'Enter Whatsapp number in International format to receive order for this location, example: +12124567890', 'woorestaurant' ),
			'type' => 'text',
		)
	);
}

add_action( 'woore_sidecart_after_content', 'woore_whatsapp_by_loc_refresh' );
/**
 * WhatApp by loc refresh.
 */
function woore_whatsapp_by_loc_refresh() {
	if ( isset( $_GET['loc'] ) ) {
		$whatsapp_loc = woo_restaurant_get_option( 'woo_restaurant_whatsapp_loc', 'woo_restaurant_whatsapp_options' );
		if ( 'yes' == $whatsapp_loc ) {
			echo '<div class="wrsf-whastsapp-byloc wrs-hidden">' . wp_kses_post( woore_add_whatsapp_order_button( true ) ) . '</div>';
		}
	}
}

/**
 * WhatsApp order button.
 *
 * @param bool $rtbt this is the check.
 *
 * @return URL
 */
function woore_add_whatsapp_order_button( $rtbt = false ) {
    $whatsapp     = woo_restaurant_get_option( 'woo_restaurant_whatsapp_nb', 'woo_restaurant_whatsapp_options' );
    $mes          = woo_restaurant_get_option( 'woo_restaurant_whatsapp_mes', 'woo_restaurant_whatsapp_options' );
    $whatsapp_loc = woo_restaurant_get_option( 'woo_restaurant_whatsapp_loc', 'woo_restaurant_whatsapp_options' );
    if ( 'yes' == $whatsapp_loc ) {
        $loc_selected    = woore_get_loc_selected();
        $whatsapp_nb_loc = get_term_meta( $loc_selected, 'wrsp_loc_whatsapp_nb', true );
        if ( '' != $whatsapp_nb_loc ) {
            $whatsapp = $whatsapp_nb_loc;
        }
    }
    if ( '' == $whatsapp || 'off' == $whatsapp ) {
        return;
    }
    $html_item_details = '';
    $i                 = 0;
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
        ++$i;
        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

            $html_item_details .= $i . '. ' . $_product->get_name() . "\n";
            $options            = wc_get_formatted_cart_item_data( $cart_item, true );
            if ( '' != $options ) {
                $options            = html_entity_decode( $options, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 );
                $html_item_details .= $options;
            }
            $html_item_details .= esc_html__( 'Price: ', 'woorestaurant' ) . html_entity_decode( apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) . "\n";
            $html_item_details .= esc_html__( 'Qty: ', 'woorestaurant' ) . $cart_item['quantity'] . "\n";
            $html_item_details .= esc_html__( 'Total: ', 'woorestaurant' ) . html_entity_decode( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) . "\n";
        }
    }
    if ( '' == $mes ) {
        if ( 1 == $i ) {
            $htext  = esc_html__( 'Hello! I want to order following product: ', 'woorestaurant' ) . "\n";
            $htext .= '------------------------------' . "\n";
        } else {
            $htext  = esc_html__( 'Hello! I want to order following products: ', 'woorestaurant' ) . "\n";
            $htext .= '------------------------------' . "\n";
        }
    } else {
        $htext = $mes . "\n";
    }
    $html_item_details  = $htext . $html_item_details;
    $html_item_details .= '------------------------------' . "\n";
    $user_odmethod      = WC()->session->get( '_user_order_method' );
    if ( '' != $user_odmethod ) {
        $user_odmethod      = 'takeaway' == $user_odmethod ? esc_html__( 'Takeaway', 'woorestaurant' ) : ( 'dinein' == $user_odmethod ? esc_html__( 'Dine-in', 'woorestaurant' ) : esc_html__( 'Delivery', 'woorestaurant' ) );
        $html_item_details .= esc_html__( 'Order method', 'woorestaurant' ) . ': ' . $user_odmethod . "\n";
    }
    $html_item_details .= esc_html__( 'Subtotal: ', 'woorestaurant' ) . html_entity_decode( WC()->cart->get_cart_subtotal(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) . "\n";
    foreach ( WC()->cart->get_fees() as $fee ) :
        $cart_totals_fee_html = WC()->cart->display_prices_including_tax() ? wc_price( $fee->total + $fee->tax ) : wc_price( $fee->total );
        $html_item_details   .= esc_html( $fee->name ) . ': ' . html_entity_decode( apply_filters( 'woocommerce_cart_totals_fee_html', $cart_totals_fee_html, $fee ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) . "\n";
    endforeach;

    $def_sp_price = isset( WC()->session->get( 'cart_totals' )['shipping_total'] ) ? WC()->session->get( 'cart_totals' )['shipping_total'] : '';
    if ( $def_sp_price > 0 && WC()->cart->needs_shipping() ) {
        $html_item_details .= esc_html__( 'Shipping fee', 'woorestaurant' ) . ': ' . html_entity_decode( wc_price( $def_sp_price ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) . "\n";
    }
    $value = WC()->cart->get_total();
    // If prices are tax inclusive, show taxes here.
    if ( wc_tax_enabled() && WC()->cart->display_prices_including_tax() ) {
        $tax_string_array = array();
        $cart_tax_totals  = WC()->cart->get_tax_totals();
        if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
            foreach ( $cart_tax_totals as $code => $tax ) {
                $tax_string_array[] = sprintf( '%s %s', $tax->formatted_amount, $tax->label );
            }
        } elseif ( ! empty( $cart_tax_totals ) ) {
            $tax_string_array[] = sprintf( '%s %s', wc_price( WC()->cart->get_taxes_total( true, true ) ), WC()->countries->tax_or_vat() );
        }
        if ( ! empty( $tax_string_array ) ) {
            $taxable_address = WC()->customer->get_taxable_address();
            if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
                $country = WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ];
                /* translators: %1$s is replaced with the tax string array, %2$s is replaced with the country */
                $tax_text = wp_kses_post( sprintf( __( '(includes %1$s estimated for %2$s)', 'woocommerce' ), implode( ', ', $tax_string_array ), $country ) );
            } else {
                /* translators: %s is replaced with the tax string array */
                $tax_text = wp_kses_post( sprintf( __( '(includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) ) );
            }
            $value .= '<small class="includes_tax">' . $tax_text . '</small>';
        }
    }
    $html_item_details .= esc_html__( 'Total: ', 'woorestaurant' ) . html_entity_decode( apply_filters( 'woocommerce_cart_totals_order_total_html', $value ), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401 ) . "\n";

    $html_item_details = apply_filters( 'woore_whatsapp_message', $html_item_details );
    $link              = 'https://wa.me/' . ( '' != $whatsapp && '+' != $whatsapp ? $whatsapp : '' ) . '/?text=' . urlencode( strip_tags( $html_item_details ) );
    $link              = '<div class="wrsf-order-whastsapp"><p class="woocommerce-mini-cart__buttons buttons"><a href="' . $link . '" target="_blank" class="button wrsf-button"><i class="ion-social-whatsapp-outline"></i>' . esc_html__( 'Order on Whatsapp', 'woorestaurant' ) . '</a></p></div>';
    if ( isset( $rtbt ) && true == $rtbt ) {
        return $link;
    } else {
        echo wp_kses_post( $link );
    }
}

add_action( 'woocommerce_widget_shopping_cart_after_buttons', 'woore_add_whatsapp_order_button', 99 );
add_action( 'woocommerce_proceed_to_checkout', 'woore_add_whatsapp_order_button', 99 );


