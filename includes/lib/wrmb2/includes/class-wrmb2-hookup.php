<?php
/**
 * It is the WRMB2 hookup file.
 *
 * @package woorestaurant
 */

/**
 * Change name.
 * Handles hooking WRMB2 forms/metaboxes into the post/attachement/user screens
 * and handles hooking in and saving those fields.
 *
 * @since  2.0.0
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Hookup extends WRMB2_Hookup_Base {

	/**
	 * Only allow JS registration once
	 *
	 * @var   bool
	 * @since 2.0.7
	 */
	protected static $js_registration_done = false;

	/**
	 * Only allow CSS registration once
	 *
	 * @var   bool
	 * @since 2.0.7
	 */
	protected static $css_registration_done = false;

	/**
	 * WRMB taxonomies array for term meta
	 *
	 * @var   array
	 * @since 2.2.0
	 */
	protected $taxonomies = array();

	/**
	 * Custom field columns.
	 *
	 * @var   array
	 * @since 2.2.2
	 */
	protected $columns = array();

	/**
	 * Array of WRMB2_Options_Hookup instances if options page metabox.
	 *
	 * @var   WRMB2_Options_Hookup[]|null
	 * @since 2.2.5
	 */
	protected $options_hookup = null;

	/**
	 * A functionalized constructor, used for the hookup action callbacks.
	 *
	 * @since  2.2.6
	 *
	 * @param  WRMB2 $wrmb The WRMB2 object to hookup.
	 *
	 * @return WRMB2_Hookup_Base $hookup The hookup object.
	 */
	public static function maybe_init_and_hookup( WRMB2 $wrmb ) {
		if ( $wrmb->prop( 'hookup' ) ) {

			$hookup = new self( $wrmb );

			// Hook in the hookup... how meta.
			return $hookup->universal_hooks();
		}

		return false;
	}

	/**
	 * It is the Universal Hook method.
	 */
	public function universal_hooks() {
		foreach ( get_class_methods( 'WRMB2_Show_Filters' ) as $filter ) {
			add_filter( 'wrmb2_show_on', array( 'WRMB2_Show_Filters', $filter ), 10, 3 );
		}
		$this->once( 'admin_enqueue_scripts', array( __CLASS__, 'register_scripts' ), 8 );
			$this->once( 'admin_enqueue_scripts', array( $this, 'do_scripts' ) );
		if ( is_admin() ) {
			// Register our scripts and styles for wrmb.
			$this->once( 'admin_enqueue_scripts', array( __CLASS__, 'register_scripts' ), 8 );
			$this->once( 'admin_enqueue_scripts', array( $this, 'do_scripts' ) );

			$this->maybe_enqueue_column_display_styles();

			switch ( $this->object_type ) {
				case 'post':
					return $this->post_hooks();
				case 'comment':
					return $this->comment_hooks();
				case 'user':
					return $this->user_hooks();
				case 'term':
					return $this->term_hooks();
				case 'options-page':
					return $this->options_page_hooks();
			}
		}

		return $this;
	}

	/**
	 * It is the post hooks method.
	 */
	public function post_hooks() {

		// Fetch the context we set in our call.
		$context = $this->wrmb->prop( 'context' ) ? $this->wrmb->prop( 'context' ) : 'normal';

		// Call the proper hook based on the context provided.
		switch ( $context ) {

			case 'form_top':
				add_action( 'edit_form_top', array( $this, 'add_context_metaboxes' ) );
				break;

			case 'before_permalink':
				add_action( 'edit_form_before_permalink', array( $this, 'add_context_metaboxes' ) );
				break;

			case 'after_title':
				add_action( 'edit_form_after_title', array( $this, 'add_context_metaboxes' ) );
				break;

			case 'after_editor':
				add_action( 'edit_form_after_editor', array( $this, 'add_context_metaboxes' ) );
				break;

			default:
				add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
		}

		add_action( 'add_meta_boxes', array( $this, 'remove_default_tax_metaboxes' ) );
		add_action( 'add_attachment', array( $this, 'save_post' ) );
		add_action( 'edit_attachment', array( $this, 'save_post' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		if ( $this->wrmb->has_columns ) {
			foreach ( $this->wrmb->box_types() as $post_type ) {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'register_column_headers' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'column_display' ), 10, 2 );
				add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'columns_sortable' ) );
				add_action( 'pre_get_posts', array( $this, 'columns_sortable_orderby' ) );
			}
		}

		return $this;
	}

	/**
	 * It is the comment hooks method.
	 */
	public function comment_hooks() {
		add_action( 'add_meta_boxes_comment', array( $this, 'add_metaboxes' ) );
		add_action( 'edit_comment', array( $this, 'save_comment' ) );

		if ( $this->wrmb->has_columns ) {
			add_filter( 'manage_edit-comments_columns', array( $this, 'register_column_headers' ) );
			add_action( 'manage_comments_custom_column', array( $this, 'column_display' ), 10, 3 );
			add_filter( 'manage_edit-comments_sortable_columns', array( $this, 'columns_sortable' ) );
			add_action( 'pre_get_posts', array( $this, 'columns_sortable_orderby' ) );
		}

		return $this;
	}

	/**
	 * It is the user hooks method.
	 */
	public function user_hooks() {
		$priority = $this->get_priority();

		add_action( 'show_user_profile', array( $this, 'user_metabox' ), $priority );
		add_action( 'edit_user_profile', array( $this, 'user_metabox' ), $priority );
		add_action( 'user_new_form', array( $this, 'user_new_metabox' ), $priority );

		add_action( 'personal_options_update', array( $this, 'save_user' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
		add_action( 'user_register', array( $this, 'save_user' ) );

		if ( $this->wrmb->has_columns ) {
			add_filter( 'manage_users_columns', array( $this, 'register_column_headers' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'return_column_display' ), 10, 3 );
			add_filter( 'manage_users_sortable_columns', array( $this, 'columns_sortable' ) );
			add_action( 'pre_get_posts', array( $this, 'columns_sortable_orderby' ) );
		}

		return $this;
	}

	/**
	 * It is the term hooks method.
	 */
	public function term_hooks() {
		if ( ! function_exists( 'get_term_meta' ) ) {
			wp_die( esc_html__( 'Term Metadata is a WordPress 4.4+ feature. Please upgrade your WordPress install.', 'wrmb2' ) );
		}

		if ( ! $this->wrmb->prop( 'taxonomies' ) ) {
			wp_die( esc_html__( 'Term metaboxes configuration requires a "taxonomies" parameter.', 'wrmb2' ) );
		}

		$this->taxonomies = (array) $this->wrmb->prop( 'taxonomies' );
		$show_on_term_add = $this->wrmb->prop( 'new_term_section' );
		$priority         = $this->get_priority( 8 );

		foreach ( $this->taxonomies as $taxonomy ) {
			// Display our form data.
			add_action( "{$taxonomy}_edit_form", array( $this, 'term_metabox' ), $priority, 2 );

			$show_on_add = is_array( $show_on_term_add )
				? in_array( $taxonomy, $show_on_term_add )
				: (bool) $show_on_term_add;

			/**
			 * Filter to determine if the term's fields should show in the "Add term" section.
			 *
			 * The dynamic portion of the hook name, $wrmb_id, is the metabox id.
			 *
			 * @param bool   $show_on_add Default is the value of the new_term_section wrmb parameter.
			 * @param object $wrmb         The WRMB2 instance
			 */
			$show_on_add = apply_filters( "wrmb2_show_on_term_add_form_{$this->wrmb->wrmb_id}", $show_on_add, $this->wrmb );

			// Display form in add-new section (unless specified not to).
			if ( $show_on_add ) {
				add_action( "{$taxonomy}_add_form_fields", array( $this, 'term_metabox' ), $priority, 2 );
			}

			if ( $this->wrmb->has_columns ) {
				add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'register_column_headers' ) );
				add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'return_column_display' ), 10, 3 );
				add_filter( "manage_edit-{$taxonomy}_sortable_columns", array( $this, 'columns_sortable' ) );
				add_action( 'pre_get_posts', array( $this, 'columns_sortable_orderby' ) );
			}
		}

		add_action( 'created_term', array( $this, 'save_term' ), 10, 3 );
		add_action( 'edited_terms', array( $this, 'save_term' ), 10, 2 );
		add_action( 'delete_term', array( $this, 'delete_term' ), 10, 3 );

		return $this;
	}

	/**
	 * It is the option page hooks method.
	 */
	public function options_page_hooks() {
		$option_keys = $this->wrmb->options_page_keys();

		if ( ! empty( $option_keys ) ) {
			foreach ( $option_keys as $option_key ) {
				$this->options_hookup[ $option_key ] = new WRMB2_Options_Hookup( $this->wrmb, $option_key );
				$this->options_hookup[ $option_key ]->hooks();
			}
		}

		return $this;
	}

	/**
	 * Registers styles for WRMB2
	 *
	 * @since 2.0.7
	 */
	protected static function register_styles() {
		if ( self::$css_registration_done ) {
			return;
		}

		// Only use minified files if SCRIPT_DEBUG is off.
		$min   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$front = is_admin() ? '' : '-front';
		$rtl   = is_rtl() ? '-rtl' : '';

		/**
		 * Filters the registered style dependencies for the wrmb2 stylesheet.
		 *
		 * @param array $dependencies The registered style dependencies for the wrmb2 stylesheet.
		 */
		$dependencies = apply_filters( 'wrmb2_style_dependencies', array() );

		wp_register_style( 'woo-restaurant-admin-css', plugins_url( '/assets/admin/css/admin.css', WOORESTAURANT_FILE ), $dependencies, WOORESTAURANT_VERSION . time() );
		wp_register_style( 'wrmb2-styles', WRMB2_Utils::url( "css/wrmb2{$front}{$rtl}{$min}.css" ), $dependencies );
		wp_register_style( 'wrmb2-display-styles', WRMB2_Utils::url( "css/wrmb2-display{$rtl}{$min}.css" ), $dependencies );

		self::$css_registration_done = true;
	}

	/**
	 * Registers scripts for WRMB2
	 *
	 * @since  2.0.7
	 */
	protected static function register_js() {
		if ( self::$js_registration_done ) {
			return;
		}

		$hook = is_admin() ? 'admin_footer' : 'wp_footer';
		add_action( $hook, array( 'WRMB2_JS', 'enqueue' ), 8 );

		self::$js_registration_done = true;
	}

	/**
	 * Registers scripts and styles for WRMB2
	 *
	 * @since  1.0.0
	 */
	public static function register_scripts() {
		self::register_styles();
		self::register_js();
	}

	/**
	 * Enqueues scripts and styles for WRMB2 in admin_head.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Current hook for the admin page.
	 */
	public function do_scripts( $hook ) {
		$hooks = array(
			'post.php',
			'post-new.php',
			'page-new.php',
			'page.php',
			'comment.php',
			'edit-tags.php',
			'term.php',
			'user-new.php',
			'profile.php',
			'user-edit.php',
		);
		// only pre-enqueue our scripts/styles on the proper pages
		// show_form_for_type will have us covered if we miss something here.
		if ( in_array( $hook, $hooks, true ) ) {
			if ( $this->wrmb->prop( 'wrmb_styles' ) ) {
				self::enqueue_wrmb_css();
			}
			if ( $this->wrmb->prop( 'enqueue_js' ) ) {
				self::enqueue_wrmb_js();
			}
		}
	}

	/**
	 * Register the WRMB2 field column headers.
	 *
	 * @since 2.2.2
	 *
	 * @param array $columns Array of columns available for the admin page.
	 */
	public function register_column_headers( $columns ) {
		foreach ( $this->wrmb->prop( 'fields' ) as $key => $field ) {
			if ( empty( $field['column'] ) ) {
				continue;
			}

			$column = $field['column'];

			if ( false === $column['position'] ) {
				$columns[ $field['id'] ] = $column['name'];
			} else {
				$before                 = array_slice( $columns, 0, absint( $column['position'] ) );
				$before[ $field['id'] ] = $column['name'];
				$columns                = $before + $columns;
			}

			$column['field']               = $field;
			$this->columns[ $field['id'] ] = $column;
		}

		return $columns;
	}

	/**
	 * The WRMB2 field column display output.
	 *
	 * @since 2.2.2
	 *
	 * @param string $column_name Current column name.
	 * @param mixed  $object_id Current object ID.
	 */
	public function column_display( $column_name, $object_id ) {
		if ( isset( $this->columns[ $column_name ] ) ) {
			$field = new WRMB2_Field(
				array(
					'field_args'  => $this->columns[ $column_name ]['field'],
					'object_type' => $this->object_type,
					'object_id'   => $this->wrmb->object_id( $object_id ),
					'wrmb_id'     => $this->wrmb->wrmb_id,
				)
			);

			$this->wrmb->get_field( $field )->render_column();
		}
	}

	/**
	 * Returns the columns sortable array.
	 *
	 * @since 2.6.1
	 *
	 * @param array $columns An array of sortable columns.
	 *
	 * @return array $columns An array of sortable columns with WRMB2 columns.
	 */
	public function columns_sortable( $columns ) {
		foreach ( $this->wrmb->prop( 'fields' ) as $key => $field ) {
			if ( ! empty( $field['column'] ) && empty( $field['column']['disable_sortable'] ) ) {
				$columns[ $field['id'] ] = $field['id'];
			}
		}

		return $columns;
	}

	/**
	 * Return the query object to order by custom columns if selected.
	 *
	 * @since 2.6.1
	 *
	 * @param object $query Object query from WordPress.
	 *
	 * @return void
	 */
	public function columns_sortable_orderby( $query ) {
		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		foreach ( $this->wrmb->prop( 'fields' ) as $key => $field ) {
			if (
				empty( $field['column'] )
				|| ! empty( $field['column']['disable_sortable'] )
				|| $field['id'] !== $orderby
			) {
				continue;
			}

			$query->set( 'meta_key', $field['id'] );

			$type = $field['type'];

			if ( ! empty( $field['attributes']['type'] ) ) {
				switch ( $field['attributes']['type'] ) {
					case 'number':
					case 'date':
						$type = $field['attributes']['type'];
						break;
					case 'range':
						$type = 'number';
						break;
				}
			}

			switch ( $type ) {
				case 'number':
				case 'text_date_timestamp':
				case 'text_datetime_timestamp':
				case 'text_money':
					$query->set( 'orderby', 'meta_value_num' );
					break;
				case 'text_time':
					$query->set( 'orderby', 'meta_value_time' );
					break;
				case 'text_date':
					$query->set( 'orderby', 'meta_value_date' );
					break;

				default:
					$query->set( 'orderby', 'meta_value' );
					break;
			}
		}
	}

	/**
	 * Returns the column display.
	 *
	 * @param string $empty It is the empty variable.
	 * @param string $custom_column It is the custom column.
	 * @param int    $object_id It is the object id.
	 *
	 * @since 2.2.2
	 */
	public function return_column_display( $empty, $custom_column, $object_id ) {
		ob_start();
		$this->column_display( $custom_column, $object_id );
		$column = ob_get_clean();

		return $column ? $column : $empty;
	}

	/**
	 * Output the WRMB2 box/fields in an alternate context (not in a standard metabox area).
	 *
	 * @since 2.2.4
	 */
	public function add_context_metaboxes() {

		if ( ! $this->show_on() ) {
			return;
		}

		$page = get_current_screen()->id;

		foreach ( $this->wrmb->box_types() as $object_type ) {
			$screen = convert_to_screen( $object_type );

			// If we're on the right post-type/object...
			if ( isset( $screen->id ) && $screen->id === $page ) {

				// Show the box.
				$this->output_context_metabox();
			}
		}
	}

	/**
	 * Output the WRMB2 box/fields in an alternate context (not in a standard metabox area).
	 *
	 * @since 2.2.4
	 */
	public function output_context_metabox() {
		$title = $this->wrmb->prop( 'title' );

		/*
		 * To keep from outputting the open/close markup, do not include
		 * a 'title' property in your metabox registration array.
		 *
		 * To output the fields 'naked' (without a postbox wrapper/style), then
		 * add a `'remove_box_wrap' => true` to your metabox registration array.
		 */
		$add_wrap   = ! empty( $title ) || ! $this->wrmb->prop( 'remove_box_wrap' );
		$add_handle = $add_wrap && ! empty( $title );

		// Open the context-box wrap.
		$this->context_box_title_markup_open( $add_handle );

		// Show the form fields.
		$this->wrmb->show_form();

		// Close the context-box wrap.
		$this->context_box_title_markup_close( $add_handle );
	}

	/**
	 * Output the opening markup for a context box.
	 *
	 * @since 2.2.4
	 * @param bool $add_handle Whether to add the metabox handle and opening div for .inside.
	 */
	public function context_box_title_markup_open( $add_handle = true ) {
		$title = $this->wrmb->prop( 'title' );

		$page = get_current_screen()->id;
		add_filter( "postbox_classes_{$page}_{$this->wrmb->wrmb_id}", array( $this, 'postbox_classes' ) );

		echo '<div id="' . esc_attr( $this->wrmb->wrmb_id ) . '" class="' . esc_attr( postbox_classes( $this->wrmb->wrmb_id, $page ) ) . '">' . "\n";

		if ( $add_handle ) {

			echo '<button type="button" class="handlediv button-link" aria-expanded="true">';
				/* translators: %s is replaced with the title */
				echo '<span class="screen-reader-text">' . sprintf( esc_attr__( 'Toggle panel: %s' ), esc_attr( $title ) ) . '</span>';
				echo '<span class="toggle-indicator" aria-hidden="true"></span>';
			echo '</button>';

			echo '<h2 class="hndle"><span>' . esc_attr( $title ) . '</span></h2>' . "\n";
			echo '<div class="inside">' . "\n";
		}
	}

	/**
	 * Output the closing markup for a context box.
	 *
	 * @since 2.2.4
	 * @param bool $add_inside_close Whether to add closing div for .inside.
	 */
	public function context_box_title_markup_close( $add_inside_close = true ) {

		// Load the closing divs for a title box.
		if ( $add_inside_close ) {
			echo '</div>' . "\n"; // .inside
		}

		echo '</div>' . "\n"; // .context-box
	}

	/**
	 * Add metaboxes (to 'post' or 'comment' object types)
	 *
	 * @since 1.0.0
	 */
	public function add_metaboxes() {

		if ( ! $this->show_on() ) {
			return;
		}

		/*
		 * To keep from registering an actual post-screen metabox,
		 * omit the 'title' property from the metabox registration array.
		 *
		 * (WordPress will not display metaboxes without titles anyway)
		 *
		 * This is a good solution if you want to handle outputting your
		 * metaboxes/fields elsewhere in the post-screen.
		 */
		if ( ! $this->wrmb->prop( 'title' ) ) {
			return;
		}

		$page = get_current_screen()->id;
		add_filter( "postbox_classes_{$page}_{$this->wrmb->wrmb_id}", array( $this, 'postbox_classes' ) );

		foreach ( $this->wrmb->box_types() as $object_type ) {
			add_meta_box(
				$this->wrmb->wrmb_id,
				$this->wrmb->prop( 'title' ),
				array( $this, 'metabox_callback' ),
				$object_type,
				$this->wrmb->prop( 'context' ),
				$this->wrmb->prop( 'priority' ),
				$this->wrmb->prop( 'mb_callback_args' )
			);
		}
	}

	/**
	 * Remove the specified default taxonomy metaboxes for a post-type.
	 *
	 * @since 2.2.3
	 */
	public function remove_default_tax_metaboxes() {
		$to_remove = array_filter( (array) $this->wrmb->tax_metaboxes_to_remove, 'taxonomy_exists' );
		if ( empty( $to_remove ) ) {
			return;
		}

		foreach ( $this->wrmb->box_types() as $post_type ) {
			foreach ( $to_remove as $taxonomy ) {
				$mb_id = is_taxonomy_hierarchical( $taxonomy ) ? "{$taxonomy}div" : "tagsdiv-{$taxonomy}";
				remove_meta_box( $mb_id, $post_type, 'side' );
			}
		}
	}

	/**
	 * Modify metabox postbox classes.
	 *
	 * @since 2.2.4
	 * @param  array $classes Array of classes.
	 * @return array           Modified array of classes
	 */
	public function postbox_classes( $classes ) {
		if ( $this->wrmb->prop( 'closed' ) && ! in_array( 'closed', $classes ) ) {
			$classes[] = 'closed';
		}

		if ( $this->wrmb->is_alternate_context_box() ) {
			$classes = $this->alternate_context_postbox_classes( $classes );
		} else {
			$classes[] = 'wrmb2-postbox';
		}

		return $classes;
	}

	/**
	 * Modify metabox altnernate context postbox classes.
	 *
	 * @since 2.2.4
	 * @param  array $classes Array of classes.
	 * @return array           Modified array of classes
	 */
	protected function alternate_context_postbox_classes( $classes ) {
		$classes[] = 'context-box';
		$classes[] = 'context-' . $this->wrmb->prop( 'context' ) . '-box';

		if ( in_array( $this->wrmb->wrmb_id, get_hidden_meta_boxes( get_current_screen() ) ) ) {
			$classes[] = 'hide-if-js';
		}

		$add_wrap = $this->wrmb->prop( 'title' ) || ! $this->wrmb->prop( 'remove_box_wrap' );

		if ( $add_wrap ) {
			$classes[] = 'wrmb2-postbox postbox';
		} else {
			$classes[] = 'wrmb2-no-box-wrap';
		}

		return $classes;
	}

	/**
	 * Display metaboxes for a post or comment object.
	 *
	 * @since  1.0.0
	 */
	public function metabox_callback() {
		$object_id = 'comment' === $this->object_type ? get_comment_ID() : get_the_ID();
		$this->wrmb->show_form( $object_id, $this->object_type );
	}

	/**
	 * Display metaboxes for new user page.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $section User section metabox.
	 */
	public function user_new_metabox( $section ) {
		if ( $section === $this->wrmb->prop( 'new_user_section' ) ) {
			$object_id = $this->wrmb->object_id();
			$this->wrmb->object_id( isset( $_REQUEST['user_id'] ) ? wp_kses_post( wp_unslash( $_REQUEST['user_id'] ) ) : $object_id );
			$this->user_metabox();
		}
	}

	/**
	 * Display metaboxes for a user object.
	 *
	 * @since  1.0.0
	 */
	public function user_metabox() {
		$this->show_form_for_type( 'user' );
	}

	/**
	 * Display metaboxes for a taxonomy term object.
	 *
	 * @since  2.2.0
	 */
	public function term_metabox() {
		$this->show_form_for_type( 'term' );
	}

	/**
	 * Display metaboxes for an object type.
	 *
	 * @since 2.2.0
	 * @param  string $type Object type.
	 * @return void
	 */
	public function show_form_for_type( $type ) {
		if ( $type != $this->object_type ) {
			return;
		}

		if ( ! $this->show_on() ) {
			return;
		}

		if ( $this->wrmb->prop( 'wrmb_styles' ) ) {
			self::enqueue_wrmb_css();
		}
		if ( $this->wrmb->prop( 'enqueue_js' ) ) {
			self::enqueue_wrmb_js();
		}
		$this->wrmb->show_form( 0, $type );
	}

	/**
	 * Determines if metabox should be shown in current context.
	 *
	 * @since 2.0.0
	 * @return bool Whether metabox should be added/shown.
	 */
	public function show_on() {
		// If metabox is requesting to be conditionally shown.
		$show = $this->wrmb->should_show();

		/**
		 * Filter to determine if metabox should show. Default is true.
		 *
		 * @param array  $show          Default is true, show the metabox.
		 * @param mixed  $meta_box_args Array of the metabox arguments.
		 * @param mixed  $wrmb           The WRMB2 instance.
		 */
		$show = (bool) apply_filters( 'wrmb2_show_on', $show, $this->wrmb->meta_box, $this->wrmb );

		return $show;
	}

	/**
	 * Get the WRMB priority property set to numeric hook priority.
	 *
	 * @since 2.2.0
	 *
	 * @param integer $default Default display hook priority.
	 * @return integer          Hook priority.
	 */
	public function get_priority( $default = 10 ) {
		$priority = $this->wrmb->prop( 'priority' );

		if ( ! is_numeric( $priority ) ) {
			switch ( $priority ) {

				case 'high':
					$priority = 5;
					break;

				case 'low':
					$priority = 20;
					break;

				default:
					$priority = $default;
					break;
			}
		}

		return $priority;
	}

	/**
	 * Save data from post metabox
	 *
	 * @since 1.0.0
	 * @param  int   $post_id Post ID.
	 * @param  mixed $post    Post object.
	 * @return void
	 */
	public function save_post( $post_id, $post = false ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'save-post' ) ) {
				return;
			}
		}

		$post_type = $post ? $post->post_type : get_post_type( $post_id );

		$do_not_pass_go = (
			! $this->can_save( $post_type )
			// Check user editing permissions.
			|| ( 'page' === $post_type && ! current_user_can( 'edit_page', $post_id ) )
			|| ! current_user_can( 'edit_post', $post_id )
		);

		if ( $do_not_pass_go ) {
			return;
		}

		$this->wrmb->save_fields( $post_id, 'post', $_POST );
	}

	/**
	 * Save data from comment metabox.
	 *
	 * @since 2.0.9
	 * @param  int $comment_id Comment ID.
	 * @return void
	 */
	public function save_comment( $comment_id ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'save-comment' ) ) {
				return;
			}
		}

		$can_edit = current_user_can( 'moderate_comments', $comment_id );

		if ( $this->can_save( get_comment_type( $comment_id ) ) && $can_edit ) {
			$this->wrmb->save_fields( $comment_id, 'comment', $_POST );
		}
	}

	/**
	 * Save data from user fields.
	 *
	 * @since 1.0.x
	 * @param  int $user_id User ID.
	 * @return void
	 */
	public function save_user( $user_id ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'save-user' ) ) {
				return;
			}
		}

		// check permissions.
		if ( $this->can_save( 'user' ) ) {
			$this->wrmb->save_fields( $user_id, 'user', $_POST );
		}
	}

	/**
	 * Save data from term fields
	 *
	 * @since 2.2.0
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term Taxonomy ID.
	 * @param string $taxonomy Taxonomy.
	 * @return void
	 */
	public function save_term( $term_id, $tt_id, $taxonomy = '' ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'save-term' ) ) {
				return;
			}
		}

		$taxonomy = $taxonomy ? $taxonomy : $tt_id;

		// check permissions.
		if ( $this->taxonomy_can_save( $taxonomy ) && $this->can_save( 'term' ) ) {
			$this->wrmb->save_fields( $term_id, 'term', $_POST );
		}
	}

	/**
	 * Delete term meta when a term is deleted.
	 *
	 * @since 2.2.0
	 * @param  int    $term_id  Term ID.
	 * @param  int    $tt_id    Term Taxonomy ID.
	 * @param  string $taxonomy Taxonomy.
	 * @return void
	 */
	public function delete_term( $term_id, $tt_id, $taxonomy = '' ) {
		if ( $this->taxonomy_can_save( $taxonomy ) ) {

			$data_to_delete = array();
			foreach ( $this->wrmb->prop( 'fields' ) as $field ) {
				$data_to_delete[ $field['id'] ] = '';
			}

			$this->wrmb->save_fields( $term_id, 'term', $data_to_delete );
		}
	}

	/**
	 * Determines if the current object is able to be saved.
	 *
	 * @since  2.0.9
	 * @param  string $type Current object type.
	 * @return bool         Whether object can be saved.
	 */
	public function can_save( $type = '' ) {

		$can_save = (
			$this->wrmb->prop( 'save_fields' )
			// check nonce.
			&& isset( $_POST[ $this->wrmb->nonce() ] )
			&& wp_verify_nonce( wp_kses_post( wp_unslash( $_POST[ $this->wrmb->nonce() ] ) ), $this->wrmb->nonce() )
			// check if autosave.
			&& ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			// get the metabox types & compare it to this type.
			&& ( $type && in_array( $type, $this->wrmb->box_types() ) )
			// Don't do updates during a switch-to-blog instance.
			&& ! ( is_multisite() && ms_is_switched() )
		);

		/**
		 * Filter to determine if metabox is allowed to save.
		 *
		 * @param bool   $can_save Whether the current metabox can save.
		 * @param object $wrmb      The WRMB2 instance.
		 */
		return apply_filters( 'wrmb2_can_save', $can_save, $this->wrmb );
	}

	/**
	 * Determine if taxonomy of term being modified is wrmb2-editable.
	 *
	 * @since 2.2.0
	 *
	 * @param string $taxonomy Taxonomy of term being modified.
	 * @return bool             Whether taxonomy is editable.
	 */
	public function taxonomy_can_save( $taxonomy ) {
		if ( empty( $this->taxonomies ) || ! in_array( $taxonomy, $this->taxonomies ) ) {
			return false;
		}

		$taxonomy_object = get_taxonomy( $taxonomy );
		// Can the user edit this term?
		if ( ! isset( $taxonomy_object->cap ) || ! current_user_can( $taxonomy_object->cap->edit_terms ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueues the 'wrmb2-display-styles' if the conditions match (has columns, on the right page, etc).
	 *
	 * @since  2.2.2.1
	 */
	protected function maybe_enqueue_column_display_styles() {
		global $pagenow;
		if (
			$pagenow
			&& $this->wrmb->has_columns
			&& $this->wrmb->prop( 'wrmb_styles' )
			&& in_array( $pagenow, array( 'edit.php', 'users.php', 'edit-comments.php', 'edit-tags.php' ), 1 )
			) {
			self::enqueue_wrmb_css( 'wrmb2-display-styles' );
		}
	}

	/**
	 * Includes WRMB2 styles.
	 *
	 * @since 2.0.0
	 *
	 * @param string $handle CSS handle.
	 * @return mixed
	 */
	public static function enqueue_wrmb_css( $handle = 'wrmb2-styles' ) {

		/**
		 * Filter to determine if WRMB2'S css should be enqueued.
		 *
		 * @param bool $enqueue_css Default is true.
		 */
		if ( ! apply_filters( 'wrmb2_enqueue_css', true ) ) {
			return false;
		}

		self::register_styles();

		/*
		 * White list the options as this method can be used as a hook callback
		 * and have a different argument passed.
		 */

		wp_enqueue_style( 'woo-restaurant-admin-css' );
		return wp_enqueue_style( 'wrmb2-display-styles' === $handle ? $handle : 'wrmb2-styles' );
	}

	/**
	 * Includes WRMB2 JS.
	 *
	 * @since  2.0.0
	 */
	public static function enqueue_wrmb_js() {

		/**
		 * Filter to determine if WRMB2'S JS should be enqueued.
		 *
		 * @param bool $enqueue_js Default is true.
		 */
		if ( ! apply_filters( 'wrmb2_enqueue_js', true ) ) {
			return false;
		}

		self::register_js();
		return true;
	}
}
