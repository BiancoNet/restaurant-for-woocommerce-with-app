<?php
/**
 * This is the tip file.
 *
 * @package woorestaurant
 */

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_setting_tip', 24 );
/**
 * Tip setting.
 */
function woo_restaurant_register_setting_tip() {
	$args = array(
		'id'           => 'woo_restaurant_tip',
		'menu_title'   => '',
		'object_types' => array( 'options-page' ),
		'option_key'   => 'woo_restaurant_tip_options',
		'parent_slug'  => 'woo_restaurant_advanced_options',
		'srz_parent'   => 'woo_restaurant_advanced_options',
		'tab_group'    => 'woo_restaurant_options',
		'capability'   => 'manage_woocommerce',
		'tab_title'    => esc_html__( 'Order Tip', 'woorestaurant' ),
	);
	// 'tab_group' property is supported in > 2.4.0.
	if ( version_compare( WRMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'woo_restaurant_options_display_with_tabs';
	}
	$tip_options = new_wrmb2_box( $args );
	$tip_options->add_field(
		array(
			'name'             => esc_html__( 'Enable Order Tip', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enable Tip feature on checkout page', 'woorestaurant' ),
			'id'               => 'woo_restaurant_enb_tip',
			'type'             => 'select',
			'show_option_none' => false,
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
			'before_row'       => 'woore_ot_add_adv_time_html',
		)
	);
	$tip_options->add_field(
		array(
			'name'             => esc_html__( 'Order Tip position', 'woorestaurant' ),
			'desc'             => esc_html__( 'Choose position of order tip', 'woorestaurant' ),
			'id'               => 'woo_restaurant_pos_tip',
			'type'             => 'select',
			'show_option_none' => false,
			'options'          => array(
				''                                  => esc_html__( 'Before checkout form', 'woorestaurant' ),
				'woocommerce_checkout_after_customer_details' => esc_html__( 'After customer details', 'woorestaurant' ),
				'woocommerce_checkout_order_review' => esc_html__( 'After order review', 'woorestaurant' ),
			),
			'before_row'       => '',
		)
	);
	$tip_options->add_field(
		array(
			'name'      => esc_html__( 'Title', 'woorestaurant' ),
			'desc'      => esc_html__( 'Enter tip title, default: Tips', 'woorestaurant' ),
			'id'        => 'woo_restaurant_tip_title',
			'type'      => 'text',
			'escape_cb' => '',
		)
	);
	$tip_options->add_field(
		array(
			'name'      => esc_html__( 'Button label', 'woorestaurant' ),
			'desc'      => esc_html__( 'Enter label of Button, default: Add', 'woorestaurant' ),
			'id'        => 'woo_restaurant_tip_lbad',
			'type'      => 'text',
			'escape_cb' => '',
		)
	);
	$tip_options->add_field(
		array(
			'name'      => esc_html__( 'Remove Button label', 'woorestaurant' ),
			'desc'      => esc_html__( 'Enter label of Button, default: Remove', 'woorestaurant' ),
			'id'        => 'woo_restaurant_tip_lbrm',
			'type'      => 'text',
			'escape_cb' => '',
		)
	);
}

add_action( 'woore_admin_adv_settings_tab_html', 'woore_adv_tip_settings_tab_html', 30, 2 );
/**
 * Tip setting tab HTML.
 *
 * @param html   $html this is the html of the tab.
 * @param string $tab this is the name of the tab.
 */
function woore_adv_tip_settings_tab_html( $html, $tab ) {
	$html .= ' | <a href="?page=woo_restaurant_tip_options" class="' . ( 'woo_restaurant_tip_options' == $tab ? 'current' : '' ) . '">' . esc_html__( 'Order Tip', 'woorestaurant' ) . '</a>';
	return $html;
}
$tip_alloptions = get_option( 'woo_restaurant_tip_options' );
$pos_of_tip     = isset( $tip_alloptions['woo_restaurant_pos_tip'] ) && '' != $tip_alloptions['woo_restaurant_pos_tip'] ? $tip_alloptions['woo_restaurant_pos_tip'] : 'woocommerce_before_checkout_form';
// $t = woo_restaurant_get_option('woo_restaurant_pos_tip','woo_restaurant_tip_options');

add_action( $pos_of_tip, 'woore_tip_form_html', 15 );
/**
 * Tip form HTML.
 */
function woore_tip_form_html() {
	$tip = woo_restaurant_get_option( 'woo_restaurant_enb_tip', 'woo_restaurant_tip_options' );
	if ( 'yes' != $tip ) {
		return;
	}
	$title = woo_restaurant_get_option( 'woo_restaurant_tip_title', 'woo_restaurant_tip_options' );
	$addlb = woo_restaurant_get_option( 'woo_restaurant_tip_lbad', 'woo_restaurant_tip_options' );
	$remlb = woo_restaurant_get_option( 'woo_restaurant_tip_lbrm', 'woo_restaurant_tip_options' );
	wp_enqueue_script( 'wrsf-tip', WOORESTAURANT_ASSETS . 'js/tip.js', array( 'jquery' ), '1.0' );
	?>
	<div class="wrsf-tip-form">
		<?php
		if ( 'off' != $title ) {
			echo '<div class="wrsf-tip-title">' . ( '' == $title ? esc_html__( 'Tips', 'woorestaurant' ) : wp_kses_post( $title ) ) . '</div>';
		}
		$plachd = apply_filters( 'woore_tip_hoder', '(' . get_woocommerce_currency_symbol() . ')' )
		?>
		<input type="text" name="wrsf-tip" placeholder="<?php echo esc_attr( $plachd ); ?>">
		<input type="button" name="wrsf-add-tip" value="<?php echo ( '' != $addlb ? wp_kses_post( $addlb ) : esc_html__( 'Add', 'woorestaurant' ) ); ?>">
		<input type="button" name="wrsf-remove-tip" value="<?php echo ( '' != $remlb ? wp_kses_post( $remlb ) : esc_html__( 'Remove', 'woorestaurant' ) ); ?>">
		<div class="wrsf-tip-error"><?php esc_html_e( 'Please enter a valid number', 'woorestaurant' ); ?></div>
	</div>
	<?php
}

add_action( 'wp_ajax_woore_update_tip', 'ajax_woore_update_tip' );
add_action( 'wp_ajax_nopriv_woore_update_tip', 'ajax_woore_update_tip' );
/**
 * Ajax update tip.
 */
function ajax_woore_update_tip() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'user-tip-fee' ) ) {
			return;
		}
	}

	$tip = 0;
	if ( isset( $_POST['tip'] ) ) {
		$tip = wp_kses_post( wp_unslash( $_POST['tip'] ) );
	}
	WC()->session->set( '_user_tip_fee', $tip );
}

add_action( 'woocommerce_cart_calculate_fees', 'woore_update_tip_fee' );
/**
 * Update tip fee.
 */
function woore_update_tip_fee() {
	global $woocommerce;
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	$_user_tip_fee = WC()->session->get( '_user_tip_fee' );
	if ( $_user_tip_fee > 0 ) {
		$tax_fee = apply_filters( 'woore_tip_fee_tax', false );
		$title   = woo_restaurant_get_option( 'woo_restaurant_tip_title', 'woo_restaurant_tip_options' );
		$woocommerce->cart->add_fee( ( '' == $title || 'off' == $title ? esc_html__( 'Tips', 'woorestaurant' ) : $title ), $_user_tip_fee, $tax_fee, '' );
	}
}
