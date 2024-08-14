/**
 * It is the reviews script assset.
 *
 * @package woorestaurant
 */

(function ($) {
	'use strict';
	jQuery( 'document' ).ready(
		function ($) {
			jQuery( 'body' ).on(
				'submit',
				'.wrsf-reviews #commentform',
				function (e) {
					// serialize and store form data in a variable.
					var commentform = $( this );
					commentform.addClass( 'wrs-loading' );
					var statusdiv = commentform.find( '#comment-status' );
					var formdata  = commentform.serialize();
					// Add a status message.
					// Extract action URL from commentform.
					var formurl = commentform.attr( 'action' );
					// Post Form with data.
					$.ajax(
						{
							type: 'post',
							url: formurl,
							data: formdata,
							error: function (request, textStatus, errorThrown) {
								commentform.removeClass( 'wrs-loading' );
								var wpErrorHtml = request.responseText.split( "<p>" ),
								wpErrorStr      = wpErrorHtml[1].split( "</p>" );
								statusdiv.html( wpErrorStr );
							},
							success: function (data, textStatus) {
								commentform.removeClass( 'wrs-loading' );
								var wpErrorHtml = data.split( "<p>" ),
								wpErrorStr      = wpErrorHtml[1].split( "</p>" );
								statusdiv.html( wpErrorStr );
							}
						}
					);
					return false;
				}
			);
			jQuery( 'body' ).on(
				'click',
				'.wrsf-md-tabs .wrsf-tab:not(.wrsf-tab-current)',
				function () {
					$( ".wrsf-md-tabs .wrsf-tab" ).removeClass( 'wrsf-tab-current' );
					$( this ).addClass( 'wrsf-tab-current' );
					var control = $( this ).attr( 'data-control' );
					$( ".wrsf-act-tab" ).fadeOut(
						"fast",
						function () {
							$( this ).removeClass( 'wrsf-act-tab' );
						}
					);
					$( "." + control ).fadeIn(
						"fast",
						function () {
							$( this ).addClass( 'wrsf-act-tab' );
							var cont_hi = $( '#food_modal .wrs-modal-big' ).height();
							var img_hi  = $( '#food_modal .fd_modal_img' ).height();
							if (cont_hi > img_hi && $( window ).width() > 767) {
								$( '#food_modal .wrs-modal-big' ).addClass( 'wrs-padimg' );
							} else {
								$( '#food_modal .wrs-modal-big' ).removeClass( 'wrs-padimg' );
							}
						}
					);
				}
			);
			jQuery( 'body' ).on(
				'click',
				'.wrsf-reviews #respond p.stars a',
				function () {
					var $star  = $( this ),
					$rating    = $( this ).closest( '#respond' ).find( '#rating' ),
					$container = $( this ).closest( '.stars' );

					$rating.val( $star.text() );
					$star.siblings( 'a' ).removeClass( 'active' );
					$star.addClass( 'active' );
					$container.addClass( 'selected' );

					return false;
				}
			);
		}
	);

}(jQuery));