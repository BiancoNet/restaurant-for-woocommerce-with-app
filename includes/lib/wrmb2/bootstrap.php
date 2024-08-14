<?php
/**
 * Bootstraps the WRMB2 process
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */

/**
 * Function to encapsulate the WRMB2 bootstrap process.
 *
 * @since  2.2.0
 * @return void
 */
function wrmb2_bootstrap() {

	if ( is_admin() ) {
		/**
		 * Fires on the admin side when WRMB2 is included/loaded.
		 *
		 * In most cases, this should be used to add metaboxes. See example-functions.php
		 */
		do_action( 'wrmb2_admin_init' );
	}

	/**
	 * Fires when WRMB2 is included/loaded
	 *
	 * Can be used to add metaboxes if needed on the front-end or WP-API (or the front and backend).
	 */
	do_action( 'wrmb2_init' );

	/**
	 * For back-compat. Does the dirty-work of instantiating all the
	 * WRMB2 instances for the wrmb2_meta_boxes filter
	 *
	 * @since  2.0.2
	 */
	$wrmb_config_arrays = apply_filters( 'wrmb2_meta_boxes', array() );
	foreach ( (array) $wrmb_config_arrays as $wrmb_config ) {
		new WRMB2( $wrmb_config );
	}

	/**
	 * Fires after all WRMB2 instances are created
	 */
	do_action( 'wrmb2_init_before_hookup' );

	/**
	 * Get all created metaboxes, and instantiate WRMB2_Hookup
	 * on metaboxes which require it.
	 *
	 * @since  2.0.2
	 */
	foreach ( WRMB2_Boxes::get_all() as $wrmb ) {

		/**
		 * Initiates the box "hookup" into WordPress.
		 *
		 * Unless the 'hookup' box property is `false`, the box will be hooked in as
		 * a post/user/comment/option/term box.
		 *
		 * And if the 'show_in_rest' box property is set, the box will be hooked
		 * into the WRMB2 REST API.
		 *
		 * The dynamic portion of the hook name, $wrmb->wrmb_id, is the box id.
		 *
		 * @since 2.2.6
		 *
		 * @param array $wrmb The WRMB2 object to hookup.
		 */
		do_action( "wrmb2_init_hookup_{$wrmb->wrmb_id}", $wrmb );
	}

	/**
	 * Fires after WRMB2 initiation process has been completed
	 */
	do_action( 'wrmb2_after_init' );
}

/* End. That's it, folks! */
