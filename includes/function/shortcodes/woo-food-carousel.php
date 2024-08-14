<?php
/**
 * It is the shortcode file for food carousel.
 *
 * @package woorestaurant
 */

/**
 * It is the shortcode carousel function.
 *
 * @param array $atts it is the attribute variable.
 */
function woo_restaurant_shortcode_carousel( $atts ) {
	if ( phpversion() >= 7 ) {
		$atts = (array) $atts;
	}
	if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
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
	global $id, $number_excerpt, $img_size, $location, $hide_atc;
	// phpcs:ignore
	$id = isset( $atts['ID'] ) && '' != $atts['ID'] ? $atts['ID'] : 'wrs-' . rand( 10, 9999 );
	if ( ! isset( $atts['ID'] ) ) {
		$atts['ID'] = $id;
	}
	$style          = isset( $atts['style'] ) && '' != $atts['style'] ? $atts['style'] : '1';
	$column         = '2';
	$posttype       = 'wr_food';
	$ids            = isset( $atts['ids'] ) ? str_replace( ' ', '', $atts['ids'] ) : '';
	$taxonomy       = isset( $atts['taxonomy'] ) ? $atts['taxonomy'] : '';
	$cat            = isset( $atts['cat'] ) ? $atts['cat'] : '';
	$tag            = isset( $atts['tag'] ) ? $atts['tag'] : '';
	$count          = isset( $atts['count'] ) && '' != $atts['count'] ? $atts['count'] : '9';
	$order          = isset( $atts['order'] ) ? $atts['order'] : '';
	$orderby        = isset( $atts['orderby'] ) ? $atts['orderby'] : '';
	$meta_key       = isset( $atts['meta_key'] ) ? $atts['meta_key'] : '';
	$meta_value     = isset( $atts['meta_value'] ) ? $atts['meta_value'] : '';
	$class          = isset( $atts['class'] ) ? $atts['class'] : '';
	$number_excerpt = isset( $atts['number_excerpt'] ) && '' != $atts['number_excerpt'] ? $atts['number_excerpt'] : '10';
	$slidesshow     = isset( $atts['slidesshow'] ) && '' != $atts['slidesshow'] ? $atts['slidesshow'] : '3';
	$slidesscroll   = isset( $atts['slidesscroll'] ) && '' != $atts['slidesscroll'] ? $atts['slidesscroll'] : '';
	$autoplay       = isset( $atts['autoplay'] ) && 1 == $atts['autoplay'] ? 1 : 0;
	$autoplayspeed  = isset( $atts['autoplayspeed'] ) && is_numeric( $atts['autoplayspeed'] ) ? $atts['autoplayspeed'] : '';
	$start_on       = isset( $atts['start_on'] ) ? $atts['start_on'] : '';
	$loading_effect = isset( $atts['loading_effect'] ) ? $atts['loading_effect'] : '';
	$infinite       = isset( $atts['infinite'] ) ? $atts['infinite'] : '';
	$enable_modal   = isset( $atts['enable_modal'] ) ? $atts['enable_modal'] : '';
	$featured       = isset( $atts['featured'] ) ? $atts['featured'] : '';
	$location       = isset( $atts['location'] ) ? $atts['location'] : '';
	$img_size       = isset( $atts['img_size'] ) ? $atts['img_size'] : '';
	$hide_atc       = isset( $atts['hide_atc'] ) ? $atts['hide_atc'] : '';
	$enb_mnd        = isset( $atts['enb_mnd'] ) ? $atts['enb_mnd'] : '';
	if ( 'yes' == $hide_atc ) {
		$atts['enable_mtod'] = 'no';}
	// remove space.
	$cat = preg_replace( '/\s+/', '', $cat );
	$ids = preg_replace( '/\s+/', '', $ids );

	$args      = woo_restaurant_query( $posttype, $count, $order, $orderby, $cat, $tag, $taxonomy, $meta_key, $ids, $meta_value, '', '', '', $featured, $location );
	$the_query = new WP_Query( $args );
	$total_it  = $the_query->found_posts;
	if ( $total_it < 1 ) {
		return;}
	ob_start();
	$class = $class . ' style-' . $style;

	$class = $class . ' wrs-food-plug ';
	if ( 1 == $loading_effect ) {
		$class = $class . ' ld-screen';
	}
	// if ( ! woore_check_open_close_time() ) {
		// $class = $class." wrsfd-out-open-time";
	// }.
	if ( 'no' == $enable_modal ) {
		$class = $class . ' wrffdisable-modal';
	}
	$class = 'yes' == $hide_atc ? $class . ' wrsf-listing-mode' : $class;
	if ( '' == $slidesscroll ) {
		$slidesscroll = $slidesshow;
	}
	$class      = 1 == $total_it ? $class . ' wrsf-column-1' : $class;
	$html_modal = '';
	wp_enqueue_script( 'wc-add-to-cart-variation' );
	if ( 'no' != $enable_modal && 'yes' === get_option( 'woocommerce_enable_reviews', 'yes' ) ) {
		wp_enqueue_script( 'wrsf-reviews' );
	}
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
		$GLOBALS['Product_Addon_Display']->addon_scripts();
	}
	wp_enqueue_style( 'ionicon' );
	wp_enqueue_style( 'wpwrs-wr_s_lick', WOORESTAURANT_ASSETS . 'js/wr_s_lick/wr_s_lick.css' );
	wp_enqueue_style( 'wpwrs-wr_s_lick-theme', WOORESTAURANT_ASSETS . 'js/wr_s_lick/wr_s_lick-theme.css' );
	wp_enqueue_script( 'wpwrs-wr_s_lick', WOORESTAURANT_ASSETS . 'js/wr_s_lick/wr_s_lick.js', array( 'jquery' ) );
	$woo_restaurant_enable_rtl = woo_restaurant_get_option( 'woo_restaurant_enable_rtl' );
	if ( is_rtl() ) {
		$woo_restaurant_enable_rtl = 'yes';
	}

	do_action( 'woo_restaurant_before_shortcode' );
	?>
	<div 
	<?php
	if ( 'yes' == $woo_restaurant_enable_rtl ) {
		echo 'dir="rtl"';
	}
	?>
	class="wrs-fdlist wrs-fdcarousel <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $id ); ?>" data-autoplay="<?php echo esc_attr( $autoplay ); ?>" data-speed="<?php echo esc_attr( $autoplayspeed ); ?>" data-rtl="<?php echo esc_attr( $woo_restaurant_enable_rtl ); ?>" data-slidesshow="<?php echo esc_attr( $slidesshow ); ?>" data-slidesscroll="<?php echo esc_attr( $slidesscroll ); ?>"  data-start_on="<?php echo esc_attr( $start_on ); ?>" data-infinite="<?php echo esc_attr( $infinite ); ?>" data-mobile_item="<?php echo esc_attr( apply_filters( 'wrswf_mobile_nbitem', 1 ) ); ?>">
		<?php
		do_action( 'woore_before_shortcode_content', $atts );
		if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
			$loc_selected = WC()->session->get( 'wr_userloc' );
			if ( '' != $location && $loc_selected != $location ) {
				WC()->session->set( 'wr_userloc', $location );
			}
			echo "<input type='hidden' name='food_loc' value='" . esc_attr( $location ) . "'/>";
		}
		if ( 'OFF' != $location ) {
			woo_restaurant_select_location_html( $location );
		}
		?>
		<?php
		// phpcs:ignore
		echo '' != $html_cart ? ( $html_cart ) : '';
		?>
		<?php
		echo '<input type="hidden"  name="ajax_url" value="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '">';
		echo '<input type="hidden" id="param_shortcode" name="param_shortcode" value="' . esc_attr( json_encode( $atts ) ) . '">';
		if ( 1 == $loading_effect ) {
			?>
			<div class="wrsfd-loadcont"><div class="wrsfd-loadicon"></div></div>
			<?php
		}
		if ( function_exists( 'woore_select_date_html' ) ) {
			woore_select_date_html( '', $enb_mnd );
		}
		?>
		<div class="parent_grid">
		<div class="ctgrid">
		<?php
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				global $product;
				$disable_is_visible = apply_filters( 'woore_disable_is_visible', 'no', $product );
				if ( 'yes' == $disable_is_visible || $product && $product->is_visible() ) {
					echo '<div class="item-grid" data-id="wr_id-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '" data-id_food="' . esc_attr( get_the_ID() ) . '" id="ctc-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) . '"> ';
					woore_custom_color( 'grid', $style, 'ctc-' . esc_attr( $id ) . '-' . esc_attr( get_the_ID() ) );
					?>
					<div class="exp-arrow">
						<?php
						woo_restaurant_template_plugin( 'grid-' . $style, 1 );
						?>
						<div class="wooresfd_clearfix"></div>
					</div>
					<?php
					echo '</div>';
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
	</div>
	<?php
	wp_reset_postdata();
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}

