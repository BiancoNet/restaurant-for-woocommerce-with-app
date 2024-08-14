<?php
/**
 * It is the WRBM2 Hookup Base file.
 *
 * @package woorestaurant
 */

/**
 * Base class for hooking WRMB2 into WordPress.
 *
 * @since  2.2.0
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 *
 * @property-read string $object_type
 * @property-read WRMB2   $wrmb
 */
abstract class WRMB2_Hookup_Base {

	/**
	 * WRMB2 object.
	 *
	 * @var   WRMB2 object
	 * @since 2.0.2
	 */
	protected $wrmb;

	/**
	 * The object type we are performing the hookup for
	 *
	 * @var   string
	 * @since 2.0.9
	 */
	protected $object_type = 'post';

	/**
	 * A functionalized constructor, used for the hookup action callbacks.
	 *
	 * @since  2.2.6
	 *
	 * @throws Exception Failed implementation.
	 *
	 * @param  WRMB2 $wrmb The WRMB2 object to hookup.
	 */
	public static function maybe_init_and_hookup( WRMB2 $wrmb ) {
		/* translators: %1$s is replaced with the function */
		throw new Exception( sprintf( esc_html__( '%1$s should be implemented by the extended class.', 'wrmb2' ), __FUNCTION__ ) );
	}

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 * @param WRMB2 $wrmb The WRMB2 object to hookup.
	 */
	public function __construct( WRMB2 $wrmb ) {
		$this->wrmb        = $wrmb;
		$this->object_type = $this->wrmb->mb_object_type();
	}

	/**
	 * It is the universal hooks function.
	 */
	abstract public function universal_hooks();

	/**
	 * Ensures WordPress hook only gets fired once per object.
	 *
	 * @since  2.0.0
	 * @param string   $action        The name of the filter to hook the $hook callback to.
	 * @param callback $hook          The callback to be run when the filter is applied.
	 * @param integer  $priority      Order the functions are executed.
	 * @param int      $accepted_args The number of arguments the function accepts.
	 */
	public function once( $action, $hook, $priority = 10, $accepted_args = 1 ) {
		static $hooks_completed = array();

		$args = func_get_args();

		// Get object hash.. This bypasses issues with serializing closures.
		if ( is_object( $hook ) ) {
			$args[1] = spl_object_hash( $args[1] );
		} elseif ( is_array( $hook ) && is_object( $hook[0] ) ) {
			$args[1][0] = spl_object_hash( $hook[0] );
		}

		$key = md5( serialize( $args ) );

		if ( ! isset( $hooks_completed[ $key ] ) ) {
			$hooks_completed[ $key ] = 1;
			add_filter( $action, $hook, $priority, $accepted_args );
		}
	}

	/**
	 * Magic getter for our object.
	 *
	 * @param string $field Property to return.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'object_type':
			case 'wrmb':
				return $this->{$field};
			default:
				/* translators: %1$s is replaced with the class, %2$s is replaced with the field */
				throw new Exception( sprintf( esc_html__( 'Invalid %1$s property: %2$s', 'wrmb2' ), __CLASS__, esc_attr( $field ) ) );
		}
	}
}
