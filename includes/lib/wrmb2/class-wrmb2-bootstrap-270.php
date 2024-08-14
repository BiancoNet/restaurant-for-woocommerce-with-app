<?php
/**
 * The initation loader for WRMB2, and the main plugin file.
 *
 * @category     WordPress_Plugin
 * @package      WRMB2
 * @author       WRMB2 team
 * @license      GPL-2.0+
 * @link         https://wrmb2.io
 *
 * Plugin Name:  WRMB2
 * Plugin URI:   https://github.com/WRMB2/WRMB2
 * Description:  WRMB2 will create metaboxes and forms with custom fields that will blow your mind.
 * Author:       WRMB2 team
 * Author URI:   https://wrmb2.io
 * Contributors: Justin Sternberg (@jtsternberg / dsgnwrks.pro)
 *               WebDevStudios (@webdevstudios / webdevstudios.com)
 *               Human Made (@humanmadeltd / hmn.md)
 *               Jared Atchison (@jaredatch / jaredatchison.com)
 *               Bill Erickson (@billerickson / billerickson.net)
 *               Andrew Norcross (@norcross / andrewnorcross.com)
 *
 * Version:      2.7.0
 *
 * Text Domain:  wrmb2
 * Domain Path:  languages
 *
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * https://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/**
 * *********************************************************************
 *               You should not edit the code below
 *               (or any code in the included files)
 *               or things might explode!
 * ***********************************************************************
 */

if ( ! class_exists( 'WRMB2_Bootstrap_270', false ) ) {

	/**
	 * Handles checking for and loading the newest version of WRMB2
	 *
	 * @since  2.0.0
	 *
	 * @category  WordPress_Plugin
	 * @package   WRMB2
	 * @author    WRMB2 team
	 * @license   GPL-2.0+
	 * @link      https://wrmb2.io
	 */
	class WRMB2_Bootstrap_270 {

		/**
		 * Current version number
		 *
		 * @var   string
		 * @since 1.0.0
		 */
		const VERSION = '2.7.0';

		/**
		 * Current version hook priority.
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 2.0.0
		 */
		const PRIORITY = 9962;

		/**
		 * Single instance of the WRMB2_Bootstrap_270 object
		 *
		 * @var WRMB2_Bootstrap_270
		 */
		public static $single_instance = null;

		/**
		 * Creates/returns the single instance WRMB2_Bootstrap_270 object
		 *
		 * @since  2.0.0
		 * @return WRMB2_Bootstrap_270 Single instance object
		 */
		public static function initiate() {
			if ( null === self::$single_instance ) {
				self::$single_instance = new self();
			}
			return self::$single_instance;
		}

		/**
		 * Starts the version checking process.
		 * Creates WRMB2_LOADED definition for early detection by other scripts
		 *
		 * Hooks WRMB2 inclusion to the init hook on a high priority which decrements
		 * (increasing the priority) with each version release.
		 *
		 * @since 2.0.0
		 */
		private function __construct() {
			/**
			 * A constant you can use to check if WRMB2 is loaded
			 * for your plugins/themes with WRMB2 dependency
			 */
			if ( ! defined( 'WRMB2_LOADED' ) ) {
				define( 'WRMB2_LOADED', self::PRIORITY );
			}

			if ( ! function_exists( 'add_action' ) ) {
				// We are running outside of the context of WordPress.
				return;
			}

			add_action( 'init', array( $this, 'include_wrmb' ), self::PRIORITY );
		}

		/**
		 * A final check if WRMB2 exists before kicking off our WRMB2 loading.
		 * WRMB2_VERSION and WRMB2_DIR constants are set at this point.
		 *
		 * @since  2.0.0
		 */
		public function include_wrmb() {
			if ( class_exists( 'WRMB2', false ) ) {
				return;
			}

			if ( ! defined( 'WRMB2_VERSION' ) ) {
				define( 'WRMB2_VERSION', self::VERSION );
			}

			if ( ! defined( 'WRMB2_DIR' ) ) {
				define( 'WRMB2_DIR', trailingslashit( __DIR__ ) );
			}

			$this->l10ni18n();

			// Include helper functions.
			require_once WRMB2_DIR . 'includes/class-wrmb2-base.php';
			require_once WRMB2_DIR . 'includes/class-wrmb2.php';
			require_once WRMB2_DIR . 'includes/helper-functions.php';

			// Now kick off the class autoloader.
			spl_autoload_register( 'wrmb2_autoload_classes' );

			// Kick the whole thing off.
			require_once wrmb2_dir( 'bootstrap.php' );
			wrmb2_bootstrap();
		}

		/**
		 * Registers WRMB2 text domain path
		 *
		 * @since  2.0.0
		 */
		public function l10ni18n() {

			$loaded = load_plugin_textdomain( 'wrmb2', false, '/languages/' );

			if ( ! $loaded ) {
				$loaded = load_muplugin_textdomain( 'wrmb2', '/languages/' );
			}

			if ( ! $loaded ) {
				$loaded = load_theme_textdomain( 'wrmb2', get_stylesheet_directory() . '/languages/' );
			}

			if ( ! $loaded ) {
				$locale = apply_filters( 'plugin_locale', function_exists( 'determine_locale' ) ? determine_locale() : get_locale(), 'wrmb2' );
				$mofile = __DIR__ . '/languages/wrmb2-' . $locale . '.mo';
				load_textdomain( 'wrmb2', $mofile );
			}
		}
	}

	// Make it so...
	WRMB2_Bootstrap_270::initiate();

}// End if().
