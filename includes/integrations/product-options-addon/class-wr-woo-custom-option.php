<?php
/**
 * Plugin Name: Custom options for WooCommerce
 * Description: Add custom options to WooCommerce products
 * Version: 1.5.0
 * Author: BiancoNet
 * Author URI: https://bianconet.co.uk/
 * Text Domain: product-options-addon
 * WC requires at least: 3.4.0
 * WC tested up to: 4.1.0
 *
 * @package woorestaurant
 */

if ( ! function_exists( 'woores_get_plugin_url' ) ) {
	/**
	 * Define JWT_AUTH_SECRET_KEY for JWT authentication.
	 */
	function woores_get_plugin_url() {
		return plugin_dir_path( __FILE__ );
	}
} else {
	return;
}
define( 'WR_WOO_OPTION_PATH', plugin_dir_url( __FILE__ ) );
// Make sure we don't expose any info if called directly.
if ( ! defined( 'WR_WOO_OPTION_PATH' ) ) {
	die( '-1' );
}

/**
 * It is the class wr_Woo_Custom_Option
 */
// phpcs:ignore
class wr_Woo_Custom_Option {
	/**
	 * It is the template URL.
	 *
	 * @var $template_url
	 */
	public $template_url;

	/**
	 * It is the plugin path.
	 *
	 * @var $plugin_path
	 */
	public $plugin_path;

	/**
	 * It is the constructor method.
	 */
	public function __construct() {
		$this->includes();
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_style' ), 99 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load text domain.
	 */
	public function load_textdomain() {
		$textdomain = 'product-options-addon';
		$locale     = '';
		if ( empty( $locale ) ) {
			if ( is_textdomain_loaded( $textdomain ) ) {
				return true;
			} else {
				return load_plugin_textdomain( $textdomain, false, plugin_basename( __DIR__ ) . '/language' );
			}
		} else {
			return load_textdomain( $textdomain, plugin_basename( __DIR__ ) . '/' . $textdomain . '-' . $locale . '.mo' );
		}
	}

	/**
	 * It returns the plugin path.
	 */
	public function plugin_path() {
		if ( $this->plugin_path ) {
			return $this->plugin_path;
		}
		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		return $this->plugin_path;
	}

	/**
	 * It includes the files.
	 */
	public function includes() {
		include_once woores_get_plugin_url() . 'admin/functions.php';
		include_once woores_get_plugin_url() . 'inc/functions.php';
	}

	/**
	 * Load js and css.
	 */
	public function frontend_scripts() {
		wp_enqueue_script( 'wrs-woo-options', plugins_url( '/js/options-addon.js', __FILE__ ), array( 'jquery' ), '1.5' );
	}

	/**
	 * It enqueues the frontend style.
	 */
	public function frontend_style() {
		wp_enqueue_style( 'wrs-woo-options', WR_WOO_OPTION_PATH . 'css/style.css', '1.5.1' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'wrs-woo-options-rtl', WOORESTAURANT_ASSETS . 'css/rtl.css' );
		}
	}
}
$wr_woo_custom_option = new wr_Woo_Custom_Option();

