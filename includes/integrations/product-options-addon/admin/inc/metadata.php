<?php
/**
 * Register metadata box
 *
 * @package woorestaurant
 */

/**
 * To hide if no product.
 *
 * @param object $field it is the field object.
 *
 * @return bool
 */
function woore_hide_if_no_product( $field ) {
	// Don't show this field if not in the cats category.
	if ( 'woores_modifiers' == get_post_type( $field->object_id ) ) {
		return false;
	}
	return true;
}

add_action( 'wrmb2_admin_init', 'woores_register_metabox' );
/**
 * Register Metabox.
 */
function woores_register_metabox() {
	$prefix = 'woores_';
	/**
	 * Food general info
	 */

	$woores_options = new_wrmb2_box(
		array(
			'id'           => $prefix . 'addition_options',
			'title'        => esc_html__( 'Additional option', 'product-options-addon' ),
			'object_types' => array( 'product', 'woores_modifiers' ), // Post type.
		)
	);
	$woores_options->add_field(
		array(
			'name'        => esc_html__( 'Exclude Global Option', 'product-options-addon' ),
			'description' => esc_html__( 'Exclude all Global Options apply this product', 'product-options-addon' ),
			'id'          => 'woores_exclude_options',
			'type'        => 'checkbox',
			'default'     => '',
			'show_on_cb'  => 'woore_hide_if_no_product',
		)
	);
	$woores_options->add_field(
		array(
			'name'            => esc_html__( 'Include global options', 'product-options-addon' ),
			'id'              => 'woores_include_options',
			'type'            => 'post_search_text',
			'desc'            => esc_html__( 'Select Option(s) to apply for this product', 'product-options-addon' ),
			'post_type'       => 'woores_modifiers',
			'select_type'     => 'checkbox',
			'select_behavior' => 'add',
			'after_field'     => '',
			'show_on_cb'      => 'woore_hide_if_no_product',
		)
	);
	$woores_options->add_field(
		array(
			'name'        => esc_html__( 'Global Option position', 'product-options-addon' ),
			'description' => esc_html__( 'Select postion of global option', 'product-options-addon' ),
			'id'          => 'woores_options_pos',
			'type'        => 'select',
			'default'     => '',
			'show_on_cb'  => 'woore_hide_if_no_product',
			'options'     => array(
				''       => esc_html__( 'After option of product', 'woorestaurant' ),
				'before' => esc_html__( 'Before option of product', 'woorestaurant' ),
			),
		)
	);
	$group_option = $woores_options->add_field(
		array(
			'id'          => $prefix . 'options',
			'type'        => 'group',
			'description' => esc_html__( 'Add additional product option to allow user can order with this product', 'product-options-addon' ),
			'options'     => array(
				'group_title'   => esc_html__( 'Option {#}', 'product-options-addon' ), // since version 1.1.4, {#} gets replaced by row number.
				'add_button'    => esc_html__( 'Add Option', 'product-options-addon' ),
				'remove_button' => esc_html__( 'Remove Option', 'product-options-addon' ),
				'sortable'      => true, // beta.
				'closed'        => true, // true to have the groups closed by default.
			),
			'after_group' => '',
		)
	);
	// Id's for group's fields only need to be unique for the group. Prefix is not needed.
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'       => esc_html__( 'Name', 'product-options-addon' ),
			'id'         => '_name',
			'type'       => 'text',
			'classes'    => 'woores-stgeneral woores-op-name',
			'before_row' => 'woore_option_sttab_html',
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'             => esc_html__( 'Option type', 'product-options-addon' ),
			'description'      => esc_html__( 'Select type of this option', 'product-options-addon' ),
			'id'               => '_type',
			'classes'          => 'woores-stgeneral extype-option woores-op-type',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''         => esc_html__( 'Checkboxes', 'product-options-addon' ),
				'radio'    => esc_html__( 'Radio buttons', 'product-options-addon' ),
				'select'   => esc_html__( 'Select box', 'product-options-addon' ),
				'text'     => esc_html__( 'Textbox', 'product-options-addon' ),
				'textarea' => esc_html__( 'Textarea', 'product-options-addon' ),
				'quantity' => esc_html__( 'Quantity', 'product-options-addon' ),
			),
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'             => esc_html__( 'Required?', 'product-options-addon' ),
			'description'      => esc_html__( 'Select this option is required or not', 'product-options-addon' ),
			'id'               => '_required',
			'type'             => 'select',
			'classes'          => 'woores-stgeneral woores-op-rq',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''    => esc_html__( 'No', 'product-options-addon' ),
				'yes' => esc_html__( 'Yes', 'product-options-addon' ),
			),
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'        => esc_html__( 'Minimun selection', 'product-options-addon' ),
			'classes'     => 'woores-stgeneral wrshide-radio wrshide-select wrshide-quantity wrshide-textbox wrshide-textarea woores-op-min',
			'description' => esc_html__( 'Enter number minimum at least option required', 'product-options-addon' ),
			'id'          => '_min_op',
			'type'        => 'text',
			'default'     => '',
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'        => esc_html__( 'Maximum selection', 'product-options-addon' ),
			'classes'     => 'woores-stgeneral wrshide-radio wrshide-select wrshide-quantity wrshide-textbox wrshide-textarea woores-op-max',
			'description' => esc_html__( 'Enter number Maximum option can select', 'product-options-addon' ),
			'id'          => '_max_op',
			'type'        => 'text',
			'default'     => '',
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'        => esc_html__( 'Options', 'product-options-addon' ),
			'classes'     => 'woores-stgeneral wrshide-textbox wrshide-quantity wrshide-textarea woores-op-ops',
			'description' => esc_html__( 'Set name and price for each option', 'product-options-addon' ),
			'id'          => '_options',
			'type'        => 'price_options',
			'repeatable'  => true,
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'             => esc_html__( 'Type of price', 'product-options-addon' ),
			'description'      => '',
			'classes'          => 'woores-stgeneral wrsshow-textbox wrsshow-quantity wrsshow-textarea woores-hidden woores-op-tpr',
			'id'               => '_price_type',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''      => esc_html__( 'Quantity Based', 'product-options-addon' ),
				'fixed' => esc_html__( 'Fixed Amount', 'product-options-addon' ),
			),
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'        => esc_html__( 'Price', 'product-options-addon' ),
			'classes'     => 'woores-stgeneral wrsshow-textbox wrsshow-quantity wrsshow-textarea woores-hidden woores-op-pri',
			'description' => '',
			'id'          => '_price',
			'type'        => 'text',
			'default'     => '',
			'after_row'   => '',
		)
	);

	$woores_options->add_group_field(
		$group_option,
		array(
			'name'        => '',
			'classes'     => 'woores-stgeneral wrsshow-textbox wrsshow-quantity wrsshow-textarea woores-hidden woores-op-pri',
			'description' => '',
			'id'          => 'plu',
			'type'        => 'hidden',
			'default'     => '',
			'after_row'   => '',
		)
	);

	$woores_options->add_group_field(
		$group_option,
		array(
			'name'        => esc_html__( 'Display type', 'product-options-addon' ),
			'classes'     => 'woores-stgeneral',
			'description' => esc_html__( 'Select Display type of this option, you can select default display type in settings page', 'product-options-addon' ),
			'id'          => '_display_type',
			'type'        => 'select',
			'default'     => '',
			'options'     => array(
				''      => esc_html__( 'Default', 'product-options-addon' ),
				'nor'   => esc_html__( 'Normal', 'product-options-addon' ),
				'accor' => esc_html__( 'Accordion', 'product-options-addon' ),
			),
			'after_row'   => '</div>',
		)
	);

	$woores_options->add_group_field(
		$group_option,
		array(
			'name'             => esc_html__( 'Enable Conditional Logic', 'product-options-addon' ),
			'description'      => esc_html__( 'Enable Conditional Logic for this option', 'product-options-addon' ),
			'classes'          => 'woores-stcon-logic',
			'id'               => '_enb_logic',
			'type'             => 'checkbox',
			'show_option_none' => false,
			'before_row'       => '<div class="woores-con-logic">',
			'default'          => '',
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'             => esc_html__( 'Conditional Logic', 'product-options-addon' ),
			'classes'          => 'woores-stcon-logic',
			'description'      => '',
			'id'               => '_con_tlogic',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => '',
			'options'          => array(
				''     => esc_html__( 'Show this option if', 'product-options-addon' ),
				'hide' => esc_html__( 'Hide this option if', 'product-options-addon' ),
			),
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'             => ' ',
			'classes'          => 'woores-stcon-logic',
			'description'      => '',
			'is_60'            => 'yes',
			'id'               => '_con_logic',
			'type'             => 'conlogic_options',
			'repeatable'       => true,
			'show_option_none' => false,
		)
	);
	$woores_options->add_group_field(
		$group_option,
		array(
			'name'             => esc_html__( 'Id of option', 'product-options-addon' ),
			'description'      => '',
			'classes'          => 'woores-stcon-logic woores-hidden',
			'id'               => '_id',
			'type'             => 'text',
			'show_option_none' => false,
			'default'          => '',
			'sanitization_cb'  => 'woores_metadata_save_id_html',
			'after_row'        => '</div>',
		)
	);

	$woore_proptions = new_wrmb2_box(
		array(
			'id'           => $prefix . 'products',
			'title'        => esc_html__( 'Products & Categories', 'woorestaurant' ),
			'object_types' => array( 'woores_modifiers' ),
		)
	);
	$woore_proptions->add_field(
		array(
			'name'           => 'Category',
			'desc'           => 'Set Category',
			'id'             => 'wiki_test_taxonomy_multicheck',
			'taxonomy'       => 'product_cat', // Enter Taxonomy Slug.
			'type'           => 'taxonomy_multicheck_inline',
			'text'           => array(
				'no_terms_text' => 'Sorry, no terms could be found.', // Change default text. Default: "No terms".
			),
			'remove_default' => 'true', // Removes the default metabox provided by WP core.
			// Optionally override the args sent to the WordPress get_terms function.
			'query_args'     => array(
			// 'orderby' => 'slug',
			// 'hide_empty' => true,
			),
		)
	);

	$woore_proptions->add_field(
		array(
			'name'            => 'Products',
			'id'              => $prefix . 'product_ids',
			'type'            => 'post_search_text',
			'desc'            => esc_html__( 'Select product to apply this options', 'woorestaurant' ),
			'post_type'       => 'product',
			'select_type'     => 'checkbox',
			'select_behavior' => 'add',
			'after_field'     => '',
			'sanitization_cb' => 'woores_save_single_id_prod',
		)
	);
}

