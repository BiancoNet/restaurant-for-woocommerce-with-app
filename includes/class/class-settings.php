<?php
/**
 * This file includes the settings of the plugin.
 *
 * @package woorestaurant
 */

namespace WooRestaurant;

/**
 * Settings.
 */
class Settings {


	/**
	 * This is the filename.
	 *
	 * @var $field_name
	 */
	public $field_name = 'srz_cat_content';

	/**
	 * This is the constructor method.
	 */
	public function __construct() {
		add_action( 'category_edit_form_fields', array( $this, 'admin_pr_edit_fields' ), 99, 2 );
		add_action( 'edited_category', array( $this, 'edit_cat_data' ) );
		// for change cate desc.
		add_filter( 'category_description', array( $this, 'cate_des_out' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * To enqueue scripts.
	 */
	public function scripts() {

		if ( is_category() ) {
			// css.
			wp_enqueue_style( 'cat-content-js-cat-content', SRZ_CAT_CONT_ASSETS . 'css/style.css', array(), SRZ_CAT_CONT_VERSION . time() );
			// js.
			wp_enqueue_script( 'cat-content-js-front', SRZ_CAT_CONT_ASSETS . 'js/front.js', array( 'jquery' ), 'wp-ss-' . SRZ_CAT_CONT_VERSION, false );
			wp_enqueue_script( 'cat-content-js-script', SRZ_CAT_CONT_ASSETS . 'js/script.js', array( 'jquery' ), 'wp-ss-' . SRZ_CAT_CONT_VERSION . time(), false );
		}
	}

	/**
	 * This is the content including file.
	 *
	 * @param string $desc it is having the description.
	 * @param object $term it is having the object of the category.
	 *
	 * @return string
	 */
	public function cate_des_out( $desc, $term ) {
		if ( ! is_category() ) {
			return $desc;
		}
		$term_id = isset( $term->term_id ) ? $term->term_id : $term;
		$term    = get_term( $term_id );
		$content = get_term_meta( $term->term_id, $this->field_name, true );
		if ( ! empty( $content ) ) {
			// allowing shortcode.
			$content = do_shortcode( $content );
			ob_start();
			include SRZ_CAT_CONT_DIR . 'views/public/content-single.php';
			$content = ob_get_clean();
			return $content;
		}
		// then normall.
		return $desc;
	}

	/**
	 * To edit cateogry data.
	 *
	 * @param int $term_id this is the id of the category.
	 */
	public function edit_cat_data( $term_id ) {
		if ( isset( $_POST['token'] ) ) {
			// phpcs:ignore
			if ( ! wp_verify_nonce( wp_kses_post( wp_unslash( $_POST['token'] ) ), 'edit-cat-data' ) ) {
				return;
			}
		}
	
		if ( isset( $_POST[ $this->field_name ] ) ) {
			update_term_meta(
				$term_id,
				$this->field_name,
				wp_kses_post( wp_unslash( $_POST[ $this->field_name ] ) )
			);
		}
	}
	

	/**
	 * This function is used to create editor field.
	 *
	 * @param object $term this is the object of the category.
	 * @param object $taxonomy this is the object of the taxonomy.
	 */
	public function admin_pr_edit_fields( $term, $taxonomy ) {
		$content = get_term_meta( $term->term_id, $this->field_name, true );
		?>
		<tr class="form-field term-display-type-wrap">
			<th scope="row" valign="top"><label>Content</label></th>
			<td>
				<?php
				wp_editor(
					$content,
					$this->field_name,
					array(
						'media_buttons' => true,
						'teeny'         => true,
						'wpautop'       => false,
					)
				);
				?>
			</td>
		</tr>
		<?php
	}
}
