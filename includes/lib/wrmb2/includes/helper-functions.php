<?php
/**
 * WRMB2 Helper Functions
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */

/**
 * Helper function to provide directory path to WRMB2
 *
 * @since  2.0.0
 * @param  string $path Path to append.
 * @return string        Directory with optional path appended
 */
function wrmb2_dir( $path = '' ) {
	return WRMB2_DIR . $path;
}

/**
 * Autoloads files with WRMB2 classes when needed
 *
 * @since  1.0.0
 * @param  string $class_name Name of the class being requested.
 */
function wrmb2_autoload_classes( $class_name ) {
	// phpcs:ignore
	if ( 0 !== strpos( $class_name, 'WRMB2' ) ) {
		return;
	}

	$path = 'includes';

	// phpcs:ignore
	if ( 'WRMB2_Type' === $class_name || 0 === strpos( $class_name, 'WRMB2_Type_' ) ) {
		$path .= '/types';
	}

	// phpcs:ignore
	if ( 'WRMB2_REST' === $class_name || 0 === strpos( $class_name, 'WRMB2_REST_' ) ) {
		$path .= '/rest-api';
	}

	// Converting it into lower case.
	$lowercase = strtolower( $class_name );
	// Replace underscores with hyphens.
	$result = str_replace( '_', '-', $lowercase );

	$class_name = 'class-' . $result;
	// phpcs:ignore
	include_once wrmb2_dir( "$path/{$class_name}.php" );
}

/**
 * Get instance of the WRMB2_Utils class
 *
 * @since  2.0.0
 * @return WRMB2_Utils object WRMB2 utilities class
 */
function wrmb2_utils() {
	static $wrmb2_utils;
	$wrmb2_utils = $wrmb2_utils ? $wrmb2_utils : new WRMB2_Utils();
	return $wrmb2_utils;
}

/**
 * Get instance of the WRMB2_Ajax class
 *
 * @since  2.0.0
 * @return WRMB2_Ajax object WRMB2 ajax class
 */
function wrmb2_ajax() {
	return WRMB2_Ajax::get_instance();
}

/**
 * Get instance of the WRMB2_Option class for the passed metabox ID
 *
 * @since  2.0.0
 *
 * @param string $key Option key to fetch.
 * @return WRMB2_Option object Options class for setting/getting options for metabox
 */
function wrmb2_options( $key ) {
	return WRMB2_Options::get( $key );
}

/**
 * Get a wrmb oEmbed. Handles oEmbed getting for non-post objects
 *
 * @since  2.0.0
 * @param  array $args Arguments. Accepts:
 *
 *       'url'         - URL to retrieve the oEmbed from,
 *       'object_id'   - $post_id,
 *       'object_type' - 'post',
 *       'oembed_args' - $embed_args, // array containing 'width', etc
 *       'field_id'    - false,
 *       'cache_key'   - false,
 *       'wp_error'    - true/false, // To return a wp_error object if no embed found.
 *
 * @return string        oEmbed string
 */
function wrmb2_get_oembed( $args = array() ) {
	$oembed = wrmb2_ajax()->get_oembed_no_edit( $args );

	// Send back our embed.
	if ( $oembed['embed'] && $oembed['embed'] != $oembed['fallback'] ) {
		return '<div class="wrmb2-oembed">' . $oembed['embed'] . '</div>';
	}

	$error = sprintf(
		/* translators: 1: results for. 2: link to codex.wordpress.org/Embeds */
		esc_html__( 'No oEmbed Results Found for %1$s. View more info at %2$s.', 'wrmb2' ),
		$oembed['fallback'],
		'<a href="https://wordpress.org/support/article/embeds/" target="_blank">codex.wordpress.org/Embeds</a>'
	);

	if ( isset( $args['wp_error'] ) && $args['wp_error'] ) {
		return new WP_Error( 'wrmb2_get_oembed_result', $error, compact( 'oembed', 'args' ) );
	}

	// Otherwise, send back error info that no oEmbeds were found.
	return '<p class="ui-state-error-text">' . $error . '</p>';
}

/**
 * Outputs the return of wrmb2_get_oembed.
 *
 * @since  2.2.2
 * @see wrmb2_get_oembed
 *
 * @param array $args oEmbed args.
 */
function wrmb2_do_oembed( $args = array() ) {
	echo esc_attr( wrmb2_get_oembed( $args ) );
}
add_action( 'wrmb2_do_oembed', 'wrmb2_do_oembed' );

/**
 * A helper function to get an option from a WRMB2 options array
 *
 * @since  1.0.1
 * @param  string $option_key Option key.
 * @param  string $field_id   Option array field key.
 * @param  mixed  $default    Optional default fallback value.
 * @return array               Options array or specific field
 */
function wrmb2_get_option( $option_key, $field_id = '', $default = false ) {
	return wrmb2_options( $option_key )->get( $field_id, $default );
}