/**
 * Save single id product.
 *
 * @param string $value it is having the value.
 * @param array  $field_args it is the field argument.
 * @param object $field it is the field object.
 */
function woores_save_single_id_prod( $value, $field_args, $field ) {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'save-single' ) ) {
			return;
		}
	}

	if ( '' != $value && isset( $_POST['post_ID'] ) && '' != $_POST['post_ID'] ) {
		delete_post_meta( wp_kses_post( wp_unslash( $_POST['post_ID'] ) ), 'woores_product_ids_arr' );
		$arr_ids = explode( ',', $value );
		foreach ( $arr_ids as $key => $item ) {
			add_post_meta( wp_kses_post( wp_unslash( $_POST['post_ID'] ) ), 'woores_product_ids_arr', str_replace( ' ', '', $item ) );
		}
	}

	return $value;
}

/**
 * Save metadata id html.
 *
 * @param string $original_value it is having the value.
 * @param array  $args it is the field argument.
 * @param object $wrmb2_field it is the field object.
 */
function woores_metadata_save_id_html( $original_value, $args, $wrmb2_field ) {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'metadata-save' ) ) {
			return;
		}
	}

	if ( ( isset( $_POST['woores_options'] ) || isset( $_POST['woores_options']['0']['_name'] ) ) && 1 == count( $_POST['woores_options'] ) ) {
		if ( '' == $_POST['woores_options']['0']['_name'] ) {
			return $original_value;
		}
	}
	if ( '' == $original_value ) {
		$original_value = 'woores-id' . rand( 10000, 10000000000 );
	}
	return $original_value; // Unsanitized value.
}

/**
 * Option tab HTML.
 *
 * @param array  $field_args it is having field arguments.
 * @param object $field it is the field object.
 */
function woore_option_sttab_html( $field_args, $field ) {
	echo '<p class="woores-gr-option">
		<a href="javascript:;" class="current" data-add=".woores-general" data-remove=".woores-con-logic">' . esc_html__( 'General', 'product-options-addon' ) . '</a>
		<a href="javascript:;" class="woores-copypre">' . esc_html__( 'Copy from previous option', 'product-options-addon' ) . '</a>
		<a href="javascript:;" class="woores-copy" data-textdis="' . esc_html__( 'Please save option before copy', 'product-options-addon' ) . '">' . esc_html__( 'Copy this option', 'product-options-addon' ) . '</a>
		<a href="javascript:;" class="woores-paste">
		<span class="woores-paste-tt">' . esc_html__( 'Paste option', 'product-options-addon' ) . '</span>
		<span class="woores-paste-mes" style="display:none">' . esc_html__( 'Completed!', 'product-options-addon' ) . '</span>

		<textarea style="display:none" class="woores-ctpaste"  placeholder="' . esc_html__( 'Paste your option here', 'product-options-addon' ) . '"></textarea></a>';

		echo '<a href="javascript:;" class="" data-add=".woores-con-logic" data-remove=".woores-general">' . esc_html__( 'Conditional logic', 'product-options-addon' ) . '</a>';

		echo '
	</p>
	<div class="woores-general">';
}

