<?php
/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 *
 * @package woorestaurant
 */

if ( file_exists( __DIR__ . '/wrmb2/class-wrmb2-bootstrap-270.php' ) ) {
	require_once __DIR__ . '/wrmb2/class-wrmb2-bootstrap-270.php';
} elseif ( file_exists( __DIR__ . '/WRMB2/class-wrmb2-bootstrap-270.php' ) ) {
	require_once __DIR__ . '/WRMB2/class-wrmb2-bootstrap-270.php';
}

require_once __DIR__ . '/Post-Search-field/class-wrmb2-post-search-field-025.php';
add_filter(
	'wrmb2_row_classes',
	function ( $oldclass ) {
		return $oldclass . ' wpc-label-item';
	}
);
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'Dashboard',
			'woo-restaurant',
			'manage_woocommerce',
			'woo-restaurant',
			'dashboard_woorestaurant_callback',
			1
		);
	}
);

/**
 * It is the dashboard callback.
 */
function dashboard_woorestaurant_callback() { }

/**
 * It is the get option method.
 *
 * @param string $key It is the key.
 * @param string $tab It is the tab.
 * @param string $default It is the default variable.
 */
function woo_restaurant_get_option( $key = '', $tab = false, $default = false ) {
	if ( isset( $tab ) && '' != $tab ) {
		$option_key = $tab;
	} else {
		$option_key = 'woo_restaurant_options';
	}
	if ( function_exists( 'wrmb2_get_option' ) ) {
		// Use wrmb2_get_option as it passes through some key filters.
		$val = wrmb2_get_option( $option_key, $key, $default );
		return apply_filters( 'woore_get_option', $val, $option_key, $key );
	}
	// Fallback to get_option if WRMB2 is not loaded yet.
	$opts = get_option( $option_key, $default );
	$val  = $default;
	if ( 'all' == $key ) {
		$val = $opts;
	} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}
	return apply_filters( 'woore_get_option', $val, $option_key, $key );
}

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_metabox' );
/**
 * Hook in and add a demo metabox. Can only happen on the 'wrmb2_admin_init' or 'wrmb2_init' hook.
 */
