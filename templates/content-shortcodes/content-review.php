<?php
/**
 * It is the content review shortcode template.
 *
 * @package woorestaurant
 */

global $product, $id_food;
if ( ! method_exists( $product, 'get_review_count' ) ) {
	$product = wc_get_product( $id_food );
}
if ( comments_open( $id_food ) ) {
	$params   = array(
		'p'         => $id_food,
		'post_type' => 'product',
	);
	$wc_query = new WP_Query( $params );
	if ( $wc_query->have_posts() ) :  ?>
		<?php
		while ( $wc_query->have_posts() ) :
			$wc_query->the_post();
			?>
	<div class="wrsf-reviews wrsf-ct-tab woocommerce">
	<div id="reviews" class="woocommerce-Reviews">

			<?php
			$count = $product->get_review_count();
			if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) :
				?>
			<div id="review_form_wrapper">
				<div id="review_form">
						<?php
						$commenter    = wp_get_current_commenter();
						$comment_form = array(
							/* translators: %s is product title */
							'title_reply'         => $count > 0 ? esc_html__( 'Add a review', 'woorestaurant' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woorestaurant' ), get_the_title() ),
							/* translators: %s is product title */
							'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woorestaurant' ),
							'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
							'title_reply_after'   => '</span>',
							'comment_notes_after' => '',
							'label_submit'        => esc_html__( 'Submit', 'woorestaurant' ),
							'logged_in_as'        => '',
							'comment_field'       => '',
						);

						$name_email_required = (bool) get_option( 'require_name_email', 1 );
						$fields              = array(
							'author' => array(
								'label'    => esc_html__( 'Name', 'woorestaurant' ),
								'type'     => 'text',
								'value'    => $commenter['comment_author'],
								'required' => $name_email_required,
							),
							'email'  => array(
								'label'    => esc_html__( 'Email', 'woorestaurant' ),
								'type'     => 'email',
								'value'    => $commenter['comment_author_email'],
								'required' => $name_email_required,
							),
						);

						$comment_form['fields'] = array();

						foreach ( $fields as $key => $field ) {
							$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
							$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

							if ( $field['required'] ) {
								$field_html .= '&nbsp;<span class="required">*</span>';
							}

							$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

							$comment_form['fields'][ $key ] = $field_html;
						}

						$account_page_url = wc_get_page_permalink( 'myaccount' );
						if ( $account_page_url ) {
							/* translators: %s opening and closing link tags respectively */
							$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woorestaurant' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
						}

						if ( wc_review_ratings_enabled() ) {
							$comment_form['comment_field'] = '<div id="comment-status"></div><div class="comment-form-rating"><label for="rating">' . esc_html__( 'Your rating', 'woorestaurant' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p><select name="rating" id="rating" required>
							<option value=""></option>
							<option value="5">5*</option>
							<option value="4">4*</option>
							<option value="3">3*</option>
							<option value="2">2*</option>
							<option value="1">1*</option>
						</select></div>';
						}

						$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woorestaurant' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

						comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
						?>
				</div>
			</div>
			<?php else : ?>
			<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woorestaurant' ); ?></p>
		<?php endif; ?>
		<div id="comments">
			<p class="wrsf-rvtitle">
				<?php

				if ( $count && wc_review_ratings_enabled() ) {
					/* translators: 1: reviews count 2: product name */
					$reviews_title = sprintf( esc_html( _n( '%1$s review for %2$s', '%1$s reviews for %2$s', $count, 'woorestaurant' ) ), esc_html( $count ), '<span>' . get_the_title() . '</span>' );
					echo esc_attr( apply_filters( 'woocommerce_reviews_title', $reviews_title, $count, $product ) ); // WPCS: XSS ok.
				} else {
					esc_html_e( 'Reviews', 'woorestaurant' );
				}
				?>
			</p>

			<?php if ( $count > 0 ) : ?>
				<ol class="commentlist">
					<?php
					wp_list_comments(
						apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ),
						get_comments(
							array(
								'post_id' => $id_food,
								'status'  => 'approve',
							)
						)
					);
					?>
				</ol>

				<?php
				if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
					echo '<nav class="woocommerce-pagination">';
					paginate_comments_links(
						apply_filters(
							'woocommerce_comment_pagination_args',
							array(
								'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
								'next_text' => is_rtl() ? '&larr;' : '&rarr;',
								'type'      => 'list',
							)
						)
					);
					echo '</nav>';
				endif;
				?>
			<?php else : ?>
				<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woorestaurant' ); ?></p>
			<?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>
	</div>
			<?php
	endwhile;
		wp_reset_postdata();
	endif;
}
