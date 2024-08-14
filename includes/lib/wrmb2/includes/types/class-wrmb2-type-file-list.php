<?php
/**
 * It is the type file list.
 *
 * @package woorestaurant
 */

/**
 * WRMB file_list field type
 *
 * @since  2.2.2
 *
 * @category  WordPress_Plugin
 * @package   WRMB2
 * @author    WRMB2 team
 * @license   GPL-2.0+
 * @link      https://wrmb2.io
 */
class WRMB2_Type_File_List extends WRMB2_Type_File_Base {
	/**
	 * It is the Render function.
	 *
	 * @param array $args it is the argument method.
	 */
	public function render( $args = array() ) {
		$field      = $this->field;
		$meta_value = $field->escaped_value();
		$name       = $this->_name();
		$img_size   = $field->args( 'preview_size' );
		$query_args = $field->args( 'query_args' );
		$output     = '';

		// get an array of image size meta data, fallback to 'thumbnail'.
		$img_size_data = parent::get_image_size_data( $img_size, 'thumbnail' );

		$output .= parent::render(
			array(
				'type'             => 'hidden',
				'class'            => 'wrmb2-upload-file wrmb2-upload-list',
				'size'             => 45,
				'desc'             => '',
				'value'            => '',
				'data-previewsize' => sprintf( '[%d,%d]', $img_size_data['width'], $img_size_data['height'] ),
				'data-sizename'    => $img_size_data['name'],
				'data-queryargs'   => ! empty( $query_args ) ? json_encode( $query_args ) : '',
				'js_dependencies'  => 'media-editor',
			)
		);

		$output .= parent::render(
			array(
				'type'  => 'button',
				'class' => 'wrmb2-upload-button button-secondary wrmb2-upload-list',
				'value' => esc_attr( $this->_text( 'add_upload_files_text', esc_html__( 'Add or Upload Files', 'wrmb2' ) ) ),
				'name'  => '',
				'id'    => '',
			)
		);

		$output .= '<ul id="' . $this->_id( '-status', false ) . '" class="wrmb2-media-status wrmb-attach-list">';

		if ( $meta_value && is_array( $meta_value ) ) {

			foreach ( $meta_value as $id => $fullurl ) {
				$id_input = parent::render(
					array(
						'type'    => 'hidden',
						'value'   => $fullurl,
						'name'    => $name . '[' . $id . ']',
						'id'      => 'filelist-' . $id,
						'data-id' => $id,
						'desc'    => '',
						'class'   => false,
					)
				);

				if ( $this->is_valid_img_ext( $fullurl ) ) {

					$output .= $this->img_status_output(
						array(
							'image'    => wp_get_attachment_image( $id, $img_size ),
							'tag'      => 'li',
							'id_input' => $id_input,
						)
					);

				} else {

					$output .= $this->file_status_output(
						array(
							'value'    => $fullurl,
							'tag'      => 'li',
							'id_input' => $id_input,
						)
					);

				}
			}
		}

		$output .= '</ul>';

		return $this->rendered( $output );
	}
}
