<?php
/**
 * Admin CLass for admin things init.
 *
 * @package woorestaurant
 **/

namespace WooRestaurant;

/**
 * Admin class.
 */
class Admin {

	/**
	 * Constructor Function.
	 */
	public function __construct() {
		// register admin menu.
		add_action( 'admin_menu', array( $this, 'admin_pages' ) );
		// menu set current.
		add_filter( 'parent_file', array( $this, 'set_current_menu' ) );
		// js & css.
		add_filter( 'admin_enqueue_scripts', array( $this, 'scripts_css' ) );
		add_filter( 'admin_footer', array( $this, 'custom_css' ) );
		add_filter( 'admin_init', array( $this, 'redirect' ) );
	}

	/**
	 * Redirect Method.
	 */
	public function redirect() {
	}

	/**
	 * This function is having admin pages.
	 */
	public function admin_pages() {
		global $submenu, $menu, $_wp_real_parent_file, $_wp_submenu_nopriv,
			$_registered_pages, $_parent_pages;

		add_menu_page(
			__( 'Woo Restaurant', 'wrmb2' ),
			__( 'Woo Restaurant', 'wrmb2' ),
			'manage_options',
			'woo-restaurant',
			array( $this, 'woo_restaurant_callback' ),
			'dashicons-food',
			4
		);

		add_submenu_page( 'woo-restaurant', '', 'Food Location', 'manage_options', 'edit-tags.php?taxonomy=woorestaurant_loc&post_type=woorestaurant_scbd', null );
	}

	/**
	 * Enqueue scripts.
	 */
	public function scripts_css() {
		wp_enqueue_style( 'woo-restaurant-admin-css' );
	}

	/**
	 * Callback method of dashboard.
	 */
	public function woo_restaurant_callback() {
		include WOORESTAURANT_DIR . 'views/admin/dashboard.php';
	}

	/**
	 * Set menu function.
	 *
	 * @param string $parent_file it is having the parent of file.
	 *
	 * @return string
	 */
	public function set_current_menu( $parent_file ) {
		global $current_screen, $pagenow;

		if ( 'woorestaurant_scbd' == $current_screen->post_type ) {
			if ( 'post.php' == $pagenow ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
				$submenu_file = 'edit.php?post_type=' . $current_screen->post_type;
			}
			if ( 'edit-tags.php' == $pagenow ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
				$submenu_file = 'edit-tags.php?taxonomy=woorestaurant_loc&post_type=' . $current_screen->post_type;
			}
			$parent_file = 'woo-restaurant';
		}
		if ( isset( $_GET['page'] ) ) {
			if ( in_array( $_GET['page'], array( 'woo_restaurant_adv_takeaway_options', 'woo_restaurant_adv_dinein_options', 'woo_restaurant_adv_timesl_options', 'woo_restaurant_tip_options' ) ) ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
				$submenu_file = 'woo-restaurant';
				$parent_file  = 'woo_restaurant_adv_takeaway_options';
			}
		}
		return $parent_file;
	}

	/**
	 * It is the custom css method.
	 */
	public function custom_css() {
		?>
		<style>
			.wrmb2-id-woo-restaurant-ign-op,
			.wrmb2-id-woo-restaurant-ign-deli,
			.wrmb2-id-woo-restaurant-ship-mode,
			.wrmb2-id-woo-restaurant-gg-api,
			.wrmb2-id-woo-restaurant-gg-distance-api,
			.wrmb2-id-woo-restaurant-restrict-km,
			.wrmb2-id-woo-restaurant-calcu-mode,
			.wrmb2-id-woo-restaurant-autocomplete-limit,
			.wrmb2-id-woo-restaurant-autocomplete-limit,
			.wrmb2-id-woo-restaurant-autocomplete-cko,
			.wrmb2-id-woo-restaurant-shipfee-bytime,
			.wrmb2-id-woores-op-cl,
			.wrmb2-id-woores-km-loc,
			.wrmb2-id-woores-adv-feekm,
			.wrmb2-id-woo-restaurant-ck-disdate,
			.wrmb2-id-woo-restaurant-ck-enadate {}
		</style>
		<?php
	}
}