add_shortcode( 'woores_view_carousel', 'woo_restaurant_shortcode_carousel' );
add_action( 'after_setup_theme', 'wr_reg_wf_carousel_vc' );
/**
 * To register carousel.
 */
function wr_reg_wf_carousel_vc() {
	if ( function_exists( 'vc_map' ) ) {
		vc_map(
			array(
				'name'     => esc_html__( 'Food Carousel', 'woorestaurant' ),
				'base'     => 'woores_view_carousel',
				'class'    => '',
				'icon'     => 'icon-grid',
				'controls' => 'full',
				'category' => esc_html__( 'Woocommerce Food', 'woorestaurant' ),
				'params'   => array(
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Style', 'woorestaurant' ),
						'param_name'  => 'style',
						'value'       => array(
							esc_html__( '1', 'woorestaurant' ) => '1',
							esc_html__( '2', 'woorestaurant' ) => '2',
							esc_html__( '3', 'woorestaurant' ) => '3',
							esc_html__( '4', 'woorestaurant' ) => '4',
						),
						'description' => esc_html__( 'Select style of carousel', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Count', 'woorestaurant' ),
						'param_name'  => 'count',
						'value'       => '',
						'description' => esc_html__( 'Enter number of foods to show', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Number item visible', 'woorestaurant' ),
						'param_name'  => 'slidesshow',
						'value'       => '',
						'description' => esc_html__( 'Number of slides to show at a time', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Number slides to scroll', 'woorestaurant' ),
						'param_name'  => 'slidesscroll',
						'value'       => '',
						'description' => esc_html__( 'Number of slides to scroll at a time', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'IDs', 'woorestaurant' ),
						'param_name'  => 'ids',
						'value'       => '',
						'description' => esc_html__( 'Specify food IDs to retrieve', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Menu', 'woorestaurant' ),
						'param_name'  => 'cat',
						'value'       => '',
						'description' => esc_html__( 'List of cat ID (or slug), separated by a comma', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Order', 'woorestaurant' ),
						'param_name'  => 'order',
						'value'       => array(
							esc_html__( 'DESC', 'woorestaurant' ) => 'DESC',
							esc_html__( 'ASC', 'woorestaurant' )  => 'ASC',
						),
						'description' => '',
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Order by', 'woorestaurant' ),
						'param_name'  => 'orderby',
						'value'       => array(
							esc_html__( 'Date', 'woorestaurant' )  => 'date',
							esc_html__( 'Custom order field', 'woorestaurant' ) => 'order_field',
							esc_html__( 'ID', 'woorestaurant' )    => 'ID',
							esc_html__( 'Author', 'woorestaurant' ) => 'author',
							esc_html__( 'Title', 'woorestaurant' ) => 'title',
							esc_html__( 'Name', 'woorestaurant' )  => 'name',
							esc_html__( 'Modified', 'woorestaurant' ) => 'modified',
							esc_html__( 'Parent', 'woorestaurant' ) => 'parent',
							esc_html__( 'Random', 'woorestaurant' ) => 'rand',
							esc_html__( 'Menu order', 'woorestaurant' ) => 'menu_order',
							esc_html__( 'Meta value', 'woorestaurant' ) => 'meta_value',
							esc_html__( 'Meta value num', 'woorestaurant' ) => 'meta_value_num',
							esc_html__( 'Post__in', 'woorestaurant' ) => 'post__in',
							esc_html__( 'None', 'woorestaurant' )  => 'none',
						),
						'description' => '',
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Meta key', 'woorestaurant' ),
						'param_name'  => 'meta_key',
						'value'       => '',
						'description' => esc_html__( 'Enter meta key to query', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Meta value', 'woorestaurant' ),
						'param_name'  => 'meta_value',
						'value'       => '',
						'description' => esc_html__( 'Enter meta value to query', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Number of Excerpt ( short description)', 'woorestaurant' ),
						'param_name'  => 'number_excerpt',
						'value'       => '',
						'description' => esc_html__( 'Enter number of Excerpt, enter:0 to disable excerpt', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Autoplay', 'woorestaurant' ),
						'param_name'  => 'autoplay',
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => '',
							esc_html__( 'Yes', 'woorestaurant' ) => '1',
						),
						'description' => '',
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'class'       => '',
						'heading'     => esc_html__( 'Autoplay Speed', 'woorestaurant' ),
						'param_name'  => 'autoplayspeed',
						'value'       => '',
						'dependency'  => array(
							'element' => 'autoplay',
							'value'   => array( '1' ),
						),
						'description' => esc_html__( 'Autoplay Speed in milliseconds. Default:3000', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Enable Loading effect', 'woorestaurant' ),
						'param_name'  => 'loading_effect',
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => '',
							esc_html__( 'Yes', 'woorestaurant' ) => '1',
						),
						'description' => '',
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Infinite', 'woorestaurant' ),
						'param_name'  => 'infinite',
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => '',
							esc_html__( 'Yes', 'woorestaurant' ) => 'yes',
						),
						'description' => esc_html__( 'Infinite loop sliding ( go to first item when end loop)', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Enable cart', 'woorestaurant' ),
						'param_name'  => 'cart_enable',
						'value'       => array(
							esc_html__( 'Default', 'woorestaurant' ) => '',
							esc_html__( 'Yes', 'woorestaurant' )     => 'yes',
							esc_html__( 'No', 'woorestaurant' )      => 'no',
						),
						'description' => esc_html__( 'Enable side cart icon', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Show only Featured food', 'woorestaurant' ),
						'param_name'  => 'featured',
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => '',
							esc_html__( 'Yes', 'woorestaurant' ) => '1',
						),
						'description' => '',
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Hide add to cart form', 'woorestaurant' ),
						'param_name'  => 'hide_atc',
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => 'no',
							esc_html__( 'Yes', 'woorestaurant' ) => 'yes',
						),
						'description' => '',
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Enable modal', 'woorestaurant' ),
						'param_name'  => 'enable_modal',
						'value'       => array(
							esc_html__( 'Default', 'woorestaurant' ) => '',
							esc_html__( 'Yes', 'woorestaurant' ) => 'yes',
							esc_html__( 'No', 'woorestaurant' )  => 'no',
						),
						'description' => '',
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