add_action(
	'dbx_post_sidebar',
	function () {
		global $pagenow;
		$is_do = false;
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woores_modifiers' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
		}
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woorestaurant_scbd' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woores_modifiers' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woorestaurant_scbd' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
		}
		if ( $is_do ) {
			echo '<input type="hidden" name="post_status" value="publish"><input type="submit" name="submit" class="wpc_mt_two wpc-btn wpc_global_addons_save" value="Save Changes">';
		}
	}
);

add_action(
	'admin_footer',
	function () {
		global $pagenow;
		$is_do = false;
		$text  = '';

		if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'woorestaurant_scbd' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
			$text  = 'Menus';
		}
		if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'woores_modifiers' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
			$text  = 'Modifiers';
		}

		if ( 'edit-tags.php' == $pagenow && isset( $_GET['taxonomy'] ) && 'woorestaurant_loc' == wp_kses_post( wp_unslash( $_GET['taxonomy'] ) ) ) {
			$is_do = true;
			$text  = 'Locations';
		}
		if ( 'term.php' == $pagenow && isset( $_GET['taxonomy'] ) && 'woorestaurant_loc' == wp_kses_post( wp_unslash( $_GET['taxonomy'] ) ) ) {
			$is_do = true;
			$text  = 'Edit Location';
		}

		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woores_modifiers' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
			$text  = 'Modifiers';
		}
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woorestaurant_scbd' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
			$text  = 'Menus';
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woores_modifiers' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
			$text  = 'Add Modifiers';
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woorestaurant_scbd' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
				$is_do = true;
				$text  = 'Add Menus';
		}
		if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'woo_restaurant_options' == wp_kses_post( wp_unslash( $_GET['page'] ) ) ) {
			$text;
		}

		if ( $is_do ) {
			?>
<div id="wooresmenu" style="display:none;">

  
<div class="wpc-admin-header" >
	<div class="wpc-admin-header-logo">
	<div class="wpc-logo-wrap">
			<img src="<?php echo wp_kses_post( WOORESTAURANT_ASSETS ) . 'admin/img/logo.jpg'; ?>" alt="logo">
		</div>
	<h1 class="wpc-settings-title wpc-header-title"><?php echo wp_kses_post( $text ); ?></h1>
	</div>
		<div class="wpc-admin-menu-wrap">
		<ul>
			<li><a href="<?php echo wp_kses_post( admin_url( '/edit.php?post_type=woores_modifiers' ) ); ?>">Modifiers</a></li> 
			<li><a href="<?php echo wp_kses_post( admin_url( '/edit.php?post_type=woorestaurant_scbd' ) ); ?>">Menu</a></li> 
			<li><a href="<?php echo wp_kses_post( admin_url( '/edit-tags.php?taxonomy=woorestaurant_loc&post_type=woorestaurant_scbd' ) ); ?>">Locations</a></li> 
			<li><a href="<?php echo wp_kses_post( admin_url( '/admin.php?page=woo_restaurant_options' ) ); ?>">Settings</a></li>
		</ul>
	</div>

<div class="wpc-header-btn-wrap">
		<div class="wpc-admin-notify-wrap">
			<div class="wpc-admin-btn-wrap">
				<svg width="26" height="25" viewBox="0 0 26 25" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M19.2106 0H6.57897C2.7895 0 0.263184 2.52632 0.263184 6.31579V13.8947C0.263184 17.6842 2.7895 20.2105 6.57897 20.2105V22.9011C6.57897 23.9116 7.70318 24.5179 8.53687 23.9495L14.1579 20.2105H19.2106C23 20.2105 25.5263 17.6842 25.5263 13.8947V6.31579C25.5263 2.52632 23 0 19.2106 0ZM12.8948 15.3726C12.3642 15.3726 11.9474 14.9432 11.9474 14.4253C11.9474 13.9074 12.3642 13.4779 12.8948 13.4779C13.4253 13.4779 13.8421 13.9074 13.8421 14.4253C13.8421 14.9432 13.4253 15.3726 12.8948 15.3726ZM14.4863 10.1305C13.9937 10.4589 13.8421 10.6737 13.8421 11.0274V11.2926C13.8421 11.8105 13.4127 12.24 12.8948 12.24C12.3769 12.24 11.9474 11.8105 11.9474 11.2926V11.0274C11.9474 9.56211 13.0211 8.84211 13.4253 8.56421C13.8927 8.24842 14.0442 8.03368 14.0442 7.70526C14.0442 7.07368 13.5263 6.55579 12.8948 6.55579C12.2632 6.55579 11.7453 7.07368 11.7453 7.70526C11.7453 8.22316 11.3158 8.65263 10.7979 8.65263C10.28 8.65263 9.85055 8.22316 9.85055 7.70526C9.85055 6.02526 11.2148 4.66105 12.8948 4.66105C14.5748 4.66105 15.939 6.02526 15.939 7.70526C15.939 9.14526 14.8779 9.86526 14.4863 10.1305Z" fill="#2F4858"></path>
				</svg>
				<div class="dropdown">
					<div class="list-item">
						<a href="#support" target="_blank">
							<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M10.0482 4.37109H4.30125C4.06778 4.37109 3.84329 4.38008 3.62778 4.40704C1.21225 4.6137 0 6.04238 0 8.6751V12.2693C0 15.8634 1.43674 16.5733 4.30125 16.5733H4.66044C4.85799 16.5733 5.1184 16.708 5.23514 16.8608L6.3127 18.2985C6.78862 18.9364 7.56087 18.9364 8.03679 18.2985L9.11435 16.8608C9.24904 16.6811 9.46456 16.5733 9.68905 16.5733H10.0482C12.6793 16.5733 14.107 15.3692 14.3136 12.9432C14.3405 12.7275 14.3495 12.5029 14.3495 12.2693V8.6751C14.3495 5.80876 12.9127 4.37109 10.0482 4.37109ZM4.04084 11.5594C3.53798 11.5594 3.14288 11.1551 3.14288 10.6609C3.14288 10.1667 3.54696 9.76233 4.04084 9.76233C4.53473 9.76233 4.93881 10.1667 4.93881 10.6609C4.93881 11.1551 4.53473 11.5594 4.04084 11.5594ZM7.17474 11.5594C6.67188 11.5594 6.27678 11.1551 6.27678 10.6609C6.27678 10.1667 6.68086 9.76233 7.17474 9.76233C7.66862 9.76233 8.07271 10.1667 8.07271 10.6609C8.07271 11.1551 7.6776 11.5594 7.17474 11.5594ZM10.3176 11.5594C9.81476 11.5594 9.41966 11.1551 9.41966 10.6609C9.41966 10.1667 9.82374 9.76233 10.3176 9.76233C10.8115 9.76233 11.2156 10.1667 11.2156 10.6609C11.2156 11.1551 10.8115 11.5594 10.3176 11.5594Z" fill="#E3106E"></path>
								<path d="M17.9423 5.08086V8.67502C17.9423 10.4721 17.3855 11.6941 16.272 12.368C16.0026 12.5298 15.6884 12.3141 15.6884 11.9996L15.6973 8.67502C15.6973 5.08086 13.641 3.0232 10.0491 3.0232L4.58048 3.03219C4.26619 3.03219 4.05067 2.7177 4.21231 2.44814C4.88578 1.33395 6.10702 0.776855 7.89398 0.776855H13.641C16.5055 0.776855 17.9423 2.21452 17.9423 5.08086Z" fill="#E3106E"></path>
							</svg> 
						<span>Need Help?</span>
						</a>
						<a href="#documentation" target="_blank">
							<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M16.1896 7.57803H13.5902C11.4586 7.57803 9.72274 5.84103 9.72274 3.70803V1.10703C9.72274 0.612031 9.318 0.207031 8.82332 0.207031H5.00977C2.23956 0.207031 0 2.00703 0 5.22003V13.194C0 16.407 2.23956 18.207 5.00977 18.207H12.0792C14.8494 18.207 17.089 16.407 17.089 13.194V8.47803C17.089 7.98303 16.6843 7.57803 16.1896 7.57803ZM8.09478 14.382H4.4971C4.12834 14.382 3.82254 14.076 3.82254 13.707C3.82254 13.338 4.12834 13.032 4.4971 13.032H8.09478C8.46355 13.032 8.76935 13.338 8.76935 13.707C8.76935 14.076 8.46355 14.382 8.09478 14.382ZM9.89363 10.782H4.4971C4.12834 10.782 3.82254 10.476 3.82254 10.107C3.82254 9.73803 4.12834 9.43203 4.4971 9.43203H9.89363C10.2624 9.43203 10.5682 9.73803 10.5682 10.107C10.5682 10.476 10.2624 10.782 9.89363 10.782Z" fill="#E3106E"></path>
							</svg>
							<span>Documentation</span>

						</a>
						<a href="#ideas" target="_blank">
							<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M13.5902 7.57803H16.1896C16.6843 7.57803 17.089 7.98303 17.089 8.47803V13.194C17.089 16.407 14.8494 18.207 12.0792 18.207H5.00977C2.23956 18.207 0 16.407 0 13.194V5.22003C0 2.00703 2.23956 0.207031 5.00977 0.207031H8.82332C9.318 0.207031 9.72274 0.612031 9.72274 1.10703V3.70803C9.72274 5.84103 11.4586 7.57803 13.5902 7.57803ZM11.9613 0.396012C11.5926 0.0270125 10.954 0.279013 10.954 0.792013V3.93301C10.954 5.24701 12.0693 6.33601 13.4274 6.33601C14.2818 6.34501 15.4689 6.34501 16.4852 6.34501H16.4854C16.998 6.34501 17.2679 5.74201 16.9081 5.38201C16.4894 4.96018 15.9637 4.42927 15.3988 3.85888L15.3932 3.85325L15.3913 3.85133L15.3905 3.8505L15.3902 3.85016C14.2096 2.65803 12.86 1.29526 11.9613 0.396012ZM3.0145 12.0732C3.0145 11.7456 3.28007 11.48 3.60768 11.48H5.32132V9.76639C5.32132 9.43879 5.58689 9.17321 5.9145 9.17321C6.2421 9.17321 6.50768 9.43879 6.50768 9.76639V11.48H8.22131C8.54892 11.48 8.8145 11.7456 8.8145 12.0732C8.8145 12.4008 8.54892 12.6664 8.22131 12.6664H6.50768V14.38C6.50768 14.7076 6.2421 14.9732 5.9145 14.9732C5.58689 14.9732 5.32132 14.7076 5.32132 14.38V12.6664H3.60768C3.28007 12.6664 3.0145 12.4008 3.0145 12.0732Z" fill="#E3106E"></path>
							</svg>
							<span>Feature Request</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
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
	}
);

