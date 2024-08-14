<?php
/**
 * This is the shortcode class file.
 *
 * @package woorestaurant
 */

namespace WooRestaurant;

/**
 * This is the Shortcode class.
 */
class Shortcodes {

	/**
	 * This is the constructor method.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'wrmb2_admin_init', array( $this, 'register_metabox' ) );
		add_action( 'save_post', array( $this, 'save_shortcode' ), 1 );
		add_shortcode( 'wooresfc', array( $this, 'run_extpsc' ) );
	}

	/**
	 * This function is getting location by menu id.
	 *
	 * @param int $menu_id it contains the menu id.
	 *
	 * @return bool
	 */
	public function is_location_by_menu_woo( $menu_id ) {

		if ( is_admin() ) {
			return false;
		}

		$location = woo_restaurant_get_option( 'woo_restaurant_multi_location', 'woo_restaurant_advanced_options' );
		if ( 'enable' == $location ) {
			$loc       = WC()->session->get( 'wr_userloc' );
			$locations = get_the_terms( $menu_id, 'woorestaurant_loc' );
			if ( ! $locations ) {
				return false;
			}
			foreach ( $locations as $location ) {
				if ( $loc == $location->slug ) {
					return true;
				}
			}
			return false;
		} else {
			return true;
		}
	}

	/**
	 * This function is creating shortcode.
	 *
	 * @param array  $atts it is having attributes.
	 * @param string $content this is having the content of the shortcode.
	 *
	 * @return string
	 */
	public function run_extpsc( $atts, $content ) {
		$id = isset( $atts['id'] ) ? $atts['id'] : '';
		$sc = get_post_meta( $id, '_tpsc', true );
		if ( '' == $id || '' == $sc ) {
			return;
		}

		$is_show = $this->is_location_by_menu_woo( $id );

		$enb_mnd = isset( $atts['enb_mnd'] ) ? $atts['enb_mnd'] : '';

		if ( 'yes' == $enb_mnd ) {
			$sc = str_replace( ']', ' enb_mnd="yes"]', $sc );
		}
		if ( ! $is_show ) {
			$sc = str_replace( ']', ' showlocx="no"]', $sc );
		}
		// phpcs:ignore
		return do_shortcode( $sc );
	}