/**
 * A helper function to update an option in a WRMB2 options array
 *
 * @since  2.0.0
 * @param  string  $option_key Option key.
 * @param  string  $field_id   Option array field key.
 * @param  mixed   $value      Value to update data with.
 * @param  boolean $single     Whether data should not be an array.
 * @return boolean             Success/Failure
 */
function wrmb2_update_option( $option_key, $field_id, $value, $single = true ) {
	if ( wrmb2_options( $option_key )->update( $field_id, $value, false, $single ) ) {
		return wrmb2_options( $option_key )->set();
	}

	return false;
}

/**
 * Get a WRMB2 field object.
 *
 * @since  1.1.0
 * @param  array  $meta_box    Metabox ID or Metabox config array.
 * @param  array  $field_id    Field ID or all field arguments.
 * @param  int    $object_id   Object ID.
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page).
 *                             Defaults to metabox object type.
 * @return WRMB2_Field|null     WRMB2_Field object unless metabox config cannot be found
 */
function wrmb2_get_field( $meta_box, $field_id, $object_id = 0, $object_type = '' ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$wrmb      = $meta_box instanceof WRMB2 ? $meta_box : wrmb2_get_metabox( $meta_box, $object_id );

	if ( ! $wrmb ) {
		return;
	}

	$wrmb->object_type( $object_type ? $object_type : $wrmb->mb_object_type() );

	return $wrmb->get_field( $field_id );
}

/**
 * Get a field's value.
 *
 * @since  1.1.0
 * @param  array  $meta_box    Metabox ID or Metabox config array.
 * @param  array  $field_id    Field ID or all field arguments.
 * @param  int    $object_id   Object ID.
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page).
 *                             Defaults to metabox object type.
 * @return mixed               Maybe escaped value
 */
function wrmb2_get_field_value( $meta_box, $field_id, $object_id = 0, $object_type = '' ) {
	$field = wrmb2_get_field( $meta_box, $field_id, $object_id, $object_type );
	return $field->escaped_value();
}

/**
 * Because OOP can be scary
 *
 * @since  2.0.2
 * @param  array $meta_box_config Metabox Config array.
 * @return WRMB2 object            Instantiated WRMB2 object
 */
function new_wrmb2_box( array $meta_box_config ) {
	return wrmb2_get_metabox( $meta_box_config );
}

/**
 * Retrieve a WRMB2 instance by the metabox ID
 *
 * @since  2.0.0
 * @param  mixed  $meta_box    Metabox ID or Metabox config array.
 * @param  int    $object_id   Object ID.
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page).
 *                             Defaults to metabox object type.
 * @return WRMB2 object
 */
function wrmb2_get_metabox( $meta_box, $object_id = 0, $object_type = '' ) {

	if ( $meta_box instanceof WRMB2 ) {
		return $meta_box;
	}

	if ( is_string( $meta_box ) ) {
		$wrmb = WRMB2_Boxes::get( $meta_box );
	} else {
		// See if we already have an instance of this metabox.
		$wrmb = WRMB2_Boxes::get( $meta_box['id'] );
		// If not, we'll initate a new metabox.
		$wrmb = $wrmb ? $wrmb : new WRMB2( $meta_box, $object_id );
	}

	if ( $wrmb && $object_id ) {
		$wrmb->object_id( $object_id );
	}

	if ( $wrmb && $object_type ) {
		$wrmb->object_type( $object_type );
	}

	return $wrmb;
}

/**
 * Returns array of sanitized field values from a metabox (without saving them)
 *
 * @since  2.0.3
 * @param  mixed $meta_box         Metabox ID or Metabox config array.
 * @param  array $data_to_sanitize Array of field_id => value data for sanitizing (likely $_POST data).
 * @return mixed                   Array of sanitized values or false if no WRMB2 object found
 */
function wrmb2_get_metabox_sanitized_values( $meta_box, array $data_to_sanitize ) {
	$wrmb = wrmb2_get_metabox( $meta_box );
	return $wrmb ? $wrmb->get_sanitized_values( $data_to_sanitize ) : false;
}

/**
 * Retrieve a metabox form
 *
 * @since  2.0.0
 * @param  mixed $meta_box  Metabox config array or Metabox ID.
 * @param  int   $object_id Object ID.
 * @param  array $args      Optional arguments array.
 * @return string             WRMB2 html form markup
 */
function wrmb2_get_metabox_form( $meta_box, $object_id = 0, $args = array() ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$wrmb      = wrmb2_get_metabox( $meta_box, $object_id );

	ob_start();
	// Get wrmb form.
	wrmb2_print_metabox_form( $wrmb, $object_id, $args );
	$form = ob_get_clean();

	return apply_filters( 'wrmb2_get_metabox_form', $form, $object_id, $wrmb );
}

/**
 * Display a metabox form & save it on submission
 *
 * @since  1.0.0
 * @param  mixed $meta_box  Metabox config array or Metabox ID.
 * @param  int   $object_id Object ID.
 * @param  array $args      Optional arguments array.
 */