add_action(
	'edit_form_topX',
	function () {
		global $pagenow;
		$is_do = false;
		$text  = '';
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woores_modifiers' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
			$text  = 'Modifiers';
		}
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woorestaurant_scbd' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
			$text  = 'Menus';
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woores_modifiers' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
			$text  = 'Modifiers';
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woorestaurant_scbd' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
			$text  = 'Menus';
		}

		if ( $is_do ) {
			?>
<div class="wpc-admin-header">
	<div class="wpc-admin-header-logo">
	<h1 class="wpc-settings-title wpc-header-title"><?php echo wp_kses_post( $text ); ?></h1>
	</div>
		<div class="wpc-admin-menu-wrap">
		<ul>
			<li><a href="<?php echo wp_kses_post( admin_url( '/edit.php?post_type=woores_modifiers' ) ); ?>">Modifiers</a></li> 
			<li><a href="<?php echo wp_kses_post( admin_url( '/edit.php?post_type=woorestaurant_scbd' ) ); ?>">Menu</a></li> 
			<li><a href="<?php echo wp_kses_post( admin_url( '/edit-tags.php?taxonomy=woorestaurant_loc&post_type=woorestaurant_scbd' ) ); ?>">Locations</a></li> 
			<li><a href="<?php echo wp_kses_post( admin_url( '/admin.php?page=woo_restaurant_options' ) ); ?>">Settings</a></li>
		</ul>
	</div>

<div class="wpc-header-btn-wrap">
		<div class="wpc-admin-notify-wrap">
			<div class="wpc-admin-btn-wrap">
				<svg width="26" height="25" viewBox="0 0 26 25" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M19.2106 0H6.57897C2.7895 0 0.263184 2.52632 0.263184 6.31579V13.8947C0.263184 17.6842 2.7895 20.2105 6.57897 20.2105V22.9011C6.57897 23.9116 7.70318 24.5179 8.53687 23.9495L14.1579 20.2105H19.2106C23 20.2105 25.5263 17.6842 25.5263 13.8947V6.31579C25.5263 2.52632 23 0 19.2106 0ZM12.8948 15.3726C12.3642 15.3726 11.9474 14.9432 11.9474 14.4253C11.9474 13.9074 12.3642 13.4779 12.8948 13.4779C13.4253 13.4779 13.8421 13.9074 13.8421 14.4253C13.8421 14.9432 13.4253 15.3726 12.8948 15.3726ZM14.4863 10.1305C13.9937 10.4589 13.8421 10.6737 13.8421 11.0274V11.2926C13.8421 11.8105 13.4127 12.24 12.8948 12.24C12.3769 12.24 11.9474 11.8105 11.9474 11.2926V11.0274C11.9474 9.56211 13.0211 8.84211 13.4253 8.56421C13.8927 8.24842 14.0442 8.03368 14.0442 7.70526C14.0442 7.07368 13.5263 6.55579 12.8948 6.55579C12.2632 6.55579 11.7453 7.07368 11.7453 7.70526C11.7453 8.22316 11.3158 8.65263 10.7979 8.65263C10.28 8.65263 9.85055 8.22316 9.85055 7.70526C9.85055 6.02526 11.2148 4.66105 12.8948 4.66105C14.5748 4.66105 15.939 6.02526 15.939 7.70526C15.939 9.14526 14.8779 9.86526 14.4863 10.1305Z" fill="#2F4858"></path>
				</svg>
				<div class="dropdown">
					<div class="list-item">
						<a href="#support" target="_blank">
							<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M10.0482 4.37109H4.30125C4.06778 4.37109 3.84329 4.38008 3.62778 4.40704C1.21225 4.6137 0 6.04238 0 8.6751V12.2693C0 15.8634 1.43674 16.5733 4.30125 16.5733H4.66044C4.85799 16.5733 5.1184 16.708 5.23514 16.8608L6.3127 18.2985C6.78862 18.9364 7.56087 18.9364 8.03679 18.2985L9.11435 16.8608C9.24904 16.6811 9.46456 16.5733 9.68905 16.5733H10.0482C12.6793 16.5733 14.107 15.3692 14.3136 12.9432C14.3405 12.7275 14.3495 12.5029 14.3495 12.2693V8.6751C14.3495 5.80876 12.9127 4.37109 10.0482 4.37109ZM4.04084 11.5594C3.53798 11.5594 3.14288 11.1551 3.14288 10.6609C3.14288 10.1667 3.54696 9.76233 4.04084 9.76233C4.53473 9.76233 4.93881 10.1667 4.93881 10.6609C4.93881 11.1551 4.53473 11.5594 4.04084 11.5594ZM7.17474 11.5594C6.67188 11.5594 6.27678 11.1551 6.27678 10.6609C6.27678 10.1667 6.68086 9.76233 7.17474 9.76233C7.66862 9.76233 8.07271 10.1667 8.07271 10.6609C8.07271 11.1551 7.6776 11.5594 7.17474 11.5594ZM10.3176 11.5594C9.81476 11.5594 9.41966 11.1551 9.41966 10.6609C9.41966 10.1667 9.82374 9.76233 10.3176 9.76233C10.8115 9.76233 11.2156 10.1667 11.2156 10.6609C11.2156 11.1551 10.8115 11.5594 10.3176 11.5594Z" fill="#E3106E"></path>
								<path d="M17.9423 5.08086V8.67502C17.9423 10.4721 17.3855 11.6941 16.272 12.368C16.0026 12.5298 15.6884 12.3141 15.6884 11.9996L15.6973 8.67502C15.6973 5.08086 13.641 3.0232 10.0491 3.0232L4.58048 3.03219C4.26619 3.03219 4.05067 2.7177 4.21231 2.44814C4.88578 1.33395 6.10702 0.776855 7.89398 0.776855H13.641C16.5055 0.776855 17.9423 2.21452 17.9423 5.08086Z" fill="#E3106E"></path>
							</svg> 
						<span>Need Help?</span>
						</a>
						<a href="#documentation" target="_blank">
							<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M16.1896 7.57803H13.5902C11.4586 7.57803 9.72274 5.84103 9.72274 3.70803V1.10703C9.72274 0.612031 9.318 0.207031 8.82332 0.207031H5.00977C2.23956 0.207031 0 2.00703 0 5.22003V13.194C0 16.407 2.23956 18.207 5.00977 18.207H12.0792C14.8494 18.207 17.089 16.407 17.089 13.194V8.47803C17.089 7.98303 16.6843 7.57803 16.1896 7.57803ZM8.09478 14.382H4.4971C4.12834 14.382 3.82254 14.076 3.82254 13.707C3.82254 13.338 4.12834 13.032 4.4971 13.032H8.09478C8.46355 13.032 8.76935 13.338 8.76935 13.707C8.76935 14.076 8.46355 14.382 8.09478 14.382ZM9.89363 10.782H4.4971C4.12834 10.782 3.82254 10.476 3.82254 10.107C3.82254 9.73803 4.12834 9.43203 4.4971 9.43203H9.89363C10.2624 9.43203 10.5682 9.73803 10.5682 10.107C10.5682 10.476 10.2624 10.782 9.89363 10.782Z" fill="#E3106E"></path>
							</svg>
							<span>Documentation</span>

						</a>
						<a href="#ideas" target="_blank">
							<svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M13.5902 7.57803H16.1896C16.6843 7.57803 17.089 7.98303 17.089 8.47803V13.194C17.089 16.407 14.8494 18.207 12.0792 18.207H5.00977C2.23956 18.207 0 16.407 0 13.194V5.22003C0 2.00703 2.23956 0.207031 5.00977 0.207031H8.82332C9.318 0.207031 9.72274 0.612031 9.72274 1.10703V3.70803C9.72274 5.84103 11.4586 7.57803 13.5902 7.57803ZM11.9613 0.396012C11.5926 0.0270125 10.954 0.279013 10.954 0.792013V3.93301C10.954 5.24701 12.0693 6.33601 13.4274 6.33601C14.2818 6.34501 15.4689 6.34501 16.4852 6.34501H16.4854C16.998 6.34501 17.2679 5.74201 16.9081 5.38201C16.4894 4.96018 15.9637 4.42927 15.3988 3.85888L15.3932 3.85325L15.3913 3.85133L15.3905 3.8505L15.3902 3.85016C14.2096 2.65803 12.86 1.29526 11.9613 0.396012ZM3.0145 12.0732C3.0145 11.7456 3.28007 11.48 3.60768 11.48H5.32132V9.76639C5.32132 9.43879 5.58689 9.17321 5.9145 9.17321C6.2421 9.17321 6.50768 9.43879 6.50768 9.76639V11.48H8.22131C8.54892 11.48 8.8145 11.7456 8.8145 12.0732C8.8145 12.4008 8.54892 12.6664 8.22131 12.6664H6.50768V14.38C6.50768 14.7076 6.2421 14.9732 5.9145 14.9732C5.58689 14.9732 5.32132 14.7076 5.32132 14.38V12.6664H3.60768C3.28007 12.6664 3.0145 12.4008 3.0145 12.0732Z" fill="#E3106E"></path>
							</svg>
							<span>Feature Request</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
				<?php
				echo '<div class="wpc-product-addons-wrapper wpc-settings">
    <h1 class="wpc-settings-title"> <i class="wpcafe-icon4 dashicons dashicons-admin-generic"></i> ' . wp_kses_post( $text ) . '</h1></div>';
		}
	}
);

