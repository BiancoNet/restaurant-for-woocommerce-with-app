<?php
/**
 * This is the food order bt shortcode file.
 *
 * @package woorestaurant
 */

/**
 * This is the order button shortcode.
 *
 * @param array $atts it is the attribute parameter of this function.
 */
function woo_restaurant_shortcode_order_button( $atts ) {
	if ( phpversion() >= 7 ) {
		$atts = (array) $atts;}
	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;}
	$html_cart   = '';
	$cart_enable = isset( $atts['cart_enable'] ) ? $atts['cart_enable'] : '';
	if ( 'no' != $cart_enable ) {
		global $wrscart_html;
		if ( 'on' != $wrscart_html || 'yes' == $cart_enable ) {
			$wrscart_html = 'on';
			$html_cart    = woo_restaurant_woo_cart_icon_html( $cart_enable );
		}
	}
	global $id;
	// phpcs:ignore
	$id = isset( $atts['ID'] ) && '' != $atts['ID'] ? $atts['ID'] : 'wrs-' . rand( 10, 9999 );
	if ( ! isset( $atts['ID'] ) ) {
		$atts['ID'] = $id;
	}
	$style        = isset( $atts['style'] ) && '' != $atts['style'] ? $atts['style'] : '1';
	$product_id   = isset( $atts['product_id'] ) ? str_replace( ' ', '', $atts['product_id'] ) : '';
	$show_price   = isset( $atts['show_price'] ) ? str_replace( ' ', '', $atts['show_price'] ) : '';
	$enable_modal = isset( $atts['enable_modal'] ) ? $atts['enable_modal'] : '';
	$class        = isset( $atts['class'] ) ? $atts['class'] : '';
	$location     = isset( $atts['location'] ) ? $atts['location'] : '';
	$enb_mnd      = isset( $atts['enb_mnd'] ) ? $atts['enb_mnd'] : '';
	// remove space.
	$product_id = preg_replace( '/\s+/', '', $product_id );
	if ( ! is_numeric( $product_id ) ) {
		return;
	}
	$args = woo_restaurant_query( '', 1, '', 'post__in', '', '', '', '', $product_id, '', '', '', '', '', '' );
	if ( '-1' == $product_id ) {
		$args['post__in'] = array( '-1' );}
	$the_query = new WP_Query( $args );
	ob_start();
	$class = $class . ' wrs-food-plug ';
	$class = $class . ' ordbutton-' . $style;
	if ( 'no' == $enable_modal ) {
		$class = $class . ' wrffdisable-modal';
	}
	$html_modal = '';
	wp_enqueue_script( 'wc-add-to-cart-variation' );
	if ( 'no' != $enable_modal && 'yes' === get_option( 'woocommerce_enable_reviews', 'yes' ) ) {
		wp_enqueue_script( 'wrsf-reviews' );
	}
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
		$GLOBALS['Product_Addon_Display']->addon_scripts();
	}
	do_action( 'woo_restaurant_before_shortcode' );
	wp_enqueue_style( 'ionicon' );
	?>
	<div class="wrs-fdlist wrsf-order-button <?php echo esc_attr( $class ); ?>" id ="<?php echo esc_attr( $id ); ?>">
		<?php
		do_action( 'woore_before_shortcode_content', $atts );
		if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
			$loc_selected = WC()->session->get( 'wr_userloc' );
			if ( '' != $location && $loc_selected != $location ) {
				WC()->session->set( 'wr_userloc', $location );
			}
			echo "<input type='hidden' name='food_loc' value='" . esc_attr( $location ) . "'/>";
		}
		echo '' != $html_cart ? esc_attr( $html_cart ) : '';
		if ( function_exists( 'woore_select_date_html' ) ) {
			woore_select_date_html( '', $enb_mnd );}
		?>
		<div class="parent_grid">	
		<div class="ctgrid">
		<?php
		$num_pg  = '';
		$arr_ids = array();
		if ( $the_query->have_posts() ) {
			$i      = 0;
			$it     = $the_query->found_posts;
			$num_pg = 1;
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				global $product;
				if ( 'no' != $show_price ) {
					add_filter( 'woocommerce_product_single_add_to_cart_text', 'woore_add_price_button' );
					add_filter( 'woore_order_button_text', 'woore_add_price_button' );
				}
				$disable_is_visible = apply_filters( 'woore_disable_is_visible', 'no', $product );
				if ( 'yes' == $disable_is_visible || $product && $product->is_visible() ) {
					$arr_ids[] = get_the_ID();
					echo '<div class="item-grid" data-id="wr_id-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '" data-id_food="' . esc_attr( get_the_ID() ) . '" id="ctc-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '"><a href="' . esc_attr( Woo_restuaran_customlink( get_the_ID() ) ) . '"></a>';
						woore_custom_color( 'grid', $style, 'ctc-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) );
						echo '<div class="wrsf-orbt">';
					woo_restaurant_booking_button_html( 1 );
					echo '</div>';
					echo '</div>';
				}
				if ( 'no' != $show_price ) {
					remove_filter( 'woocommerce_product_single_add_to_cart_text', 'woore_add_price_button' );
					remove_filter( 'woore_order_button_text', 'woore_add_price_button' );
				}
			}
		}
		?>
		</div>
		</div>
		<!-- Modal ajax -->
		<?php
		global $modal_html;
		if ( ! isset( $modal_html ) || 'on' != $modal_html || 'yes' == $enable_modal ) {
			$modal_html = 'on';
			echo "<div id='food_modal' class='wr_modal'></div>";
		}
		?>
		<?php
		woo_restaurant_ajax_navigate_html( $id, $atts, $num_pg, $args, $arr_ids );
		?>
	</div>
	<?php
	wp_reset_postdata();
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}

