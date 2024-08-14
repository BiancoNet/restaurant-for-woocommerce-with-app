<?php
/**
 * It is having the functions related to taxonomies.
 *
 * @package woorestaurant
 */

namespace WooRestaurant;

/**
 * This is the taxonomy class.
 */
class Taxonomy {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_category_taxonomies' ) );
		add_action( 'init', array( $this, 'register_location_taxonomies' ) );
		add_action( 'wrmb2_admin_init', array( $this, 'register_taxonomy_category_metabox' ) );
		add_filter( 'manage_edit-product_cat_columns', array( $this, '_edit_columns_woo_restaurant_menu' ) );
		add_action( 'manage_product_cat_custom_column', array( $this, '_custom_columns_content_woo_restaurant_menu' ), 10, 3 );
	}

	/**
	 * Registering Category Taxonomy.
	 */
	public function register_category_taxonomies() {
		$labels = array(
			'name'              => esc_html__( 'Food Menu', 'woorestaurant' ),
			'singular_name'     => esc_html__( 'Food Menu', 'woorestaurant' ),
			'search_items'      => esc_html__( 'Food Menu', 'woorestaurant' ),
			'all_items'         => esc_html__( 'All Menu', 'woorestaurant' ),
			'parent_item'       => esc_html__( 'Parent Menu', 'woorestaurant' ),
			'parent_item_colon' => esc_html__( 'Parent Menu:', 'woorestaurant' ),
			'edit_item'         => esc_html__( 'Edit Menu', 'woorestaurant' ),
			'update_item'       => esc_html__( 'Update Menu', 'woorestaurant' ),
			'add_new_item'      => esc_html__( 'Add New Menu', 'woorestaurant' ),
			'menu_name'         => esc_html__( 'Food Menus', 'woorestaurant' ),
		);
		$args   = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'food-menu' ),
		);
	}

	/**
	 * Registering Location Taxonomy.
	 */
	public function register_location_taxonomies() {
		$labels     = array(
			'name'              => esc_html__( 'Location', 'woorestaurant' ),
			'singular_name'     => esc_html__( 'Location', 'woorestaurant' ),
			'search_items'      => esc_html__( 'Location', 'woorestaurant' ),
			'all_items'         => esc_html__( 'All Location', 'woorestaurant' ),
			'parent_item'       => esc_html__( 'Parent Location', 'woorestaurant' ),
			'parent_item_colon' => esc_html__( 'Parent Location:', 'woorestaurant' ),
			'edit_item'         => esc_html__( 'Edit Location', 'woorestaurant' ),
			'update_item'       => esc_html__( 'Update Location', 'woorestaurant' ),
			'add_new_item'      => esc_html__( 'Add New Location', 'woorestaurant' ),
			'menu_name'         => esc_html__( 'Food Locations', 'woorestaurant' ),
		);
		$args       = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'food-menu' ),
		);
		$menu_byloc = 'no';// woo_restaurant_get_option('woo_restaurant_enable_loc','woo_restaurant_options');.
		if ( 'yes' == $menu_byloc ) {
			register_taxonomy( 'woorestaurant_loc', array( 'woores_menubydate' ), $args );
		} else {
			register_taxonomy( 'woorestaurant_loc', array( 'product', 'woorestaurant_scbd' ), $args );
			// register_taxonomy('woorestaurant_loc', $args);.
		}
	}

	/**
	 * Register email field in location.
	 */
	public function register_taxonomy_category_metabox() {
		$prefix = 'wooreswp_loc_';
		/**
		 * Metabox to add fields to categories and tags.
		 */
		$woores_log_meta = new_wrmb2_box(
			array(
				'id'               => $prefix . 'data',
				'title'            => esc_html__( 'Category Metabox', 'woorestaurant' ), // Doesn't output for term boxes.
				'object_types'     => array( 'term' ), // Tells WRMB2 to use term_meta vs post_meta.
				'taxonomies'       => array( 'woorestaurant_loc' ), // Tells WRMB2 which taxonomies should have these fields.
				'new_term_section' => true, // Will display in the "Add New Category" section.
			)
		);

		do_action( 'wooreswp_loc_fields_before', $woores_log_meta );
		$woores_log_meta->add_field(
			array(
				'name' => esc_html__( 'Address', 'woorestaurant' ),
				'id'   => $prefix . 'address',
				'desc' => esc_html__( 'Add full address of this location to calculate radius shipping', 'woorestaurant' ),
				'type' => 'text',
			)
		);
		$woores_log_meta->add_field(
			array(
				'name' => esc_html__( 'Distance restrict (km)', 'woorestaurant' ),
				'id'   => $prefix . 'diskm',
				'desc' => esc_html__( 'Enter number of kilometer to restrict delivery for this location, leave blank to use value from setting page', 'woorestaurant' ),
				'type' => 'text',
			)
		);
		$woores_log_meta->add_field(
			array(
				'name' => esc_html__( 'Minimum Order Amount required', 'woorestaurant' ),
				'id'   => $prefix . 'min_amount',
				'desc' => esc_html__( 'Set minimum amount required for this location', 'woorestaurant' ),
				'type' => 'text',
			)
		);
		$woores_log_meta->add_field(
			array(
				'name'            => esc_html__( 'Shipping fee', 'woorestaurant' ),
				'desc'            => esc_html__( 'Set Shipping fee for delivery, enter number', 'woorestaurant' ),
				'id'              => $prefix . 'ship_fee',
				'type'            => 'text',
				'sanitization_cb' => '',
			)
		);
		$woores_log_meta->add_field(
			array(
				'name'            => esc_html__( 'Minimum order amount to free shipping', 'woorestaurant' ),
				'desc'            => esc_html__( 'Enter number', 'woorestaurant' ),
				'id'              => $prefix . 'ship_free',
				'type'            => 'text',
				'sanitization_cb' => '',
			)
		);
		$woores_log_meta->add_field(
			array(
				'name' => esc_html__( 'Email recipients', 'woorestaurant' ),
				'id'   => $prefix . 'email',
				'desc' => esc_html__( 'Set email to get notification when user order food from this location', 'woorestaurant' ),
				'type' => 'text',
			)
		);
		$woores_log_meta->add_field(
			array(
				'name'              => esc_html__( 'Hide menu/category filter', 'woorestaurant' ),
				'id'                => $prefix . 'hide_menu',
				'desc'              => esc_html__( 'Select menu/category filter to hide from this location', 'woorestaurant' ),
				'taxonomy'          => 'product_cat',
				'type'              => 'taxonomy_multicheck_inline',
				'remove_default'    => 'true', // Removes the default metabox provided by WP core.
				'select_all_button' => false,
				'query_args'        => array(
				// 'orderby' => 'slug',
				// 'hide_empty' => true,
				),
				'classes'           => 'wrmb-type-taxonomy-multicheck-inline',
			)
		);
		// Open close time.
		$loc_opcls = woo_restaurant_get_option( 'woo_restaurant_open_close_loc', 'woo_restaurant_advanced_options' );
		if ( 'yes' == $loc_opcls ) {
			$woores_log_meta->add_field(
				array(
					'name'       => esc_html__( 'Opening and Closing time', 'woorestaurant' ),
					'desc'       => esc_html__( 'Leave blank to use value from setting', 'woorestaurant' ),
					'id'         => 'woores_op_cl',
					'type'       => 'title',
					'before_row' => '<div class="woores-collapse">',
				)
			);
			$woores_log_meta->add_field(
				array(
					'name'        => esc_html__( 'Monday', 'woorestaurant' ),
					'id'          => 'woores_Mon_opcl_time',
					'type'        => 'openclose',
					'time_format' => 'H:i',
					'repeatable'  => true,
					'attributes'  => array(
						'data-timepicker' => json_encode(
							array(
								'stepMinute' => 1,
								'timeFormat' => 'HH:mm',
							)
						),
					),
					'before_row'  => '<div class="woores-collapse-con">',
				)
			);
			$woores_log_meta->add_field(
				array(
					'name'        => esc_html__( 'Tuesday', 'woorestaurant' ),
					'id'          => 'woores_Tue_opcl_time',
					'type'        => 'openclose',
					'time_format' => 'H:i',
					'repeatable'  => true,
					'attributes'  => array(
						'data-timepicker' => json_encode(
							array(
								'stepMinute' => 1,
								'timeFormat' => 'HH:mm',
							)
						),
					),
				)
			);
			$woores_log_meta->add_field(
				array(
					'name'        => esc_html__( 'Wednesday', 'woorestaurant' ),
					'id'          => 'woores_Wed_opcl_time',
					'type'        => 'openclose',
					'time_format' => 'H:i',
					'repeatable'  => true,
					'attributes'  => array(
						'data-timepicker' => json_encode(
							array(
								'stepMinute' => 1,
								'timeFormat' => 'HH:mm',
							)
						),
					),
				)
			);
			$woores_log_meta->add_field(
				array(
					'name'        => esc_html__( 'Thursday', 'woorestaurant' ),
					'id'          => 'woores_Thu_opcl_time',
					'type'        => 'openclose',
					'time_format' => 'H:i',
					'repeatable'  => true,
					'attributes'  => array(
						'data-timepicker' => json_encode(
							array(
								'stepMinute' => 1,
								'timeFormat' => 'HH:mm',
							)
						),
					),
				)
			);
			$woores_log_meta->add_field(
				array(
					'name'        => esc_html__( 'Friday', 'woorestaurant' ),
					'id'          => 'woores_Fri_opcl_time',
					'type'        => 'openclose',
					'time_format' => 'H:i',
					'repeatable'  => true,
					'attributes'  => array(
						'data-timepicker' => json_encode(
							array(
								'stepMinute' => 1,
								'timeFormat' => 'HH:mm',
							)
						),
					),

				)
			);
			$woores_log_meta->add_field(
				array(
					'name'        => esc_html__( 'Saturday', 'woorestaurant' ),
					'id'          => 'woores_Sat_opcl_time',
					'type'        => 'openclose',
					'time_format' => 'H:i',
					'repeatable'  => true,
					'attributes'  => array(
						'data-timepicker' => json_encode(
							array(
								'stepMinute' => 1,
								'timeFormat' => 'HH:mm',
							)
						),
					),

				)
			);
			$woores_log_meta->add_field(
				array(
					'name'        => esc_html__( 'Sunday', 'woorestaurant' ),
					'id'          => 'woores_Sun_opcl_time',
					'type'        => 'openclose',
					'time_format' => 'H:i',
					'repeatable'  => true,
					'attributes'  => array(
						'data-timepicker' => json_encode(
							array(
								'stepMinute' => 1,
								'timeFormat' => 'HH:mm',
							)
						),
					),
					'after_row'   => '</div></div>',
				)
			);
		}
		$loc_shipkm = woo_restaurant_get_option( 'woores_km_loc', 'woo_restaurant_shpping_options' );
		if ( 'yes' == $loc_shipkm ) {
			$woores_log_meta->add_field(
				array(
					'name'       => esc_html__( 'Shipping fee by km', 'woorestaurant' ),
					'desc'       => esc_html__( 'Leave blank to use value from setting page', 'woorestaurant' ),
					'id'         => 'woores_sh_km',
					'type'       => 'title',
					'before_row' => '<div class="woores-collapse">',
				)
			);
			$feebykm_option = $woores_log_meta->add_field(
				array(
					'id'           => 'woores_adv_feekm',
					'type'         => 'group',
					'description'  => esc_html__( 'Set shipping fee by km, leave blank to use default shipping fee above', 'woorestaurant' ),
					// 'repeatable'  => false, // use false if you want non-repeatable group
					'options'      => array(
						'group_title'   => esc_html__( 'Shipping fee by km {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
						'add_button'    => esc_html__( 'Add new', 'woorestaurant' ),
						'remove_button' => esc_html__( 'Remove', 'woorestaurant' ),
						'sortable'      => true, // beta.
						'closed'        => false, // true to have the groups closed by default.
					),
					'after_group'  => '</div></div>',
					'before_group' => '<div class="woores-collapse-con">',
				)
			);
			$woores_log_meta->add_group_field(
				$feebykm_option,
				array(
					'name'    => esc_html__( 'Max number of km', 'tv-schedule' ),
					'id'      => 'km',
					'type'    => 'text',

					'classes' => 'column-4',
				)
			);
			$woores_log_meta->add_group_field(
				$feebykm_option,
				array(
					'name'    => esc_html__( 'Fee', 'tv-schedule' ),
					'id'      => 'fee',
					'type'    => 'text',

					'classes' => 'column-4',
				)
			);
			$woores_log_meta->add_group_field(
				$feebykm_option,
				array(
					'name'    => esc_html__( 'Free if total amount reach', 'tv-schedule' ),
					'id'      => 'free',
					'type'    => 'text',

					'classes' => 'column-4',
				)
			);
			$woores_log_meta->add_group_field(
				$feebykm_option,
				array(
					'name'      => esc_html__( 'Minimum amount required', 'tv-schedule' ),
					'id'        => 'min_amount',
					'type'      => 'text',

					'classes'   => 'column-4',
					'after_row' => '',
				)
			);
		}
		$woores_log_meta->add_field(
			array(
				'name' => esc_html__( 'Postcodes', 'woorestaurant' ),
				'id'   => $prefix . 'ship_postcodes',
				'desc' => esc_html__( 'Enter list of your Postcodes here, separated by a comma, leave blank to use default postcodes from settings page', 'woorestaurant' ),
				'type' => 'textarea',
			)
		);
		do_action( 'wooreswp_loc_fields_after', $woores_log_meta );
	}

	/**
	 * To edit column restraunt menu.
	 *
	 * @param array $columns It is having the columns of menu.
	 */
	public function _edit_columns_woo_restaurant_menu( $columns ) {
		$columns['_order'] = esc_html__( 'Order Menu', 'woorestaurant' );
		return $columns;
	}

	/**
	 * It is having the content of restraunt menu.
	 *
	 * @param string $content it is having the content of column.
	 * @param string $column_name it is having the column name.
	 * @param int    $term_id it is having the taxonomy id.
	 */
	public function _custom_columns_content_woo_restaurant_menu( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case '_order':
				$term_order = get_term_meta( $term_id, 'woo_restaurant_menu_order', true );
				echo '<input type="number" class="woorsd-sort-menu" data-id="' . esc_attr( $term_id ) . '" name="woorsd_sort_menu" value="' . esc_attr( $term_order ) . '">';
				break;
		}
	}
}
