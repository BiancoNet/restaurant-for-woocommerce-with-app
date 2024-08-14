<?php
/**
 * It is the food grid shortcode file.
 *
 * @package woorestaurant
 */

/**
 * It is the shortcode grid function.
 *
 * @param array $atts it is the attribute parameter.
 */
function woo_restaurant_shortcode_grid( $atts ) {

	if ( phpversion() >= 7 ) {
		$atts = (array) $atts;
	}
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
	global $id, $number_excerpt, $img_size, $location, $hide_atc;
	// phpcs:ignore
	$id = isset( $atts['ID'] ) && '' != $atts['ID'] ? $atts['ID'] : 'wrs-' . rand( 10, 9999 );
	if ( ! isset( $atts['ID'] ) ) {
		$atts['ID'] = $id;
	}
	$style    = isset( $atts['style'] ) && '' != $atts['style'] ? $atts['style'] : '1';
	$column   = isset( $atts['column'] ) && '' != $atts['column'] ? $atts['column'] : '2';
	$posttype = 'wr_food';
	$ids      = isset( $atts['ids'] ) ? str_replace( ' ', '', $atts['ids'] ) : '';
	$taxonomy = isset( $atts['taxonomy'] ) ? $atts['taxonomy'] : '';
	$cat      = isset( $atts['cat'] ) ? str_replace( ' ', '', $atts['cat'] ) : '';

	$order_cat      = isset( $atts['order_cat'] ) ? $atts['order_cat'] : '';
	$tag            = isset( $atts['tag'] ) ? $atts['tag'] : '';
	$count          = isset( $atts['count'] ) && '' != $atts['count'] ? $atts['count'] : '9';
	$menu_filter    = isset( $atts['menu_filter'] ) ? $atts['menu_filter'] : 'hide';
	$active_filter  = isset( $atts['active_filter'] ) ? $atts['active_filter'] : '';
	$posts_per_page = isset( $atts['posts_per_page'] ) && '' != $atts['posts_per_page'] ? $atts['posts_per_page'] : '3';
	$order          = isset( $atts['order'] ) ? $atts['order'] : '';
	$orderby        = isset( $atts['orderby'] ) ? $atts['orderby'] : '';
	$meta_key       = isset( $atts['meta_key'] ) ? $atts['meta_key'] : '';
	$meta_value     = isset( $atts['meta_value'] ) ? $atts['meta_value'] : '';
	$class          = isset( $atts['class'] ) ? $atts['class'] : '';
	$page_navi      = isset( $atts['page_navi'] ) ? $atts['page_navi'] : '';
	$number_excerpt = isset( $atts['number_excerpt'] ) && '' != $atts['number_excerpt'] ? $atts['number_excerpt'] : '10';
	$enable_modal   = isset( $atts['enable_modal'] ) ? $atts['enable_modal'] : '';
	$img_size       = isset( $atts['img_size'] ) ? $atts['img_size'] : '';
	$featured       = isset( $atts['featured'] ) ? $atts['featured'] : '';
	$filter_style   = isset( $atts['filter_style'] ) ? $atts['filter_style'] : '';
	$show_count     = isset( $atts['show_count'] ) ? $atts['show_count'] : '';
	$hide_ftall     = isset( $atts['hide_ftall'] ) ? $atts['hide_ftall'] : '';
	$enable_search  = isset( $atts['enable_search'] ) ? $atts['enable_search'] : '';
	$hide_atc       = isset( $atts['hide_atc'] ) ? $atts['hide_atc'] : '';
	$enb_mnd        = isset( $atts['enb_mnd'] ) ? $atts['enb_mnd'] : '';
	if ( 'yes' == $hide_atc ) {
		$atts['enable_mtod'] = 'no';}
	$location = isset( $atts['location'] ) ? $atts['location'] : '';
	if ( 'yes' == $hide_ftall && '' == $active_filter && ! empty( $cat ) ) {
		if ( ! empty( $cat ) ) {
			$cats          = explode( ',', $cat );
			$active_filter = $cats[0];
		} else {
			$args_ca = array(
				'hide_empty' => true,
				'parent'     => '0',
			);
			if ( 'yes' == $order_cat ) {
				$args_ca['meta_key'] = 'woo_restaurant_menu_order';
				$args_ca['orderby']  = 'meta_value_num';
			}
			// phpcs:ignore
			$terms        = get_terms( 'product_cat', $args_ca );
			$loc_selected = woore_get_loc_selected();
			$exclude      = array();
			if ( '' != $loc_selected ) {
				$exclude = get_term_meta( $loc_selected, 'wrsp_loc_hide_menu', true );
			}
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					// phpcs:ignore
					if ( is_array( $exclude ) && ! empty( $exclude ) && in_array( $term->slug, $exclude ) ) {
						// if exclude.
					} else {
						$active_filter = $term->slug;
						break;
					}
				}
			}
		}
	}
	// remove space.
	$cat = preg_replace( '/\s+/', '', $cat );
	$ids = preg_replace( '/\s+/', '', $ids );
	if ( isset( $_GET['menu'] ) && '' != $_GET['menu'] ) {
		$active_filter = wp_kses_post( wp_unslash( $_GET['menu'] ) );
	}

	$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );

	$args      = woo_restaurant_query( $posttype, $posts_per_page, $order, $orderby, $cat, $tag, $taxonomy, $meta_key, $ids, $meta_value, '', '', $active_filter, $featured, $location );
	$the_query = new WP_Query( $args );
	ob_start();
	$class = $class . ' wrs-food-plug ';
	$class = $class . ' column-' . $column;
	$class = $class . ' style-' . $style;
	$class = 'yes' == $hide_atc ? $class . ' wrsf-listing-mode' : $class;
	// if ( ! woore_check_open_close_time() ) {
		// $class = $class." wrsfd-out-open-time";
	// }.
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
	<div class="wrs-fdlist wrs-fdgrid <?php echo esc_attr( $class ); ?>" id ="<?php echo esc_attr( $id ); ?>">
		<?php
		do_action( 'woore_before_shortcode_content', $atts );
		if ( 'yes' == woo_restaurant_get_option( 'woo_restaurant_enable_loc' ) ) {
			$loc_selected = WC()->session->get( 'wr_userloc' );
			if ( '' != $location && $loc_selected != $location ) {
				WC()->session->set( 'wr_userloc', $location );
			}
			echo "<input type='hidden' name='food_loc' value='" . esc_attr( $location ) . "'/>";
		}
		woore_search_html( $enable_search );
		if ( 'OFF' != $location ) {
			woo_restaurant_select_location_html( $location );
		}
		if ( 'show' == $menu_filter ) {
			woo_restaurant_search_form_html( $cat, $order_cat, '', $active_filter, $filter_style, $hide_ftall, $show_count );
		}
		// phpcs:ignore
		echo '' != $html_cart ? ( $html_cart ) : '';
		if ( function_exists( 'woore_select_date_html' ) ) {
			woore_select_date_html( '', $enb_mnd );
		}
		?>
		<div class="parent_grid">
		<?php
		if ( '' != $active_filter ) {

			$term = get_term_by( 'slug', $active_filter, 'product_cat' );
			if ( '' != $term->description ) {
				echo '<div class="wrsf-dcat" style="display:block;">' . esc_attr( $term->description ) . '</div>';
			}
		}
		?>
			
		<div class="ctgrid">
		<?php
		$num_pg  = '';
		$it_ep = '';
		$arr_ids = array();
		if ( $the_query->have_posts() ) {
			$i  = 0;
			$it = $the_query->found_posts;
			if ( $it < $count || '-1' == $count ) {
				$count = $it;}
			if ( $count > $posts_per_page ) {
				$num_pg = ceil( $count / $posts_per_page );
				$it_ep  = $count % $posts_per_page;
			} else {
				$num_pg = 1;
			}
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				global $product;
				$disable_is_visible = apply_filters( 'woore_disable_is_visible', 'no', $product );
				if ( 'yes' == $disable_is_visible || $product && $product->is_visible() ) {
					$arr_ids[] = get_the_ID();
					++$i;
					if ( ( $num_pg == $paged ) && '-1' != $num_pg ) {
						if ( $i > $it_ep ) {
							break;}
					}
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
		} else {
			echo '<span class="wrsf-no-rs">' . esc_html__( 'No matching records found', 'woorestaurant' ) . '</span>';}
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
		if ( 'loadmore' == $page_navi ) {
			woo_restaurant_ajax_navigate_html( $id, $atts, $num_pg, $args, $arr_ids );
		} else {
			?>
			<div class="wrsfd-pagination-parent">
				<?php woo_restaurant_page_number_html( $the_query, $id, $atts, $num_pg, $args, $arr_ids ); ?>
			</div>
			<?php
		}
		?>

	</div>
	<?php
	wp_reset_postdata();
	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}

