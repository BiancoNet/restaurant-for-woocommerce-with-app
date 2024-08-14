<?php
/**
 * This is the main file which contains the main functions.
 *
 * @package woorestaurant
 */

namespace WooRestaurant;

/**
 * This is the Main class.
 */
class Main {

	/**
	 * This is the template URL.
	 *
	 * @var $template_url
	 */
	public $template_url;

	/**
	 * This is the plugin path.
	 *
	 * @var $plugin_path
	 */
	public $plugin_path;

	/**
	 * This is the constructor method.
	 */
	public function __construct() {
		$this->includes();
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'wp_footer', array( $this, 'styles_css_print' ), 10 );
		add_filter( 'template_include', array( $this, 'template_loader' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_style' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'after_setup_theme', array( $this, 'calthumb_register' ) );
		// Override Template Part's.
		add_filter( 'wc_get_template_part', array( $this, 'override_woocommerce_template_part' ), 10, 3 );
		// Override Template's.
		add_filter( 'woocommerce_locate_template', array( $this, 'override_woocommerce_template' ), 10, 3 );
	}

	/**
	 * Load text domain.
	 *
	 * @return function
	 */
	public function load_textdomain() {
		$textdomain = 'woorestaurant';
		$locale     = '';
		if ( empty( $locale ) ) {
			if ( is_textdomain_loaded( $textdomain ) ) {
				return true;
			} else {
				return load_plugin_textdomain( $textdomain, false, plugin_basename( dirname( WOORESTAURANT_FILE ) ) . '/languages' );
			}
		} else {
			return load_textdomain( $textdomain, plugin_basename( dirname( WOORESTAURANT_FILE ) ) . '/woorestaurant/' . $textdomain . '-' . $locale . '.mo' );
		}
	}

	/**
	 * Thumbnails register.
	 */
	public function calthumb_register() {
		add_image_size( 'woo_restaurant_80x80', 120, 120, true );
		add_image_size( 'woo_restaurant_400x400', 400, 400, true );
	}

	/**
	 * This is the plugin path function.
	 */
	public function plugin_path() {
		if ( $this->plugin_path ) {
			return $this->plugin_path;
		}
		$this->plugin_path = untrailingslashit( plugin_dir_path( WOORESTAURANT_FILE ) );
		return $this->plugin_path;
	}

	/**
	 * This is the template loader function.
	 *
	 * @param string $template this contains the name of the template.
	 *
	 * @return string
	 */
	public function template_loader( $template ) {
		if ( is_tax( 'woorestaurant_loc' ) ) {
			wp_safe_redirect( get_template_part( '404' ) );
			exit;
		}
		return $template;
	}

	/**
	 * This is the includes function.
	 */
	public function includes() {
	}

	/**
	 * This is the style function.
	 */
	public function styles_css_print() {
		?>
		<style>
			<?php echo wp_kses_post( woo_restaurant_custom_css() ); ?>
		</style>
		<?php
	}

	/**
	 * Load js and css.
	 */
	public function frontend_scripts() {
		$main_font_default          = 'Source Sans Pro';
		$g_fonts                    = array( $main_font_default );
		$woo_restaurant_font_family = woo_restaurant_get_option( 'woo_restaurant_font_family' );
		if ( '' != $woo_restaurant_font_family ) {
			$woo_restaurant_font_family = woo_restaurant_get_google_font_name( $woo_restaurant_font_family );
			array_push( $g_fonts, $woo_restaurant_font_family );
		}
		$woo_restaurant_headingfont_family = woo_restaurant_get_option( 'woo_restaurant_headingfont_family' );
		if ( '' != $woo_restaurant_headingfont_family ) {
			$woo_restaurant_headingfont_family = woo_restaurant_get_google_font_name( $woo_restaurant_headingfont_family );
			array_push( $g_fonts, $woo_restaurant_headingfont_family );
		}
	}

	/**
	 * This function contains the styles.
	 */
	public function frontend_style() {
		$api_map = woo_restaurant_get_option( 'woo_restaurant_gg_api', 'woo_restaurant_shpping_options' );
		if ( '' != $api_map ) {
			$map_lang = urlencode( apply_filters( 'woore_map_lang', 'en' ) );
			wp_enqueue_script( 'wrsf-auto-address', '//maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_map ) . '&language=' . $map_lang . '&libraries=places' );
		}
		wp_enqueue_script( 'woo-restaurant', plugins_url( '/assets/js/food.min.js', WOORESTAURANT_FILE ), array( 'jquery' ), '1.0.00.1' );
		$js_params = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( 'woo-restaurant', 'woore_jspr', $js_params );
		wp_enqueue_script( 'woo-restaurant-ajax-cart', plugins_url( '/assets/js/ajax-add-to-cart.min.js', WOORESTAURANT_FILE ), array( 'jquery', 'wc-add-to-cart' ), '2.9x' );
		wp_register_script( 'wrsf-reviews', plugins_url( '/assets/js/reviews.js', WOORESTAURANT_FILE ), array( 'jquery' ), '1.0' );
		wp_register_script( 'wrsf-minmax-quantity', plugins_url( '/assets/js/minmax-quantity.js', WOORESTAURANT_FILE ), array( 'jquery' ), '1.0' );
		wp_enqueue_style( 'woo-restaurant', WOORESTAURANT_ASSETS . 'css/style.css', '1.0.2' );
		wp_enqueue_style( 'ionicon', WOORESTAURANT_ASSETS . 'css/ionicon/css/ionicons.min.css', '1.0.0.1' );
		wp_enqueue_style( 'woo-restaurant-list', WOORESTAURANT_ASSETS . 'css/style-list.css', '1.0' );
		wp_enqueue_style( 'woo-restaurant-table', WOORESTAURANT_ASSETS . 'css/style-table.css', '1.0' );
		wp_enqueue_style( 'woo-restaurant-modal', WOORESTAURANT_ASSETS . 'css/modal.css', '1.5.2' );
		wp_enqueue_style( 'woo-restaurant-cartcss', WOORESTAURANT_ASSETS . 'css/cart.css', array(), '1.1.1.1' );
		wp_enqueue_style( 'wrs-wp-s_lick', WOORESTAURANT_ASSETS . 'js/wr_s_lick/wr_s_lick.css' );
		wp_enqueue_style( 'wr_wp_s_lick-theme', WOORESTAURANT_ASSETS . 'js/wr_s_lick/wr_s_lick-theme.css' );
		wp_enqueue_script( 'wr_wp_s_lick', WOORESTAURANT_ASSETS . 'js/wr_s_lick/wr_s_lick.js', array( 'jquery' ), '1.0.01' );
		$woo_restaurant_enable_rtl = woo_restaurant_get_option( 'woo_restaurant_enable_rtl' );
		wp_enqueue_style(
			'woorst-custom-css',
			WOORESTAURANT_ASSETS . 'js/wr_s_lick/wr_s_lick.css'
		);

		if ( 'yes' == $woo_restaurant_enable_rtl || is_rtl() ) {
			wp_enqueue_style( 'woo-restaurant-rtl', WOORESTAURANT_ASSETS . 'css/rtl.css' );
			wp_enqueue_style(
				'woorst-custom-css',
				WOORESTAURANT_ASSETS . 'css/rtl.css'
			);
		}
	}

	/**
	 * Template Part's
	 *
	 * @param  string $template Default template file path.
	 * @param  string $slug     Template file slug.
	 * @param  string $name     Template file name.
	 * @return string           Return the template part from plugin.
	 */
	public function override_woocommerce_template_part( $template, $slug, $name ) {
		$template_directory = untrailingslashit( plugin_dir_path( WOORESTAURANT_FILE ) ) . '/templates/';
		if ( $name ) {
			$path = $template_directory . "{$slug}-{$name}.php";
		} else {
			$path = $template_directory . "{$slug}.php";
		}
		return file_exists( $path ) ? $path : $template;
	}

	/**
	 * Template File
	 *
	 * @param  string $template      Default template file  path.
	 * @param  string $template_name Template file name.
	 * @param  string $template_path Template file directory file path.
	 * @return string                Return the template file from plugin.
	 */
	public function override_woocommerce_template( $template, $template_name, $template_path ) {
		$template_directory = untrailingslashit( plugin_dir_path( WOORESTAURANT_FILE ) ) . '/templates/';
		$path               = $template_directory . $template_name;
		return file_exists( $path ) ? $path : $template;
	}
}