function woo_restaurant_register_metabox() {
	$prefix = 'woo_restaurant_';

	/**
	 * Food general info
	 */
	$food_info = new_wrmb2_box(
		array(
			'id'           => $prefix . 'metabox',
			'title'        => esc_html__( 'Food info', 'woorestaurant' ),
			'object_types' => array( 'product' ), // Post type.
		)
	);

	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Protein', 'woorestaurant' ),
			'desc'    => esc_html__( 'Example: 50mg', 'woorestaurant' ),
			'id'      => $prefix . 'protein',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Calories', 'woorestaurant' ),
			'desc'    => esc_html__( 'Example: 50mg', 'woorestaurant' ),
			'id'      => $prefix . 'calo',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Cholesterol', 'woorestaurant' ),
			'desc'    => esc_html__( 'Example: 50mg', 'woorestaurant' ),
			'id'      => $prefix . 'choles',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Dietary fibre', 'woorestaurant' ),
			'desc'    => esc_html__( 'Example: 50mg', 'woorestaurant' ),
			'id'      => $prefix . 'fibel',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Sodium', 'woorestaurant' ),
			'desc'    => esc_html__( 'Example: 50mg', 'woorestaurant' ),
			'id'      => $prefix . 'sodium',
			'type'    => 'text',
			'classes' => 'column-3',
		)
	);
	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Carbohydrates', 'woorestaurant' ),
			'desc'    => esc_html__( 'Example: 50mg', 'woorestaurant' ),
			'id'      => $prefix . 'carbo',
			'type'    => 'text',
			'classes' => 'column-3',
		)
	);
	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Fat total', 'woorestaurant' ),
			'desc'    => esc_html__( 'Example: 50mg', 'woorestaurant' ),
			'id'      => $prefix . 'fat',
			'type'    => 'text',
			'classes' => 'column-3',
		)
	);
	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Custom Price', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter anything to replace with price', 'woorestaurant' ),
			'id'      => $prefix . 'custom_price',
			'type'    => 'text',
			'classes' => 'column-2',
		)
	);
	$food_info->add_field(
		array(
			'name'    => esc_html__( 'Custom Color', 'woorestaurant' ),
			'desc'    => esc_html__( 'Set custom color for this food', 'woorestaurant' ),
			'id'      => $prefix . 'custom_color',
			'type'    => 'colorpicker',
			'classes' => 'column-2',
		)
	);
	/**
	 * Build-in ordering system
	 */
	if ( woo_restaurant_get_option( 'wooresf_addon' ) == 'yes' ) {
		$addition_option = new_wrmb2_box(
			array(
				'id'           => $prefix . 'addition_options',
				'title'        => esc_html__( 'Additional option', 'woorestaurant' ),
				'object_types' => array( 'product' ), // Post type.
			)
		);
		$group_option    = $addition_option->add_field(
			array(
				'id'          => $prefix . 'addition_data',
				'type'        => 'group',
				'description' => esc_html__( 'Add additional food option to allow user can order with this food', 'woorestaurant' ),
				// use false if you want non-repeatable group: 'repeatable'  => false,.
				'options'     => array(
					'group_title'   => esc_html__( 'Option {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
					'add_button'    => esc_html__( 'Add Option', 'woorestaurant' ),
					'remove_button' => esc_html__( 'Remove Option', 'woorestaurant' ),
					'sortable'      => true, // beta
					// true to have the groups closed by default: 'closed'     => true,.
				),
				'after_group' => 'woo_restaurant_repeatable_titles_for_options',
			)
		);
		// Id's for group's fields only need to be unique for the group. Prefix is not needed.
		$addition_option->add_group_field(
			$group_option,
			array(
				'name' => esc_html__( 'Name', 'woorestaurant' ),
				'id'   => '_name',
				'type' => 'text',
			// Repeatable fields are supported w/in repeatable groups (for most types): 'repeatable' => true,.
			)
		);
		$addition_option->add_group_field(
			$group_option,
			array(
				'name'             => esc_html__( 'Option type', 'woorestaurant' ),
				'description'      => esc_html__( 'Select type of this option', 'woorestaurant' ),
				'id'               => '_type',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''       => esc_html__( 'Checkboxes', 'woorestaurant' ),
					'radio'  => esc_html__( 'Radio buttons', 'woorestaurant' ),
					'select' => esc_html__( 'Select box', 'woorestaurant' ),
				),
			)
		);
		$addition_option->add_group_field(
			$group_option,
			array(
				'name'             => esc_html__( 'Required?', 'woorestaurant' ),
				'description'      => esc_html__( 'Select this option is required or not', 'woorestaurant' ),
				'id'               => '_required',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''      => esc_html__( 'No', 'woorestaurant' ),
					'radio' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$addition_option->add_group_field(
			$group_option,
			array(
				'name'        => esc_html__( 'Options', 'woorestaurant' ),
				'description' => esc_html__( 'Enter name of option and price separator by | Example: Option 1 | 100', 'woorestaurant' ),
				'id'          => '_value',
				'type'        => 'text',
				'repeatable'  => true,
				'attributes'  => array(
					'placeholder' => esc_html__( 'Name | Price', 'woorestaurant' ),
				),
			)
		);
	}

	$custom_data = new_wrmb2_box(
		array(
			'id'           => $prefix . 'custom_data',
			'title'        => esc_html__( 'Food Custom Info', 'woorestaurant' ),
			'object_types' => array( 'product' ),
		)
	);
	$group_data  = $custom_data->add_field(
		array(
			'id'          => $prefix . 'custom_data_gr',
			'type'        => 'group',
			'description' => esc_html__( 'Add food info, example: Fat saturated... Or anything you want to show', 'woorestaurant' ),
			// use false if you want non-repeatable group: 'repeatable'  => false,.
			'options'     => array(
				'group_title'   => esc_html__( 'Food Info {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
				'add_button'    => esc_html__( 'Add Another Food info', 'woorestaurant' ),
				'remove_button' => esc_html__( 'Remove Custom Food info', 'woorestaurant' ),
				'sortable'      => true, // beta.
				// true to have the groups closed by default: 'closed'     => true,.
			),
			'after_group' => 'woo_restaurant_add_js_for_repeatable_titles',
		)
	);
	// Id's for group's fields only need to be unique for the group. Prefix is not needed.
	$custom_data->add_group_field(
		$group_data,
		array(
			'name' => esc_html__( 'Name', 'woorestaurant' ),
			'id'   => '_name',
			'type' => 'text',
		// Repeatable fields are supported w/in repeatable groups (for most types): 'repeatable' => true,.
		)
	);
	$custom_data->add_group_field(
		$group_data,
		array(
			'name'        => esc_html__( 'Info', 'woorestaurant' ),
			'description' => '',
			'id'          => '_value',
			'type'        => 'text',
		)
	);

	$woore_cticon = new_wrmb2_box(
		array(
			'id'           => $prefix . 'cticon',
			'title'        => esc_html__( 'Custom Label Icon', 'woorestaurant' ),
			'object_types' => array( 'product' ),
			'context'      => 'side',
			'priority'     => 'low',
		)
	);
	$group_data   = $woore_cticon->add_field(
		array(
			'id'          => $prefix . 'cticon_gr',
			'type'        => 'group',
			'description' => esc_html__( 'Add label icon like spicy ...', 'woorestaurant' ),
			// use false if you want non-repeatable group: 'repeatable'  => false,.
			'options'     => array(
				'group_title'   => esc_html__( 'Label Icon {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
				'add_button'    => esc_html__( 'Add Another Label Icon', 'woorestaurant' ),
				'remove_button' => esc_html__( 'Remove Label Icon', 'woorestaurant' ),
				'sortable'      => true, // beta.
				// true to have the groups closed by default: 'closed'     => true,.
			),
			'after_group' => 'woo_restaurant_add_js_for_repeatable_titles',
		)
	);
	// Id's for group's fields only need to be unique for the group. Prefix is not needed.
	$woore_cticon->add_group_field(
		$group_data,
		array(
			'name'             => esc_html__( 'Icon', 'woorestaurant' ),
			'id'               => 'icon',
			'type'             => 'file',
			'default'          => '',
			'show_option_none' => false,
			'query_args'       => array(
				'type' => array(
					'image/gif',
					'image/jpeg',
					'image/png',
				),
			),
			'text'             => array(
				'add_upload_file_text' => esc_html__( 'Add File', 'woorestaurant' ), // Change upload button text. Default: "Add or Upload File".
			),
			'preview_size'     => array( 30, 30 ),
		// Repeatable fields are supported w/in repeatable groups (for most types): 'repeatable' => true,.
		)
	);
	$woore_cticon->add_group_field(
		$group_data,
		array(
			'name'        => esc_html__( 'Label name', 'woorestaurant' ),
			'description' => '',
			'id'          => 'lb_name',
			'type'        => 'text',
		)
	);
	$woore_cticon->add_group_field(
		$group_data,
		array(
			'name'        => esc_html__( 'Background color', 'woorestaurant' ),
			'description' => '',
			'id'          => 'bgcolor',
			'type'        => 'colorpicker',
		)
	);
}

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_taxonomy_metabox' );
/**
 * Regiter metadata fo menu.
 */
function woo_restaurant_register_taxonomy_metabox() {
	$prefix = 'woo_restaurant_menu_';
	/**
	 * Metabox to add fields to categories and tags.
	 */
	$wrmb_term = new_wrmb2_box(
		array(
			'id'               => $prefix . 'data',
			'title'            => esc_html__( 'Category Metabox', 'woorestaurant' ), // Doesn't output for term boxes.
			'object_types'     => array( 'term' ), // Tells WRMB2 to use term_meta vs post_meta.
			'taxonomies'       => array( 'product_cat' ), // Tells WRMB2 which taxonomies should have these fields.
			'new_term_section' => true, // Will display in the "Add New Category" section.
		)
	);

	$wrmb_term->add_field(
		array(
			'name'            => esc_html__( 'Order Menu', 'woorestaurant' ),
			'id'              => $prefix . 'order',
			'type'            => 'text',
			'attributes'      => array(
				'type'    => 'number',
				'pattern' => '\d*',
			),
			'sanitization_cb' => 'absint',
			'escape_cb'       => 'absint',
		)
	);
	$wrmb_term->add_field(
		array(
			'name'            => esc_html__( 'Icon shortcode', 'woorestaurant' ),
			'id'              => $prefix . 'iconsc',
			'description'     => esc_html__( 'Add your icon shortcode to replace with Icon image', 'woorestaurant' ),
			'type'            => 'text',
			'sanitization_cb' => 'woores_allow_metadata_save_html',
		)
	);
}

/**
 * It is the dashboard callback.
 *
 * @param object $original_value It is the original value.
 * @param array  $args It is the argument.
 * @param object $wrmb2_field It is the field object.
 */
function woo_restaurant_allow_metadata_save_html( $original_value, $args, $wrmb2_field ) {
	return $original_value; // Unsanitized value.
}

/**
 * It is the Add js repeatable titles.
 */
function woo_restaurant_add_js_for_repeatable_titles() {
	add_action( is_admin() ? 'admin_footer' : 'wp_footer', 'woo_restaurant_js_repeatable_titles_custom_data' );
}

/**
 * It is the Repeatable titles custom data.
 */
function woo_restaurant_js_repeatable_titles_custom_data() {
	woo_restaurant_js_for_repeatable_titles( 'woo_restaurant_custom_data' );
}

/**
 * It is the repeatable titles for options.
 */
function woo_restaurant_repeatable_titles_for_options() {
	add_action( is_admin() ? 'admin_footer' : 'wp_footer', 'woo_restaurant_js_repeatable_titles_options' );
}

/**
 * It is the Repeatable titles options.
 */
function woo_restaurant_js_repeatable_titles_options() {
	woo_restaurant_js_for_repeatable_titles( 'woo_restaurant_addition_options' );
}

/**
 * It is the Repeatable titles.
 *
 * @param int $id It is the id.
 */
function woo_restaurant_js_for_repeatable_titles( $id ) {
}

/**
 * Callback to define the optionss-saved message.
 *
 * @param WRMB2 $wrmb The WRMB2 object.
 * @param array $args {
 *     An array of message arguments.
 *
 *     @type bool   $is_options_page Whether current page is this options page.
 *     @type bool   $should_notify   Whether options were saved and we should be notified.
 *     @type bool   $is_updated      Whether options were updated with save (or stayed the same).
 *     @type string $setting         For add_settings_error(), Slug title of the setting to which
 *                                   this error applies.
 *     @type string $code            For add_settings_error(), Slug-name to identify the error.
 *                                   Used as part of 'id' attribute in HTML output.
 *     @type string $message         For add_settings_error(), The formatted message text to display
 *                                   to the user (will be shown inside styled `<div>` and `<p>` tags).
 *                                   Will be 'Settings updated.' if $is_updated is true, else 'Nothing to update.'
 *     @type string $type            For add_settings_error(), Message type, controls HTML class.
 *                                   Accepts 'error', 'updated', '', 'notice-warning', etc.
 *                                   Will be 'updated' if $is_updated is true, else 'notice-warning'.
 * }
 */
function woo_restaurant_options_page_message_( $wrmb, $args ) {
	if ( ! empty( $args['should_notify'] ) ) {

		if ( $args['is_updated'] ) {

			// Modify the updated message.
			/* translators: %s is replaced with the title */
			$args['message'] = sprintf( esc_html__( '%s &mdash; Updated!', 'woorestaurant' ), $wrmb->prop( 'title' ) );
		}

		add_settings_error( $args['setting'], $args['code'], $args['message'], $args['type'] );
	}
}

/**
 * It is the Register setting options.
 */
function woo_restaurant_register_setting_options() {
	/**
	 * Registers main options page menu item and form.
	 */
	$args = array(
		'id'           => 'woo_restaurant_options_page',
		'menu_title'   => esc_html__( 'Settings', 'woorestaurant' ),
		'title'        => esc_html__( 'Storefront', 'woorestaurant' ),
		'object_types' => array( 'options-page' ),
		'option_key'   => 'woo_restaurant_options',
		'parent_slug'  => 'woo-restaurant',
		'tab_group'    => 'woo_restaurant_options',
		'capability'   => 'manage_woocommerce',
		'sub_tabsX'    => array(
			'slug1' => 'General',
			'slug2' => 'Time Slots',
			'slug3' => 'Takeway',
			'slug4' => 'Dine In',
			'slug5' => 'Order Tips',
		),
		'tab_title'    => esc_html__( 'Storefront', 'woorestaurant' ),
		'message_cb'   => 'woo_restaurant_options_page_message_',
		'icon'         => 'dashicons-admin-generic',
	);
	// 'tab_group' property is supported in > 2.4.0.
	if ( version_compare( WRMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'woo_restaurant_options_display_with_tabs';
	}
	$main_options = new_wrmb2_box( $args );
	/**
	 * Options fields ids only need
	 * to be unique within this box.
	 * Prefix is not needed.
	 */
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Main Color', 'woorestaurant' ),
			'desc'    => esc_html__( 'Choose Main Color for plugin', 'woorestaurant' ),
			'id'      => 'woo_restaurant_color',
			'type'    => 'colorpicker',

			'default' => '',
		)
	);

	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Content Font Family', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter Google font-family name . For example, if you choose "Source Sans Pro" Google Font, enter Source Sans Pro', 'woorestaurant' ),
			'id'      => 'woo_restaurant_font_family',
			'type'    => 'text',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Content Font Size', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter size of main font, default:13px, Ex: 14px', 'woorestaurant' ),
			'id'      => 'woo_restaurant_font_size',
			'type'    => 'text',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Content Font Color', 'woorestaurant' ),
			'desc'    => esc_html__( 'Choose Content Font Color for plugin', 'woorestaurant' ),
			'id'      => 'woo_restaurant_ctcolor',
			'type'    => 'colorpicker',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Heading Font Family', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter Google font-family name. For example, if you choose "Oswald" Google Font, enter Oswald', 'woorestaurant' ),
			'id'      => 'woo_restaurant_headingfont_family',
			'type'    => 'text',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Heading Font Size', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter size of heading font, default: 20px, Ex: 22px', 'woorestaurant' ),
			'id'      => 'woo_restaurant_headingfont_size',
			'type'    => 'text',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Heading Font Color', 'woorestaurant' ),
			'desc'    => esc_html__( 'Choose Heading Font Color for plugin', 'woorestaurant' ),
			'id'      => 'woo_restaurant_hdcolor',
			'type'    => 'colorpicker',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Price Font Family', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter Google font-family name. For example, if you choose "Oswald" Google Font, enter Oswald', 'woorestaurant' ),
			'id'      => 'woo_restaurant_pricefont_family',
			'type'    => 'text',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Price Font Size', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter size of Price font, default: 20px, Ex: 22px', 'woorestaurant' ),
			'id'      => 'woo_restaurant_pricefont_size',
			'type'    => 'text',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Price Font Color', 'woorestaurant' ),
			'desc'    => esc_html__( 'Choose Price Font Color for plugin', 'woorestaurant' ),
			'id'      => 'woo_restaurant_pricecolor',
			'type'    => 'colorpicker',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Meta Font Family', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter Google font-family name. For example, if you choose "Ubuntu" Google Font, enter Ubuntu', 'woorestaurant' ),
			'id'      => 'woo_restaurant_metafont_family',
			'type'    => 'text',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Meta Font Size', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter size of metadata font, default:13px, Ex: 12px', 'woorestaurant' ),
			'id'      => 'woo_restaurant_metafont_size',
			'type'    => 'text',
			'default' => '',
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Meta Font Color', 'woorestaurant' ),
			'desc'    => esc_html__( 'Choose Meta Font Color for plugin', 'woorestaurant' ),
			'id'      => 'woo_restaurant_mtcolor',
			'type'    => 'colorpicker',
			'default' => '',
		)
	);

	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Disable Extra Options', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select yes to disable default Extra Options', 'woorestaurant' ),
			'id'               => 'woo_restaurant_disable_wrsoptions',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
		)
	);
	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Extra Options Style', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select style of Extra Options', 'woorestaurant' ),
			'id'               => 'woo_restaurant_wrsoptions_style',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''          => esc_html__( 'Default', 'woorestaurant' ),
				'accordion' => esc_html__( 'Accordion', 'woorestaurant' ),
			),
		)
	);

	$main_options->add_field(
		array(
			'name'             => esc_html__( 'RTL mode', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enable RTL mode for RTL language', 'woorestaurant' ),
			'id'               => 'woo_restaurant_enable_rtl',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
		)
	);

	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Enable Food by location', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select yes to enable Food by location ( You need to set each food for each location)', 'woorestaurant' ),
			'id'               => 'woo_restaurant_enable_loc',
			'type'             => 'select',
			'default'          => '',
			'show_option_none' => false,
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
		)
	);
	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Auto close popup after add item to cart', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select yes close popup after add item to cart', 'woorestaurant' ),
			'id'               => 'woo_restaurant_clsose_pop',
			'type'             => 'select',
			'default'          => '',
			'show_option_none' => false,
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
		)
	);
	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Popup location icon', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select Icon for location popup, only work when enable popup location', 'woorestaurant' ),
			'id'               => 'woo_restaurant_loc_icon',
			'type'             => 'file',
			'default'          => '',
			'show_option_none' => false,
			'query_args'       => array(
				'type' => array(
					'image/gif',
					'image/jpeg',
					'image/png',
				),
			),
			'preview_size'     => array( 50, 50 ),
		)
	);
	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Food menu by order method', 'woorestaurant' ),
			'desc'             => esc_html__( 'This feature allow you can set food by each order method', 'woorestaurant' ),
			'id'               => 'woo_restaurant_foodby_odmt',
			'type'             => 'select',
			'default'          => '',
			'show_option_none' => false,
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
			'show_on_cb'       => 'woore_show_if_enable_odmt',
			'sanitization_cb'  => 'woore_create_tern_method',
		)
	);
	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Food menu by date', 'woorestaurant' ),
			'desc'             => esc_html__( 'This feature allow you can create food menu by date and  user only can order food of current date', 'woorestaurant' ),
			'id'               => 'woo_restaurant_foodby_date',
			'type'             => 'select',
			'default'          => '',
			'show_option_none' => false,
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
		)
	);
	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Multi menus by date', 'woorestaurant' ),
			'desc'             => esc_html__( 'This feature allow you can create multi menus like Lunch or Breakfast or Dinner for each date', 'woorestaurant' ),
			'id'               => 'woo_restaurant_foodby_timesl',
			'type'             => 'select',
			'default'          => '',
			'show_option_none' => false,
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
			'show_on_cb'       => 'woore_hide_if_enable_mndate',
		)
	);
	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Live total price', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select yes to enable live update total price', 'woorestaurant' ),
			'id'               => 'woo_restaurant_enable_livetotal',
			'type'             => 'select',
			'default'          => '',
			'show_option_none' => false,
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
		)
	);
	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Open single product on popup', 'woorestaurant' ),
			'desc'             => esc_html__( 'Add your food menu page (contain food shortcode) link here to open default single product on popup instead of default single product page ', 'woorestaurant' ),
			'id'               => 'woo_restaurant_menu_url',
			'type'             => 'text',
			'default'          => '',
			'show_option_none' => false,
		)
	);

	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Store Open/Close', 'woorestaurant' ),
			'desc'             => esc_html__( 'Use the toggle button to  Open or Close the store.', 'woorestaurant' ),
			'id'               => 'woo_restaurant_store_close',
			'type'             => 'checkbox',
			'woo_switch'       => true,
			'default'          => '',
			'show_option_none' => false,
		)
	);

	$main_options->add_field(
		array(
			'name'             => esc_html__( 'Automatic Fullfilment', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enable this option to make the order status change to completed.', 'woorestaurant' ),
			'id'               => 'woo_restaurant_fullfil_enable',
			'type'             => 'checkbox',
			'woo_switch'       => true,
			'default'          => '',
			'show_option_none' => false,
		)
	);
	$main_options->add_field(
		array(
			'name'    => esc_html__( 'Autmatice Fullfilment Check Duration (mins)', 'woorestaurant' ),
			'desc'    => esc_html__( 'Enter the time (minutes)', 'woorestaurant' ),
			'id'      => 'woo_restaurant_fullfil_time',
			'type'    => 'text',
			'default' => '',
		)
	);
}

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_setting_options', 12 );