add_shortcode( 'woores_view_grid', 'woo_restaurant_shortcode_grid' );
add_action( 'after_setup_theme', 'wr_reg_wfood_grid_vc' );
/**
 * To register food grid.
 */
function wr_reg_wfood_grid_vc() {
	if ( function_exists( 'vc_map' ) ) {
		vc_map(
			array(
				'name'     => esc_html__( 'Food Grid', 'woorestaurant' ),
				'base'     => 'woores_view_grid',
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
						'description' => esc_html__( 'Select style of grid', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Columns', 'woorestaurant' ),
						'param_name'  => 'column',
						'value'       => array(
							esc_html__( '2 columns', 'woorestaurant' ) => '2',
							esc_html__( '3 columns', 'woorestaurant' ) => '3',
							esc_html__( '4 columns', 'woorestaurant' ) => '4',
							esc_html__( '5 columns', 'woorestaurant' ) => '5',
							esc_html__( '1 column', 'woorestaurant' )  => '1',
						),
						'description' => esc_html__( 'Select number column of grid', 'woorestaurant' ),
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
						'heading'     => esc_html__( 'Food per page', 'woorestaurant' ),
						'param_name'  => 'posts_per_page',
						'value'       => '',
						'description' => esc_html__( 'Number food per page', 'woorestaurant' ),
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
							esc_html__( 'Sale', 'woorestaurant' )  => 'sale',
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
						'heading'     => esc_html__( 'Page navi', 'woorestaurant' ),
						'param_name'  => 'page_navi',
						'value'       => array(
							esc_html__( 'Number', 'woorestaurant' )    => '',
							esc_html__( 'Load more', 'woorestaurant' ) => 'loadmore',
						),
						'description' => esc_html__( 'Select type of page navigation', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Menu filter', 'woorestaurant' ),
						'param_name'  => 'menu_filter',
						'value'       => array(
							esc_html__( 'Hide', 'woorestaurant' ) => 'hide',
							esc_html__( 'Show', 'woorestaurant' ) => 'show',
						),
						'description' => esc_html__( 'Select show or hide Menu filter', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Menu/Category count', 'woorestaurant' ),
						'param_name'  => 'show_count',
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => '',
							esc_html__( 'Yes', 'woorestaurant' ) => 'yes',
						),
						'description' => esc_html__( 'Select Yes to show menu/category count', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Menu filter style', 'woorestaurant' ),
						'param_name'  => 'filter_style',
						'value'       => array(
							esc_html__( 'Default', 'woorestaurant' ) => '',
							esc_html__( 'Icon', 'woorestaurant' ) => 'icon',
						),
						'description' => esc_html__( 'Select Menu filter style', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Active filter', 'woorestaurant' ),
						'param_name'  => 'active_filter',
						'value'       => '',
						'description' => esc_html__( 'Enter slug of menu to active', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'heading'     => esc_html__( 'Order Menu Filter', 'woorestaurant' ),
						'param_name'  => 'order_cat',
						'description' => esc_html__( 'Order Menu with custom order', 'woorestaurant' ),
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => '',
							esc_html__( 'Yes', 'woorestaurant' ) => 'yes',
						),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'heading'     => esc_html__( "Hide 'All' Filter", 'woorestaurant' ),
						'param_name'  => 'hide_ftall',
						'description' => esc_html__( "Select 'yes' to disalbe 'All' filter", 'woorestaurant' ),
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => '',
							esc_html__( 'Yes', 'woorestaurant' ) => 'yes',
						),
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
						'heading'     => esc_html__( 'Enable Live search', 'woorestaurant' ),
						'param_name'  => 'enable_search',
						'value'       => array(
							esc_html__( 'No', 'woorestaurant' )  => '',
							esc_html__( 'Yes', 'woorestaurant' ) => 'yes',
						),
						'description' => esc_html__( 'Enable ajax live search', 'woorestaurant' ),
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