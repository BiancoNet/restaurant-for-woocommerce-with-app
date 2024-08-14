<?php
/**
 * This is the init load file.
 *
 * @package woorestaurant
 */

namespace WooRestaurant;

/**
 * This class for plugin load :)
 */
class Init {
	/**
	 * It is the lib file.
	 */
	public function lib() {
		require_once WOORESTAURANT_DIR . 'includes/lib/wrmb2init.php';
	}

	/**
	 * It is the functions method.
	 */
	public function functions() {
		require_once WOORESTAURANT_DIR . 'includes/function/helper.php';
		require_once WOORESTAURANT_DIR . 'includes/integrations/init.php';
		require_once WOORESTAURANT_DIR . 'includes/function/viewinit.php';
		require_once WOORESTAURANT_DIR . 'assets/css/custom-css.php';
	}

	/**
	 * It is having dependencies.
	 */
	public function depencies() {
		require_once WOORESTAURANT_DIR . 'includes/class/class-admin.php';
		require_once WOORESTAURANT_DIR . 'includes/class/class-taxonomy.php';
		require_once WOORESTAURANT_DIR . 'includes/class/class-shortcodes.php';
		require_once WOORESTAURANT_DIR . 'includes/class/class-woores-widget.php';
		require_once WOORESTAURANT_DIR . 'includes/class/class-main.php';
		require_once WOORESTAURANT_DIR . 'includes/class/class-cron.php';
	}

	/**
	 * These are classes.
	 */
	public function classes() {
		// admin class init.
		new Admin();
		// taxonomy init.
		new Taxonomy();
		// shortcodes builder.
		new Shortcodes();
		// main init.
		new Main();
		// cron classes.
		new Cron();
	}

	/**
	 * It is the run method.
	 */
	public function run() {
		self::lib();
		self::functions();
		self::depencies();
		// initialize classes.
		self::classes();
	}
}
