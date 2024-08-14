<?php
/**
 * This is the Min Max Quantity File.
 *
 * @package woorestaurant
 */

/**
 * This is the Min Max Qunatity class.
 */
class WooResta_Minmax_Quantity {
	/**
	 * Constructor Method.
	 */
	public function __construct() {
		add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'meta_options' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_options' ), 10, 2 );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variation_settings_fields' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_settings_fields' ), 10, 2 );
		add_action( 'woo_restaurant_before_shortcode', array( $this, 'js_verify' ), 10, 2 );
		add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'js_verify' ), 10, 2 );
		add_filter( 'woocommerce_quantity_input_args', array( $this, 'input_args' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 100, 5 );
		add_filter( 'woocommerce_update_cart_validation', array( $this, 'update_cart_validation' ), 100, 4 );
		add_filter( 'woocommerce_available_variation', array( $this, 'load_variation_settings_fields' ), 15, 3 );
	}

	/**
	 * This is meta option function.
	 */
	public function meta_options() {
		echo '<div class="options_group">';
		woocommerce_wp_text_input(
			array(
				'id'                => 'woore_minquantity',
				'value'             => get_post_meta( get_the_ID(), 'woore_minquantity', true ),
				'label'             => esc_html__( 'Min quantity', 'woorestaurant' ),
				'desc_tip'          => true,
				'description'       => esc_html__( 'Set Min quantity for this product', 'woorestaurant' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
					'min'  => '1',
				),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => 'woore_maxquantity',
				'value'             => get_post_meta( get_the_ID(), 'woore_maxquantity', true ),
				'label'             => esc_html__( 'Max quantity', 'woorestaurant' ),
				'desc_tip'          => true,
				'description'       => esc_html__( 'Set Max quantity for this product', 'woorestaurant' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '1',
				),
			)
		);
		echo '</div>';
	}

	/**
	 * This is save option function.
	 *
	 * @param int    $id this is the option id.
	 * @param object $post it is the post object.
	 */
	public function save_options( $id, $post ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'save-option' ) ) {
				return;
			}
		}

		if ( isset( $_POST['woore_minquantity'] ) ) {
			update_post_meta( $id, 'woore_minquantity', wp_kses_post( wp_unslash( $_POST['woore_minquantity'] ) ) );
		}
		if ( isset( $_POST['woore_maxquantity'] ) ) {
			update_post_meta( $id, 'woore_maxquantity', wp_kses_post( wp_unslash( $_POST['woore_maxquantity'] ) ) );
		}
	}

	/**
	 * These are variation settings fields function.
	 *
	 * @param object $loop it is the loop of fields.
	 * @param array  $variation_data it is the variation data.
	 * @param object $variation it is the object of variation.
	 */
	public function variation_settings_fields( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input(
			array(
				'id'                => 'woore_minquantity[' . $variation->ID . ']',
				'label'             => esc_html__( 'Minimum quantity', 'woorestaurant' ),
				'desc_tip'          => 'true',
				'wrapper_class'     => 'form-row form-row-first',
				'placeholder'       => esc_html__( 'Enter number', 'woorestaurant' ),
				'description'       => esc_html__( 'Set Min quantity for this variation', 'woorestaurant' ),
				'value'             => get_post_meta( $variation->ID, 'woore_minquantity', true ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '1',
				),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => 'woore_maxquantity[' . $variation->ID . ']',
				'label'             => esc_html__( 'Maximum quantity', 'woorestaurant' ),
				'desc_tip'          => 'true',
				'wrapper_class'     => 'form-row form-row-last',
				'placeholder'       => esc_html__( 'Enter number', 'woorestaurant' ),
				'description'       => esc_html__( 'Set Max quantity for this variation', 'woorestaurant' ),
				'value'             => get_post_meta( $variation->ID, 'woore_maxquantity', true ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '1',
				),
			)
		);
	}

	/**
	 * To save variation settings fields.
	 *
	 * @param int $post_id it is the post id.
	 */
	public function save_variation_settings_fields( $post_id ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'variation-setting' ) ) {
				return;
			}
		}

		if ( isset( $_POST['woore_minquantity'][ $post_id ] ) ) {
			$woore_minquantity = wp_kses_post( wp_unslash( $_POST['woore_minquantity'][ $post_id ] ) );
		}
		if ( isset( $woore_minquantity ) ) {
			update_post_meta( $post_id, 'woore_minquantity', wp_kses_post( wp_unslash( $woore_minquantity ) ) );
		}
		if ( isset( $_POST['woore_maxquantity'][ $post_id ] ) ) {
			$woore_maxquantity = wp_kses_post( wp_unslash( $_POST['woore_maxquantity'][ $post_id ] ) );
		}
		if ( isset( $woore_maxquantity ) ) {
			update_post_meta( $post_id, 'woore_maxquantity', wp_kses_post( wp_unslash( $woore_maxquantity ) ) );
		}
	}

	/**
	 * To get variation settings fields.
	 *
	 * @param array  $data it is having the variation settings fields.
	 * @param object $product it is the object of the product.
	 * @param object $_product_vari it is having the variation object.
	 *
	 * @return array
	 */
	public function load_variation_settings_fields( $data, $product, $_product_vari ) {
		$data['woore_minquantity'] = get_post_meta( $data['variation_id'], 'woore_minquantity', true );
		$data['min_qty']           = $data['woore_minquantity'];
		$data['woore_maxquantity'] = get_post_meta( $data['variation_id'], 'woore_maxquantity', true );
		$data['max_qty']           = $data['woore_maxquantity'];
		return $data;
	}

	/**
	 * It is having the html of min max data.
	 */
	public function data_minmax() {
		$woore_minquantity  = get_post_meta( get_the_ID(), 'woore_minquantity', true );
		$woore_maxquantity  = get_post_meta( get_the_ID(), 'woore_maxquantity', true );
		$_sold_individually = get_post_meta( get_the_ID(), '_sold_individually', true );
		if ( 'yes' != $_sold_individually ) {
			echo '
			<input type="hidden" name="woore_minq" value="' . esc_attr( $woore_minquantity ) . '">
			<input type="hidden" name="woore_maxq" value="' . esc_attr( $woore_maxquantity ) . '">';
		}
	}

	/**
	 * To enqueue script.
	 */
	public function js_verify() {
		wp_enqueue_script( 'wrsf-minmax-quantity' );
	}

	/**
	 * It is having the input arguments.
	 *
	 * @param array  $args it is the argument of input fields.
	 * @param object $product it is having the object of the product.
	 *
	 * @return array
	 */
	public function input_args( $args, $product ) {
		$_sold_individually = get_post_meta( get_the_ID(), '_sold_individually', true );
		if ( 'yes' == $_sold_individually ) {
			return $args;
		}
		$woore_minquantity = get_post_meta( get_the_ID(), 'woore_minquantity', true );
		$woore_maxquantity = get_post_meta( get_the_ID(), 'woore_maxquantity', true );
		if ( '' != $woore_minquantity && $woore_minquantity > 0 ) {
			$args['min_value']   = $woore_minquantity;
			$args['input_value'] = $woore_minquantity;
		}
		if ( '' != $woore_maxquantity && $woore_maxquantity > 0 ) {
			$args['max_value'] = $woore_maxquantity;
		}
		if ( method_exists( $product, 'managing_stock' ) && $product->managing_stock() && ! $product->backorders_allowed() ) {
			$stock             = $product->get_stock_quantity();
			$args['max_value'] = min( $stock, $args['max_value'] );
		}
		return $args;
	}

	/**
	 * To validate add to cart.
	 *
	 * @param bool   $passed it is the check to pass cart.
	 * @param int    $product_id it is the product id.
	 * @param int    $quantity it is the cart quantity.
	 * @param int    $variation_id it is the variation id.
	 * @param object $variations it is the object of the variation.
	 *
	 * @return bool
	 */
	public function add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'add-to-cart-validation' ) ) {
				return;
			}
		}

		if ( isset( $_POST['wrsf-up-cartitem'] ) && '' != $_POST['wrsf-up-cartitem'] ) {
			WC()->cart->set_quantity( wp_kses_post( wp_unslash( $_POST['wrsf-up-cartitem'] ) ), 0 );
		}
		$id           = $product_id;
		$woore_varmax = '';
		$woore_varmin = '';
		if ( is_numeric( $variation_id ) && $variation_id > 0 ) {
			$id           = $variation_id;
			$woore_varmin = get_post_meta( $id, 'woore_minquantity', true );
			$woore_varmax = get_post_meta( $id, 'woore_maxquantity', true );
		}
		$woore_minquantity = '' != $woore_varmin ? $woore_varmin : get_post_meta( $product_id, 'woore_minquantity', true );
		$woore_maxquantity = '' != $woore_varmax ? $woore_varmax : get_post_meta( $product_id, 'woore_maxquantity', true );
		$already_in_cart   = self::get_cart_qty( $product_id );
		$product_title     = get_the_title( $id );

		if ( '' != $woore_maxquantity && $woore_maxquantity > 0 ) {
			if ( ( $already_in_cart + $quantity ) > $woore_maxquantity ) {
				$passed = false;
				if ( $already_in_cart > 0 ) {
					wc_add_notice(
						sprintf(
							/* translators: %1$s is replaced with the max quantity, %2$s is replaced with the product title, %3$s is replaced with the cart link, %4$s is replaced with the cart check */
							esc_html__( 'You only can add a maximum of %1$s %2$s\'s to %3$s. You already have %4$s.', 'woorestaurant' ),
							$woore_maxquantity,
							$product_title,
							'<a href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'your cart', 'woorestaurant' ) . '</a>',
							$already_in_cart
						),
						'error'
					);
				} else {
					/* translators: %s is replaced with the max quantity */
					wc_add_notice( sprintf( esc_html__( 'Please select a value no more than %s.', 'woorestaurant' ), $woore_maxquantity ), 'error' );
				}
			}
		}
		if ( '' != $woore_minquantity && $woore_minquantity > 0 ) {
			if ( ( $already_in_cart + $quantity ) < $woore_minquantity ) {
				/* translators: %s is replaced with the min quantity */
				wc_add_notice( sprintf( esc_html__( 'Please select a value no less than %s.', 'woorestaurant' ), $woore_minquantity ), 'error' );
				$passed = false;
			}
		}
		return $passed;
	}

	/**
	 * To update cart validation.
	 *
	 * @param bool   $passed to pass the update of the cart.
	 * @param string $cart_item_key it is the key of the cart.
	 * @param array  $values it is the values on the cart.
	 * @param int    $quantity it is the quantity of the cart.
	 *
	 * @return bool
	 */
	public function update_cart_validation( $passed, $cart_item_key, $values, $quantity ) {
		$_product     = apply_filters( 'woocommerce_cart_item_product', $values['data'], $values, $cart_item_key );
		$product_id   = $_product->get_id();
		$woore_varmax = '';
		$woore_varmin = '';
		if ( $_product->get_parent_id() ) {
			$woore_varmin = get_post_meta( $_product->get_parent_id(), 'woore_minquantity', true );
			$woore_varmax = get_post_meta( $_product->get_parent_id(), 'woore_maxquantity', true );
		}
		$woore_minquantity = '' != $woore_varmin ? $woore_varmin : get_post_meta( $product_id, 'woore_minquantity', true );
		$woore_maxquantity = '' != $woore_varmax ? $woore_varmax : get_post_meta( $product_id, 'woore_maxquantity', true );

		$already_in_cart = self::get_cart_qty( $product_id, $cart_item_key );
		$product_title   = get_the_title( $product_id );
		if ( '' != $woore_maxquantity && $woore_maxquantity > 0 ) {
			if ( ( $already_in_cart + $quantity ) > $woore_maxquantity ) {
				$passed = false;
				if ( $already_in_cart > 0 ) {
					wc_add_notice(
						sprintf(
							/* translators: %1$s is replaced with the max quantity, %2$s is replaced with the product title, %3$s is replaced with the cart link */
							esc_html__( 'You only can add a maximum of %1$s %2$s\'s to %3$s', 'woorestaurant' ),
							$woore_maxquantity,
							$product_title,
							esc_html__( 'your cart', 'woorestaurant' ),
							$already_in_cart
						),
						'error'
					);
				} else {
					/* translators: %s is replaced with the max quantity */
					wc_add_notice( sprintf( esc_html__( 'Please select a value no more than %s.', 'woorestaurant' ), $woore_maxquantity ), 'error' );
				}
			}
		}
		if ( '' != $woore_minquantity && $woore_minquantity > 0 ) {
			if ( ( $already_in_cart + $quantity ) < $woore_minquantity ) {
				/* translators: %s is replaced with the min quantity */
				wc_add_notice( sprintf( esc_html__( 'Please select a value no less than %s.', 'woorestaurant' ), $woore_minquantity ), 'error' );
				$passed = false;
			}
		}
		return $passed;
	}

	/**
	 * To get the quantity of the cart.
	 *
	 * @param int    $product_id it is the id of the product.
	 * @param string $key it is the key of the cart.
	 *
	 * @return int
	 */
	public function get_cart_qty( $product_id, $key = false ) {
		global $woocommerce;
		$running_qty = 0;
		foreach ( $woocommerce->cart->get_cart() as $other_cart_item_keys => $values ) {
			if ( isset( $key ) && '' != $key ) {
				if ( $key == $other_cart_item_keys ) {
					continue;
				}
			}
			if ( $product_id == $values['product_id'] ) {
				$running_qty += (int) $values['quantity'];
			}
		}

		return $running_qty;
	}
}
$woo_resta_minmax_quantity = new WooResta_Minmax_Quantity();
