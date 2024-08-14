<?php
/**
 * Cron job request handle.
 *
 * @package woorestaurant
 */

namespace WooRestaurant;

/**
 * Cron class.
 */
class Cron {

	/**
	 * Constructor Method.
	 */
	public function __construct() {
		// plugin on active/deactive.
		register_activation_hook( WOORESTAURANT_FILE, array( $this, 'activation' ) );
		register_deactivation_hook( WOORESTAURANT_FILE, array( $this, 'deactivation' ) );

		// add custom schedule.
		add_filter( 'cron_schedules', array( $this, 'cron_interval' ) );
		// run the task on exact time.
		add_action( 'woorestaurant_fullfillment', array( $this, 'run_fullfil_tasks' ) );
	}

	/**
	 * Cron internal Method.
	 *
	 * @param array $schedules this includes the schedules of the cron.
	 *
	 * @return array
	 */
	public function cron_interval( $schedules ) {
		$mins = 1;
		if ( ! isset( $schedules['woores_fullfillment_duration'] ) ) {
			$schedules['woores_fullfillment_duration'] = array(
				'interval' => 60 * $mins,
				'display'  => 'Every ' . $mins . ' mins',
			);
		}
		return $schedules;
	}

	/**
	 * Activation of the cron.
	 */
	public function activation() {
		if ( ! wp_next_scheduled( 'woorestaurant_fullfillment' ) ) {
			wp_schedule_event( time(), 'woores_fullfillment_duration', 'woorestaurant_fullfillment' );
		}
	}

	/**
	 * Deactivation of cron.
	 */
	public function deactivation() {
		wp_clear_scheduled_hook( 'woorestaurant_fullfillment' );
	}

	/**
	 * Function to run tasks.
	 */
	public function run_fullfil_tasks() {
		$is_enabled = woo_restaurant_get_option( 'woo_restaurant_fullfil_enable', 'woo_restaurant_options' );

		if ( $is_enabled && ! empty( $is_enabled ) ) {
			// start request.

			$time_fullfilement = woo_restaurant_get_option( 'woo_restaurant_fullfil_time', 'woo_restaurant_options' );

			$mins    = isset( $time_fullfilement ) && ! empty( $time_fullfilement ) ? intval( $time_fullfilement ) : 20;
			$seconds = $mins * 60;
			$query   = $this->get_ready_to_pickup_orders();
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_id    = get_the_ID();
					$pickuptime = get_post_meta( $post_id, 'woo_restaurant_ready_to_pickup_time', true );
					$order_id   = get_post_meta( $post_id, '_exo_order_id', true );
					if ( empty( $pickuptime ) || empty( $order_id ) ) {
						continue;
					}
					$pickuptime    = intval( $pickuptime );
					$secondspassed = time() - $pickuptime;
					if ( $secondspassed > $seconds ) {
						// update status to completed.
						$this->update_order( $post_id, 'completed' );
						update_post_meta( $post_id, 'woo_restaurant_ready_to_pickup_time', time() );
						// order_status_make_otter($post_id, $order_ID,'FULFILLED');.
					}
				}
			}
		}
	}

	/**
	 * Ready to pick orders function.
	 */
	public function get_ready_to_pickup_orders() {
		$args = array(
			'post_type'      => 'shop_order',
			'post_status'    => 'wc-ready-to-pickup',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'woo_restaurant_ready_to_pickup_time',
					'compare' => 'EXISTS',
				),
			),
		);

		$query = new \WP_Query( $args );
		return $query;
	}

	/**
	 * This function will update orders.
	 *
	 * @param int    $order_id this is the id of the order.
	 * @param string $status this is the status of the order.
	 */
	public function update_order( $order_id, $status ) {
		$order = wc_get_order( $order_id );
		// check order existance.
		if ( ! $order ) {
			return;
		}
		$order->update_status( $status );
	}
}
