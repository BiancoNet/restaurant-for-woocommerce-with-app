<?php
/**
 * It is having the widget functionality.
 *
 * @package woorestaurant
 */

namespace WooRestaurant;

/**
 * This is the Widget class.
 */
class Woores_Widget extends \WP_Widget {

	/**
	 * This is the Constructor Method.
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'woo-restaurant-widget',
			'description' => esc_html__( 'Display your food from shortcode builder via widget', 'woorestaurant' ),
		);
		parent::__construct( 'woores-widget', esc_html__( 'WooCommerce Food', 'woorestaurant' ), $widget_ops );
	}

	/**
	 * This is the widget method.
	 *
	 * @param array $args it is having arguments of widget.
	 * @param array $instance it is having the instance of widget.
	 */
	public function widget( $args, $instance ) {
		// phpcs:ignore
		extract( $args );
		$title = empty( $instance['title'] ) ? '' : $instance['title'];
		$title = apply_filters( 'widget_title', $title );
		$id_sc = empty( $instance['id_sc'] ) ? '' : $instance['id_sc'];
		$html  = $before_widget;
		if ( $title ) {
			$html .= $before_title . $title . $after_title;
		}
		$html .= do_shortcode( '[wooresfc id="' . $id_sc . '"]' );
		$html .= $after_widget;
		echo ' ' . esc_attr( $html );
	}

	/**
	 * This is the update method.
	 *
	 * @param array $new_instance it is having the new instance.
	 * @param array $old_instance it is having the old instance.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['id_sc'] = strip_tags( $new_instance['id_sc'] );
		return $instance;
	}

	/**
	 * This is the form method.
	 *
	 * @param array $instance it is having the instance of form.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$id_sc = isset( $instance['id_sc'] ) ? esc_attr( $instance['id_sc'] ) : '';
		?>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'woorestaurant' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'sort_by' ) ); ?>">
				<?php esc_html_e( 'Select Shortcode', 'woorestaurant' ); ?>:
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'id_sc' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'id_sc' ) ); ?>">
					<?php
					echo '<option value="0" ' . selected( $id_sc, 'date', 0 ) . '>' . esc_html__( 'Choose a shortcode', 'woorestaurant' ) . '</option>';
					$id_query = new WP_Query( 'post_type=woorestaurant_scbd&posts_per_page=-1' );
					if ( $id_query->have_posts() ) {
						while ( $id_query->have_posts() ) {
							$id_query->the_post();
							$id_array[ get_the_ID() ] = get_the_title();
							echo '<option value="' . esc_attr( get_the_ID() ) . '" ' . selected( $id_sc, get_the_ID(), 0 ) . '>' . esc_attr( get_the_title() ) . '</option>';
						}
					}
					wp_reset_postdata();
					?>
				</select>
			</label>
		</p>
		<?php
	}
}
add_action(
	'widgets_initX',
	function () {
		$widget = new Woores_Widget();
		register_widget( $widget );
	}
);