	/**
	 * This function is use to save code.
	 *
	 * @param int $post_id it is the id of the post.
	 */
	public function save_shortcode( $post_id ) {
		if ( 'woorestaurant_scbd' != get_post_type() ) {
			return;
		}

		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'save-shortcode' ) ) {
				return;
			}
		}

		if ( isset( $_POST['sc_type'] ) ) {
			$layout         = isset( $_POST['sc_layout'] ) ? wp_kses_post( wp_unslash( $_POST['sc_layout'] ) ) : 'grid';
			$style          = isset( $_POST['style'] ) ? wp_kses_post( wp_unslash( $_POST['style'] ) ) : 1;
			$column         = isset( $_POST['column'] ) ? wp_kses_post( wp_unslash( $_POST['column'] ) ) : 3;
			$count          = isset( $_POST['count'] ) && '' != $_POST['count'] ? wp_kses_post( wp_unslash( $_POST['count'] ) ) : '9';
			$posts_per_page = isset( $_POST['posts_per_page'] ) ? wp_kses_post( wp_unslash( $_POST['posts_per_page'] ) ) : '';
			$heading        = isset( $_POST['sc_heading'] ) ? wp_kses_post( wp_unslash( $_POST['sc_heading'] ) ) : '';
			$slidesshow     = isset( $_POST['slidesshow'] ) ? wp_kses_post( wp_unslash( $_POST['slidesshow'] ) ) : '';
			$ids            = isset( $_POST['ids'] ) ? wp_kses_post( wp_unslash( $_POST['ids'] ) ) : '';
			// phpcs:ignore
			$cat            = isset( $_POST['cat'] ) ? implode( ',', ( wp_unslash( $_POST['cat'] ) ) ) : '';
			$order_cat      = isset( $_POST['order_cat'] ) ? wp_kses_post( wp_unslash( $_POST['order_cat'] ) ) : '';
			$order          = isset( $_POST['order'] ) ? wp_kses_post( wp_unslash( $_POST['order'] ) ) : '';
			$orderby        = isset( $_POST['orderby'] ) ? wp_kses_post( wp_unslash( $_POST['orderby'] ) ) : '';
			$meta_key       = isset( $_POST['meta_key'] ) ? wp_kses_post( wp_unslash( $_POST['meta_key'] ) ) : '';
			$meta_value     = isset( $_POST['meta_value'] ) ? wp_kses_post( wp_unslash( $_POST['meta_value'] ) ) : '';
			$number_excerpt = isset( $_POST['number_excerpt'] ) ? wp_kses_post( wp_unslash( $_POST['number_excerpt'] ) ) : '';
			$page_navi      = isset( $_POST['page_navi'] ) ? wp_kses_post( wp_unslash( $_POST['page_navi'] ) ) : '';
			$cart_enable    = isset( $_POST['cart_enable'] ) ? wp_kses_post( wp_unslash( $_POST['cart_enable'] ) ) : '';
			$enable_search  = isset( $_POST['enable_search'] ) ? wp_kses_post( wp_unslash( $_POST['enable_search'] ) ) : '';
			$menu_filter    = isset( $_POST['menu_filter'] ) ? wp_kses_post( wp_unslash( $_POST['menu_filter'] ) ) : '';
			$show_count     = isset( $_POST['show_count'] ) ? wp_kses_post( wp_unslash( $_POST['show_count'] ) ) : '';
			$active_filter  = isset( $_POST['active_filter'] ) ? wp_kses_post( wp_unslash( $_POST['active_filter'] ) ) : '';
			$menu_pos       = isset( $_POST['menu_pos'] ) ? wp_kses_post( wp_unslash( $_POST['menu_pos'] ) ) : '';
			$enable_modal   = isset( $_POST['enable_modal'] ) ? wp_kses_post( wp_unslash( $_POST['enable_modal'] ) ) : '';
			$featured       = isset( $_POST['featured'] ) ? wp_kses_post( wp_unslash( $_POST['featured'] ) ) : '';
			$live_sort      = isset( $_POST['live_sort'] ) ? wp_kses_post( wp_unslash( $_POST['live_sort'] ) ) : '';
			$autoplay       = isset( $_POST['autoplay'] ) ? wp_kses_post( wp_unslash( $_POST['autoplay'] ) ) : '';
			$autoplayspeed  = isset( $_POST['autoplayspeed'] ) ? wp_kses_post( wp_unslash( $_POST['autoplayspeed'] ) ) : '';
			$loading_effect = isset( $_POST['loading_effect'] ) ? wp_kses_post( wp_unslash( $_POST['loading_effect'] ) ) : '';
			$infinite       = isset( $_POST['infinite'] ) ? wp_kses_post( wp_unslash( $_POST['infinite'] ) ) : '';
			$filter_style   = isset( $_POST['filter_style'] ) ? wp_kses_post( wp_unslash( $_POST['filter_style'] ) ) : '';
			$hide_ftall     = isset( $_POST['hide_ftall'] ) ? wp_kses_post( wp_unslash( $_POST['hide_ftall'] ) ) : '';
			$img_size       = isset( $_POST['img_size'] ) ? wp_kses_post( wp_unslash( $_POST['img_size'] ) ) : '';
			$class          = isset( $_POST['class'] ) ? wp_kses_post( wp_unslash( $_POST['class'] ) ) : '';
			$hide_atc       = isset( $_POST['hide_atc'] ) ? wp_kses_post( wp_unslash( $_POST['hide_atc'] ) ) : '';
			$enable_mtod    = isset( $_POST['enable_mtod'] ) ? wp_kses_post( wp_unslash( $_POST['enable_mtod'] ) ) : '';

			if ( 'grid' == $_POST['sc_type'] ) {

				$sc = '[woores_view_grid style="' . esc_attr( $style ) . '" column="' . esc_attr( $column ) . '" count="' . esc_attr( $count ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" ids="' . esc_attr( $ids ) . '" cat="' . esc_attr( $cat ) . '" order="' . esc_attr( $order ) . '" orderby="' . esc_attr( $orderby ) . '" meta_key="' . esc_attr( $meta_key ) . '" meta_value="' . esc_attr( $meta_value ) . '" number_excerpt="' . esc_attr( $number_excerpt ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_search="' . esc_attr( $enable_search ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" menu_filter="' . esc_attr( $menu_filter ) . '" show_count="' . esc_attr( $show_count ) . '" filter_style="' . esc_attr( $filter_style ) . '" hide_ftall="' . esc_attr( $hide_ftall ) . '" active_filter="' . esc_attr( $active_filter ) . '" order_cat="' . esc_attr( $order_cat ) . '" page_navi="' . esc_attr( $page_navi ) . '" featured="' . esc_attr( $featured ) . '" img_size="' . esc_attr( $img_size ) . '" hide_atc="' . esc_attr( $hide_atc ) . '" enable_mtod="' . esc_attr( $enable_mtod ) . '" class="' . esc_attr( $class ) . '"]';
			} elseif ( 'list' == $_POST['sc_type'] ) {
				$sc = '[woores_view_list style="' . esc_attr( $style ) . '" count="' . esc_attr( $count ) . '" column="' . esc_attr( $column ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" ids="' . esc_attr( $ids ) . '" cat="' . esc_attr( $cat ) . '" order="' . esc_attr( $order ) . '" orderby="' . esc_attr( $orderby ) . '" meta_key="' . esc_attr( $meta_key ) . '" meta_value="' . esc_attr( $meta_value ) . '" number_excerpt="' . esc_attr( $number_excerpt ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_search="' . esc_attr( $enable_search ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" menu_filter="' . esc_attr( $menu_filter ) . '" show_count="' . esc_attr( $show_count ) . '" filter_style="' . esc_attr( $filter_style ) . '" hide_ftall="' . esc_attr( $hide_ftall ) . '" active_filter="' . esc_attr( $active_filter ) . '" order_cat="' . esc_attr( $order_cat ) . '" menu_pos="' . esc_attr( $menu_pos ) . '"  page_navi="' . esc_attr( $page_navi ) . '" featured="' . esc_attr( $featured ) . '" img_size="' . esc_attr( $img_size ) . '" hide_atc="' . esc_attr( $hide_atc ) . '" enable_mtod="' . esc_attr( $enable_mtod ) . '" class="' . esc_attr( $class ) . '"]';
			} elseif ( 'table' == $_POST['sc_type'] ) {

				$sc = '[woores_view_table style="' . esc_attr( $style ) . '" count="' . esc_attr( $count ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" ids="' . esc_attr( $ids ) . '" cat="' . esc_attr( $cat ) . '" order="' . esc_attr( $order ) . '" orderby="' . esc_attr( $orderby ) . '" meta_key="' . esc_attr( $meta_key ) . '" meta_value="' . esc_attr( $meta_value ) . '" number_excerpt="' . esc_attr( $number_excerpt ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_search="' . esc_attr( $enable_search ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" menu_filter="' . esc_attr( $menu_filter ) . '" show_count="' . esc_attr( $show_count ) . '" filter_style="' . esc_attr( $filter_style ) . '" hide_ftall="' . esc_attr( $hide_ftall ) . '" active_filter="' . esc_attr( $active_filter ) . '" order_cat="' . esc_attr( $order_cat ) . '" live_sort="' . esc_attr( $live_sort ) . '"  page_navi="' . esc_attr( $page_navi ) . '" featured="' . esc_attr( $featured ) . '" img_size="' . esc_attr( $img_size ) . '" hide_atc="' . esc_attr( $hide_atc ) . '"  enable_mtod="' . esc_attr( $enable_mtod ) . '" class="' . esc_attr( $class ) . '"]';
			} elseif ( 'mn_group' == $_POST['sc_type'] ) {
				$sc = '[woores_view_mngroup layout="' . esc_attr( $layout ) . '" style="' . esc_attr( $style ) . '" column="' . esc_attr( $column ) . '" count="' . esc_attr( $count ) . '" posts_per_page="' . esc_attr( $posts_per_page ) . '" heading_style="' . esc_attr( $heading ) . '" cat="' . esc_attr( $cat ) . '" order="' . esc_attr( $order ) . '" orderby="' . esc_attr( $orderby ) . '" meta_key="' . esc_attr( $meta_key ) . '" meta_value="' . esc_attr( $meta_value ) . '" number_excerpt="' . esc_attr( $number_excerpt ) . '" cart_enable="' . esc_attr( $cart_enable ) . '"  enable_modal="' . esc_attr( $enable_modal ) . '"  order_cat="' . esc_attr( $order_cat ) . '"  page_navi="' . esc_attr( $page_navi ) . '" featured="' . esc_attr( $featured ) . '" img_size="' . esc_attr( $img_size ) . '" hide_atc="' . esc_attr( $hide_atc ) . '"  enable_mtod="' . esc_attr( $enable_mtod ) . '" show_count="' . esc_attr( $show_count ) . '" class="' . esc_attr( $class ) . '"]';
			} else {
				$sc = '[woores_view_carousel style="' . esc_attr( $style ) . '" count="' . esc_attr( $count ) . '" slidesshow="' . esc_attr( $slidesshow ) . '" ids="' . esc_attr( $ids ) . '" cat="' . esc_attr( $cat ) . '" order="' . esc_attr( $order ) . '" orderby="' . esc_attr( $orderby ) . '" meta_key="' . esc_attr( $meta_key ) . '" meta_value="' . esc_attr( $meta_value ) . '" number_excerpt="' . esc_attr( $number_excerpt ) . '"  autoplay="' . esc_attr( $autoplay ) . '" cart_enable="' . esc_attr( $cart_enable ) . '" enable_modal="' . esc_attr( $enable_modal ) . '" autoplayspeed="' . esc_attr( $autoplayspeed ) . '" loading_effect="' . esc_attr( $loading_effect ) . '" infinite="' . esc_attr( $infinite ) . '" featured="' . esc_attr( $featured ) . '" img_size="' . esc_attr( $img_size ) . '" hide_atc="' . esc_attr( $hide_atc ) . '"  enable_mtod="' . esc_attr( $enable_mtod ) . '" class="' . esc_attr( $class ) . '"]';
			}
			if ( '' != $sc ) {
				update_post_meta( $post_id, '_tpsc', $sc );
			}
			update_post_meta( $post_id, '_shortcode', '[wooresfc id="' . $post_id . '"]' );
		}
	}

	/**
	 * Registering the Menu posttype.
	 */
	public function register_post_type() {
		$labels  = array(
			'name'               => esc_html__( 'Menu', 'woorestaurant' ),
			'singular_name'      => esc_html__( 'Menu', 'woorestaurant' ),
			'add_new'            => esc_html__( 'Add New Menu', 'woorestaurant' ),
			'add_new_item'       => esc_html__( 'Add New Menu', 'woorestaurant' ),
			'edit_item'          => esc_html__( 'Edit Menu', 'woorestaurant' ),
			'new_item'           => esc_html__( 'New Menu', 'woorestaurant' ),
			'all_items'          => esc_html__( 'Create Menu', 'woorestaurant' ),
			'view_item'          => esc_html__( 'View Menu', 'woorestaurant' ),
			'search_items'       => esc_html__( 'Search Menu', 'woorestaurant' ),
			'not_found'          => esc_html__( 'No Menu found', 'woorestaurant' ),
			'not_found_in_trash' => esc_html__( 'No Menu found in Trash', 'woorestaurant' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'Menu Builder', 'woorestaurant' ),
		);
		$rewrite = false;
		$args    = array(
			'labels'             => $labels,
			'supports'           => array( 'title', 'custom-fields' ),
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'woo-restaurant',
			'menu_icon'          => 'dashicons-editor-ul',
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'rewrite'            => $rewrite,
		);
		register_post_type( 'woorestaurant_scbd', $args );
	}

	/**
	 * Registering the Shortcode type metabox.
	 */
	public function register_metabox() {
		/**
		 * Sample metabox to demonstrate each field type included.
		 */
		$layout = new_wrmb2_box(
			array(
				'id'           => 'wooresf_sc',
				'title'        => esc_html__( 'Shortcode type', 'woorestaurant' ),
				'object_types' => array( 'woorestaurant_scbd' ), // Post type.
			)
		);

		$layout->add_field(
			array(
				'name'             => esc_html__( 'Type', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select type of shortcode', 'woorestaurant' ),
				'id'               => 'sc_type',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => 'grid',
				'options'          => array(
					'grid'     => esc_html__( 'Grid', 'woorestaurant' ),
					'table'    => esc_html__( 'Table', 'woorestaurant' ),
					'list'     => esc_html__( 'List', 'woorestaurant' ),
					'carousel' => esc_html__( 'Carousel', 'woorestaurant' ),
					'mn_group' => esc_html__( 'Menu Group', 'woorestaurant' ),
					'menu_new' => esc_html__( 'Menu New', 'woorestaurant' ),
				),
				'classes'          => '',
			)
		);

		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'register-metabox' ) ) {
				return;
			}
		}

		if ( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
			$layout->add_field(
				array(
					'name'       => esc_html__( 'Shortcode', 'woorestaurant' ),
					'desc'       => esc_html__( 'Copy this shortcode and paste it into your post, page, or text widget content:', 'woorestaurant' ),
					'id'         => '_shortcode',
					'type'       => 'text',
					'classes'    => '',
					'attributes' => array(
						'readonly' => 'readonly',
					),
				)
			);
		}
		$layout->add_field(
			array(

				'name'           => esc_html__( 'Location', 'woorestaurant' ),
				'desc'           => esc_html__( 'Select Locations', 'woorestaurant' ),
				'id'             => 'location',
				'classes'        => 'column-3 fullwidth nodesclabel',

				'taxonomy'       => 'woorestaurant_loc', // Enter Taxonomy Slug.
				'type'           => 'taxonomy_multicheck_inline',
				// Optional.
				'text'           => array(
					'no_terms_text' => 'Sorry, no location could be found.', // Change default text. Default: "No terms".
				),
				'remove_default' => 'true', // Removes the default metabox provided by WP core.
				// Optionally override the args sent to the WordPress get_terms function.
				'query_args'     => array(
					// 'orderby' => 'slug',.
					'hide_empty' => false,
				),

			)
		);

		$sc_option = new_wrmb2_box(
			array(
				'id'           => 'scwf_option',
				'title'        => esc_html__( 'Shortcode Option', 'woorestaurant' ),
				'object_types' => array( 'woorestaurant_scbd' ),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Layout', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select Layout of Menu group', 'woorestaurant' ),
				'id'               => 'sc_layout',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => 'grid',
				'options'          => array(
					'grid'  => esc_html__( 'Grid', 'woorestaurant' ),
					'table' => esc_html__( 'Table', 'woorestaurant' ),
					'list'  => esc_html__( 'List', 'woorestaurant' ),
				),
				'classes'          => 'column-3 hide-incarousel hide-ingrid hide-inmenu_new hide-intable hide-inlist show-mn_group',
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Style', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select style of shortcode', 'woorestaurant' ),
				'id'               => 'style',
				'type'             => 'select',
				'classes'          => 'column-3',
				'show_option_none' => false,
				'default'          => '1',
				'options'          => array(
					'1' => esc_html__( '1', 'woorestaurant' ),
					'2' => esc_html__( '2', 'woorestaurant' ),
					'3' => esc_html__( '3', 'woorestaurant' ),
					'4' => esc_html__( '4', 'woorestaurant' ),
				),
			)
		);

		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Columns', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select Columns of shortcode', 'woorestaurant' ),
				'id'               => 'column',
				'type'             => 'select',
				'classes'          => 'column-3 hide-incarousel hide-intable show-inlist show-ingrid hide-inmenu_new',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					'1' => esc_html__( '1 column', 'woorestaurant' ),
					'2' => esc_html__( '2 columns', 'woorestaurant' ),
					'3' => esc_html__( '3 columns', 'woorestaurant' ),
					'4' => esc_html__( '4 columns', 'woorestaurant' ),
					'5' => esc_html__( '5 columns', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Heading style', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select Heading style', 'woorestaurant' ),
				'id'               => 'sc_heading',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''  => esc_html__( 'Default', 'woorestaurant' ),
					'1' => esc_html__( '1', 'woorestaurant' ),
					'2' => esc_html__( '2', 'woorestaurant' ),
					'3' => esc_html__( '3', 'woorestaurant' ),
					'4' => esc_html__( '4', 'woorestaurant' ),
				),
				'classes'          => 'column-3 hide-incarousel hide-ingrid hide-intable hide-inlist show-mn_group',
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Count', 'woorestaurant' ),
				'desc'    => esc_html__( 'Number of posts', 'woorestaurant' ),
				'id'      => 'count',
				'type'    => 'text',
				'classes' => 'column-3',
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Posts per page', 'woorestaurant' ),
				'desc'    => esc_html__( 'Number items per page', 'woorestaurant' ),
				'id'      => 'posts_per_page',
				'type'    => 'text',
				'classes' => 'column-3 hide-incarousel show-intable show-inlist show-ingrid',
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Number items visible', 'woorestaurant' ),
				'desc'    => esc_html__( 'Enter number', 'woorestaurant' ),
				'id'      => 'slidesshow',
				'type'    => 'text',
				'classes' => 'column-3 show-incarousel hide-intable hide-inlist hide-ingrid hide-mn_group',
			)
		);

		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Order', 'woorestaurant' ),
				'desc'             => '',
				'id'               => 'order',
				'type'             => 'select',
				'classes'          => 'column-2',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					'DESC' => esc_html__( 'DESC', 'woorestaurant' ),
					'ASC'  => esc_html__( 'ASC', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Order by', 'woorestaurant' ),
				'desc'             => '',
				'id'               => 'orderby',
				'type'             => 'select',
				'classes'          => 'column-2',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					'date'           => esc_html__( 'Date', 'woorestaurant' ),
					'order_field'    => esc_html__( 'Custom order field', 'woorestaurant' ),
					'sale'           => esc_html__( 'Sale', 'woorestaurant' ),
					'ID'             => esc_html__( 'ID', 'woorestaurant' ),
					'author'         => esc_html__( 'Author', 'woorestaurant' ),
					'title'          => esc_html__( 'Title', 'woorestaurant' ),
					'name'           => esc_html__( 'Name', 'woorestaurant' ),
					'modified'       => esc_html__( 'Modified', 'woorestaurant' ),
					'parent'         => esc_html__( 'Parent', 'woorestaurant' ),
					'rand'           => esc_html__( 'Rand', 'woorestaurant' ),
					'menu_order'     => esc_html__( 'Menu order', 'woorestaurant' ),
					'meta_value'     => esc_html__( 'Meta value', 'woorestaurant' ),
					'meta_value_num' => esc_html__( 'Meta value num', 'woorestaurant' ),
					'post__in'       => esc_html__( 'Post__in', 'woorestaurant' ),
					'None'           => esc_html__( 'None', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Meta key', 'woorestaurant' ),
				'desc'    => esc_html__( 'Enter meta key to query', 'woorestaurant' ),
				'id'      => 'meta_key',
				'type'    => 'text',
				'classes' => 'column-2',
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Meta value', 'woorestaurant' ),
				'desc'    => esc_html__( 'Enter meta value to query', 'woorestaurant' ),
				'id'      => 'meta_value',
				'type'    => 'text',
				'classes' => 'column-2',
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Number of Excerpt', 'woorestaurant' ),
				'desc'    => esc_html__( 'Enter number', 'woorestaurant' ),
				'id'      => 'number_excerpt',
				'type'    => 'text',
				'classes' => 'column-2',
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Page navi', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select type of page navigation', 'woorestaurant' ),
				'id'               => 'page_navi',
				'type'             => 'select',
				'classes'          => 'column-2 hide-incarousel show-intable show-inlist show-ingrid',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''         => esc_html__( 'Number', 'woorestaurant' ),
					'loadmore' => esc_html__( 'Load more', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Menu filter', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select show or hide menu filter bar', 'woorestaurant' ),
				'id'               => 'menu_filter',
				'type'             => 'select',
				'classes'          => 'column-3 hide-incarousel show-intable show-inlist show-ingrid hide-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					'hide' => esc_html__( 'Hide', 'woorestaurant' ),
					'show' => esc_html__( 'Show', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Menu/Category count', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select Yes to show menu/category count', 'woorestaurant' ),
				'id'               => 'show_count',
				'type'             => 'select',
				'classes'          => 'column-3 hide-incarousel show-intable show-inlist show-ingrid show-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'No', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Menu filter style', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select Menu filter style', 'woorestaurant' ),
				'id'               => 'filter_style',
				'type'             => 'select',
				'classes'          => 'column-3 hide-incarousel show-intable show-inlist show-ingrid hide-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''     => esc_html__( 'Default', 'woorestaurant' ),
					'icon' => esc_html__( 'Icon', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Active filter', 'woorestaurant' ),
				'desc'    => esc_html__( 'Enter slug of menu to active', 'woorestaurant' ),
				'id'      => 'active_filter',
				'type'    => 'text',
				'classes' => 'column-2 hide-incarousel show-intable show-inlist show-ingrid hide-mn_group',
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Order Menu Filter', 'woorestaurant' ),
				'desc'             => esc_html__( 'Order Menu Filter with custom order', 'woorestaurant' ),
				'id'               => 'order_cat',
				'type'             => 'select',
				'classes'          => 'column-2 hide-incarousel show-intable show-inlist show-ingrid',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'No', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( "Hide 'All' Filter", 'woorestaurant' ),
				'desc'             => esc_html__( "Select 'yes' to disalbe 'All' filter", 'woorestaurant' ),
				'id'               => 'hide_ftall',
				'type'             => 'select',
				'classes'          => 'column-2 hide-incarousel show-intable show-inlist show-ingrid hide-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'No', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Menu filter Position', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select posstion of menu filter', 'woorestaurant' ),
				'id'               => 'menu_pos',
				'type'             => 'select',
				'classes'          => 'column-2 hide-incarousel hide-intable show-inlist hide-ingrid hide-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					'top'  => esc_html__( 'Top', 'woorestaurant' ),
					'left' => esc_html__( 'Left', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Show Side cart', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select show or hide side cart', 'woorestaurant' ),
				'id'               => 'cart_enable',
				'type'             => 'select',
				'classes'          => 'column-3',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'Default', 'woorestaurant' ),
					'yes' => esc_html__( 'Show', 'woorestaurant' ),
					'no'  => esc_html__( 'Hide', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Enable Ajax Search', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select yes to enable ajax search feature', 'woorestaurant' ),
				'id'               => 'enable_search',
				'type'             => 'select',
				'classes'          => 'column-3 hide-incarousel hide-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'No', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Live Sort', 'woorestaurant' ),
				'desc'             => esc_html__( 'Enable Live Sort', 'woorestaurant' ),
				'id'               => 'live_sort',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''  => esc_html__( 'No', 'woorestaurant' ),
					'1' => esc_html__( 'Yes', 'woorestaurant' ),
				),
				'classes'          => 'column-3 hide-incarousel show-intable hide-inlist hide-ingrid hide-mn_group',
			)
		);

		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Autoplay', 'woorestaurant' ),
				'desc'             => esc_html__( 'Enable Autoplay', 'woorestaurant' ),
				'id'               => 'autoplay',
				'type'             => 'select',
				'classes'          => 'column-2 show-incarousel hide-intable hide-inlist hide-ingrid hide-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''  => esc_html__( 'No', 'woorestaurant' ),
					'1' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Autoplay Speed', 'woorestaurant' ),
				'desc'    => esc_html__( 'Autoplay Speed in milliseconds. Default:3000', 'woorestaurant' ),
				'id'      => 'autoplayspeed',
				'type'    => 'text',
				'classes' => 'column-2 show-incarousel hide-intable hide-inlist hide-ingrid hide-mn_group',
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Loading effect', 'woorestaurant' ),
				'desc'             => esc_html__( 'Enable Loading effect', 'woorestaurant' ),
				'id'               => 'loading_effect',
				'type'             => 'select',
				'classes'          => 'column-2 show-incarousel hide-intable hide-inlist hide-ingrid hide-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''  => esc_html__( 'No', 'woorestaurant' ),
					'1' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Infinite', 'woorestaurant' ),
				'desc'             => esc_html__( 'Infinite loop sliding ( go to first item when end loop)', 'woorestaurant' ),
				'id'               => 'infinite',
				'type'             => 'select',
				'classes'          => 'column-2 show-incarousel hide-intable hide-inlist hide-ingrid hide-mn_group',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'No', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Enable modal', 'woorestaurant' ),
				'desc'             => esc_html__( 'Enable modal details food info', 'woorestaurant' ),
				'id'               => 'enable_modal',
				'type'             => 'select',
				'classes'          => 'column-3',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'Default', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
					'no'  => esc_html__( 'No', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Featured food', 'woorestaurant' ),
				'desc'             => esc_html__( 'Show only Featured food', 'woorestaurant' ),
				'id'               => 'featured',
				'type'             => 'select',
				'classes'          => 'column-3',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''  => esc_html__( 'No', 'woorestaurant' ),
					'1' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Hide add to cart form', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select Yes to hide add to cart form', 'woorestaurant' ),
				'id'               => 'hide_atc',
				'type'             => 'select',
				'classes'          => 'column-3',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'No', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'             => esc_html__( 'Popup order method', 'woorestaurant' ),
				'desc'             => esc_html__( 'Enable popup select order method', 'woorestaurant' ),
				'id'               => 'enable_mtod',
				'type'             => 'select',
				'classes'          => 'column-3',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'Default', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
					'no'  => esc_html__( 'no', 'woorestaurant' ),
				),
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Image Size', 'woorestaurant' ),
				'desc'    => esc_html__( 'Leave blank to use default image size', 'woorestaurant' ),
				'id'      => 'img_size',
				'type'    => 'text',
				'classes' => 'column-3',
			)
		);
		$sc_option->add_field(
			array(
				'name'    => esc_html__( 'Class name', 'woorestaurant' ),
				'desc'    => esc_html__( 'add a class name and refer to it in custom CSS', 'woorestaurant' ),
				'id'      => 'class',
				'type'    => 'text',
				'classes' => 'column-3',
			)
		);

		$sc_option->add_field(
			array(
				'name'            => esc_html__( 'IDs', 'woorestaurant' ),
				'id'              => 'ids',
				'classes'         => 'column-3 hide-mn_group',
				'type'            => 'post_search_text',
				'desc'            => esc_html__( 'Select product to apply this options', 'woorestaurant' ),
				'post_type'       => 'product',
				'select_type'     => 'checkbox',
				'select_behavior' => 'add',
				'after_field'     => '',
				'sanitization_cb' => 'woores_save_single_id_prod',
			)
		);
		$sc_option->add_field(
			array(

				'name'           => esc_html__( 'Categories', 'woorestaurant' ),
				'desc'           => esc_html__( 'Select categories', 'woorestaurant' ),
				'id'             => 'cat',
				'classes'        => 'column-3 fullwidth nodesclabel',
				'taxonomy'       => 'product_cat', // Enter Taxonomy Slug.
				'type'           => 'taxonomy_multicheck_inline',
				'text'           => array(
					'no_terms_text' => 'Sorry, no terms could be found.', // Change default text. Default: "No terms".
				),
				'remove_default' => 'true', // Removes the default metabox provided by WP core.
				// Optionally override the args sent to the WordPress get_terms function.
				'query_args'     => array(
					// 'orderby' => 'slug',.
					'hide_empty' => true,
				),

			)
		);
	}
}
