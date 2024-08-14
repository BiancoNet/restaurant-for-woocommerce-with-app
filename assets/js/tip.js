/**
 * It is the tip script assets.
 *
 * @package woorestaurant
 */

(function ($) {
	'use strict';
	jQuery( 'document' ).ready(
		function ($) {
			function woore_update_tipping(tip_val){
				$( '.wrsf-tip-form' ).addClass( 'wrs-loading' );
				var param     = {
					action: 'woore_update_tip',
					tip: tip_val,
				};
				var $url_ajax = woore_jspr.ajaxurl;
				$.ajax(
					{
						type: "post",
						url: $url_ajax,
						dataType: 'json',
						data: (param),
						success: function (data) {
							$( '.wrsf-tip-form' ).removeClass( 'wrs-loading' );
							$( 'body' ).trigger( 'update_checkout' );
						}
					}
				);

			}
			$( 'body' ).on(
				'click',
				'.wrsf-tip-form input[name=wrsf-add-tip]',
				function (e) {
					// $(document).on('submit', '.wrsf-tip-form form', function (e) {
					e.preventDefault();
					var $form   = $( this ).closest( '.wrsf-tip-form' );
					var tip_val = $form.find( 'input[name=wrsf-tip]' ).val();
					if (tip_val == '' || ! $.isNumeric( tip_val ) ) {
						$form.find( '.wrsf-tip-error' ).fadeIn();
						return;
					} else {
						$form.find( '.wrsf-tip-error' ).fadeOut();
					}
					woore_update_tipping( tip_val );
					return false;
				}
			);
			$( 'body' ).on(
				'click',
				'.wrsf-tip-form input[name=wrsf-remove-tip]',
				function (e) {
					woore_update_tipping( '0' );
					return false;
				}
			);
		}
	);

}(jQuery));