add_shortcode( 'woores_view_ordbutton', 'woo_restaurant_shortcode_order_button' );
/**
 * Add price button function.
 *
 * @param string $text it is the text of the button.
 */
function woore_add_price_button( $text ) {
	global $product;
	$id_food      = $product->get_id();
	$custom_price = get_post_meta( $id_food, 'woo_restaurant_custom_price', true );
	$price        = woo_restaurant_price_with_currency( $id_food );
	if ( '' != $custom_price ) {
		$price = $custom_price;
	}
	return apply_filters( 'woore_price_on_button', $text . ' (' . strip_tags( $price ) . ')', $text );
}

add_action( 'after_setup_theme', 'wr_reg_wfood_order_button_vc' );
/**
 * To register order button.
 */
function wr_reg_wfood_order_button_vc() {
	if ( function_exists( 'vc_map' ) ) {
		vc_map(
			array(
				'name'     => esc_html__( 'Order button', 'woorestaurant' ),
				'base'     => 'woores_view_ordbutton',
				'class'    => '',
				'icon'     => '',
				'controls' => 'full',
				'category' => esc_html__( 'Woocommerce Food', 'woorestaurant' ),
				'params'   => array(
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'ID', 'woorestaurant' ),
						'param_name'  => 'product_id',
						'value'       => '',
						'description' => esc_html__( 'Enter specify food ID to display order button', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Enable cart', 'woorestaurant' ),
						'param_name'  => 'cart_enable',
						'value'       => array(
							esc_html__( 'Default', 'woorestaurant' ) => '',
							esc_html__( 'Yes', 'woorestaurant' ) => 'yes',
							esc_html__( 'No', 'woorestaurant' )  => 'no',
						),
						'description' => esc_html__( 'Enable side cart icon', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Show price', 'woorestaurant' ),
						'param_name'  => 'show_price',
						'value'       => array(
							esc_html__( 'Yes', 'woorestaurant' ) => '',
							esc_html__( 'No', 'woorestaurant' )  => 'no',
						),
						'description' => esc_html__( 'Show price on button', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Class name', 'woorestaurant' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_html__( 'add a class name and refer to it in custom CSS', 'woorestaurant' ),
					),
				),
			)
		);
	}
}
