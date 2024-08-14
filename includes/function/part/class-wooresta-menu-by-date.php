<?php
/**
 * This is the Menu by date file.
 *
 * @package woorestaurant
 */

/**
 * This is the WooResta_Menu_by_date class.
 */
class WooResta_Menu_By_Date {

	/**
	 * This is the Constructor Method.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'wrmb2_admin_init', array( $this, 'register_metabox' ) );
	}

	/**
	 * Registering POST Type Menu by date.
	 */
	public function register_post_type() {
		$labels  = array(
			'name'               => esc_html__( 'Menu by date', 'woorestaurant' ),
			'singular_name'      => esc_html__( 'Shortcodes', 'woorestaurant' ),
			'add_new'            => esc_html__( 'Add New Menu', 'woorestaurant' ),
			'add_new_item'       => esc_html__( 'Add New Menu', 'woorestaurant' ),
			'edit_item'          => esc_html__( 'Edit Menu', 'woorestaurant' ),
			'new_item'           => esc_html__( 'New Menu', 'woorestaurant' ),
			'all_items'          => esc_html__( 'Menu by date', 'woorestaurant' ),
			'view_item'          => esc_html__( 'View Menu', 'woorestaurant' ),
			'search_items'       => esc_html__( 'Search Menu', 'woorestaurant' ),
			'not_found'          => esc_html__( 'No Menu found', 'woorestaurant' ),
			'not_found_in_trash' => esc_html__( 'No Menu found in Trash', 'woorestaurant' ),
			'parent_item_colon'  => '',
			'menu_name'          => esc_html__( 'Menu by date', 'woorestaurant' ),
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
		register_post_type( 'woore_menubydate', $args );
	}

	/**
	 * Registering metabox.
	 */
	public function register_metabox() {
		/**
		 * Sample metabox to demonstrate each field type included
		 */
		$prefix = 'woore_';
		$mnbd   = new_wrmb2_box(
			array(
				'id'           => $prefix . 'menubydate',
				'title'        => esc_html__( 'Menu', 'woorestaurant' ),
				'object_types' => array( 'woore_menubydate' ), // Post type.
			)
		);
		$mnbd->add_field(
			array(
				'name'             => esc_html__( 'Date', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select date of this menu', 'woorestaurant' ),
				'id'               => $prefix . 'mndate',
				'type'             => 'text_date_timestamp',
				'default'          => '',
				'date_format'      => 'Y-m-d',
				'repeatable'       => false,
				'show_option_none' => true,

			)
		);
		$mnbd->add_field(
			array(
				'name'             => esc_html__( 'Add by special food', 'woorestaurant' ),
				'desc'             => esc_html__( 'Select food (or category below) and add it into food menu', 'woorestaurant' ),
				'id'               => 'menu_foods',
				'type'             => 'post_search_text',
				'show_option_none' => false,
				'default'          => '',
				'post_type'        => 'product',
				'select_type'      => 'checkbox',
				'select_behavior'  => 'add',
				'after_field'      => '',
			)
		);
		$mnbd->add_field(
			array(
				'name'              => esc_html__( 'Or Add food by category', 'woorestaurant' ),
				'desc'              => esc_html__( 'Select category for this date ( If you add food by special food this option will be ignored )', 'woorestaurant' ),
				'id'                => 'menu_food_cats',
				'taxonomy'          => 'product_cat', // Enter Taxonomy Slug.
				'type'              => 'taxonomy_multicheck_inline',
				'select_all_button' => false,
				'remove_default'    => 'true', // Removes the default metabox provided by WP core.
				'query_args'        => array(
					// 'orderby' => 'slug',
					// 'hide_empty' => true,
				),
				'classes'           => 'wrmb-type-taxonomy-multicheck-inline',
			)
		);
		// menu by time.
		$mntimesl = woo_restaurant_get_option( 'woo_restaurant_foodby_timesl' );
		if ( 'yes' == $mntimesl ) {
			$group_toption = $mnbd->add_field(
				array(
					'id'          => 'woore_menu_timesl',
					'type'        => 'group',
					'description' => esc_html__( 'If you want to create multi menus by multi times (like AM/PM) for the date, please create each group for each of time, If you have same menu for the date, please ignore this setting', 'woorestaurant' ),
					// 'repeatable'  => false, // use false if you want non-repeatable group.
					'options'     => array(
						'group_title'   => esc_html__( 'Menu by time {#}', 'woorestaurant' ), // since version 1.1.4, {#} gets replaced by row number.
						'add_button'    => esc_html__( 'Add new', 'woorestaurant' ),
						'remove_button' => esc_html__( 'Remove', 'woorestaurant' ),
						'sortable'      => true, // beta.
						'closed'        => true, // true to have the groups closed by default.
					),
					'after_group' => '',
				)
			);
			$mnbd->add_group_field(
				$group_toption,
				array(
					'name'        => esc_html__( 'Time from', 'woorestaurant' ),
					'desc'        => esc_html__( 'Select Start Time of this menu', 'woorestaurant' ),
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
					'classes'     => 'menu-time-sl wrsf-mnt-st',
					'before_row'  => 'woore_copy_mndate_html',
				)
			);
			$mnbd->add_group_field(
				$group_toption,
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
					'classes'     => 'menu-time-sl wrsf-mnt-ed',
				)
			);
			$mnbd->add_group_field(
				$group_toption,
				array(
					'name'        => esc_html__( 'Name of menu', 'woorestaurant' ),
					'desc'        => esc_html__( 'Enter name of menu like: Lunch or Breakfast or Dinner', 'woorestaurant' ),
					'id'          => 'mn_name',
					'type'        => 'text',
					'time_format' => 'H:i',
					'repeatable'  => false,
					'classes'     => 'menu-time-sl wrsf-mnt-name',
				)
			);
			$mnbd->add_group_field(
				$group_toption,
				array(
					'name'      => esc_html__( 'Max order', 'woorestaurant' ),
					'desc'      => esc_html__( 'Set Max order for this menu slot', 'woorestaurant' ),
					'id'        => 'max_order',
					'type'      => 'text',
					'classes'   => 'menu-time-sl wrsf-mnt-max',
					'after_row' => '',
				)
			);
			$mnbd->add_group_field(
				$group_toption,
				array(
					'name'             => esc_html__( 'Add by special food', 'woorestaurant' ),
					'desc'             => esc_html__( 'Select food (or category below) and add it into food menu', 'woorestaurant' ),
					'id'               => 'menu_foods',
					'type'             => 'post_search_text',
					'show_option_none' => false,
					'default'          => '',
					'post_type'        => 'product',
					'select_type'      => 'checkbox',
					'select_behavior'  => 'add',
					'after_field'      => '',
					'classes'          => 'wrsf-mnt-ids',
				)
			);
			$terms_pro    = get_terms( 'product_cat' );
			$term_options = array();
			if ( ! empty( $terms_pro ) ) {
				foreach ( $terms_pro as $term ) {
					$term_options[ $term->term_id ] = $term->name;
				}
			}
			$mnbd->add_group_field(
				$group_toption,
				array(
					'name'           => esc_html__( 'Or Add food by category', 'woorestaurant' ),
					'desc'           => esc_html__( 'Select category for this date ( If you add food by special food this option will be ignored )', 'woorestaurant' ),
					'id'             => 'menu_food_cats',
					'remove_default' => 'true',
					'type'           => 'multicheck',
					'options'        => $term_options,
					'classes'        => 'wrmb-type-multicheck-inline wrmb-inline wrsf-mnt-cat',
				)
			);
			$mt_options  = array(
				'delivery' => esc_html__( 'Delivery', 'woorestaurant' ),
				'takeaway' => esc_html__( 'Takeaway', 'woorestaurant' ),
				'dinein'   => esc_html__( 'Dine-In', 'woorestaurant' ),
			);
			$arr_methods = woore_adm_get_method_enable();
			if ( is_array( $arr_methods ) && ! empty( $arr_methods ) ) {
				foreach ( $mt_options as $key => $item ) {
					if ( ! in_array( $key, $arr_methods ) ) {
						unset( $mt_options[ $key ] );
					}
				}
			} else {
				$mt_options = array();
			}
			if ( ! empty( $mt_options ) && count( $mt_options ) > 1 ) {
				$mnbd->add_group_field(
					$group_toption,
					array(
						'name'              => esc_html__( 'Disable order method', 'woorestaurant' ),
						'desc'              => esc_html__( 'Select not available method for this menu', 'woorestaurant' ),
						'id'                => 'dis_method',
						'remove_default'    => 'true',
						'type'              => 'multicheck',
						'select_all_button' => false,
						'options'           => $mt_options,
						'classes'           => 'wrmb-type-multicheck-inline wrmb-inline wrsf-mnt-omt',
					)
				);
			}
		}
		// Repeat.
		$repeat_option = new_wrmb2_box(
			array(
				'id'           => $prefix . 'mnrepeat',
				'title'        => esc_html__( 'Repeat on', 'woorestaurant' ),
				'object_types' => array( 'woore_menubydate' ), // Post type.
			)
		);
		$repeat_option->add_field(
			array(
				'name'    => esc_html__( 'Monday', 'woorestaurant' ),
				'id'      => $prefix . 'mnrepeat_Mon',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$repeat_option->add_field(
			array(
				'name'    => esc_html__( 'Tuesday', 'woorestaurant' ),
				'id'      => $prefix . 'mnrepeat_Tue',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$repeat_option->add_field(
			array(
				'name'    => esc_html__( 'Wednesday', 'woorestaurant' ),
				'id'      => $prefix . 'mnrepeat_Wed',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$repeat_option->add_field(
			array(
				'name'    => esc_html__( 'Thursday', 'woorestaurant' ),
				'id'      => $prefix . 'mnrepeat_Thu',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$repeat_option->add_field(
			array(
				'name'    => esc_html__( 'Friday', 'woorestaurant' ),
				'id'      => $prefix . 'mnrepeat_Fri',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$repeat_option->add_field(
			array(
				'name'    => esc_html__( 'Saturday', 'woorestaurant' ),
				'id'      => $prefix . 'mnrepeat_Sat',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
		$repeat_option->add_field(
			array(
				'name'    => esc_html__( 'Sunday', 'woorestaurant' ),
				'id'      => $prefix . 'mnrepeat_Sun',
				'type'    => 'checkbox',
				'classes' => 'column-7',
			)
		);
	}
}
$woo_resta_menu_by_date = new WooResta_Menu_By_Date();

// phpcs:ignore
function woore_copy_mndate_html($field_args, $field) {
	echo '<p class="woores-gr-option">
		<a href="javascript:;" class="woores-copypre">' . esc_html__( 'Copy from previous option', 'woorestaurant' ) . '</a>
		<a href="javascript:;" class="woores-copy" data-textdis="' . esc_html__( 'Please save option before copy', 'woorestaurant' ) . '">' . esc_html__( 'Copy this option', 'woorestaurant' ) . '</a>
		<a href="javascript:;" class="woores-paste">
		<span class="woores-paste-tt">' . esc_html__( 'Paste option', 'woorestaurant' ) . '</span>
		<span class="woores-paste-mes" style="display:none">' . esc_html__( 'Completed!', 'woorestaurant' ) . '</span>
		<textarea style="display:none" class="woores-ctpaste"  placeholder="' . esc_html__( 'Paste your option here', 'woorestaurant' ) . '"></textarea></a>';
	echo '
	</p>';
}
/**
 * This is select date by html function.
 *
 * @param bool $sdate this is the starting date.
 * @param bool $enb_mnd this is the end date.
 */
function woore_select_date_html( $sdate = false, $enb_mnd = false ) {
	$date_selected = WC()->session->get( '_menudate' );
	if ( '' != $date_selected || isset( $sdate ) && true == $sdate ) {
		if ( '' != $date_selected ) {
			global $wp;
			$_fmdate  = apply_filters( 'woore_food_by_date_fm', get_option( 'date_format' ) );
			$date     = date_i18n( $_fmdate, strtotime( $date_selected ) );
			$cr_url   = home_url( $wp->request );
			$cr_url   = apply_filters( 'woore_current_link', $cr_url );
			$time_slt = WC()->session->get( '_menutime' );
			$time_slt = '' != $time_slt ? ' - ' . $time_slt : '';
			echo '<div class="wrsf-menuof-date">
				<a class="mndate-sl" href="' . esc_attr( add_query_arg( array( 'menu-date' => '' ), esc_attr( $cr_url ) ) ) . '" data-text="' . esc_html__( 'The products added to cart for this date will reset. Are you sure you want to change this date ?', 'woorestaurant' ) . '">
					<span class="">' . esc_html__( 'Date: ', 'woorestaurant' ) . esc_attr( $date ) . esc_attr( $time_slt ) . '</span>
					<span class="mndate-close">&times;</span>
				</a>	
			</div>';
		}
		return;
	}
	global $woore_menudate;
	if ( ! isset( $woore_menudate ) || 'on' != $woore_menudate || ( isset( $enb_mnd ) && 'yes' == $enb_mnd ) ) {
		$woore_menudate = 'on';
	} elseif ( 'on' == $woore_menudate ) {
		return;
	}
	?>
	<div class="wrsf-menu-bydate wrs-popup-location">
		<div class="wrs-popup-content">
			<div class="wrs-popup-info">
				<h1><?php esc_html_e( 'Please choose the date to view menu', 'woorestaurant' ); ?></h1>
				<div class="woorst-select-loc">
					<div>
						<?php echo esc_attr( woore_date_selecter() ); ?>
					</div>
				</div>
			</div>
		</div>

	</div>
	<?php
}

/**
 * This is the date selector method.
 *
 * @return html
 */
function woore_date_selecter() {

	$date_before = woo_restaurant_get_option( 'woo_restaurant_ck_beforedate', 'woo_restaurant_advanced_options' );
	$enb_date    = woo_restaurant_get_option( 'woo_restaurant_ck_enadate', 'woo_restaurant_advanced_options' );
	$dis_day     = woo_restaurant_get_option( 'woo_restaurant_ck_disday', 'woo_restaurant_advanced_options' );

	$cure_time  = strtotime( 'now' );
	$gmt_offset = get_option( 'gmt_offset' );

	if ( '' != $date_before && is_numeric( $date_before ) ) {
		$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( "+$date_before day" ) );
	} elseif ( '' != $date_before && is_numeric( str_replace( 'm', '', $date_before ) ) ) {
		$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( '+' . str_replace( 'm', '', $date_before ) . ' minutes' ) );
	}
	if ( '' != $gmt_offset ) {
		$cure_time = $cure_time + ( $gmt_offset * 3600 );
	}
	$date      = strtotime( gmdate( 'Y-m-d', $cure_time ) );
	$maxl      = apply_filters( 'woore_number_date_select', 10 );
	$deli_date = array();
	$html_ot   = '';
	global $wp;
	$cr_url  = home_url( $wp->request );
	$cr_url  = apply_filters( 'woore_current_link', $cr_url );
	$_fmdate = apply_filters( 'woore_food_by_date_fm', get_option( 'date_format' ) );
	if ( is_array( $enb_date ) && count( $enb_date ) > 0 ) {
		$html_ot .= '<option selected="true" value="" disabled>' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';
		foreach ( $enb_date as $enb_date_it ) {
			if ( $enb_date_it > $date ) {
				$date_fm                   = date_i18n( $_fmdate, $enb_date_it );
				$deli_date[ $enb_date_it ] = $date_fm;
				$url                       = add_query_arg( array( 'menu-date' => gmdate( 'Y-m-d', $enb_date_it ) ), $cr_url );
				$html_ot                  .= '<option value="' . esc_attr( $url ) . '" data-date="' . esc_attr( gmdate( 'Y-m-d', $date_un ) ) . '">' . $date_fm . '</option>';
			}
		}
	} else {
		$html_ot .= '<option selected="true" value="" disabled>' . esc_html__( '-- Select --', 'woorestaurant' ) . '</option>';
		for ( $i = 0; $i <= $maxl; $i++ ) {
			$date_un    = strtotime( "+$i day", $date );
			$day_ofdate = gmdate( 'N', $date_un );
			if ( ( ! empty( $dis_day ) && count( $dis_day ) == 7 ) ) {
				break;
			}
			if ( ( ! empty( $dis_date ) && in_array( $date_un, $dis_date ) ) || ( ! empty( $dis_day ) && in_array( $day_ofdate, $dis_day ) ) ) {
				$maxl = $maxl++;
			} else {
				$date_fm               = date_i18n( $_fmdate, $date_un );
				$deli_date[ $date_un ] = $date_fm;
				$url                   = add_query_arg( array( 'menu-date' => gmdate( 'Y-m-d', $date_un ) ), $cr_url );
				$html_ot              .= '<option value="' . esc_attr( $url ) . '" data-date="' . esc_attr( gmdate( 'Y-m-d', $date_un ) ) . '">' . $date_fm . '</option>';
			}
		}
	}
	$mntimesl = woo_restaurant_get_option( 'woo_restaurant_foodby_timesl' );
	$css_cls  = '';
	if ( 'yes' == $mntimesl ) {
		$css_cls = 'wrsf-mn-timesl wrsf-disable-red';
	}
	$html = '<select class="wrsf-menu-date wrs-loc-select ' . esc_attr( $css_cls ) . '" name="menu-date" data-current_url="' . esc_attr( $cr_url ) . '">' . $html_ot . '</select>';
	return $html;
}

/**
 * Available time function.
 *
 * @param string $date_mn it is having the date.
 * @param string $time_mn it is having the time.
 */
function woore_if_mn_timesl_available( $date_mn, $time_mn ) {
	$timesl      = WC()->session->get( '_user_menusl' );
	$date_before = woo_restaurant_get_option( 'woo_restaurant_ck_beforedate', 'woo_restaurant_advanced_options' );
	$cure_time   = strtotime( 'now' );
	$gmt_offset  = get_option( 'gmt_offset' );

	if ( '' != $date_before && is_numeric( $date_before ) ) {
		$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( "+$date_before day" ) );
	} elseif ( '' != $date_before && is_numeric( str_replace( 'm', '', $date_before ) ) ) {
		$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( '+' . str_replace( 'm', '', $date_before ) . ' minutes' ) );
	}
	if ( '' != $gmt_offset ) {
		$cure_time = $cure_time + ( $gmt_offset * 3600 );
	}
	if ( ! is_array( $timesl ) || empty( $timesl ) ) {
		WC()->session->set( '_menudate', '' );
		WC()->session->set( '_menutime', '' );
	} else {
		$f_available = false;
		if ( isset( $timesl['time_from'] ) ) {
			$timesl[0] = $timesl;
		}
		$method      = WC()->session->get( '_user_order_method' );
		$method      = '' != $method ? $method : 'delivery';
		$_dis_method = isset( $timesl[0]['dis_method'] ) ? $timesl[0]['dis_method'] : '';
		if ( isset( $_GET['change-address'] ) && '1' == $_GET['change-address'] || isset( $_GET['change-method'] ) || is_array( $_dis_method ) && ! in_array( $method, $_dis_method ) || '' == $_dis_method ) {
			WC()->session->set( '_menutime_empty', '' );
		} elseif ( ! isset( $_GET['change-method'] ) ) {
			WC()->session->set( '_error_mes', esc_html__( 'Sorry the menu time you have selected has expired or not available, please try again with different date time', 'woorestaurant' ) );
			WC()->session->set( '_menudate', '' );
			WC()->session->set( '_menutime', '' );
		}
		foreach ( $timesl as $key => $it_timesl ) {
			if ( ! isset( $it_timesl['post_id'] ) || '' == $it_timesl['post_id'] ) {
				break;
			}
			$avai_sl      = get_post_meta( $it_timesl['post_id'], 'woore_menu_timesl', true );
			$find_avai_sl = $it_timesl;
			unset( $find_avai_sl['post_id'] );
			$f_avai_sl = 0;
			foreach ( $avai_sl as $k => $it_avai_sl ) {
				if ( $find_avai_sl == $it_avai_sl ) {
					$f_avai_sl = 1;
					break;
				}
			}
			if ( 0 == $f_avai_sl ) {
				break;
			}
			$timefrom   = isset( $it_timesl['time_from'] ) && '' != $it_timesl['time_from'] ? $it_timesl['time_from'] : '';
			$time_to    = isset( $it_timesl['time_to'] ) && '' != $it_timesl['time_to'] ? $it_timesl['time_to'] : '';
			$name       = isset( $it_timesl['mn_name'] ) && '' != $it_timesl['mn_name'] ? $it_timesl['mn_name'] : ( '' != $timefrom && '' != $time_to ? $timefrom . ' - ' . $time_to : ( '' != $timefrom ? $timefrom : ( '' != $time_to ? $time_to : '' ) ) );
			$_time_base = apply_filters( 'woore_timebase_to_check_menudate', ( isset( $it_timesl['time_from'] ) ? $it_timesl['time_from'] : '' ), $it_timesl );

			if ( '' == $_time_base ) {
				if ( $name == $time_mn ) {
					WC()->session->set( '_menutime', $time_mn );
					WC()->session->set( '_user_menusl', array( $it_timesl ) );
					$f_available = true;
					break;
				}
			} else {
				$_timeck = $_time_base;
				$_timeck = explode( ':', $_timeck );
				$_timeck = $_timeck[1] * 60 + $_timeck[0] * 3600;
				if ( ( strtotime( $date_mn ) + $_timeck ) >= $cure_time && $name == $time_mn ) {
					WC()->session->set( '_menutime', $time_mn );
					WC()->session->set( '_user_menusl', array( $it_timesl ) );
					$f_available = true;
					break;
				}
			}
		}
		if ( false == $f_available ) {
			WC()->session->set( '_error_mes', esc_html__( 'Sorry the menu time you have selected has expired or not available, please try again with different date time', 'woorestaurant' ) );
			WC()->session->set( '_menudate', '' );
			WC()->session->set( '_menutime', '' );
		}
	}
}
// add user menu date.
add_action( 'init', 'woore_user_select_menudate', 20 );
/**
 * User select menu date function.
 */
function woore_user_select_menudate() {
	if ( is_admin() || ! isset( WC()->session ) ) {
		return;
	}
	WC()->session->set( '_error_mes', '' );
	$date_slt = WC()->session->get( '_menudate' );
	$time_slt = WC()->session->get( '_menutime' );
	$mntimesl = woo_restaurant_get_option( 'woo_restaurant_foodby_timesl' );
	if ( 'yes' != $mntimesl ) {
		WC()->session->set( '_menutime', '' );
		WC()->session->set( '_user_menusl', '' );
	}

	/*
	Else if($date_slt!='' && $time_slt ==''){
		$_ids_menudate =  WC()->session->get( '_ids_menudate');
		if(is_array($_ids_menudate) && !empty($_ids_menudate)){
			foreach ($_ids_menudate as $_id) {
				$sl = get_post_meta( $_id, 'woore_menu_timesl', true );
				if(is_array($sl) && !empty($sl)){
					$date_slt = '';
					WC()->session->set( '_menudate' ,'' );
					break;
					//return;
				}
			}
		}
	}
	*/
	$cure_time  = strtotime( 'now' );
	$gmt_offset = get_option( 'gmt_offset' );
	if ( '' != $gmt_offset ) {
		$cure_time = $cure_time + ( $gmt_offset * 3600 );
	}
	$date = strtotime( gmdate( 'Y-m-d', $cure_time ) );

	if ( '' != $date_slt ) {
		if ( $date > strtotime( $date_slt ) ) {
			WC()->session->set( '_menudate', '' );
		}
		if ( '' != $time_slt ) {
			$timesl = WC()->session->get( '_user_menusl' );
			if ( ! is_array( $timesl ) || empty( $timesl ) ) {
				WC()->session->set( '_menudate', '' );
				WC()->session->set( '_menutime', '' );
			} else {
				woore_if_mn_timesl_available( $date_slt, $time_slt );
			}
		}
	} else {
		global $woocommerce;
		$woocommerce->cart->empty_cart();
	}
	if ( isset( $_GET['menu-date'] ) && $date < ( strtotime( wp_kses_post( wp_unslash( $_GET['menu-date'] ) ) ) + 86399 ) ) {
		if ( '' != $date_slt || ( '' != $date_slt && $date_slt != $_GET['menu-date'] ) || ( '' == $time_slt && isset( $_GET['menu-time'] ) && '' != $_GET['menu-time'] ) || ( '' != $time_slt && isset( $_GET['menu-time'] ) && $_GET['menu-time'] != $time_slt ) ) {
			global $woocommerce;
			$woocommerce->cart->empty_cart();
		}
		WC()->session->set( '_menudate', wp_kses_post( wp_unslash( $_GET['menu-date'] ) ) );
		if ( isset( $_GET['menu-time'] ) && '' != $_GET['menu-time'] ) {
			woore_if_mn_timesl_available( wp_kses_post( wp_unslash( $_GET['menu-date'] ) ), wp_kses_post( wp_unslash( $_GET['menu-time'] ) ) );

			/*
			$date_before = woo_restaurant_get_option('woo_restaurant_ck_beforedate','woo_restaurant_advanced_options');
			$cure_time =  strtotime("now");
			$gmt_offset = get_option('gmt_offset');

			if($date_before!='' && is_numeric($date_before)){
				$cure_time =  apply_filters( 'wrst_disable_book_day', strtotime("+$date_before day") );
			}else if($date_before!='' && is_numeric(str_replace("m","",$date_before))){
				$cure_time = apply_filters( 'wrst_disable_book_day', strtotime("+".str_replace("m","",$date_before)." minutes") );
			}
			if($gmt_offset!=''){
				$cure_time = $cure_time + ($gmt_offset*3600);
			}
			if(!is_array($timesl) || empty($timesl)){
				WC()->session->set( '_menudate' ,'' );
				WC()->session->set( '_menutime' ,'' );
			}else{
				$f_available = false;
				foreach ($timesl as $key => $it_timesl) {
					if(!isset($it_timesl['post_id']) || $it_timesl['post_id']==''){
						break;
					}
					$avai_sl = get_post_meta( $it_timesl['post_id'], 'woore_menu_timesl', true );
					unset($it_timesl['post_id']);
					$f_avai_sl = 0;
					foreach ($avai_sl as $k=> $it_avai_sl) {
						if($it_timesl ==$it_avai_sl ){
							$f_avai_sl = 1;
							break;
						}
					}
					$timefrom = isset($it_timesl['time_from']) && $it_timesl['time_from']!='' ? $it_timesl['time_from'] :'';
					$time_to = isset($it_timesl['time_to']) && $it_timesl['time_to']!='' ? $it_timesl['time_to'] :'';
					$name = isset($it_timesl['mn_name']) && $it_timesl['mn_name']!=''? $it_timesl['mn_name'] : ($timefrom!='' && $time_to!='' ? $timefrom.' - '.$time_to : ($timefrom!='' ? $timefrom : ( $time_to!='' ? $time_to : '')  ) );
					$_time_base = apply_filters('woore_timebase_to_check_menudate',(isset($it_timesl['time_from'])? $it_timesl['time_from']:''),$it_timesl);
					//echo $name. ''.$_GET["menu-time"];
					if($_time_base==''){
						WC()->session->set( '_menutime' , $_GET["menu-time"] );
						WC()->session->set( '_user_menusl',$it_timesl);
						$f_available = true;
						break;
					}else{
						$_timeck = $_time_base;
						$_timeck = explode(':', $_timeck);
						$_timeck = $_timeck[1] * 60 + $_timeck[0] * 3600;
						if((strtotime($_GET["menu-date"]) + $_timeck) >= $cure_time && $name == $_GET["menu-time"]){
							WC()->session->set( '_menutime' , $_GET["menu-time"] );
							WC()->session->set( '_user_menusl',$it_timesl);
							$f_available = true;
							break;
						}
					}
				}
				if($f_available == false){
					WC()->session->set( '_menudate' ,'' );
					WC()->session->set( '_menutime' ,'' );
				}
			}
			*/
		}
	} elseif ( isset( $_GET['menu-date'] ) && '' == $_GET['menu-date'] ) {
		WC()->session->set( '_menudate', '' );
		WC()->session->set( '_menutime', '' );
	} elseif ( isset( $_GET['change-method'] ) ) {
		$timesl      = WC()->session->get( '_user_menusl' );
		$_dis_method = isset( $timesl[0]['dis_method'] ) ? $timesl[0]['dis_method'] : '';
		if ( is_array( $_dis_method ) && in_array( $_GET['change-method'], $_dis_method ) ) {
			WC()->session->set( '_menudate', '' );
			WC()->session->set( '_menutime', '' );
		}
	}
}

/**
 * Get menu by date seleted.
 */
function woore_menuby_date_selected() {
	$date_slt = WC()->session->get( '_menudate' );
	if ( '' != $date_slt ) {
		return strtotime( $date_slt );
	}
}

/**
 * Get menu by time seleted.
 */
function woore_menuby_time_selected() {
	$time_slt = WC()->session->get( '_menutime' );
	return $time_slt;
}

/**
 * Get menu details by time selected.
 */
function woore_menuby_time_selected_details() {
	$_user_menusl = WC()->session->get( '_user_menusl' );
	return ( is_array( $_user_menusl ) && ! empty( $_user_menusl ) ? $_user_menusl[0] : '' );
}

/**
 * Get cats includes.
 */
function woore_menuby_cats_included() {
	$_cats_menudate = WC()->session->get( '_cats_menudate' );
	return $_cats_menudate;
}

if ( ! function_exists( 'woore_query_by_menu_date' ) ) {
	/**
	 * Menu date query.
	 *
	 * @param array $args these are query arguments.
	 * @param array $cats_qr this iss category query.
	 */
	function woore_query_by_menu_date( $args, $cats_qr = false ) {
		$date_slt = WC()->session->get( '_menudate' );
		if ( '' != $date_slt ) {
			$food_ids = array();
			$cat_ids  = array();
			$mntimesl = woo_restaurant_get_option( 'woo_restaurant_foodby_timesl' );
			$time_slt = WC()->session->get( '_menutime' );
			if ( 'yes' != $mntimesl || '' == $time_slt ) {
				$date_slt = strtotime( $date_slt );
				$wday     = gmdate( 'D', $date_slt );
				$args_mn  = array(
					'post_type'        => 'woore_menubydate',
					'post_status'      => array( 'publish' ),
					'numberposts'      => -1,
					'suppress_filters' => true,
				);

				$args_mn['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'     => 'woore_mndate',
						'value'   => $date_slt,
						'compare' => '=',
					),
					array(
						'key'     => 'woore_mnrepeat_' . $wday,
						'value'   => 'on',
						'compare' => '=',
					),
				);

				/*
				$menu_byloc = woo_restaurant_get_option('woo_restaurant_enable_loc','woo_restaurant_options');
				$user_log = WC()->session->get( '_user_deli_log' );
				if ($menu_byloc =='yes' && $user_log!='') {
					$args_mn['tax_query'][] = array(
						'relation' => 'OR',
						array(
							'taxonomy'     => 'woorestaurant_loc',
							'operator' => 'NOT EXISTS',
						),
						array(
							'key'     => 'taxonomy',
							'field'    => 'slug',
							'terms'    => array($user_log),
						)
					);
				}
				*/
				$menu_f = get_posts( $args_mn );
				if ( ! empty( $menu_f ) && count( $menu_f ) > 0 ) {
					$ids_dt = array();
					foreach ( $menu_f as $f_item ) {
						$ids_dt[] = $f_item->ID;
						$ids      = get_post_meta( $f_item->ID, 'menu_foods', true );
						if ( '' != $ids ) {
							$ids      = explode( ',', $ids );
							$food_ids = array_merge( $food_ids, $ids );
						}
						$cats = get_the_terms( $f_item->ID, 'product_cat' );
						if ( ! empty( $cats ) ) {
							$terms_ids = wp_list_pluck( $cats, 'term_id' );
							$cat_ids   = array_merge( $cat_ids, $terms_ids );
						}
					}
					WC()->session->set( '_ids_menudate', $ids_dt );
				}
			} else {
				$menusl   = WC()->session->get( '_user_menusl' );
				$food_ids = isset( $menusl[0]['menu_foods'] ) ? explode( ',', $menusl[0]['menu_foods'] ) : array();
				$cat_ids  = isset( $menusl[0]['menu_food_cats'] ) ? $menusl[0]['menu_food_cats'] : array();
			}
			WC()->session->set( '_cats_menudate', $cat_ids );
			if ( is_array( $food_ids ) && ! empty( $food_ids ) || ! empty( $cat_ids ) ) {
				if ( ! empty( $food_ids ) && ! empty( array_filter( $food_ids ) ) ) {
					$args['post__in'] = $food_ids;
				} elseif ( ! empty( $cat_ids ) ) {
					if ( ! isset( $args['tax_query'] ) || ! is_array( $args['tax_query'] ) ) {
						$args['tax_query'] = array();
					} else {
						if ( is_array( $cats_qr ) ) {
							$cats = isset( $cats_qr['cat'] ) ? $cats_qr['cat'] : '';
						} else {
							$cats = $cats_qr;
						}
						if ( '' == $cats ) {
							$args['tax_query']['relation'] = 'AND';
							$args['tax_query'][]           =
								array(
									'taxonomy' => 'product_cat',
									'field'    => 'term_id',
									'terms'    => $cat_ids,
								);
						} else {
							$cats = explode( ',', $cats );
							if ( is_numeric( $cats[0] ) ) {
								if ( count( $cats ) == 1 ) {
									if ( ! in_array( $cats[0], $cat_ids ) ) {
										$args['post__in'] = array( '0' );
									}
								} else {
									$args['tax_query'] = array();
									// $args['tax_query']['relation'] = 'AND';.
									$args['tax_query'][] =
										array(
											'taxonomy' => 'product_cat',
											'field'    => 'term_id',
											'terms'    => $cat_ids,
										);
								}
							} elseif ( ! is_numeric( $cats[0] ) ) {
								if ( count( $cats ) == 1 ) {
									$mn_tern = get_term_by( 'slug', $cats[0], 'product_cat' );
									if ( ! in_array( $mn_tern->term_id, $cat_ids ) ) {
										$args['post__in'] = array( '0' );
									}
								} else {
									$args['tax_query'] = array();
									// $args['tax_query']['relation'] = 'AND';.
									$args['tax_query'][] =
										array(
											'taxonomy' => 'product_cat',
											'field'    => 'term_id',
											'terms'    => $cat_ids,
										);
								}
							}
						}
					}
				}
			} else {
				$args['post__in'] = array( '0' );
			}
		}
		return $args;
	}
}
add_filter( 'woo_restaurant_query', 'woore_query_by_menu_date', 15, 2 );
add_filter( 'woore_ajax_query_args', 'woore_query_by_menu_date', 15, 2 );
add_filter( 'woore_ajax_filter_query_args', 'woore_query_by_menu_date', 15, 2 );

add_action( 'pre_get_posts', 'woore_query_pre_change', 101 );
if ( ! function_exists( 'woore_query_pre_change' ) ) {
	/**
	 * Before query change.
	 *
	 * @param object $query this is the query object.
	 */
	function woore_query_pre_change( $query ) {
		if ( ! is_admin() && ( in_array( $query->get( 'post_type' ), array( 'product' ) ) ) && ! $query->is_main_query() ) {
			if ( ! isset( WC()->session ) ) {
				return;
			}
			$date_slt = WC()->session->get( '_menudate' );
			if ( '' != $date_slt ) {
				$date_slt = strtotime( $date_slt );
				$wday     = gmdate( 'D', $date_slt );
				$args_mn  = array(
					'post_type'        => 'woore_menubydate',
					'post_status'      => array( 'publish' ),
					'numberposts'      => -1,
					'suppress_filters' => true,
				);

				$args_mn['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'     => 'woore_mndate',
						'value'   => $date_slt,
						'compare' => '=',
					),
					array(
						'key'     => 'woore_mnrepeat_' . $wday,
						'value'   => 'on',
						'compare' => '=',
					),
				);
				$menu_f                  = get_posts( $args_mn );
				$food_ids                = array();
				if ( ! empty( $menu_f ) && count( $menu_f ) > 0 ) {
					foreach ( $menu_f as $f_item ) {
						$ids = get_post_meta( $f_item->ID, 'menu_foods', true );
						if ( '' != $ids ) {
							$ids      = explode( ',', $ids );
							$food_ids = array_merge( $food_ids, $ids );
						}
					}
				}
				if ( is_array( $food_ids ) && ! empty( $food_ids ) ) {
					$query->set( 'post__in', $food_ids );
				} else {
					$query->set( 'post__in', array( 0 ) );
				}
			}
		}
	}
}

add_action( 'wp_ajax_woore_menu_by_timesl', 'ajax_woore_menu_by_timesl' );
add_action( 'wp_ajax_nopriv_woore_menu_by_timesl', 'ajax_woore_menu_by_timesl' );
/**
 * Ajax get menu by time.
 */
function ajax_woore_menu_by_timesl() {
	if ( isset( $_POST['token'] ) ) {
		// phpcs:ignore
		if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'menu-timesl' ) ) {
			return;
		}
	}

	$date_slt = isset( $_POST['date'] ) ? wp_kses_post( wp_unslash( $_POST['date'] ) ) : '';
	$cr_url   = isset( $_POST['cr_url'] ) ? wp_kses_post( wp_unslash( $_POST['cr_url'] ) ) : '';
	$html     = '';
	if ( '' == $date_slt ) {
		$html = 'Error';
	} else {
		$date_slt                = strtotime( $date_slt );
		$wday                    = gmdate( 'D', $date_slt );
		$args_mn                 = array(
			'post_type'        => 'woore_menubydate',
			'post_status'      => array( 'publish' ),
			'numberposts'      => 1,
			'suppress_filters' => true,
		);
		$args_mn['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'     => 'woore_mndate',
				'value'   => $date_slt,
				'compare' => '=',
			),
			array(
				'key'     => 'woore_mnrepeat_' . $wday,
				'value'   => 'on',
				'compare' => '=',
			),
		);
		$args_mn                 = apply_filters( 'woore_menu_by_timesl_find', $args_mn );
		$menu_f                  = get_posts( $args_mn );
		if ( ! empty( $menu_f ) && count( $menu_f ) > 0 ) {
			$itemsl = array();
			if ( '' == $cr_url ) {
				global $wp;
				$cr_url = home_url( $wp->request );
			}
			$cr_url      = apply_filters( 'woore_current_link_ajax', $cr_url );
			$date_before = woo_restaurant_get_option( 'woo_restaurant_ck_beforedate', 'woo_restaurant_advanced_options' );
			$cure_time   = strtotime( 'now' );
			$gmt_offset  = get_option( 'gmt_offset' );

			if ( '' != $date_before && is_numeric( $date_before ) ) {
				$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( "+$date_before day" ) );
			} elseif ( '' != $date_before && is_numeric( str_replace( 'm', '', $date_before ) ) ) {
				$cure_time = apply_filters( 'wrst_disable_book_day', strtotime( '+' . str_replace( 'm', '', $date_before ) . ' minutes' ) );
			}
			if ( '' != $gmt_offset ) {
				$cure_time = $cure_time + ( $gmt_offset * 3600 );
			}
			$html_sl                        = '';
			$method                         = WC()->session->get( '_user_order_method' );
			$method                         = '' != $method ? $method : 'delivery';
			$data_vrf                       = array();
			$data_vrf['wooresfd_date_deli'] = $date_slt;
			foreach ( $menu_f as $f_item ) {
				$sl = get_post_meta( $f_item->ID, 'woore_menu_timesl', true );
				if ( is_array( $sl ) && ! empty( $sl ) ) {
					foreach ( $sl as $item ) {
						$_dis_method = isset( $item['dis_method'] ) ? $item['dis_method'] : '';
						if ( is_array( $_dis_method ) && ! in_array( $method, $_dis_method ) || '' == $_dis_method ) {
							$_time_base = apply_filters( 'woore_timebase_to_check_menudate', ( isset( $item['time_from'] ) ? $item['time_from'] : '' ), $item );
							$_timeck    = '';
							if ( '' != $_time_base ) {
								$_timeck = $_time_base;
								$_timeck = explode( ':', $_timeck );
								$_timeck = $_timeck[1] * 60 + $_timeck[0] * 3600;
							}
							if ( '' == $_time_base || ( is_numeric( $_timeck ) && ( $date_slt + $_timeck ) >= $cure_time ) ) {
								$item['post_id']                = $f_item->ID;
								$itemsl[]                       = $item;
								$timefrom                       = isset( $item['time_from'] ) && '' != $item['time_from'] ? $item['time_from'] : '';
								$time_to                        = isset( $item['time_to'] ) && '' != $item['time_to'] ? $item['time_to'] : '';
								$name                           = isset( $item['mn_name'] ) && '' != $item['mn_name'] ? $item['mn_name'] : ( '' != $timefrom && '' != $time_to ? $timefrom . ' - ' . $time_to : ( '' != $timefrom ? $timefrom : ( '' != $time_to ? $time_to : '' ) ) );
								$data_vrf['wooresfd_time_deli'] = $name;
								$max_stt                        = woore_menu_time_maxorder_status( $data_vrf, $item, true );
								if ( '' == $max_stt ) {
									$url      = add_query_arg(
										array(
											'menu-date' => gmdate( 'Y-m-d', $date_slt ),
											'menu-time' => esc_attr( $name ),
										),
										$cr_url
									);
									$html_sl .= '<a href="' . esc_attr( $url ) . '" data-time="">' . $name . '</a>';
								}
							}
						}
					}
					if ( '' == $html_sl ) {
						$html_sl = esc_html__( 'All menus of this date has passed or not available, please choose another date', 'woorestaurant' );
					}
				}
			}
			if ( '' != $html_sl ) {
				$html .= '<div class="wrsf-mnsl">' . $html_sl . '</div>';
			}
			WC()->session->set( '_user_menusl', $itemsl );
		}
	}
	$output = array( 'html_content' => $html );
	echo esc_attr( str_replace( '\/', '/', json_encode( $output ) ) );
	die;
}

add_filter( 'woore_arr_enable_method', 'woore_arr_enable_method_in_timesl' );
/**
 * Enable method in time.
 *
 * @param array $methods it is having the methods.
 *
 * @return array
 */
function woore_arr_enable_method_in_timesl( $methods ) {
	if ( '' == $methods || empty( $methods ) ) {
		return $methods;
	}
	$timesl = isset( WC()->session ) ? WC()->session->get( '_user_menusl' ) : '';
	if ( isset( $timesl['time_from'] ) ) {
		$timesl[0] = $timesl;
	}
	$_dis_method = isset( $timesl[0]['dis_method'] ) ? $timesl[0]['dis_method'] : '';
	if ( is_array( $_dis_method ) && ! empty( $_dis_method ) ) {
		$methods = array_diff( $methods, $_dis_method );
	}
	return $methods;
}

// if user add to cart from single product.
add_action( 'woocommerce_checkout_process', 'woore_verify_food_by_date_', 20 );
/**
 * Verify food by date.
 */
function woore_verify_food_by_date_() {
	$all_options = get_option( 'woo_restaurant_options' );
	if ( isset( $all_options['woo_restaurant_foodby_date'] ) && 'yes' == $all_options['woo_restaurant_foodby_date'] ) {
		$mntimesl = woo_restaurant_get_option( 'woo_restaurant_foodby_timesl' );
		$time_slt = WC()->session->get( '_menutime' );
		if ( isset( $all_options['woo_restaurant_foodby_timesl'] ) && 'yes' == $all_options['woo_restaurant_foodby_timesl'] && '' != $time_slt ) {
			return;
		}
		$ids     = woore_query_by_menu_date( array() );
		$cat_ids = WC()->session->get( '_cats_menudate' );
		if ( isset( $ids['post__in'] ) && ! empty( $ids['post__in'] ) ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$id_cr = $cart_item['product_id'];
				if ( ! in_array( $id_cr, $ids['post__in'] ) ) {
					/* translators: %s is replaced with the title of category */
					wc_add_notice( sprintf( esc_html__( 'You cannot order "%s" on this date', 'woorestaurant' ), get_the_title( $id_cr ) ), 'error' );
				}
			}
		} elseif ( is_array( $cat_ids ) && ! empty( $cat_ids ) ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$id_cr       = $cart_item['product_id'];
				$product_cat = get_the_terms( $id_cr, 'product_cat' );
				$cats        = array();
				if ( ! empty( $product_cat ) && ! is_wp_error( $product_cat ) ) {
					$cats = wp_list_pluck( $product_cat, 'term_id' );
				}
				if ( $cats && empty( array_intersect( $cat_ids, $cats ) ) ) {
					/* translators: %s is replaced with the title of category */
					wc_add_notice( sprintf( esc_html__( 'You cannot order "%s" on this date', 'woorestaurant' ), get_the_title( $id_cr ) ), 'error' );
				}
			}
		} else {
			$mes_err = WC()->session->get( '_error_mes' );
			if ( '' != $mes_err ) {
				wc_add_notice( $mes_err, 'error' );
			} else {
				wc_add_notice( esc_html__( 'No food available for this date', 'woorestaurant' ), 'error' );
			}
		}
	}
}
add_action( 'woore_verify_date_time', 'woore_menu_time_maxorder_status', 20 );
/**
 * Get menu time max order status.
 *
 * @param array $data it is the data of maxorder.
 * @param array $_menu_details it is having the details of the menu.
 * @param bool  $return it is the return value.
 */
function woore_menu_time_maxorder_status( $data, $_menu_details = false, $return = false ) {
	$_menu_details = isset( $menu_details ) && ! empty( $menu_details ) ? $menu_details : woore_menuby_time_selected_details();
	if ( is_array( $_menu_details ) && isset( $_menu_details['max_order'] ) && is_numeric( $_menu_details['max_order'] ) ) {
		$date_deli = isset( $data['wooresfd_date_deli'] ) ? $data['wooresfd_date_deli'] : '';
		if ( ! is_numeric( $date_deli ) ) {
			$date_deli = strtotime( $data['wooresfd_date_deli'] );
			if ( '' == $date_deli ) {
				return;
			}
		}
		$method   = WC()->session->get( '_user_order_method' );
		$method   = '' != $method ? $method : 'delivery';
		$timefrom = isset( $_menu_details['time_from'] ) && '' != $_menu_details['time_from'] ? $_menu_details['time_from'] : '';
		$time_to  = isset( $_menu_details['time_to'] ) && '' != $_menu_details['time_to'] ? $_menu_details['time_to'] : '';
		$name     = isset( $_menu_details['mn_name'] ) && '' != $_menu_details['mn_name'] ? $_menu_details['mn_name'] : ( '' != $timefrom && '' != $time_to ? $timefrom . ' - ' . $time_to : ( '' != $timefrom ? $timefrom : ( '' != $time_to ? $time_to : '' ) ) );
		$locat    = isset( $data['woo_restaurant_ck_loca'] ) ? $data['woo_restaurant_ck_loca'] : '';
		if ( '' == $locat ) {
			$locat = WC()->session->get( '_user_deli_log' );
		}
		$total_rs = woore_get_number_order_timeslot( $date_deli, $data['wooresfd_time_deli'], $locat, $method, '1', $_menu_details );
		if ( $total_rs >= $_menu_details['max_order'] ) {
			$text_datedel = woore_date_time_text( 'date' );
			$text_timedel = woore_date_time_text( 'time' );
			/* translators: %1$s is replaced with the text time delay, %2$s is replaced with the text date delay */
			$msg = sprintf( esc_html__( 'Sorry, the %1$s you have selected has full order, please try again with different  %2$s or time', 'woorestaurant' ), $text_timedel, $text_datedel );
			if ( isset( $return ) && true == $return ) {
				return $msg;
			} else {
				wc_add_notice( $msg, 'error' );
			}
		}
	}
	return;
}