add_action(
	'admin_footer',
	function () {
		global $pagenow;
		$is_do = false;
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woores_modifiers' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
		}
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woorestaurant_scbd' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woores_modifiers' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woorestaurant_scbd' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
		}
		if ( $is_do ) {
			?>
<style>
h1.wp-heading-inline{
	display:none !important;
}
#postbox-container-1, .handle-actions,#postbox-container-1, a.page-title-action,h1.wp-heading-inline{
	display:none !important;}
	.postbox-header{
	/* pointer-events: none; */
	}
	#wrmb2-metabox-scwf_option {
	display: flex;
	flex-wrap: wrap;
	align-content: center;
	align-items: center;
	flex-direction: flex-start;
	}
	#wrmb2-metabox-scwf_option >.wrmb-row{
		width: 29%;
	}

#post-body, h1.wpc-settings-title{
	width:100% !important;
}
#title{
	border: 1px solid #d5d5d5;
	padding: 15px;
	height: 42px;}
#poststuff{
	background: white;padding: 40px 40px 0;
	margin-top: 30px;
	border-radius: 10px;
	-webkit-border-radius: 10px;
	-ms-border-radius: 10px;
	position: relative;background: #fff;
	margin-right: 8px;
}
.postbox-header{
	
		border-bottom: unset !important;
}
h2.hndle{
	font-size: 19px !important;
}
.postbox{ 
	border-radius:13px !important;
	border:1px solid #dcdcdc !important;
	box-shadow: unset!important; 
}
.wpc-settings-title{
	
	padding-top: 30px;
}
.wrmb-repeatable-grouping {
	background: rgb(238, 240, 248) !important;
	color: black !important;
	padding: 17px !important;
}