/**
 * Register advance.
 *
 * @param array $field_args It is the field arguments.
 * @param array $field It is the field array.
 */
function woore_ot_add_adv_time_html( $field_args, $field ) {
	return;
	$tab         = isset( $_GET['page'] ) && '' != $_GET['page'] ? wp_kses_post( wp_unslash( $_GET['page'] ) ) : '';
	$method_ship = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
	$html        = '
		<a href="?page=woo_restaurant_advanced_options" class="' . ( 'woo_restaurant_advanced_options' == $tab ? 'current' : '' ) . '">' . esc_html__( 'General', 'woorestaurant' ) . '</a>';
	if ( '' != $method_ship && 'delivery' != $method_ship ) {
		$html .= ' | <a href="?page=woo_restaurant_adv_takeaway_options" class="' . ( 'woo_restaurant_adv_takeaway_options' == $tab ? 'current' : '' ) . '">' . esc_html__( 'Takeaway', 'woorestaurant' ) . '</a>';
	}
	$dine_in = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
	if ( 'yes' == $dine_in ) {
		$html .= ' | <a href="?page=woo_restaurant_adv_dinein_options" class="' . ( 'woo_restaurant_adv_dinein_options' == $tab ? 'current' : '' ) . '">' . esc_html__( 'Dine-In', 'woorestaurant' ) . '</a>';
	}

	$html .= ' | <a href="?page=woo_restaurant_adv_timesl_options" class="' . ( 'woo_restaurant_adv_timesl_options' == $tab ? 'current' : '' ) . '">' . esc_html__( 'Advanced Time slots', 'woorestaurant' ) . '</a>';

	$html = apply_filters( 'woore_admin_adv_settings_tab_html', $html, $tab );
	echo '<p class="woore-sub-option">' . wp_kses_post( $html ) . '</p>';
}

/**
 * Hide if enable date.
 *
 * @param object $field It is the field.
 */
function woore_hide_if_enable_mndate( $field ) {
	$mndate = woo_restaurant_get_option( 'woo_restaurant_foodby_date' );
	if ( 'yes' != $mndate ) {
		return false;
	}
	return true;
}

/**
 * Show if enable odmt.
 *
 * @param object $field It is the field.
 */
function woore_show_if_enable_odmt( $field = '' ) {
	$method = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
	$dinein = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
	if ( ( 'yes' != $dinein && 'both' != $method ) || ( 'yes' == $dinein && '' == $method ) ) {
		return false;
	}
	return true;
}

/**
 * Show if enable dinein.
 */
function woore_show_if_enable_dinein() {
	$dinein = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
	if ( 'yes' == $dinein ) {
		return true;
	}
	return false;
}

/**
 * Show if disabled shipping method.
 */
function woore_show_if_disabled_shipping_method() {
	$dinein = woo_restaurant_get_option( 'woo_restaurant_enable_method', 'woo_restaurant_shpping_options' );
	if ( '' == $dinein ) {
		return true;
	}
	return false;
}

/**
 * Create tern method.
 *
 * @param string $original_value It is the original value.
 * @param array  $args It is the argument.
 * @param object $wrmb2_field It is the field.
 */
function woore_create_tern_method( $original_value, $args, $wrmb2_field ) {
	if ( 'yes' == $original_value ) {
		global $woore_fist_create;
		$woore_fist_create = true;
		$term              = term_exists( 'delivery', 'woore_odmethod' );
		if ( null == $term ) {
			wp_insert_term(
				'Delivery',
				'woore_odmethod',
				array(
					'slug'        => 'delivery',
					'description' => '',
				)
			);
		}
		$term_takeaway = term_exists( 'takeaway', 'woore_odmethod' );
		if ( null == $term_takeaway ) {
			wp_insert_term(
				'Takeaway',
				'woore_odmethod',
				array(
					'slug'        => 'takeaway',
					'description' => '',
				)
			);
		}
		$term_dinein = term_exists( 'dinein', 'woore_odmethod' );
		if ( null == $term_dinein ) {
			wp_insert_term(
				'Dinein',
				'woore_odmethod',
				array(
					'slug'        => 'dinein',
					'description' => '',
				)
			);
		}
		$woore_fist_create = false;
	}
	return $original_value;
}

/**
 * Hide if disable loc.
 *
 * @param object $field It is the field variable.
 */
function woore_hide_if_disable_loc( $field ) {
	$loca_field = woo_restaurant_get_option( 'woo_restaurant_ck_loca', 'woo_restaurant_advanced_options' );
	if ( '' == $loca_field ) {
		return false;
	}
	return true;
}

/**
 * Hide if disable timefield.
 *
 * @param object $field It is the field object.
 */
function woore_hide_if_disable_timefield( $field ) {
	$ck_time = woo_restaurant_get_option( 'woo_restaurant_ck_time', 'woo_restaurant_advanced_options' );
	if ( 'disable' == $ck_time ) {
		return false;
	}
	return true;
}

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_setting_advanced', 13 );

/**
 * Register setting advance.
 */
