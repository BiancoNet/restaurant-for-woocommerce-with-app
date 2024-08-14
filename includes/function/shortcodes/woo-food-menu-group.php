<?php
/**
 * It is the shortcode of food menu group file.
 *
 * @package woorestaurant
 */

/**
 * It is the shortcode of menu group.
 *
 * @param array $atts it is the attribute parameter.
 */
function woo_restaurant_shortcode_menu_group( $atts ) {
	if ( phpversion() >= 7 ) {
		$atts = (array) $atts;
	}
	if ( is_admin() && ! defined( 'DOING_AJAX' ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	global $id, $number_excerpt, $img_size, $location;

	$layout         = isset( $atts['layout'] ) && '' != $atts['layout'] ? $atts['layout'] : 'grid';
	$style          = isset( $atts['style'] ) && '' != $atts['style'] ? $atts['style'] : '1';
	$column         = isset( $atts['column'] ) && '' != $atts['column'] ? $atts['column'] : '1';
	$cat            = isset( $atts['cat'] ) ? str_replace( ' ', '', $atts['cat'] ) : '';
	$order_cat      = isset( $atts['order_cat'] ) ? $atts['order_cat'] : '';
	$count          = isset( $atts['count'] ) && '' != $atts['count'] ? $atts['count'] : '9';
	$menu_visible   = isset( $atts['menu_visible'] ) && '' != $atts['menu_visible'] ? $atts['menu_visible'] : '';
	$menu_onscroll  = isset( $atts['menu_onscroll'] ) && '' != $atts['menu_onscroll'] ? $atts['menu_onscroll'] : $menu_visible;
	$heading_style  = isset( $atts['heading_style'] ) ? $atts['heading_style'] : '';
	$show_count     = isset( $atts['show_count'] ) ? $atts['show_count'] : '';
	$menu_filter    = isset( $atts['menu_filter'] ) ? $atts['menu_filter'] : 'hide';
	$posts_per_page = isset( $atts['posts_per_page'] ) && '' != $atts['posts_per_page'] ? $atts['posts_per_page'] : '3';
	$order          = isset( $atts['order'] ) ? $atts['order'] : '';
	$orderby        = isset( $atts['orderby'] ) ? $atts['orderby'] : '';
	$meta_key       = isset( $atts['meta_key'] ) ? $atts['meta_key'] : '';
	$meta_value     = isset( $atts['meta_value'] ) ? $atts['meta_value'] : '';
	$page_navi      = isset( $atts['page_navi'] ) ? $atts['page_navi'] : '';
	$number_excerpt = isset( $atts['number_excerpt'] ) && '' != $atts['number_excerpt'] ? $atts['number_excerpt'] : '10';
	$cart_enable    = isset( $atts['cart_enable'] ) ? $atts['cart_enable'] : '';
	$enable_modal   = isset( $atts['enable_modal'] ) ? $atts['enable_modal'] : '';
	$img_size       = isset( $atts['img_size'] ) ? $atts['img_size'] : '';
	$featured       = isset( $atts['featured'] ) ? $atts['featured'] : '';
	$filter_style   = isset( $atts['filter_style'] ) ? $atts['filter_style'] : '';
	$location       = isset( $atts['location'] ) ? $atts['location'] : '';
	$class          = isset( $atts['class'] ) ? $atts['class'] : '';
	$live_sort      = isset( $atts['live_sort'] ) ? $atts['live_sort'] : '';
	$hide_atc       = isset( $atts['hide_atc'] ) ? $atts['hide_atc'] : '';
	$enable_mtod    = isset( $atts['enable_mtod'] ) ? $atts['enable_mtod'] : '';

	ob_start();
	$args = array(
		'hide_empty' => true,
		'parent'     => '0',
	);
	if ( '' != $cat ) {
		unset( $args['parent'] );
	}
	$cat = '' != $cat ? explode( ',', $cat ) : array();
	if ( ! empty( $cat ) && ! is_numeric( $cat[0] ) ) {
		$args['slug']    = $cat;
		$args['orderby'] = 'slug__in';
	} elseif ( ! empty( $cat ) ) {
		$args['include'] = $cat;
		$args['orderby'] = 'include';
	}
	if ( 'yes' == $order_cat ) {
		$args['meta_key'] = 'woo_restaurant_menu_order';
		$args['orderby']  = 'meta_value_num';
	}
	global $woore_mngr;
	// phpcs:ignore
	$woore_mngr    = get_terms( 'product_cat', $args );
	$id_sc         = 'wrsf-mn-' . esc_attr( rand( 1, 10000000 ) ) . '-' . esc_attr( rand( 1, 10000000 ) );
	$atts['id_sc'] = $id_sc;
	if ( ! empty( $woore_mngr ) && ! is_wp_error( $woore_mngr ) ) {
		$nb_mn         = count( $woore_mngr );
		$mngr_ntag     = apply_filters( 'woore_mngroup_title_tag', 'h2' );
		$filter_slhtml = '';
		$content_html  = '';
		$filter_html   = '';
		$i             = 0;
		foreach ( $woore_mngr as $k_mn => $term ) {
			++$i;
			if ( 2 == $i ) {
				$enable_mtod = '';
			}
			$icon_html = '';
			$_iconsc   = get_term_meta( $term->term_id, 'woo_restaurant_menu_iconsc', true );
			if ( '' != $_iconsc ) {
				$icon_html = '<span class="wrsf-caticon wrsf-iconsc">' . $_iconsc . '</span>';
			} else {
				$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
				if ( '' != $thumbnail_id ) {
					// get the medium-sized image url.
					$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
					// Output in img tag.
					if ( isset( $image[0] ) && '' != $image[0] ) {
						$icon_html = '<span class="wrsf-caticon"><img src="' . $image[0] . '" alt="" /></span>';
					}
				}
			}
			$t_count = '';
			if ( 'yes' == $show_count ) {
				$t_count = ' (' . $term->count . ')';
			}
			$filter_html      .= '<a class="filtermngr-item" href="javascript:;" data-menu="' . esc_attr( $term->slug ) . '" data-id="' . esc_attr( $id_sc ) . '">' . esc_attr( $term->name ) . $t_count . '</a>';
			$filter_slhtml    .= '<option value="' . esc_attr( $term->slug ) . '">' . wp_kses_post( $term->name ) . $t_count . '</option>';
			$content_html     .= '
				<div class="wrsf-mngr-item" data-menu="' . esc_attr( $term->slug ) . '">
					<div class="wrsf-mnheading mnheading-' . esc_attr( $heading_style ) . '">
						<' . $mngr_ntag . ' class="mn-namegroup"><span>' . $icon_html . $term->name . $t_count . '</span></' . $mngr_ntag . '>
						' . ( '' != $term->description ? '<div class="mn-desgroup">' . $term->description . '</div>' : '' ) . '
					</div>';
				$content_html .= '<div class="wrsf-mnlayout">';
			if ( 'list' == $layout ) {
				$content_html .= do_shortcode( '[woores_view_list column="' . esc_attr( $column ) . '" style="' . esc_attr( $style ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" count="' . esc_attr( $count ) . '" cat="' . esc_attr( $term->slug ) . '" orderby="' . esc_attr( $orderby ) . '" order="' . esc_attr( $order ) . '" page_navi="' . esc_attr( $page_navi ) . '" menu_filter="' . esc_attr( $menu_filter ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" featured="' . esc_attr( $featured ) . '" meta_key="' . esc_attr( $meta_key ) . '" img_size="' . esc_attr( $img_size ) . '" class="' . esc_attr( $class ) . '" enable_mtod="' . esc_attr( $enable_mtod ) . '" hide_atc="' . esc_attr( $hide_atc ) . '" location="' . esc_attr( $location ) . '"]' );
			} elseif ( 'table' == $layout ) {
				$content_html .= do_shortcode( '[woores_view_table count="' . esc_attr( $count ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" page_navi="' . esc_attr( $page_navi ) . '" live_sort="' . esc_attr( $live_sort ) . '" cat="' . esc_attr( $term->slug ) . '" orderby="' . esc_attr( $orderby ) . '" order="' . esc_attr( $order ) . '"  menu_filter="' . esc_attr( $menu_filter ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" featured="' . esc_attr( $featured ) . '" meta_key="' . esc_attr( $meta_key ) . '" img_size="' . esc_attr( $img_size ) . '" class="' . esc_attr( $class ) . '" location="' . esc_attr( $location ) . '" enable_mtod="' . esc_attr( $enable_mtod ) . '" hide_atc="' . esc_attr( $hide_atc ) . '"]' );
			} else {
				$content_html .= do_shortcode( '[woores_view_grid column="' . esc_attr( $column ) . '" style="' . esc_attr( $style ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" count="' . esc_attr( $count ) . '" cat="' . esc_attr( $term->slug ) . '" orderby="' . esc_attr( $orderby ) . '" order="' . esc_attr( $order ) . '"  page_navi="' . esc_attr( $page_navi ) . '" menu_filter="' . esc_attr( $menu_filter ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" featured="' . esc_attr( $featured ) . '" meta_key="' . esc_attr( $meta_key ) . '" img_size="' . esc_attr( $img_size ) . '" class="' . esc_attr( $class ) . '" location="' . esc_attr( $location ) . '" enable_mtod="' . esc_attr( $enable_mtod ) . '" hide_atc="' . esc_attr( $hide_atc ) . '"]' );
			}
				$content_html .= '
					</div>
				</div>';
			if ( is_numeric( $menu_visible ) && $menu_visible > 0 && $i <= $menu_visible ) {
				unset( $woore_mngr[ $k_mn ] );
				if ( $i == $menu_visible ) {
					break;
				}
			}
		}
		$css_class = '';
		if ( $menu_visible > 0 && $nb_mn > $menu_visible ) {
			$css_class      = 'wrsf-mngroup-more';
			$filter_html   .= '<a class="filtermngr-item wrsf-btmore" alt="' . esc_html__( 'Scroll down to see more!', 'woorestaurant' ) . '" href="javascript:;" data-menu="wrsmmore" data-id="' . esc_attr( $id_sc ) . '">' . esc_html__( 'More...', 'woorestaurant' ) . '</a>';
			$filter_slhtml .= '<option value="wrsmmore">' . esc_html__( 'More...', 'woorestaurant' ) . '</option>';
		}
		echo '<div class="wrsf-mngroup mngroup-' . esc_attr( $layout ) . ' mngroup-st-' . esc_attr( $style ) . ' ' . esc_attr( $css_class ) . '" id="' . esc_attr( $id_sc ) . '" data-menu="' . esc_attr( json_encode( $woore_mngr ) ) . '" data-sc="' . esc_attr( json_encode( $atts ) ) . '">';
			echo '<div class="wrs-fdlist wrsf-mngrfilter">
			<div class="wrsfd-filter">
	    		<div class="wrsfd-filter-group">
	            	<div class="wrs-menu-list">';
					echo wp_kses_post( $filter_html );
			echo '</div>
				<div class="wrs-menu-select">
	            	<div>
			            <select name="wooresf_menu" data-id="' . esc_attr( $id_sc ) . '">' . esc_attr( $filter_slhtml ) . '</select>
			        </div>
			    </div>        
				</div></div></div>';
			echo '<div class="wrsf-mngr-content">';
				// phpcs:ignore
				echo ( $content_html );
			echo '</div>';
			echo '<div class="wrsf-mngr-endel"></div>';
		echo '</div>';
	}

	$output_string = ob_get_contents();
	ob_end_clean();
	return $output_string;
}

add_shortcode( 'woores_view_mngroup', 'woo_restaurant_shortcode_menu_group' );
add_action( 'after_setup_theme', 'wr_reg_wfood_mngroup_vc' );
/**
 * It is the Register food group function.
 */
function wr_reg_wfood_mngroup_vc() {
	if ( function_exists( 'vc_map' ) ) {
		vc_map(
			array(
				'name'     => esc_html__( 'Food Menu Group', 'woorestaurant' ),
				'base'     => 'woores_view_mngroup',
				'class'    => '',
				'icon'     => 'icon-grid',
				'controls' => 'full',
				'category' => esc_html__( 'Woocommerce Food', 'woorestaurant' ),
				'params'   => array(
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Layout', 'woorestaurant' ),
						'param_name'  => 'layout',
						'value'       => array(
							esc_html__( 'Grid', 'woorestaurant' )  => 'grid',
							esc_html__( 'List', 'woorestaurant' )  => 'list',
							esc_html__( 'Table', 'woorestaurant' ) => 'table',
						),
						'description' => esc_html__( 'Select Layout of Menu group', 'woorestaurant' ),
					),
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
						'description' => esc_html__( 'Select style of layout ( Grid supports style 1,2,3,4 List supports style 1,2,3 and Table supports style 1)', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Columns', 'woorestaurant' ),
						'param_name'  => 'column',
						'value'       => array(
							esc_html__( '1 column', 'woorestaurant' )  => '1',
							esc_html__( '2 columns', 'woorestaurant' ) => '2',
							esc_html__( '3 columns', 'woorestaurant' ) => '3',
							esc_html__( '4 columns', 'woorestaurant' ) => '4',
							esc_html__( '5 columns', 'woorestaurant' ) => '5',
						),
						'dependency'  => array(
							'element' => 'layout',
							'value'   => array( 'grid', 'list' ),
						),
						'description' => esc_html__( 'Select number column of grid or list style', 'woorestaurant' ),
					),
					array(
						'admin_label' => true,
						'type'        => 'dropdown',
						'class'       => '',
						'heading'     => esc_html__( 'Heading style', 'woorestaurant' ),
						'param_name'  => 'heading_style',
						'value'       => array(
							esc_html__( 'Default', 'woorestaurant' ) => '',
							esc_html__( 'Style 1', 'woorestaurant' ) => '1',
							esc_html__( 'Style 2', 'woorestaurant' ) => '2',
							esc_html__( 'Style 3', 'woorestaurant' ) => '3',
							esc_html__( 'Style 4', 'woorestaurant' ) => '4',
						),
						'description' => esc_html__( 'Select Heading style', 'woorestaurant' ),
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
						'description' => esc_html__( 'Enter Number food per page', 'woorestaurant' ),
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
						'heading'     => esc_html__( 'Order Menu', 'woorestaurant' ),
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

add_action( 'wp_ajax_woo_restaurant_more_menu', 'ajax_woo_restaurant_more_menu' );
add_action( 'wp_ajax_nopriv_woo_restaurant_more_menu', 'ajax_woo_restaurant_more_menu' );
/**
 * Menu group load more.
 */
function ajax_woo_restaurant_more_menu() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'restaurant-menu' ) ) {
			return;
		}
	}

	if ( isset( $_POST['param_shortcode'] ) || isset( $_POST['data_menu'] ) ) {
		$atts       = json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['param_shortcode'] ) ) ), true );
		$woore_mngr = json_decode( stripslashes( wp_kses_post( wp_unslash( $_POST['data_menu'] ) ) ), true );
	}

	$layout         = isset( $atts['layout'] ) && '' != $atts['layout'] ? $atts['layout'] : 'grid';
	$style          = isset( $atts['style'] ) && '' != $atts['style'] ? $atts['style'] : '1';
	$column         = isset( $atts['column'] ) && '' != $atts['column'] ? $atts['column'] : '1';
	$cat            = isset( $atts['cat'] ) ? str_replace( ' ', '', $atts['cat'] ) : '';
	$order_cat      = isset( $atts['order_cat'] ) ? $atts['order_cat'] : '';
	$count          = isset( $atts['count'] ) && '' != $atts['count'] ? $atts['count'] : '9';
	$menu_visible   = isset( $atts['menu_visible'] ) && '' != $atts['menu_visible'] ? $atts['menu_visible'] : '';
	$menu_onscroll  = isset( $atts['menu_onscroll'] ) && '' != $atts['menu_onscroll'] ? $atts['menu_onscroll'] : $menu_visible;
	$heading_style  = isset( $atts['heading_style'] ) ? $atts['heading_style'] : '';
	$menu_filter    = isset( $atts['menu_filter'] ) ? $atts['menu_filter'] : 'hide';
	$posts_per_page = isset( $atts['posts_per_page'] ) && '' != $atts['posts_per_page'] ? $atts['posts_per_page'] : '3';
	$order          = isset( $atts['order'] ) ? $atts['order'] : '';
	$orderby        = isset( $atts['orderby'] ) ? $atts['orderby'] : '';
	$meta_key       = isset( $atts['meta_key'] ) ? $atts['meta_key'] : '';
	$meta_value     = isset( $atts['meta_value'] ) ? $atts['meta_value'] : '';
	$page_navi      = isset( $atts['page_navi'] ) ? $atts['page_navi'] : '';
	$number_excerpt = isset( $atts['number_excerpt'] ) && '' != $atts['number_excerpt'] ? $atts['number_excerpt'] : '10';
	$cart_enable    = isset( $atts['cart_enable'] ) ? $atts['cart_enable'] : '';
	$enable_modal   = isset( $atts['enable_modal'] ) ? $atts['enable_modal'] : '';
	$img_size       = isset( $atts['img_size'] ) ? $atts['img_size'] : '';
	$featured       = isset( $atts['featured'] ) ? $atts['featured'] : '';
	$filter_style   = isset( $atts['filter_style'] ) ? $atts['filter_style'] : '';
	$location       = isset( $atts['location'] ) ? $atts['location'] : '';
	$class          = isset( $atts['class'] ) ? $atts['class'] : '';
	$live_sort      = isset( $atts['live_sort'] ) ? $atts['live_sort'] : '';
	$hide_atc       = isset( $atts['hide_atc'] ) ? $atts['hide_atc'] : '';

	$id_sc = isset( $atts['id_sc'] ) ? $atts['id_sc'] : '';

	if ( ! empty( $woore_mngr ) ) {
		$mngr_ntag     = apply_filters( 'woore_mngroup_title_tag', 'h2' );
		$content_html  = '';
		$filter_html   = '';
		$filter_slhtml = '';
		$i             = 0;
		foreach ( $woore_mngr as $k_mn => $term ) {
			++$i;
			$icon_html = '';
			$_iconsc   = get_term_meta( $term['term_id'], 'woo_restaurant_menu_iconsc', true );
			if ( '' != $_iconsc ) {
				$icon_html = '<span class="wrsf-caticon wrsf-iconsc">' . $_iconsc . '</span>';
			} else {
				$thumbnail_id = get_term_meta( $term['term_id'], 'thumbnail_id', true );
				if ( '' != $thumbnail_id ) {
					// get the medium-sized image url.
					$image = wp_get_attachment_image_src( $thumbnail_id, 'full' );
					// Output in img tag.
					if ( isset( $image[0] ) && '' != $image[0] ) {
						$icon_html = '<span class="wrsf-caticon"><img src="' . $image[0] . '" alt="" /></span>';
					}
				}
			}
			$filter_html      .= '<a class="filtermngr-item" href="javascript:;" data-menu="' . esc_attr( $term['slug'] ) . '" data-id="' . esc_attr( $id_sc ) . '">' . esc_attr( $term['name'] ) . '</a>';
			$filter_slhtml    .= '<option value="' . esc_attr( $term['slug'] ) . '">' . wp_kses_post( $term['name'] ) . '</option>';
			$content_html     .= '
			<div class="wrsf-mngr-item" data-menu="' . esc_attr( $term['slug'] ) . '">
				<div class="wrsf-mnheading mnheading-' . esc_attr( $heading_style ) . '">
					<' . $mngr_ntag . ' class="mn-namegroup"><span>' . $icon_html . $term['name'] . '</span></' . $mngr_ntag . '>
					' . ( '' != $term['description'] ? '<div class="mn-desgroup">' . $term['description'] . '</div>' : '' ) . '
				</div>';
				$content_html .= '<div class="wrsf-mnlayout">';
			if ( 'list' == $layout ) {
				$content_html .= do_shortcode( '[woores_view_list column="' . esc_attr( $column ) . '" style="' . esc_attr( $style ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" count="' . esc_attr( $count ) . '" cat="' . esc_attr( $term['slug'] ) . '" orderby="' . esc_attr( $orderby ) . '" order="' . esc_attr( $order ) . '" page_navi="' . esc_attr( $page_navi ) . '" menu_filter="' . esc_attr( $menu_filter ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" featured="' . esc_attr( $featured ) . '" meta_key="' . esc_attr( $meta_key ) . '" img_size="' . esc_attr( $img_size ) . '" class="' . esc_attr( $class ) . '" hide_atc="' . esc_attr( $hide_atc ) . '" location="' . esc_attr( $location ) . '"]' );
			} elseif ( 'table' == $layout ) {
				$content_html .= do_shortcode( '[woores_view_table count="' . esc_attr( $count ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" page_navi="' . esc_attr( $page_navi ) . '" live_sort="' . esc_attr( $live_sort ) . '" cat="' . esc_attr( $term['slug'] ) . '" orderby="' . esc_attr( $orderby ) . '" order="' . esc_attr( $order ) . '"  menu_filter="' . esc_attr( $menu_filter ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" featured="' . esc_attr( $featured ) . '" meta_key="' . esc_attr( $meta_key ) . '" img_size="' . esc_attr( $img_size ) . '" class="' . esc_attr( $class ) . '" location="' . esc_attr( $location ) . '" hide_atc="' . esc_attr( $hide_atc ) . '"]' );
			} else {
				$content_html .= do_shortcode( '[woores_view_grid column="' . esc_attr( $column ) . '" style="' . esc_attr( $style ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" count="' . esc_attr( $count ) . '" cat="' . esc_attr( $term['slug'] ) . '" orderby="' . esc_attr( $orderby ) . '" order="' . esc_attr( $order ) . '"  page_navi="' . esc_attr( $page_navi ) . '" menu_filter="' . esc_attr( $menu_filter ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" featured="' . esc_attr( $featured ) . '" meta_key="' . esc_attr( $meta_key ) . '" img_size="' . esc_attr( $img_size ) . '" class="' . esc_attr( $class ) . '" location="' . esc_attr( $location ) . '" hide_atc="' . esc_attr( $hide_atc ) . '"]' );
			}
				$content_html .= '
				</div>
			</div>';
			if ( is_numeric( $menu_onscroll ) && $menu_onscroll > 0 && $i <= $menu_onscroll ) {
				unset( $woore_mngr[ $k_mn ] );
				if ( $i == $menu_onscroll ) {
					break;
				}
			}
		}
		if ( count( $woore_mngr ) > 0 ) {
			$filter_html   .= '<a class="filtermngr-item wrsf-btmore" alt="' . esc_html__( 'Scroll down to see more!', 'woorestaurant' ) . '" href="javascript:;" data-menu="wrsmmore" data-id="' . esc_attr( $id_sc ) . '">' . esc_html__( 'More...', 'woorestaurant' ) . '</a>';
			$filter_slhtml .= '<option value="wrsmmore">' . esc_html__( 'More...', 'woorestaurant' ) . '</option>';
		}
	}
	$output = array(
		'html_content'  => $content_html,
		'arr_menu'      => ( json_encode( $woore_mngr ) ),
		'html_infilter' => $filter_html,
		'html_slfilter' => $filter_slhtml,
	);
	echo esc_attr( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}