.wrmb-repeatable-group .wrmb-group-title {
	background-color: unset !important;}
	.wrmb-repeatable-grouping{
	background: rgb(238, 240, 248) !important;
	color: black !important;
	}
	.wrmb2-metabox button.dashicons-before.dashicons-no-alt.wrmb-remove-group-row:not([disabled]){
		 
	padding: 5px;
	background: #e3106e !important;
	border-radius: 1rem;
	color: white;
	}
	.wrmb2-metabox .wrmbhandle:before{
		 
	right: 12px !important;
	}
	.wrmb2-metabox button.dashicons-before.dashicons-no-alt.wrmb-remove-group-row{
		  
	top: 1.5em !important;
	}
 
.wrmb2-metabox button.dashicons-before.dashicons-no-alt.wrmb-remove-group-row {
	-webkit-appearance: none!important;
	background: 0 0!important;
	border: none!important;
	position: absolute;
	right: 10px !important;
	left: unset !important;}
	.wrmb-td{
	width:100% !important;
}
.wpc-label-item { 
	padding: 9px !important;
}
.wrmb-type-group .wrmb-row .wrmbhandle, .wrmb2-postbox .wrmb-row .wrmbhandle {
	top: 13px;
	left: 3px !important;
	position: absolute !important;
	color: #222;
}

.wrmb2-metabox .postbox.closed .wrmbhandle:before {
	content: '\f333' !important;
	font-size: 32px;
}
.woores-gr-option{
	display: flex;
	flex-wrap: wrap;
}
.woores-gr-option a{
	margin: 5px !Important;
	text-decoration: none;
	font-weight: 600;
	border: 1px solid #d7d7d7;
	padding: 4px;
	border-radius: 7px;
}
.wrmb-add-row .wrmb-add-group-row {
	
	-webkit-font-smoothing: subpixel-antialiased;
	box-sizing: border-box;
	font-family: inherit;
	-webkit-border-radius: 5px;
	-webkit-transition: all 0.4s ease;
	display: inline-block;
	text-decoration: none;
	line-height: 45px;
	cursor: pointer;
	text-transform: uppercase;
	font-size: 15px;
	background: rgba(227, 16, 110, 0.08);
	border: none;
	padding: 2px 15px;
	color: #e3106e;
	font-weight: 600;
}
</style>
			<?php
		}
	}
);


add_filter(
	'screen_options_show_screenX',
	function ( $bool, $screen ) {

		global $pagenow;
		$is_do = false;
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woores_modifiers' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
		}
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woorestaurant_scbd' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woores_modifiers' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woorestaurant_scbd' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
		}
		if ( $is_do ) {
			return false;
		}
		return $bool;
	},
	10,
	2
);

/**
 * Remove page excerpt.
 */
function wpdocs_remove_page_excerpt_field() {
	global $pagenow;
	$is_do = false;
	if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woores_modifiers' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
		// post.php.
		$is_do = true;
	}
	if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woorestaurant_scbd' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
		// post.php.
		$is_do = true;
	}

	if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woores_modifiers' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
		$is_do = true;
	}

	if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woorestaurant_scbd' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
		$is_do = true;
	}

	if ( $is_do ) {
		remove_meta_box( 'postcustom', 'woorestaurant_scbd', 'side' );
		remove_meta_box( 'submitdiv', 'woorestaurant_scbd', 'side' );
		remove_meta_box( 'slugdiv', 'woorestaurant_scbd', 'side' );
		remove_meta_box( 'product_catdiv', 'woorestaurant_scbd', 'side' );

		remove_meta_box( 'postcustom', 'woores_modifiers', 'side' );
		remove_meta_box( 'submitdiv', 'woores_modifiers', 'side' );
		remove_meta_box( 'slugdiv', 'woores_modifiers', 'side' );
		remove_meta_box( 'product_catdiv', 'woores_modifiers', 'side' );

		wp_enqueue_style( 'woo-restaurant-admin-css' );
	}
}
add_action( 'admin_init', 'wpdocs_remove_page_excerpt_field' );

// Metadata repeat field.
add_filter(
	'screen_settings',
	function ( $bool, $post ) {
		global $pagenow;
		$is_do = false;
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woores_modifiers' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
		}
		if ( 'post.php' == $pagenow && isset( $_GET['post'] ) && 'woorestaurant_scbd' == get_post_type( wp_kses_post( wp_unslash( $_GET['post'] ) ) ) ) {
			// post.php.
			$is_do = true;
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woores_modifiers' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
		}

		if ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && 'woorestaurant_scbd' == wp_kses_post( wp_unslash( $_GET['post_type'] ) ) ) {
			$is_do = true;
		}

		if ( $is_do ) {
			remove_all_actions( 'submitpost_box' );
		}
		return $bool;
	},
	10,
	2
);