function woo_restaurant_register_setting_advanced() {
	$args = array(
		'id'           => 'woo_restaurant_advanced',
		'menut_title'  => 'Advanced',
		'title'        => 'Advanced',
		'object_types' => array( 'options-page' ),
		'option_key'   => 'woo_restaurant_advanced_options',
		'parent_slug'  => 'woo_restaurant_options',
		'sub_tabs'     => array(
			'woo_restaurant_advanced_options'     => 'General',
			'woo_restaurant_adv_takeaway_options' => 'Takeaway',
			'woo_restaurant_adv_dinein_options'   => 'Dine-In',
			'woo_restaurant_adv_timesl_options'   => 'Advanced Time slots',
			'woo_restaurant_tip_options'          => 'Order Tip',
			'woo_restaurant_whatsapp_options'     => 'Order On whatsapp',
		),
		'tab_group'    => 'woo_restaurant_options',
		'capability'   => 'manage_woocommerce',
		'tab_title'    => esc_html__( 'Advanced', 'woorestaurant' ),
		'icon'         => 'dashicons-admin-settings',
	);
	$args = apply_filters( 'woo_restaurant_advanced_datas', $args );
	// 'tab_group' property is supported in > 2.4.0.
	if ( version_compare( WRMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'woo_restaurant_options_display_with_tabs';
	}
	$adv_options = new_wrmb2_box( $args );
	$tab         = isset( $_GET['section'] ) && '' != $_GET['section'] ? wp_kses_post( wp_unslash( $_GET['section'] ) ) : '';
	do_action( 'woo_restaurant_advanced_before', $adv_options );
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Date field', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select Date field is Required or Optional or Disable', 'woorestaurant' ),
				'id'               => 'woo_restaurant_ck_date',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''        => esc_html__( 'Optional', 'woorestaurant' ),
					'yes'      => esc_html__( 'Required', 'woorestaurant' ),
					'disable' => esc_html__( 'Disable', 'woorestaurant' ),
				),
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Minimum time to order food before', 'woorestaurant' ),
				'desc'             => esc_html__( 'This feature allow user only can order food before X day or X minutes, Enter number for day or enter number + m for minutes, Example: 1 for 1 day or: 30m for 30 minutes', 'woorestaurant' ),
				'id'               => 'woo_restaurant_ck_beforedate',
				'type'             => 'text',
				'show_option_none' => true,
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Disable dates', 'woorestaurant' ),
				'desc'             => esc_html__( 'Disable special Delivery Date', 'woorestaurant' ),
				'id'               => 'woo_restaurant_ck_disdate',
				'is_60'            => 'yes',
				'type'             => 'text_date_timestamp',
				'default'          => '',
				'date_format'      => 'Y-m-d',
				'repeatable'       => true,
				'show_option_none' => true,
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Enable Special delivery dates', 'woorestaurant' ),
				'desc'             => esc_html__( 'Add dates to allow user only can select these special Delivery Dates (only support display delivery date in Select box)', 'woorestaurant' ),
				'id'               => 'woo_restaurant_ck_enadate',
				'type'             => 'text_date_timestamp',
				'default'          => '',
				'date_format'      => 'Y-m-d',
				'repeatable'       => true,
				'show_option_none' => true,
			)
		);
		$adv_options->add_field(
			array(
				'name'    => esc_html__( 'Disable days', 'woorestaurant' ),
				'desc'    => esc_html__( 'Disable special Day Delivery', 'woorestaurant' ),
				'id'      => 'woo_restaurant_ck_disday',
				'is_60'   => 'yes',
				'type'    => 'multicheck_inline',
				'options' => array(
					'1' => esc_html__( 'Monday', 'woorestaurant' ),
					'2' => esc_html__( 'Tuesday', 'woorestaurant' ),
					'3' => esc_html__( 'Wednesday', 'woorestaurant' ),
					'4' => esc_html__( 'Thursday', 'woorestaurant' ),
					'5' => esc_html__( 'Friday', 'woorestaurant' ),
					'6' => esc_html__( 'Saturday', 'woorestaurant' ),
					'7' => esc_html__( 'Sunday', 'woorestaurant' ),
				),

			)
		);
		$adv_options->add_field(
			array(
				'name'    => esc_html__( 'Display Delivery date in', 'woorestaurant' ),
				'desc'    => esc_html__( 'Set style of Delivery date', 'woorestaurant' ),
				'id'      => 'woo_restaurant_dd_display',
				'type'    => 'select',
				'default' => 'picker',
				'options' => array(
					'select' => esc_html__( 'Select box', 'woorestaurant' ),
					'picker' => esc_html__( 'Calendar Picker', 'woorestaurant' ),
				),
			)
		);
		$adv_options->add_field(
			array(
				'name'    => esc_html__( 'Calendar picker format', 'woorestaurant' ),
				'desc'    => esc_html__( 'Set format for calendart date picker, default: mm/dd/yyyy', 'woorestaurant' ),
				'id'      => 'woo_restaurant_datepk_fm',
				'type'    => 'select',
				'options' => array(
					'mm/dd/yyyy' => esc_html__( 'mm/dd/yyyy', 'woorestaurant' ),
					'dd-mm-yyyy' => esc_html__( 'dd-mm-yyyy', 'woorestaurant' ),
				),
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Time field', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select time field is Required or Optional or Disable', 'woorestaurant' ),
				'id'               => 'woo_restaurant_ck_time',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''        => esc_html__( 'Optional', 'woorestaurant' ),
					'yes'      => esc_html__( 'Required', 'woorestaurant' ),
					'disable' => esc_html__( 'Disable', 'woorestaurant' ),
				),
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Disable Time slot option', 'woorestaurant' ),
				'desc'             => esc_html__( 'This feature allow you can disable special time slot instead delete it', 'woorestaurant' ),
				'id'               => 'woo_restaurant_disable_tslot',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => 'yes',
				'options'          => array(
					''    => esc_html__( 'No', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
				),
				'show_on_cb'       => 'woore_hide_if_disable_timefield',
			)
		);
		$adv_options->add_field(
			array(
				'name'        => esc_html__( 'Time slots', 'woorestaurant' ),
				'id'          => 'woores_deli_time',
				'type'        => 'timedelivery',
				'is_60'       => 'yes',
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
		$adv_options->add_field(
			array(
				'name'            => esc_html__( 'Minimum Order Amount required', 'woorestaurant' ),
				'desc'            => esc_html__( 'Set minimum amount required for each Order', 'woorestaurant' ),
				'id'              => 'woo_restaurant_ck_mini_amount',
				'type'            => 'text',
				'sanitization_cb' => '',
				'escape_cb'       => '',
				'after_field'     => '',
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Location field', 'woorestaurant' ),
				'desc'             => esc_html__( 'Enable location field in Checkout to allow user can choose area they want to order', 'woorestaurant' ),
				'id'               => 'woo_restaurant_ck_loca',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'Disable', 'woorestaurant' ),
					'req' => esc_html__( 'Required', 'woorestaurant' ),
					'op'  => esc_html__( 'Optional', 'woorestaurant' ),
				),
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Advanced Time slot By location', 'woorestaurant' ),
				'desc'             => esc_html__( 'Enable Advanced Time slot By location', 'woorestaurant' ),
				'id'               => 'woo_restaurant_adv_loca',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''       => esc_html__( 'Disable', 'woorestaurant' ),
					'enable' => esc_html__( 'Enable', 'woorestaurant' ),
				),
				'show_on_cb'       => 'woore_hide_if_disable_loc',
			)
		);
		$adv_options->add_field(
			array(
				'name'              => esc_html__( 'Disable locations', 'woorestaurant' ),
				'desc'              => esc_html__( 'Disable special location from Delivery', 'woorestaurant' ),
				'id'                => 'woo_restaurant_adv_dislog',
				'taxonomy'          => 'woorestaurant_loc',
				'type'              => 'taxonomy_multicheck_inline',
				'select_all_button' => false,
				'remove_default'    => 'true',
				'query_args'        => array(),
				'classes'           => 'wrmb-type-taxonomy-multicheck-inline',
				'show_on_cb'        => 'woore_hide_if_disable_loc',
			)
		);

		$adv_options->add_field(
			array(
				'name'            => esc_html__( 'Disable WooCommerce Food fields in products', 'woorestaurant' ),
				'id'              => 'woo_restaurant_ign_deli',
				'type'            => 'post_search_text',
				'desc'            => esc_html__( 'Select product (or by category below) to disable order method, date, time, location fields of WooCommerce Food when checkout if you want to sell normal products', 'woorestaurant' ),
				'post_type'       => 'product',
				'select_type'     => 'checkbox',
				'select_behavior' => 'add',
				'after_field'     => '',
			)
		);
		$adv_options->add_field(
			array(
				'name'              => esc_html__( 'Disable WooCommerce Food fields in category', 'woorestaurant' ),
				'desc'              => esc_html__( 'Select category to disable order method, date, time, location fields of WooCommerce Food when checkout if you want to sell normal products', 'woorestaurant' ),
				'id'                => 'woo_restaurant_igncat_deli',
				'taxonomy'          => 'product_cat', // Enter Taxonomy Slug.
				'type'              => 'taxonomy_multicheck_inline',
				'select_all_button' => false,
				'remove_default'    => 'true', // Removes the default metabox provided by WP core.
				'query_args'        => array(),
				'classes'           => 'wrmb-type-taxonomy-multicheck-inline',
			)
		);
		// Open close time.
		$adv_options->add_field(
			array(
				'name' => esc_html__( 'Opening and Closing time', 'woorestaurant' ),
				'desc' => '',
				'id'   => 'woores_op_cl',
				'type' => 'title',
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Opening and Closing time', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select yes to enable Opening and Closing time', 'woorestaurant' ),
				'id'               => 'woo_restaurant_open_close',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''       => esc_html__( 'Disable', 'woorestaurant' ),
					'enable' => esc_html__( 'Enable', 'woorestaurant' ),
					'closed' => esc_html__( 'Closed', 'woorestaurant' ),
				),
			)
		);
		$adv_options->add_field(
			array(
				'name'             => esc_html__( 'Enable Opening and Closing time for each location', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select yes to enable Opening and Closing time settings for each location', 'woorestaurant' ),
				'id'               => 'woo_restaurant_open_close_loc',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => '',
				'options'          => array(
					''    => esc_html__( 'No', 'woorestaurant' ),
					'yes' => esc_html__( 'Yes', 'woorestaurant' ),
				),
			)
		);
		$adv_options->add_field(
			array(
				'name'            => esc_html__( 'Allow products', 'woorestaurant' ),
				'id'              => 'woo_restaurant_ign_op',
				'type'            => 'post_search_text',
				'desc'            => esc_html__( 'Allow user can purchase products when your shop is closed.', 'woorestaurant' ),
				'post_type'       => 'product',
				'select_type'     => 'checkbox',
				'select_behavior' => 'add',
				'after_field'     => '',
			)
		);
		$adv_options->add_field(
			array(
				'name'        => esc_html__( 'Monday', 'woorestaurant' ),
				'id'          => 'woores_Mon_opcl_time',
				'type'        => 'openclose',
				'is_60'       => 'yes',
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
		$adv_options->add_field(
			array(
				'name'        => esc_html__( 'Tuesday', 'woorestaurant' ),
				'id'          => 'woores_Tue_opcl_time',
				'type'        => 'openclose',
				'is_60'       => 'yes',
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
		$adv_options->add_field(
			array(
				'name'        => esc_html__( 'Wednesday', 'woorestaurant' ),
				'id'          => 'woores_Wed_opcl_time',
				'type'        => 'openclose',
				'is_60'       => 'yes',
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
		$adv_options->add_field(
			array(
				'name'        => esc_html__( 'Thursday', 'woorestaurant' ),
				'id'          => 'woores_Thu_opcl_time',
				'type'        => 'openclose',
				'is_60'       => 'yes',
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
		$adv_options->add_field(
			array(
				'name'        => esc_html__( 'Friday', 'woorestaurant' ),
				'id'          => 'woores_Fri_opcl_time',
				'type'        => 'openclose',
				'is_60'       => 'yes',
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
		$adv_options->add_field(
			array(
				'name'        => esc_html__( 'Saturday', 'woorestaurant' ),
				'id'          => 'woores_Sat_opcl_time',
				'type'        => 'openclose',
				'is_60'       => 'yes',
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
		$adv_options->add_field(
			array(
				'name'        => esc_html__( 'Sunday', 'woorestaurant' ),
				'id'          => 'woores_Sun_opcl_time',
				'type'        => 'openclose',
				'is_60'       => 'yes',
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

		$woore_opcls_dtd = $adv_options->add_field(
			array(
				'id'          => 'woores_opcl_datetodate',
				'name'        => esc_html__( 'Closed from date to date', 'woorestaurant' ),
				'type'        => 'group',
				'description' => esc_html__( 'This setting will higher priority than Open and closing by day of week', 'woorestaurant' ),
				'options'     => array(
					'group_title'   => esc_html__( 'Closed {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
					'add_button'    => esc_html__( 'Add new', 'woorestaurant' ),
					'remove_button' => esc_html__( 'Remove', 'woorestaurant' ),
					'sortable'      => false, // beta.
					'closed'        => false, // true to have the groups closed by default.
				),
				'after_group' => '',
			)
		);
		$adv_options->add_group_field(
			$woore_opcls_dtd,
			array(
				'name'             => esc_html__( 'From', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select start Closed date', 'woorestaurant' ),
				'id'               => 'opcl_start',
				'type'             => 'text_datetime_timestamp',
				'show_option_none' => false,
			)
		);
		$adv_options->add_group_field(
			$woore_opcls_dtd,
			array(
				'name'             => esc_html__( 'To', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select end Closed date', 'woorestaurant' ),
				'id'               => 'opcl_end',
				'type'             => 'text_datetime_timestamp',
				'show_option_none' => false,
			)
		);

	do_action( 'woo_restaurant_advanced_after', $adv_options );
}

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_setting_advanced_timessl', 15 );

/**
 * Regisster setting advance times.
 */
function woo_restaurant_register_setting_advanced_timessl() {
	$args = array(
		'id'           => 'woo_restaurant_advanced_timesl',
		'title'        => 'Advanced Time slots',
		'object_types' => array( 'options-page' ),
		'option_key'   => 'woo_restaurant_adv_timesl_options',
		'parent_slug'  => 'woo_restaurant_options',
		'tab_group'    => 'woo_restaurant_options',
		'srz_parent'   => 'woo_restaurant_advanced_options',
		'capability'   => 'manage_woocommerce',
		'tab_title'    => esc_html__( 'Advanced Time slots', 'woorestaurant' ),
		'icon'         => 'srzicon',
	);
	// 'tab_group' property is supported in > 2.4.0.
	if ( version_compare( WRMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'woo_restaurant_options_display_with_tabs';
	}
	$advsl_options = new_wrmb2_box( $args );
	// Advanced time delivery.
	$mntime = woo_restaurant_get_option( 'woo_restaurant_foodby_timesl' );
	if ( 'yes' != $mntime ) {
		$advsl_options->add_field(
			array(
				'name' => esc_html__( 'Advanced time slots', 'woorestaurant' ),
				'desc' => '',
				'id'   => 'woores_adv_tdel',
				'type' => 'title',
			)
		);
		$group_option = $advsl_options->add_field(
			array(
				'id'          => 'woores_adv_timedeli',
				'type'        => 'group',
				'description' => esc_html__( 'Set time slots for each day of week (leave blank to use General setting)', 'woorestaurant' ),
				'options'     => array(
					'group_title'   => esc_html__( 'Time Delivery {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
					'add_button'    => esc_html__( 'Add new', 'woorestaurant' ),
					'remove_button' => esc_html__( 'Remove', 'woorestaurant' ),
					'sortable'      => true, // beta.
					'closed'        => false, // true to have the groups closed by default.
				),
				'after_group' => '',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'             => esc_html__( 'Shipping method', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select Shipping method for this time delivery', 'woorestaurant' ),
				'id'               => 'deli_method',
				'type'             => 'select',
				'show_option_none' => false,
				'options'          => array(
					''         => esc_html__( 'Default', 'woorestaurant' ),
					'takeaway' => esc_html__( 'Only Takeaway', 'woorestaurant' ),
					'delivery' => esc_html__( 'Only Delivery', 'woorestaurant' ),
					'dinein'   => esc_html__( 'Only Dine-in', 'woorestaurant' ),
				),
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'        => esc_html__( 'Time from', 'woorestaurant' ),
				'desc'        => esc_html__( 'Select Start Time to auto generate time slot', 'woorestaurant' ),
				'id'          => 'time_from',
				'type'        => 'text_time',
				'time_format' => 'H:i',
				'repeatable'  => false,
				'attributes'  => array(
					'data-timepicker' => json_encode(
						array(
							'stepMinute' => 1,
							'timeFormat' => 'HH:mm',
						)
					),
				),
				'classes'     => 'woore-auto-sl sltime-fr',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'        => esc_html__( 'Time to', 'woorestaurant' ),
				'desc'        => esc_html__( 'Select End Time to auto generate time slot', 'woorestaurant' ),
				'id'          => 'time_to',
				'type'        => 'text_time',
				'time_format' => 'H:i',
				'repeatable'  => false,
				'attributes'  => array(
					'data-timepicker' => json_encode(
						array(
							'stepMinute' => 1,
							'timeFormat' => 'HH:mm',
						)
					),
				),
				'classes'     => 'woore-auto-sl sltime-to',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'      => esc_html__( 'Max order', 'woorestaurant' ),
				'desc'      => esc_html__( 'Set Max order for each time slot', 'woorestaurant' ),
				'id'        => 'max_order',
				'type'      => 'text',
				'classes'   => 'woore-auto-sl sltime-maxod',
				'after_row' => '',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'      => esc_html__( 'Number of minutes', 'woorestaurant' ),
				'desc'      => esc_html__( 'Select Number of minutes for each time slot', 'woorestaurant' ),
				'id'        => 'number_minutes',
				'type'      => 'text',
				'classes'   => 'woore-auto-sl sltime-minu',
				'after_row' => 'woore_generate_sl_html',
			)
		);
		$enable_adv_tl = woo_restaurant_get_option( 'woo_restaurant_adv_loca', 'woo_restaurant_advanced_options' );
		if ( 'enable' == $enable_adv_tl ) {
			$advsl_options->add_group_field(
				$group_option,
				array(
					'name'           => esc_html__( 'Locations', 'woorestaurant' ),
					'desc'           => esc_html__( 'Select Locations for this time delivery, leave blank to apply for all locations', 'woorestaurant' ),
					'id'             => 'times_loc',
					'taxonomy'       => 'woorestaurant_loc', // Enter Taxonomy Slug.
					'type'           => 'taxonomy_multicheck_inline',
					'remove_default' => 'true', // Removes the default metabox provided by WP core.
					'query_args'     => array(),
					'classes'        => 'wrmb-type-taxonomy-multicheck-inline',
				)
			);
		}

		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'    => esc_html__( 'Monday', 'woorestaurant' ),
				'id'      => 'repeat_Mon',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'    => esc_html__( 'Tuesday', 'woorestaurant' ),
				'id'      => 'repeat_Tue',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'    => esc_html__( 'Wednesday', 'woorestaurant' ),
				'id'      => 'repeat_Wed',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'    => esc_html__( 'Thursday', 'woorestaurant' ),
				'id'      => 'repeat_Thu',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'    => esc_html__( 'Friday', 'woorestaurant' ),
				'id'      => 'repeat_Fri',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'    => esc_html__( 'Saturday', 'woorestaurant' ),
				'id'      => 'repeat_Sat',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'    => esc_html__( 'Sunday', 'woorestaurant' ),
				'id'      => 'repeat_Sun',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);

		$advsl_options->add_group_field(
			$group_option,
			array(
				'name'        => esc_html__( 'Time slots', 'woorestaurant' ),
				'id'          => 'woores_deli_time',
				'type'        => 'timedelivery',
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
	} else {
		$advsl_options->add_field(
			array(
				'name' => esc_html__( 'When you enable Multi menus by date (menu by time slot), you need create each menu for each slot and this setting is not available', 'woorestaurant' ),
				'desc' => '',
				'id'   => 'woores_adv_tdel',
				'type' => 'title',
			)
		);
	}
}

/**
 * Generate HTML.
 */
function woore_generate_sl_html() {
	echo '<span class="woore-generatesl"><a href="javascript:;" class="">' . esc_html__( 'Generate Time slots', 'woorestaurant' ) . '</a></span>';
}

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_setting_takeway', 17 );
/**
 * Take away setting.
 */
function woo_restaurant_register_setting_takeway() {
	$args = array(
		'id'           => 'woo_restaurant_takeway',
		'title'        => 'Takeaway',
		'object_types' => array( 'options-page' ),
		'option_key'   => 'woo_restaurant_adv_takeaway_options',
		'parent_slug'  => 'woo_restaurant_options',
		'tab_group'    => 'woo_restaurant_options',
		'srz_parent'   => 'woo_restaurant_advanced_options',
		'capability'   => 'manage_woocommerce',
		'tab_title'    => esc_html__( 'Takeaway', 'woorestaurant' ),
		'icon'         => 'srzicon',
	);
	// 'tab_group' property is supported in > 2.4.0.
	if ( version_compare( WRMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'woo_restaurant_options_display_with_tabs';
	}
	$takeaway_options = new_wrmb2_box( $args );
	$takeaway_options->add_field(
		array(
			'name'             => esc_html__( 'User need order Pickup Date food before', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enter number, This feature allow user only can select Pickup Date food before X day or X minutes (enter number + m) from now', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_beforedate',
			'type'             => 'text',
			'show_option_none' => true,
		)
	);
	$takeaway_options->add_field(
		array(
			'name'             => esc_html__( 'Disable dates', 'woorestaurant' ),
			'desc'             => esc_html__( 'Disable special Pickup Date', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_disdate',
			'is_60'            => 'yes',
			'type'             => 'text_date_timestamp',
			'default'          => '',
			'date_format'      => 'Y-m-d',
			'repeatable'       => true,
			'show_option_none' => true,
		)
	);
	$takeaway_options->add_field(
		array(
			'name'             => esc_html__( 'Enable Special Pickup dates', 'woorestaurant' ),
			'desc'             => esc_html__( 'Add dates to allow user only can select these special Pickup Dates (only support display delivery date in Select box)', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_enadate',
			'type'             => 'text_date_timestamp',
			'default'          => '',
			'date_format'      => 'Y-m-d',
			'repeatable'       => true,
			'show_option_none' => true,
		)
	);
	$takeaway_options->add_field(
		array(
			'name'    => esc_html__( 'Disable days', 'woorestaurant' ),
			'desc'    => esc_html__( 'Disable special Pickup Day', 'woorestaurant' ),
			'id'      => 'woo_restaurant_ck_disday',
			'type'    => 'multicheck_inline',
			'options' => array(
				'1' => esc_html__( 'Monday', 'woorestaurant' ),
				'2' => esc_html__( 'Tuesday', 'woorestaurant' ),
				'3' => esc_html__( 'Wednesday', 'woorestaurant' ),
				'4' => esc_html__( 'Thursday', 'woorestaurant' ),
				'5' => esc_html__( 'Friday', 'woorestaurant' ),
				'6' => esc_html__( 'Saturday', 'woorestaurant' ),
				'7' => esc_html__( 'Sunday', 'woorestaurant' ),
			),

		)
	);
	$takeaway_options->add_field(
		array(
			'name'    => esc_html__( 'Disable address fields', 'woorestaurant' ),
			'desc'    => esc_html__( 'Disable address fields when user select order method is Takeaway', 'woorestaurant' ),
			'id'      => 'woo_restaurant_ck_disaddr',
			'type'    => 'select',
			'options' => array(
				'no'  => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),

		)
	);
	$takeaway_options->add_field(
		array(
			'name'             => esc_html__( 'Takeaway Surcharge/Discount', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enter number:10 or Percentage of total price: 10% (enter negative number for discount)', 'woorestaurant' ),
			'id'               => 'woo_restaurant_takeaway_sur',
			'type'             => 'text',
			'before_row'       => '',
			'show_option_none' => true,
		)
	);
	$takeaway_options->add_field(
		array(
			'name'             => esc_html__( 'Minimum Order Amount required', 'woorestaurant' ),
			'desc'             => esc_html__( 'Set Minimum Order Amount required for Takeaway, leave blank to use default setting from General tab', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_mini_amount',
			'type'             => 'text',
			'before_row'       => '',
			'show_option_none' => true,
		)
	);
	$takeaway_options->add_field(
		array(
			'name'              => esc_html__( 'Disable locations', 'woorestaurant' ),
			'desc'              => esc_html__( 'Disable special location from Takeaway', 'woorestaurant' ),
			'id'                => 'woo_restaurant_adv_dislog',
			'taxonomy'          => 'woorestaurant_loc',
			'type'              => 'taxonomy_multicheck_inline',
			'select_all_button' => false,
			'remove_default'    => 'true',
			'query_args'        => array(),
			'classes'           => 'wrmb-type-taxonomy-multicheck-inline',
			'show_on_cb'        => 'woore_hide_if_disable_loc',
		)
	);
}

// Dinein  setting.
$dine_in = woo_restaurant_get_option( 'woo_restaurant_enable_dinein', 'woo_restaurant_shpping_options' );
if ( 'yes' == $dine_in ) {
	add_action( 'wrmb2_admin_init', 'woo_restaurant_register_setting_dinein', 21 );
}

/**
 * Register Setting Dinein.
 */
function woo_restaurant_register_setting_dinein() {
	$args = array(
		'id'           => 'woo_restaurant_dinein',
		'title'        => 'Dine-In',
		'object_types' => array( 'options-page' ),
		'option_key'   => 'woo_restaurant_adv_dinein_options',
		'parent_slug'  => 'woo_restaurant_options',
		'tab_group'    => 'woo_restaurant_options',
		'srz_parent'   => 'woo_restaurant_advanced_options',
		'capability'   => 'manage_woocommerce',
		'tab_title'    => esc_html__( 'Dine-In', 'woorestaurant' ),
		'icon'         => 'srzicon',
	);
	// 'tab_group' property is supported in > 2.4.0.
	if ( version_compare( WRMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'woo_restaurant_options_display_with_tabs';
	}
	$dinein_options = new_wrmb2_box( $args );
	$dinein_options->add_field(
		array(
			'name'             => esc_html__( 'User need order table before', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enter number, This feature allow user only can select date before X day or X minutes (enter number + m) from now', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_beforedate',
			'type'             => 'text',
			'show_option_none' => true,
		)
	);
	$dinein_options->add_field(
		array(
			'name'             => esc_html__( 'Disable dates', 'woorestaurant' ),
			'desc'             => esc_html__( 'Disable special Date', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_disdate',
			'type'             => 'text_date_timestamp',
			'is_60'            => 'yes',
			'default'          => '',
			'date_format'      => 'Y-m-d',
			'repeatable'       => true,
			'show_option_none' => true,
		)
	);
	$dinein_options->add_field(
		array(
			'name'             => esc_html__( 'Enable Special dates', 'woorestaurant' ),
			'desc'             => esc_html__( 'Add dates to allow user only can select these special Dates (only support display date in Select box)', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_enadate',
			'type'             => 'text_date_timestamp',
			'default'          => '',
			'date_format'      => 'Y-m-d',
			'repeatable'       => true,
			'show_option_none' => true,
		)
	);
	$dinein_options->add_field(
		array(
			'name'    => esc_html__( 'Disable days', 'woorestaurant' ),
			'desc'    => esc_html__( 'Disable special Day', 'woorestaurant' ),
			'id'      => 'woo_restaurant_ck_disday',
			'type'    => 'multicheck_inline',
			'options' => array(
				'1' => esc_html__( 'Monday', 'woorestaurant' ),
				'2' => esc_html__( 'Tuesday', 'woorestaurant' ),
				'3' => esc_html__( 'Wednesday', 'woorestaurant' ),
				'4' => esc_html__( 'Thursday', 'woorestaurant' ),
				'5' => esc_html__( 'Friday', 'woorestaurant' ),
				'6' => esc_html__( 'Saturday', 'woorestaurant' ),
				'7' => esc_html__( 'Sunday', 'woorestaurant' ),
			),

		)
	);
	$dinein_options->add_field(
		array(
			'name'    => esc_html__( 'Number of persons', 'woorestaurant' ),
			'desc'    => esc_html__( 'Select yes to enable number of persons field', 'woorestaurant' ),
			'id'      => 'woo_restaurant_ck_nbperson',
			'type'    => 'select',
			'options' => array(
				'op'      => esc_html__( 'Optional', 'woorestaurant' ),
				'req'     => esc_html__( 'Required', 'woorestaurant' ),
				'disable' => esc_html__( 'Disable', 'woorestaurant' ),
			),

		)
	);
	$dinein_options->add_field(
		array(
			'name'             => esc_html__( 'Maxinum number of person user can select', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enter number, defaul is 6', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_maxperson',
			'type'             => 'text',
			'before_row'       => '',
			'show_option_none' => true,
		)
	);
	$dinein_options->add_field(
		array(
			'name'    => esc_html__( 'Disable address fields', 'woorestaurant' ),
			'desc'    => esc_html__( 'Disable address fields when user select order method is Dine-in', 'woorestaurant' ),
			'id'      => 'woo_restaurant_ck_disaddr',
			'type'    => 'select',
			'options' => array(
				'no'  => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),

		)
	);
	$dinein_options->add_field(
		array(
			'name'             => esc_html__( 'Dine-in Surcharge/Discount', 'woorestaurant' ),
			'desc'             => esc_html__( 'Enter number:10 or Percentage of total price: 10% (enter negative number for discount)', 'woorestaurant' ),
			'id'               => 'woo_restaurant_dinein_sur',
			'type'             => 'text',
			'before_row'       => '',
			'show_option_none' => true,
		)
	);
	$dinein_options->add_field(
		array(
			'name'             => esc_html__( 'Minimum Order Amount required', 'woorestaurant' ),
			'desc'             => esc_html__( 'Set Minimum Order Amount required for Dine-in, leave blank to use default setting from General tab', 'woorestaurant' ),
			'id'               => 'woo_restaurant_ck_mini_amount',
			'type'             => 'text',
			'before_row'       => '',
			'show_option_none' => true,
		)
	);
	$dinein_options->add_field(
		array(
			'name'              => esc_html__( 'Disable locations', 'woorestaurant' ),
			'desc'              => esc_html__( 'Disable special location from Dine-in', 'woorestaurant' ),
			'id'                => 'woo_restaurant_adv_dislog',
			'taxonomy'          => 'woorestaurant_loc',
			'type'              => 'taxonomy_multicheck_inline',
			'select_all_button' => false,
			'remove_default'    => 'true',
			'query_args'        => array(),
			'classes'           => 'wrmb-type-taxonomy-multicheck-inline',
			'show_on_cb'        => 'woore_hide_if_disable_loc',
		)
	);
}

/**
 * Register shipping.
 */
function woore_hide_if_ship_radius() {
	$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
	if ( '' == $ship_mode ) {
		return false;
	}
	return true;
}

add_action( 'wrmb2_admin_init', 'woo_restaurant_register_setting_shipping', 23 );

/**
 * Register setting shipping.
 */
function woo_restaurant_register_setting_shipping() {
	// Shipping.
	$args = array(
		'id'           => 'woo_restaurant_shipping',
		'title'        => 'Shipping',
		'object_types' => array( 'options-page' ),
		'option_key'   => 'woo_restaurant_shpping_options',
		'parent_slug'  => 'woo_restaurant_options',
		'tab_group'    => 'woo_restaurant_options',
		'capability'   => 'manage_woocommerce',
		'tab_title'    => esc_html__( 'Shipping', 'woorestaurant' ),
		'icon'         => 'dashicons-cart',
	);
	if ( version_compare( WRMB2_VERSION, '2.4.0' ) ) {
		$args['display_cb'] = 'woo_restaurant_options_display_with_tabs';
	}
	$shpping_options = new_wrmb2_box( $args );
	$shpping_options->add_field(
		array(
			'name'             => esc_html__( 'Shipping method', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select disable to use default shipping method feature of WooCommerce', 'woorestaurant' ),
			'id'               => 'woo_restaurant_enable_method',
			'type'             => 'select',
			'show_option_none' => false,
			'options'          => array(
				''         => esc_html__( 'Disable', 'woorestaurant' ),
				'both'     => esc_html__( 'Delivery and Takeaway', 'woorestaurant' ),
				'takeaway' => esc_html__( 'Only Takeaway', 'woorestaurant' ),
				'delivery' => esc_html__( 'Only Delivery', 'woorestaurant' ),
			),
		)
	);
	$shpping_options->add_field(
		array(
			'name'             => esc_html__( 'Enable Dine-in option', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select yes to enable Dine-in option', 'woorestaurant' ),
			'id'               => 'woo_restaurant_enable_dinein',
			'type'             => 'select',
			'show_option_none' => false,
			'options'          => array(
				'no'  => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
		)
	);

	$shpping_options->add_field(
		array(
			'name'    => esc_html__( 'Show close button', 'woorestaurant' ),
			'desc'    => esc_html__( 'Show close button on popup select shipping method (only work if you do not set different food for each location)', 'woorestaurant' ),
			'id'      => 'woo_restaurant_cls_method',
			'type'    => 'select',
			'options' => array(
				'no'  => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),

		)
	);

	$shpping_options->add_field(
		array(
			'name'    => esc_html__( 'Limit shipping by', 'woorestaurant' ),
			'desc'    => esc_html__( 'Select shipping by Radius with Google Map API or by Postcodes feature of WooCommerce', 'woorestaurant' ),
			'id'      => 'woo_restaurant_ship_mode',
			'type'    => 'select',
			'options' => array(
				''         => esc_html__( 'Radius with Google Map API', 'woorestaurant' ),
				'postcode' => esc_html__( 'Postcodes feature of WooCommerce', 'woorestaurant' ),
			),

		)
	);

	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Postcodes', 'woorestaurant' ),
			'desc'            => esc_html__( 'Enter list of your Postcodes here, separated by a comma', 'woorestaurant' ),
			'id'              => 'woo_restaurant_ship_postcodes',
			'type'            => 'textarea_small',
			'sanitization_cb' => '',
			'show_on_cb'      => 'woore_hide_if_ship_radius',
		)
	);

	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Shipping fee', 'woorestaurant' ),
			'desc'            => esc_html__( 'Set Shipping fee for delivery, enter number', 'woorestaurant' ),
			'id'              => 'woo_restaurant_ship_fee',
			'type'            => 'text',
			'sanitization_cb' => '',
		)
	);
	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Minimum order amount to free shipping', 'woorestaurant' ),
			'desc'            => esc_html__( 'Enter number', 'woorestaurant' ),
			'id'              => 'woo_restaurant_ship_free',
			'type'            => 'text',
			'sanitization_cb' => '',
		)
	);
	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Google API', 'woorestaurant' ),
			'desc'            => esc_html__( 'The API key is required to calculate Distance, please follow this guide to create API: https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key', 'woorestaurant' ),
			'id'              => 'woo_restaurant_gg_api',
			'type'            => 'text',
			'sanitization_cb' => 'woores_allow_metadata_save_html',
		)
	);
	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Distance Matrix API', 'woorestaurant' ),
			'desc'            => esc_html__( 'If you want to restrict Google API by HTTP referrers (web sites) you will need to create seperate API for Distance Matrix API, Because you only can restrict Distance Matrix API by IP addresses (web servers, cron jobs, etc.)', 'woorestaurant' ),
			'id'              => 'woo_restaurant_gg_distance_api',
			'type'            => 'text',
			'sanitization_cb' => 'woores_allow_metadata_save_html',
		)
	);
	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Distance restrict (km)', 'woorestaurant' ),
			'desc'            => esc_html__( 'Enter number of kilometer to restrict delivery', 'woorestaurant' ),
			'id'              => 'woo_restaurant_restrict_km',
			'type'            => 'text',
			'sanitization_cb' => 'woores_allow_metadata_save_html',
		)
	);
	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Distance calculation using', 'woorestaurant' ),
			'desc'            => esc_html__( 'Select transportation mode for the calculation of distances', 'woorestaurant' ),
			'id'              => 'woo_restaurant_calcu_mode',
			'type'            => 'select',
			'options'         => array(
				''           => esc_html__( 'Driving ', 'woorestaurant' ),
				'walking '   => esc_html__( 'Walking ', 'woorestaurant' ),
				'bicycling ' => esc_html__( 'Bicycling ', 'woorestaurant' ),
			),
			'sanitization_cb' => '',
		)
	);
	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Limit auto address by country', 'woorestaurant' ),
			'desc'            => esc_html__( 'Enter country code to limit auto address complete by country, you can find your country code here: https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes, Example:US', 'woorestaurant' ),
			'id'              => 'woo_restaurant_autocomplete_limit',
			'type'            => 'text',
			'sanitization_cb' => 'woores_allow_metadata_save_html',
		)
	);
	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Disable auto address complete on checkout page', 'woorestaurant' ),
			'desc'            => esc_html__( 'Select Yes to disable auto address complete on checkout page', 'woorestaurant' ),
			'id'              => 'woo_restaurant_autocomplete_cko',
			'type'            => 'select',
			'options'         => array(
				''    => esc_html__( 'No ', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes ', 'woorestaurant' ),
			),
			'sanitization_cb' => '',
		)
	);
	$shpping_options->add_field(
		array(
			'name'            => esc_html__( 'Shipping fee by time slot', 'woorestaurant' ),
			'desc'            => esc_html__( 'Enable Shipping fee by time slot instead of km', 'woorestaurant' ),
			'id'              => 'woo_restaurant_shipfee_bytime',
			'type'            => 'select',
			'options'         => array(
				''    => esc_html__( 'No ', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes ', 'woorestaurant' ),
			),
			'sanitization_cb' => '',
		)
	);
	// Fee by km setting.
	$shpping_options->add_field(
		array(
			'name'       => esc_html__( 'Shipping fee by km', 'woorestaurant' ),
			'desc'       => '',
			'id'         => 'woores_op_cl',
			'type'       => 'title',
			'show_on_cb' => 'woore_hide_if_enable_postcodes',
		)
	);
	$shpping_options->add_field(
		array(
			'name'             => esc_html__( 'Enable shipping fee by km for each location', 'woorestaurant' ),
			'desc'             => esc_html__( 'Select yes then go to edit location and set shipping fee by km for each location', 'woorestaurant' ),
			'id'               => 'woores_km_loc',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''    => esc_html__( 'No', 'woorestaurant' ),
				'yes' => esc_html__( 'Yes', 'woorestaurant' ),
			),
			'show_on_cb'       => 'woore_hide_if_enable_postcodes',
		)
	);
	$feebykm_option = $shpping_options->add_field(
		array(
			'id'          => 'woores_adv_feekm',
			'type'        => 'group',
			'description' => esc_html__( 'Set shipping fee by km, leave blank to use default shipping fee above', 'woorestaurant' ),
			'options'     => array(
				'group_title'   => esc_html__( 'Shipping fee by km {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
				'add_button'    => esc_html__( 'Add new', 'woorestaurant' ),
				'remove_button' => esc_html__( 'Remove', 'woorestaurant' ),
				'sortable'      => true, // beta.
				'closed'        => false, // true to have the groups closed by default.
			),
			'after_group' => '',
			'show_on_cb'  => 'woore_hide_if_enable_postcodes',
		)
	);
	$shpping_options->add_group_field(
		$feebykm_option,
		array(
			'name'    => esc_html__( 'Max number of km', 'tv-schedule' ),
			'id'      => 'km',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$shpping_options->add_group_field(
		$feebykm_option,
		array(
			'name'    => esc_html__( 'Fee', 'tv-schedule' ),
			'id'      => 'fee',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$shpping_options->add_group_field(
		$feebykm_option,
		array(
			'name'    => esc_html__( 'Free if total amount reach', 'tv-schedule' ),
			'id'      => 'free',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$shpping_options->add_group_field(
		$feebykm_option,
		array(
			'name'    => esc_html__( 'Minimum amount required', 'tv-schedule' ),
			'id'      => 'min_amount',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	// Shippng postcodes.
	$shpping_options->add_field(
		array(
			'name'       => esc_html__( 'Postcodes settings', 'woorestaurant' ),
			'desc'       => '',
			'id'         => 'woores_poc',
			'type'       => 'title',
			'show_on_cb' => 'woore_hide_if_disable_postcodes',
		)
	);
	$feebypos_option = $shpping_options->add_field(
		array(
			'id'          => 'woores_adv_feepos',
			'type'        => 'group',
			'description' => esc_html__( 'Set shipping fee by postcode, leave blank to use default shipping fee from WooCommerce setting', 'woorestaurant' ),
			'options'     => array(
				'group_title'   => esc_html__( 'Shipping fee by postcode {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
				'add_button'    => esc_html__( 'Add new', 'woorestaurant' ),
				'remove_button' => esc_html__( 'Remove', 'woorestaurant' ),
				'sortable'      => true, // beta.
				'closed'        => false, // true to have the groups closed by default.
			),
			'after_group' => '',
			'show_on_cb'  => 'woore_hide_if_disable_postcodes',
		)
	);
	$shpping_options->add_group_field(
		$feebypos_option,
		array(
			'name'    => esc_html__( 'Postcode', 'tv-schedule' ),
			'id'      => 'postcode',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$shpping_options->add_group_field(
		$feebypos_option,
		array(
			'name'    => esc_html__( 'Fee', 'tv-schedule' ),
			'id'      => 'fee',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$shpping_options->add_group_field(
		$feebypos_option,
		array(
			'name'    => esc_html__( 'Free if total amount reach', 'tv-schedule' ),
			'id'      => 'free',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
	$shpping_options->add_group_field(
		$feebypos_option,
		array(
			'name'    => esc_html__( 'Minimum amount required', 'tv-schedule' ),
			'id'      => 'min_amount',
			'type'    => 'text',
			'classes' => 'column-4',
		)
	);
}

/**
 * Hide if enable postcodes.
 *
 * @param object $field It is the field object.
 */
function woore_hide_if_enable_postcodes( $field ) {
	$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
	if ( 'postcode' == $ship_mode ) {
		return false;
	}
	return true;
}

/**
 * Hide if disable postcodes.
 *
 * @param object $field It is the field object.
 */
function woore_hide_if_disable_postcodes( $field ) {
	$ship_mode = woo_restaurant_get_option( 'woo_restaurant_ship_mode', 'woo_restaurant_shpping_options' );
	if ( 'postcode' != $ship_mode ) {
		return false;
	}
	return true;
}

/**
 * Allow metadata save html.
 *
 * @param object $original_value It is the object value.
 * @param object $args It is the argument.
 * @param object $wrmb2_field It is the field object.
 */
function woores_allow_metadata_save_html( $original_value, $args, $wrmb2_field ) {
	return $original_value; // Unsanitized value.
}

/**
 * Get param restaurant.
 *
 * @param string $url It is the URL.
 * @param string $param_name It is the param name.
 */
function get_param_woorestaurant( $url, $param_name ) {
	$url_data  = parse_url( $url, PHP_URL_QUERY );
	$url_data  = explode( '&', $url_data );
	$url_datax = array();
	foreach ( $url_data as $datas ) {
		$s_data             = explode( '=', $datas );
		$name               = isset( $s_data[0] ) ? $s_data[0] : '';
		$value              = isset( $s_data[1] ) ? $s_data[1] : '';
		$url_datax[ $name ] = $value;
	}
	return isset( $url_datax[ $param_name ] ) ? $url_datax[ $param_name ] : null;
}

/**
 * A WRMB2 options-page display callback override which adds tab navigation among
 * WRMB2 options pages which share this same display callback.
 *
 * @param WRMB2_Options_Hookup $wrmb_options The WRMB2_Options_Hookup object.
 */
function woo_restaurant_options_display_with_tabs( $wrmb_options ) {
	$tabs = woo_restaurant_options_page_tabs( $wrmb_options );
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$current_url = get_site_url( null, wp_kses_post( wp_unslash( $_SERVER['REQUEST_URI'] ) ), null );
	}
	$page         = get_param_woorestaurant( $current_url, 'page' );
	$_GET['page'] = ( $page );
	$tab          = isset( $_GET['page'] ) && '' != $_GET['page'] ? wp_kses_post( wp_unslash( $_GET['page'] ) ) : '';
	$current_data = $tabs[ wp_kses_post( wp_unslash( $_GET['page'] ) ) ];
	$current      = isset( $current_data[2] ) && ! empty( $current_data[2] ) ? $current_data[2] : false;
	$parent_data  = ! $current ? array() : $tabs[ $current ];
	$titlemenu    = '';
	if ( $wrmb_options->wrmb->prop( 'title' ) ) {
		$titlemenu = wp_kses_post( $wrmb_options->wrmb->prop( 'title' ) );
	} elseif ( false !== $current && isset( $parent_data[0] ) ) {
		$titlemenu = wp_kses_post( $current_data[0] );
	}
	?>
	<div class="wpc-settings wrap wrmb2-options-page option-<?php echo wp_kses_post( $wrmb_options->option_key ); ?>" style="max-width: unset !important;">
	  

<div class="wrap">
	
		<ul class="wpc-settings-tab nav nav-tabs wpc-tab">
			<?php
			foreach ( $tabs as $option_key => $tab_data ) :
				if ( 'woo_restaurant_adv_timesl_options' == $option_key || 'woo_restaurant_adv_takeaway_options' == $option_key
				|| 'woo_restaurant_adv_dinein_options' == $option_key || 'woo_restaurant_tip_options' == $option_key || 'woo_restaurant_whatsapp_options' == $option_key ) {
					continue;
				}
				?>
			<li>
		<a href="<?php menu_page_url( $option_key ); ?>" class="nav-tab
										<?php
										if ( ( ( isset( $_GET['page'] ) && $option_key === $_GET['page'] ) || ( false !== $current && $current == $option_key ) ) ) :
											?>
			nav-tab-active<?php endif; ?>" data-id="<?php echo wp_kses_post( $option_key ); ?>">
		
			<span><?php echo wp_kses_post( $tab_data[0] ); ?></span>
		</a>
	</li>
			<?php endforeach; ?>
		</ul>
		<form class="wrmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo wp_kses_post( $wrmb_options->wrmb->wrmb_id ); ?>" enctype="multipart/form-data" encoding="multipart/form-data">
			<input type="hidden" name="action" value="<?php echo wp_kses_post( $wrmb_options->option_key ); ?>">
			<div class="tab-content settings-content-wraps">

			<div class="wpc-tab-wrapper wpc-tab-style2">
			<?php
			if ( ( isset( $current_data[3] ) && ! empty( $current_data[3] ) ) || ( false !== $current && isset( $parent_data[3] ) && ! empty( $parent_data[3] ) ) ) {
				?>
			<div class="wpc-tab-wrapper wpc-tab-style2"> <?php } ?>
			<?php
			if ( ( isset( $current_data[3] ) && ! empty( $current_data[3] ) ) || ( false !== $current && isset( $parent_data[3] ) && ! empty( $parent_data[3] ) ) ) {
				?>
			<ul class="wpc-nav mb-30">
				<?php
				$i        = 0;
				$sub_tabs = isset( $current_data[3] ) ? $current_data[3] : array();

				$sub_tabs = empty( $sub_tabs ) ? $parent_data[3] : $sub_tabs;
				if ( ! woore_show_if_enable_dinein() ) {
					unset( $sub_tabs['woo_restaurant_adv_dinein_options'] );
				}
				if ( woore_show_if_disabled_shipping_method() ) {
					unset( $sub_tabs['woo_restaurant_adv_dinein_options'] );
					unset( $sub_tabs['woo_restaurant_adv_takeaway_options'] );
				}
				foreach ( $sub_tabs as $slug => $title ) {
					$act = $slug == $tab ? 'wpc-active' : '';
					echo ' <li>
            <a class="wpc-tab-a ' . wp_kses_post( $act ) . '" href="' . esc_html( admin_url( '/admin.php?page=' . $slug ) ) . '" data-id="' . wp_kses_post( $slug ) . '"> ' . wp_kses_post( $title ) . ' </a>
        </li>';
					++$i;
				}
				?>
			</ul><?php } ?>

			<div class="wpc-tab-content">
			<div class="tab-pane active">
			<?php $wrmb_options->options_page_metabox(); ?>
			<div class="">
				<?php submit_button( wp_kses_post( $wrmb_options->wrmb->prop( 'save_button' ) ), 'wpc_mt_two wpc-btn', 'submit-wrmb', true, array( 'id' => 'submit_woores' ) ); ?>
			</div>
			</div>
			</div>
			
			<?php
			if ( ( isset( $current_data[3] ) && ! empty( $current_data[3] ) ) || ( false !== $current && isset( $current[3] ) && ! empty( $current[3] ) ) ) {

				?>
			</div> <?php } ?>

			
			</div>
		
		</div>
		</form>
	</div>	
</div>

<div id="wooresmenu" style="display:none;">
	<?php woores_menu_head( $titlemenu ); ?>
</div>
<script>
jQuery(document).ready(function($) {
	$('#wooresmenu').show();
	$("#wooresmenu").prependTo("#wpbody-content");


});
</script>
<style>.update-nag{display:none !important;}</style>

	<?php
}
/**
 * Gets navigation tabs array for WRMB2 options pages which share the given
 * display_cb param.
 *
 * @param WRMB2_Options_Hookup $wrmb_options The WRMB2_Options_Hookup object.
 *
 * @return array Array of tab information.
 */
function woo_restaurant_options_page_tabs( $wrmb_options ) {
	$tab_group = $wrmb_options->wrmb->prop( 'tab_group' );
	$tabs      = array();
	foreach ( WRMB2_Boxes::get_all() as $wrmb_id => $wrmb ) {
		if ( $tab_group === $wrmb->prop( 'tab_group' ) ) {
			$title                                 = $wrmb->prop( 'tab_title' )
				? $wrmb->prop( 'tab_title' )
				: $wrmb->prop( 'title' );
			$icon                                  = $wrmb->prop( 'icon' )
				? $wrmb->prop( 'icon' )
				: 'dashicons-admin-generic';
			$parent                                = $wrmb->prop( 'srz_parent' )
				? $wrmb->prop( 'srz_parent' )
				: '';
			$sub_tabs                              = $wrmb->prop( 'sub_tabs' )
				? $wrmb->prop( 'sub_tabs' )
				: array();
			$tabs[ $wrmb->options_page_keys()[0] ] = array( $title, $icon, $parent, $sub_tabs );
		}
	}
	return $tabs;
}

/**
 * Render openclose Field.
 *
 * @param object $field It is the field object.
 * @param string $value It is the value.
 * @param int    $object_id It is the object id.
 * @param string $object_type It is the object type.
 * @param object $field_type It is the field object.
 */
function woorewrmb2_render_openclose_field_callback( $field, $value, $object_id, $object_type, $field_type ) {
	// make sure we specify each part of the value we need.
	$value = wp_parse_args(
		$value,
		array(
			'open-time'  => '',
			'close-time' => '',
		)
	);
	?>
	<div class="woore-open-time"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_open_time' ) ); ?>"><?php esc_html_e( 'Opening time', 'woorestaurant' ); ?></label></p>
		<?php
		// phpcs:ignore
		echo ( $field_type->input(
			array(
				'class'           => 'wrmb2-timepicker text-time',
					// phpcs:ignore
					'name'            => ( $field_type->_name( '[open-time]' ) ),
					// phpcs:ignore
					'id'              => ( $field_type->_id( '_open_time' ) ),
					// phpcs:ignore
					'value'           => ( $value['open-time'] ),
				'type'            => 'text',
				'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ),
				'desc'            => '',
			)
		) );
		?>
	</div>
	<div class="woore-close-time"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_close_time' ) ); ?>'"><?php esc_html_e( 'Closing time', 'woorestaurant' ); ?></label></p>
		<?php
		// phpcs:ignore
		echo ( $field_type->input(
			array(
				'class'           => 'wrmb2-timepicker text-time',
					// phpcs:ignore
					'name'            => ( $field_type->_name( '[close-time]' ) ),
					// phpcs:ignore
					'id'              => ( $field_type->_id( '_close_time' ) ),
					// phpcs:ignore
					'value'           => ( $value['close-time'] ),
				'type'            => 'text',
				'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ),
				'desc'            => '',
			)
		) );
		?>
	</div>
	<br class="clear">
	<?php
	// phpcs:ignore
	echo ( $field_type->_desc( true ) );
}

add_filter( 'wrmb2_render_openclose', 'woorewrmb2_render_openclose_field_callback', 10, 5 );
/**
 * Sanitize open close callback.
 *
 * @param string $override_value It is the override value.
 * @param string $value It is the value.
 */
function woorewrmb2_sanitize_openclose_callback( $override_value, $value ) {
	echo '<pre>';
	print_r( $value );
	exit;
	return $value;
}
// add_filter( 'wrmb2_sanitize_openclose', 'woorewrmb2_sanitize_openclose_callback', 10, 2 );.


add_filter( 'wrmb2_sanitize_openclose', 'wooresanitize', 10, 5 );
add_filter( 'wrmb2_types_esc_openclose', 'wooreescape', 10, 4 );

/**
 * Resanitize.
 *
 * @param bool   $check It is the check.
 * @param string $meta_value It is the meta value.
 * @param object $object_id It is the object id.
 * @param array  $field_args It is the field argument.
 * @param object $sanitize_object It is the sanitize object.
 */
function wooresanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {

	// if not repeatable, bail out.
	if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
		return $check;
	}

	foreach ( $meta_value as $key => $val ) {
		$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
	}

	return array_filter( $meta_value );
}

/**
 * Reescape.
 *
 * @param bool   $check It is the check.
 * @param string $meta_value It is the meta value.
 * @param array  $field_args It is the field argument.
 * @param object $field_object It is the sanitize object.
 */
function wooreescape( $check, $meta_value, $field_args, $field_object ) {
	// if not repeatable, bail out.
	if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
		return $check;
	}

	foreach ( $meta_value as $key => $val ) {
		$meta_value[ $key ] = array_filter( array_map( 'wp_kses_post', $val ) );
	}

	return array_filter( $meta_value );
}

/**
 * Render timedelivery field callback.
 *
 * @param object $field It is the field object.
 * @param string $value It is the value.
 * @param int    $object_id It is the object id.
 * @param object $object_type It is the object type.
 * @param object $field_type It is the field type.
 */
function woorewrmb2_render_timedelivery_field_callback( $field, $value, $object_id, $object_type, $field_type ) {
	// Make sure we specify each part of the value we need.
	$value       = wp_parse_args(
		$value,
		array(
			'start-time'   => '',
			'end-time'     => '',
			'name-ts'      => '',
			'max-odts'     => '',
			'ship-fee'     => '',
			'disable-slot' => '',
		)
	);
	$ship_bytime = woo_restaurant_get_option( 'woo_restaurant_shipfee_bytime', 'woo_restaurant_shpping_options' );
	$disable_sl  = woo_restaurant_get_option( 'woo_restaurant_disable_tslot', 'woo_restaurant_advanced_options' );
	$class       = 'yes' == $ship_bytime ? 'fee-bytimeslot' : '';
	if ( 'yes' == $disable_sl ) {
		$class .= ' disable-slt';
	}
	?>
	<div class="woore-timeslots <?php echo wp_kses_post( $class ); ?>">
		<div class="woore-open-time"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_st_time' ) ); ?>"><?php esc_html_e( 'Start time', 'woorestaurant' ); ?></label></p>
			<?php
			// phpcs:ignore
			echo ( $field_type->input(
				array(
					'class'           => 'wrmb2-timepicker text-time',
						// phpcs:ignore
						'name'            => ( $field_type->_name( '[start-time]' ) ),
						// phpcs:ignore
						'id'              => ( $field_type->_id( '_st_time' ) ),
						// phpcs:ignore
						'value'           => ( $value['start-time'] ),
					'type'            => 'text',
					'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ),
					'desc'            => '',
				)
			) );
			?>
		</div>
		<div class="woore-close-time"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_ed_time' ) ); ?>'"><?php esc_html_e( 'End time', 'woorestaurant' ); ?></label></p>
			<?php
			// phpcs:ignore
			echo ( $field_type->input(
				array(
					'class'           => 'wrmb2-timepicker text-time',
						// phpcs:ignore
						'name'            => ( $field_type->_name( '[end-time]' ) ),
						// phpcs:ignore
						'id'              => ( $field_type->_id( '_ed_time' ) ),
						// phpcs:ignore
						'value'           => ( $value['end-time'] ),
					'type'            => 'text',
					'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-datetimepicker' ),
					'desc'            => '',
				)
			) );
			?>
		</div>
		<div class="woore-name-time"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_name_ts' ) ); ?>'"><?php esc_html_e( 'Name of time slot', 'woorestaurant' ); ?></label></p>
			<?php
			// phpcs:ignore
			echo ( $field_type->input(
				array(
					'class'     => 'regular-text',
						// phpcs:ignore
						'name'  => ( $field_type->_name( '[name-ts]' ) ),
						// phpcs:ignore
						'id'    => ( $field_type->_id( '_name_ts' ) ),
						// phpcs:ignore
						'value' => ( $value['name-ts'] ),
					'type'      => 'text',
					'desc'      => '',
				)
			) );
			?>
		</div>
		<div class="woore-max-order"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_max_odts' ) ); ?>'"><?php esc_html_e( 'Max number of order', 'woorestaurant' ); ?></label></p>
			<?php
			// phpcs:ignore
			echo ( $field_type->input(
				array(
					'class'     => 'regular-text',
						// phpcs:ignore
						'name'  => ( $field_type->_name( '[max-odts]' ) ),
						// phpcs:ignore
						'id'    => ( $field_type->_id( '_max_odts' ) ),
						// phpcs:ignore
						'value' => ( $value['max-odts'] ),
					'type'      => 'text',
					'desc'      => '',
				)
			) );
			?>
		</div>
		<?php if ( 'yes' == $ship_bytime ) { ?>
			<div class="woore-shipping-fee" style="display: none;"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_ship_fee' ) ); ?>'"><?php esc_html_e( 'Shipping fee', 'woorestaurant' ); ?></label></p>
				<?php
				// phpcs:ignore
				echo ( $field_type->input(
					array(
						'class'     => 'regular-text',
							// phpcs:ignore
							'name'  => ( $field_type->_name( '[ship-fee]' ) ),
							// phpcs:ignore
							'id'    => ( $field_type->_id( '_ship_fee' ) ),
							// phpcs:ignore
							'value' => ( $value['ship-fee'] ),
						'type'      => 'text',
						'desc'      => '',
					)
				) );
				?>
			</div>
		<?php } ?>
		<?php if ( 'yes' == $disable_sl ) { ?>
			<div class="woore-disable-slot"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_disable_sl' ) ); ?>'"><?php esc_html_e( 'Disable ?', 'woorestaurant' ); ?></label></p>
				<?php
				$arr_ck = array(
					'class' => 'checkbox-dis',
					// phpcs:ignore
					'name'  => ( $field_type->_name( '[disable-slot]' ) ),
					// phpcs:ignore
					'id'    => ( $field_type->_id( '_disable_sl' ) ),
					// phpcs:ignore
					'value' => ( $value['disable-slot'] ),
					'type'  => 'checkbox',
					'desc'  => '',
				);
				if ( '1' == $value['disable-slot'] ) {
					$arr_ck['checked'] = 'checked';
				}
				// phpcs:ignore
				echo ( $field_type->input( $arr_ck ) );
				// phpcs:ignore
				echo ( $field_type->input(
					array(
						'class'     => 'regular-text',
							// phpcs:ignore
							'name'  => ( $field_type->_name( '[disable-slot]' ) ),
							// phpcs:ignore
							'id'    => ( $field_type->_id( '_disable_sl' ) ),
							// phpcs:ignore
							'value' => ( $value['disable-slot'] ),
						'type'      => 'hidden',
						'desc'      => '',
					)
				) );
				?>
			</div>
		<?php } ?>
	</div>
	<br class="clear">
	<?php
	// phpcs:ignore
	echo ( $field_type->_desc( true ) );
}
add_filter( 'wrmb2_render_timedelivery', 'woorewrmb2_render_timedelivery_field_callback', 10, 5 );

add_filter( 'wrmb2_sanitize_timedelivery', 'wooresanitize', 10, 5 );
add_filter( 'wrmb2_types_esc_timedelivery', 'wooreescape', 10, 4 );