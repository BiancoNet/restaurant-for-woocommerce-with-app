/**
 * It is the option addons script file.
 *
 * @package woorestaurant
 */

(function ($) {
	"use strict";
	$( document ).ready(
		function () {
			$( "body" ).on(
				"submit",
				".woorst-woocommerce form.cart, .product form.cart",
				function (e) {
					if (
					! $( this ).find( ".wrrow-group.wrs-required" ).length &&
					! $( this ).find( ".wrrow-group.wrs-required-min" ).length
					) {
						return;
					}
					var $validate = true;
					$(
						".wrs-required-message, .wrs-required-min-message, .wrs-required-max-message"
					).fadeOut();
					$( this )
					.find( ".wrrow-group.wrs-required:not(.wrs-required-min)" )
					.each(
						function () {
							var $this_sl = $( this );
							if ($this_sl.hasClass( "wrsf-offrq" )) {
							} else {
								if (
								$this_sl.hasClass( "wrs-radio" ) ||
								$this_sl.hasClass( "wrs-checkbox" )
								) {
									if ( ! $this_sl.find( ".wrs-options" ).is( ":checked" )) {
											$this_sl.find( ".wrs-required-message" ).fadeIn();
											$this_sl
										.find( ".wooresf-label:not(.woores-active)" )
										.trigger( "click" );
											$validate = false;
									}
								} else {
									if ($this_sl.find( ".wrs-options" ).val() == "") {
										$this_sl
										.find( ".wooresf-label:not(.woores-active)" )
										.trigger( "click" );
										$this_sl.find( ".wrs-required-message" ).fadeIn();
										$validate = false;
									}
								}
							}
						}
					);
					$( this )
					.find( ".wrrow-group.wrs-checkbox.wrs-required-min" )
					.each(
						function () {
							var $this_sl = $( this );
							if ($this_sl.hasClass( "wrsf-offrq" )) {
							} else {
								var $minsl = $this_sl.data( "minsl" );
								var $nbsl  = $this_sl.find( ".wrs-options:checked" ).length;
								if ($nbsl < $minsl) {
									$this_sl
									.find( ".wooresf-label:not(.woores-active)" )
									.trigger( "click" );
									$this_sl.find( ".wrs-required-min-message" ).fadeIn();
									$validate = false;
								}
							}
						}
					);
					if ($validate != true) {
						e.preventDefault();
						e.stopPropagation();
						return;
					}
					return true;
				}
			);
			$( "body" ).on(
				"change",
				".wrs-checkbox.wrs-required-max .wrs-options",
				function () {
					var $this_sl = $( this );
					if ($this_sl.hasClass( "wrsf-offrq" )) {
					} else {
						var $maxsl = $this_sl
						.closest( ".wrs-checkbox.wrs-required-max" )
						.data( "maxsl" );
						var $nbsl  = $this_sl
						.closest( ".wrs-checkbox.wrs-required-max" )
						.find( ".wrs-options:checked" ).length;
						if ($nbsl > $maxsl) {
							$this_sl
							.closest( ".wrs-checkbox.wrs-required-max" )
							.find( ".wrs-required-max-message" )
							.fadeIn();
							this.checked = false;
							event.preventDefault();
						}
					}
				}
			);
			$( "body" ).on(
				"click",
				".woores-accordion-style .wrrow-group .wooresf-label",
				function (e) {
					var $this = $( this );
					$( $this ).next( ".woores-container" ).slideToggle( 200 );
					if ($this.hasClass( "woores-active" )) {
						$this.removeClass( "woores-active" );
					} else {
						$this.addClass( "woores-active" );
					}
				}
			);
		}
	);
})( jQuery );
