<?php
/**
 * It is the boxes file.
 *
 * @package woorestaurant
 */

/**
 * A WRMB2 object instance registry for storing every WRMB2 instance.
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Boxes {

	/**
	 * Array of all metabox objects.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	protected static $wrmb2_instances = array();

	/**
	 * Add a WRMB2 instance object to the registry.
	 *
	 * @since 1.X.X
	 *
	 * @param WRMB2 $wrmb_instance WRMB2 instance.
	 */
	public static function add( WRMB2 $wrmb_instance ) {
		self::$wrmb2_instances[ $wrmb_instance->wrmb_id ] = $wrmb_instance;
	}

	/**
	 * Remove a WRMB2 instance object from the registry.
	 *
	 * @since 1.X.X
	 *
	 * @param string $wrmb_id A WRMB2 instance id.
	 */
	public static function remove( $wrmb_id ) {
		if ( array_key_exists( $wrmb_id, self::$wrmb2_instances ) ) {
			unset( self::$wrmb2_instances[ $wrmb_id ] );
		}
	}

	/**
	 * Retrieve a WRMB2 instance by wrmb id.
	 *
	 * @since 1.X.X
	 *
	 * @param string $wrmb_id A WRMB2 instance id.
	 *
	 * @return WRMB2|bool False or WRMB2 object instance.
	 */
	public static function get( $wrmb_id ) {
		if ( empty( self::$wrmb2_instances ) || empty( self::$wrmb2_instances[ $wrmb_id ] ) ) {
			return false;
		}

		return self::$wrmb2_instances[ $wrmb_id ];
	}

	/**
	 * Retrieve all WRMB2 instances registered.
	 *
	 * @since  1.X.X
	 * @return WRMB2[] Array of all registered wrmb2 instances.
	 */
	public static function get_all() {
		return self::$wrmb2_instances;
	}

	/**
	 * Retrieve all WRMB2 instances that have the specified property set.
	 *
	 * @since  2.4.0
	 * @param  string $property Property name.
	 * @param  mixed  $compare  (Optional) The value to compare.
	 * @return WRMB2[]           Array of matching wrmb2 instances.
	 */
	public static function get_by( $property, $compare = 'nocompare' ) {
		$boxes = array();

		foreach ( self::$wrmb2_instances as $wrmb_id => $wrmb ) {
			$prop = $wrmb->prop( $property );

			if ( 'nocompare' === $compare ) {
				if ( ! empty( $prop ) ) {
					$boxes[ $wrmb_id ] = $wrmb;
				}
				continue;
			}

			if ( $compare === $prop ) {
				$boxes[ $wrmb_id ] = $wrmb;
			}
		}

		return $boxes;
	}

	/**
	 * Retrieve all WRMB2 instances as long as they do not include the ignored property.
	 *
	 * @since  2.4.0
	 * @param  string $property  Property name.
	 * @param  mixed  $to_ignore The value to ignore.
	 * @return WRMB2[]            Array of matching wrmb2 instances.
	 */
	public static function filter_by( $property, $to_ignore = null ) {
		$boxes = array();

		foreach ( self::$wrmb2_instances as $wrmb_id => $wrmb ) {

			if ( $to_ignore === $wrmb->prop( $property ) ) {
				continue;
			}

			$boxes[ $wrmb_id ] = $wrmb;
		}

		return $boxes;
	}

	/**
	 * Deprecated and left for back-compatibility. The original `get_by_property`
	 * method was misnamed and never actually used by WRMB2 core.
	 *
	 * @since  2.2.3
	 *
	 * @param  string $property  Property name.
	 * @param  mixed  $to_ignore The value to ignore.
	 * @return WRMB2[]            Array of matching wrmb2 instances.
	 */
	public static function get_by_property( $property, $to_ignore = null ) {
		_deprecated_function( __METHOD__, '2.4.0', 'WRMB2_Boxes::filter_by()' );
		return self::filter_by( $property );
	}
}
