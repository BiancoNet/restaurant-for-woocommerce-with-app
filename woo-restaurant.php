<?php
/**
 * Plugin Name: Restaurant for WooCommerce with App
 * Plugin URI: https://woorestaurant.co.uk/
 * Description: Restaurant Management with Menu Sync.
 * Author: BiancoNet
 * Version: 1.0.0
 * Text Domain: woorestaurant
 * Author URI: https://bianconet.co.uk/
 * Domain Path: /languages
 *
 * @package woorestaurant
 */

// No direct accessable.
if ( ! defined( 'ABSPATH' ) ) {
	die(); // Exit if accessed directly.
}

// constants.
define( 'WOORESTAURANT_VERSION', '1.0.1' );
define( 'WOORESTAURANT_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOORESTAURANT_ASSETS', plugins_url( 'assets/', __FILE__ ) );
define( 'WOORESTAURANT_URL', plugins_url( '/', __FILE__ ) );
define( 'WOORESTAURANT_BASE', plugin_basename( __FILE__ ) );
define( 'WOORESTAURANT_FILE', __FILE__ );

require WOORESTAURANT_DIR . 'includes/class-init.php';

if ( ! function_exists( 'woorestaurant_app_init' ) ) {
	/**
	 * It is the app init function.
	 */
	function woorestaurant_app_init() {
		$plugin = new WooRestaurant\Init();
		$plugin->run();
	}
}
woorestaurant_app_init();