/**
 * Get price type options.
 *
 * @param string $text_domain it is the text-domain.
 * @param string $value it is the value.
 */
function wooreswrmb2_get_price_type_options( $text_domain, $value = false ) {
	$_list = array(
		''      => esc_html__( 'Quantity Based', 'product-options-addon' ),
		'fixed' => esc_html__( 'Fixed Amount', 'product-options-addon' ),
	);

	$_options = '';
	foreach ( $_list as $abrev => $state ) {
		$_options .= '<option value="' . $abrev . '" ' . selected( $value, $abrev, false ) . '>' . $state . '</option>';
	}

	return $_options;
}

/**
 * Render price option field HTML.
 *
 * @param string $field it is the field.
 * @param string $value it is the value.
 * @param int    $object_id it is the object id.
 * @param string $object_type it is the object type.
 * @param object $field_type it is the field type.
 */
function wooreswrmb2_render_price_options_field_callback( $field, $value, $object_id, $object_type, $field_type ) {
	$text_domain = woores_text_domain();
	// make sure we specify each part of the value we need.
	$value = wp_parse_args(
		$value,
		array(
			'name'  => '',
			'type'  => '',
			'def'   => '',
			'plu'   => '',
			'dis'   => '',
			'price' => '',
		)
	);
	?>
	<div class="woores-options woores-name-option"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_name' ) ); ?>"><?php esc_html_e( 'Option name', 'product-options-addon' ); ?></label></p>
		<?php
		// phpcs:ignore
		echo ( $field_type->input(
			array(
				'class'     => 'hidden',
					// phpcs:ignore
					'name'  => ( $field_type->_name( '[plu]' ) ),
					// phpcs:ignore
					'value' => ( $value['plu'] ),
				'type'      => 'hidden',
				'desc'      => '',
			)
		) );
		?>
		
		<?php
			// phpcs:ignore
			echo ( $field_type->input(
				array(
					'class'     => '',
						// phpcs:ignore
						'name'  => ( $field_type->_name( '[name]' ) ),
						// phpcs:ignore
						'id'    => ( $field_type->_id( '_name' ) ),
						// phpcs:ignore
						'value' => ( $value['name'] ),
					'type'      => 'text',
					'desc'      => '',
				)
			) );
		?>
		
	</div>
	<div class="woores-options woores-def-option">
		<p><label for="<?php echo wp_kses_post( $field_type->_id( '_def' ) ); ?>"><?php esc_html_e( 'Default', 'product-options-addon' ); ?></label></p>
		<input type="checkbox" class="" name="<?php echo wp_kses_post( $field_type->_name( '[def]' ) ); ?>" id="<?php echo wp_kses_post( $field_type->_id( '_def' ) ); ?>" value="yes" data-hash="<?php echo wp_kses_post( $field->hash_id( '_def' ) ); ?>" <?php checked( $value['def'], 'yes' ); ?>>
	</div>
	<div class="woores-options woores-dis-option">
		<p><label for="<?php echo wp_kses_post( $field_type->_id( '_dis' ) ); ?>"><?php esc_html_e( 'Disable ?', 'product-options-addon' ); ?></label></p>
		<input type="checkbox" class="" name="<?php echo wp_kses_post( $field_type->_name( '[dis]' ) ); ?>" id="<?php echo wp_kses_post( $field_type->_id( '_dis' ) ); ?>" value="yes" data-hash="<?php echo wp_kses_post( $field->hash_id( '_dis' ) ); ?>" <?php checked( $value['dis'], 'yes' ); ?>>
	</div>
	<div class="woores-options woores-price-option"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_price' ) ); ?>'"><?php esc_html_e( 'Price', 'product-options-addon' ); ?></label></p>
		<?php
			// phpcs:ignore
			echo ( $field_type->input(
				array(
					'class'     => '',
						// phpcs:ignore
						'name'  => ( $field_type->_name( '[price]' ) ),
						// phpcs:ignore
						'id'    => ( $field_type->_id( '_price' ) ),
						// phpcs:ignore
						'value' => ( $value['price'] ),
					'type'      => 'text',
					'desc'      => '',
				)
			) );
		?>
	</div>
	<div class="woores-options woores-type-option"><p><label for="<?php echo wp_kses_post( $field_type->_id( '_type' ) ); ?>'"><?php esc_html_e( 'Type of price', 'product-options-addon' ); ?></label></p>
		<?php
			// phpcs:ignore
			echo ( $field_type->select(
				array(
					'class'       => '',
						// phpcs:ignore
						'name'    => ( $field_type->_name( '[type]' ) ),
						// phpcs:ignore
						'id'      => ( $field_type->_id( '_type' ) ),
						// phpcs:ignore
						'value'   => ( $value['type'] ),
						// phpcs:ignore
						'options' => ( wooreswrmb2_get_price_type_options( 'product-options-addon', $value['type'] ) ),
					'desc'        => '',
				)
			) );
		?>
	</div>
	<br class="clear">
	<?php
	// phpcs:ignore
	echo ( $field_type->_desc( true ) );
}

add_filter( 'wrmb2_render_price_options', 'wooreswrmb2_render_price_options_field_callback', 10, 5 );

/**
 * Sanitize price options callback.
 *
 * @param string $override_value it is the override value.
 * @param string $value it is the value.
 */
function wooreswrmb2_sanitize_price_options_callback( $override_value, $value ) {
	return $value;
}

// add_filter( 'wrmb2_sanitize_openclose', 'wooreswrmb2_sanitize_price_options_callback', 10, 2 );.
/**
 * Get select type option.
 *
 * @param array  $_list it is the list.
 * @param string $value it is the value.
 * @param int    $pos_gr it is the gr value.
 */
function wooreswrmb2_get_select_type_options( $_list, $value = false, $pos_gr = false ) {

	$_options = '';
	$i        = 0;
	foreach ( $_list as $abrev => $state ) {
		$disable = '';
		if ( isset( $pos_gr ) && is_numeric( $pos_gr ) ) {
			if ( 'varia' == $abrev || '' == $abrev ) {
				$pos_gr = $pos_gr++;
			}
			if ( $pos_gr == $i ) {
				$disable = '1';
			}
		}
		$_options .= '<option value="' . $abrev . '" ' . selected( $value, $abrev, false ) . ' ' . disabled( $disable, '1', false ) . '>' . $state . '</option>';
		++$i;
	}

	return $_options;
}

/**
 * Condition logic.
 *
 * @param object $field it is the field value.
 * @param string $value it is the value.
 * @param int    $object_id it is the object id.
 * @param string $object_type it is the object type.
 * @param object $field_type it is the field type.
 */