function wrmb2_print_metabox_form( $meta_box, $object_id = 0, $args = array() ) {

	$object_id = $object_id ? $object_id : get_the_ID();
	$wrmb      = wrmb2_get_metabox( $meta_box, $object_id );

	// if passing a metabox ID, and that ID was not found.
	if ( ! $wrmb ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		array(
			'form_format' => '<form class="wrmb-form" method="post" id="%1$s" enctype="multipart/form-data" encoding="multipart/form-data"><input type="hidden" name="object_id" value="%2$s">%3$s<input type="submit" name="submit-wrmb" value="%4$s" class="button-primary"></form>',
			'save_button' => esc_html__( 'Save', 'wrmb2' ),
			'object_type' => $wrmb->mb_object_type(),
			'wrmb_styles' => $wrmb->prop( 'wrmb_styles' ),
			'enqueue_js'  => $wrmb->prop( 'enqueue_js' ),
		)
	);

	// Set object type explicitly (rather than trying to guess from context).
	$wrmb->object_type( $args['object_type'] );

	// Save the metabox if it's been submitted
	// check permissions
	// @todo more hardening?
	if (
		$wrmb->prop( 'save_fields' )
		// check nonce.
		&& isset( $_POST['submit-wrmb'], $_POST['object_id'], $_POST[ $wrmb->nonce() ] )
		&& wp_verify_nonce( wp_kses_post( wp_unslash( $_POST[ $wrmb->nonce() ] ) ), $wrmb->nonce() )
		&& $object_id && $_POST['object_id'] == $object_id
	) {
		$wrmb->save_fields( $object_id, $wrmb->object_type(), $_POST );
	}

	// Enqueue JS/CSS.
	if ( $args['wrmb_styles'] ) {
		WRMB2_Hookup::enqueue_wrmb_css();
	}

	if ( $args['enqueue_js'] ) {
		WRMB2_Hookup::enqueue_wrmb_js();
	}

	$form_format = apply_filters( 'wrmb2_get_metabox_form_format', $args['form_format'], $object_id, $wrmb );

	$format_parts = explode( '%3$s', $form_format );

	// Show wrmb form.
	printf( esc_attr( $format_parts[0] ), esc_attr( $wrmb->wrmb_id ), esc_attr( $object_id ) );
	$wrmb->show_form();

	if ( isset( $format_parts[1] ) && $format_parts[1] ) {
		printf( esc_attr( str_ireplace( '%4$s', '%1$s', $format_parts[1] ) ), esc_attr( $args['save_button'] ) );
	}
}

/**
 * Display a metabox form (or optionally return it) & save it on submission.
 *
 * @since  1.0.0
 * @param  mixed $meta_box  Metabox config array or Metabox ID.
 * @param  int   $object_id Object ID.
 * @param  array $args      Optional arguments array.
 * @return string
 */
function wrmb2_metabox_form( $meta_box, $object_id = 0, $args = array() ) {
	if ( ! isset( $args['echo'] ) || $args['echo'] ) {
		wrmb2_print_metabox_form( $meta_box, $object_id, $args );
	} else {
		return wrmb2_get_metabox_form( $meta_box, $object_id, $args );
	}
}

if ( ! function_exists( 'date_create_from_format' ) ) {

	/**
	 * Reimplementation of DateTime::createFromFormat for PHP < 5.3. :(
	 * Borrowed from http://stackoverflow.com/questions/5399075/php-datetimecreatefromformat-in-5-2
	 *
	 * @param string $date_format Date format.
	 * @param string $date_value  Date value.
	 *
	 * @return DateTime
	 */
	function date_create_from_format( $date_format, $date_value ) {

		$schedule_format = str_replace(
			array( 'M', 'Y', 'm', 'd', 'H', 'i', 'a' ),
			array( '%b', '%Y', '%m', '%d', '%H', '%M', '%p' ),
			$date_format
		);

		/*
		 * %Y, %m and %d correspond to date()'s Y m and d.
		 * %I corresponds to H, %M to i and %p to a
		 */
		$parsed_time = strptime( $date_value, $schedule_format );

		$ymd = sprintf(
			/**
			 * This is a format string that takes six total decimal
			 * arguments, then left-pads them with zeros to either
			 * 4 or 2 characters, as needed
			 */
			'%04d-%02d-%02d %02d:%02d:%02d',
			$parsed_time['tm_year'] + 1900,  // This will be "111", so we need to add 1900.
			$parsed_time['tm_mon'] + 1,      // This will be the month minus one, so we add one.
			$parsed_time['tm_mday'],
			$parsed_time['tm_hour'],
			$parsed_time['tm_min'],
			$parsed_time['tm_sec']
		);

		return new DateTime( $ymd );
	}
}// End if.

if ( ! function_exists( 'date_timestamp_get' ) ) {

	/**
	 * Returns the Unix timestamp representing the date.
	 * Reimplementation of DateTime::getTimestamp for PHP < 5.3. :(
	 *
	 * @param DateTime $date DateTime instance.
	 *
	 * @return int
	 */
	function date_timestamp_get( DateTime $date ) {
		return $date->format( 'U' );
	}
}// End if.