function wooreswrmb2_render_conlogic_options_field_callback( $field, $value, $object_id, $object_type, $field_type ) {
	$text_domain = woores_text_domain();
	// make sure we specify each part of the value we need.
	$value   = wp_parse_args(
		$value,
		array(
			'type_rel' => '',
			'type_con' => '',
			'type_op'  => '',
			'val'      => '',
		)
	);
	$id      = get_the_ID();
	$product = wc_get_product( $id );
	?>
	<div class="woores-options woores-type_rel-option">
		<?php
		$list_rule = array(
			'' => esc_html__( 'Or', 'product-options-addon' ),
		);
		// phpcs:ignore
		echo ( $field_type->select(
			array(
				'class'       => '',
					// phpcs:ignore
					'name'    => ( $field_type->_name( '[type_rel]' ) ),
					// phpcs:ignore
					'id'      => ( $field_type->_id( '_type_rel' ) ),
					// phpcs:ignore
					'value'   => ( $value['type_rel'] ),
					// phpcs:ignore
					'options' => ( wooreswrmb2_get_select_type_options( $list_rule, $value['type_rel'] ) ),
				'desc'        => '',
			)
		) );
		?>
	</div>
	<div class="woores-options woores-type_op-option">
		<?php
			$pos_gr      = $field->group->index;
			$list_op     = array();
			$list_val    = array();
			$op_val_list = '';
			$list_op[''] = esc_html__( '--', 'product-options-addon' );
		if ( is_object( $product ) && method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) ) {
				$list_op['varia'] = esc_html__( 'Variation', 'product-options-addon' );
		}
			$extra_op = get_post_meta( $id, 'woores_options', true );
		if ( is_array( $extra_op ) && count( $extra_op ) > 0 ) {
			foreach ( $extra_op as $op ) {
				$id_op = isset( $op['_id'] ) ? $op['_id'] : '';
				$name  = isset( $op['_name'] ) ? $op['_name'] : '';
				if ( '' != $id_op ) {
					$list_op[ $id_op ] = $name . ' - ' . $id_op;
				}
				$op_val = isset( $op['_options'] ) ? $op['_options'] : '';
				if ( is_array( $op_val ) && ! empty( $op_val ) ) {
					foreach ( $op_val as $valkey => $val ) {
						$val['id'] = $id_op;
						$active    = '';
						if ( $value['type_op'] == $val['id'] && $value['val'] == $valkey ) {
							$active = 'woores-current';
						}
						$op_val_list .= '<li class="' . wp_kses_post( $val['id'] . ' ' . $active ) . '" data-val="' . wp_kses_post( $valkey . '-' . $val['name'] ) . '">' . $val['name'] . '</li>';
					}
				}
			}
		}

		// phpcs:ignore
		echo ( $field_type->select(
			array(
				'class'       => '',
					// phpcs:ignore
					'name'    => ( $field_type->_name( '[type_op]' ) ),
					// phpcs:ignore
					'id'      => ( $field_type->_id( '_type_op' ) ),
					// phpcs:ignore
					'value'   => ( $value['type_op'] ),
					// phpcs:ignore
					'options' => ( wooreswrmb2_get_select_type_options( $list_op, $value['type_op'], $pos_gr ) ),
				'desc'        => '',
			)
		) );
		?>
	</div>
	<div class="woores-options woores-type_con-option">
		<?php
			$list_con = array(
				''       => esc_html__( 'is', 'product-options-addon' ),
				'is_not' => esc_html__( 'is not', 'product-options-addon' ),
			);
			// phpcs:ignore
			echo ( $field_type->select(
				array(
					'class'       => '',
						// phpcs:ignore
						'name'    => ( $field_type->_name( '[type_con]' ) ),
						// phpcs:ignore
						'id'      => ( $field_type->_id( '_type_con' ) ),
						// phpcs:ignore
						'value'   => ( $value['type_con'] ),
						// phpcs:ignore
						'options' => ( wooreswrmb2_get_select_type_options( $list_con, $value['type_con'] ) ),
					'desc'        => '',
				)
			) );
		?>
	</div>
	<div class="woores-options woores-val-option">
		<?php
			$ar_variations   = array();
			$variations      = array();
			$ar_variations[] = '';
		if ( is_object( $product ) && method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) ) {
			$variations = $product->get_children();
		}

		// phpcs:ignore
		echo ( $field_type->input(
			array(
				'class'     => 'woores-conval',
					// phpcs:ignore
					'name'     => ( $field_type->_name( '[val]' ) ),
					// phpcs:ignore
					'id'       => ( $field_type->_id( '_val' ) ),
					// phpcs:ignore
					'value'    => ( $value['val'] ),
				'type'      => 'text',
				'desc'      => '',
				'readonly'  => 'readonly',
			)
		) );

		if ( ( is_array( $variations ) && ! empty( $variations ) ) || '' != $op_val_list ) {
			echo '<ul class="woores-list-value">';
			echo wp_kses_post( $op_val_list );
			if ( is_array( $variations ) && ! empty( $variations ) ) {
				foreach ( $variations as $variation ) {
					$active = '';
					if ( $value['val'] == $variation ) {
						$active = 'woores-current';
					}
					echo '<li class="woores-variation ' . wp_kses_post( $active ) . '" data-val="' . wp_kses_post( $variation ) . '">' . wp_kses_post( $variation ) . '-' . wp_kses_post( get_the_title( $variation ) ) . '</li>';
				}
			}
			echo '</ul>';
		}
		?>
	</div>
	<br class="clear">
	<?php
		echo wp_kses_post( $field_type->_desc( true ) );
}

add_filter( 'wrmb2_render_conlogic_options', 'wooreswrmb2_render_conlogic_options_field_callback', 12, 5 );
add_filter( 'wrmb2_sanitize_conlogic_options', 'wooressanitize', 10, 5 );
add_filter( 'wrmb2_types_esc_conlogic_options', 'wooresescape', 10, 4 );

add_filter( 'wrmb2_sanitize_price_options', 'wooressanitize', 10, 5 );
add_filter( 'wrmb2_types_esc_price_options', 'wooresescape', 10, 4 );
/**
 * Wooressanitize function.
 *
 * @param string $check it is the check.
 * @param string $meta_value it is the value.
 * @param int    $object_id it is the object id.
 * @param array  $field_args it is the field argument.
 * @param object $sanitize_object it is the object to sanitize.
 */
function wooressanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {
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
 * Wooressanitize function.
 *
 * @param string $check it is the check.
 * @param string $meta_value it is the value.
 * @param array  $field_args it is the field argument.
 * @param object $field_object it is the object to field.
 */
function wooresescape( $check, $meta_value, $field_args, $field_object ) {
	// if not repeatable, bail out.
	if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
		return $check;
	}

	foreach ( $meta_value as $key => $val ) {
		$meta_value[ $key ] = array_filter( array_map( 'wp_kses_post', $val ) );
	}

	return array_filter( $meta_value );
}