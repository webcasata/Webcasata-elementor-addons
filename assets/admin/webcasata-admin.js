/**
 * Webcasata Addons settings screen: "Enable all / Disable all" and the
 * on-state highlight. Plain JS, no dependencies.
 */
( function () {
	'use strict';

	function sync( input ) {
		var card = input.closest( '.wa-card' );
		if ( card ) {
			card.classList.toggle( 'is-on', input.checked );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.querySelector( '.wa-form' );
		if ( ! form ) {
			return;
		}

		var sections = form.querySelector( '.wa-sections' );
		var master = form.querySelector( 'input[name$="[master]"]' );

		form.addEventListener( 'change', function ( event ) {
			var target = event.target;
			if ( ! target || 'checkbox' !== target.type ) {
				return;
			}
			sync( target );
			if ( target === master && sections ) {
				sections.classList.toggle( 'is-master-off', ! master.checked );
			}
		} );

		var bulk = form.querySelectorAll( '[data-wa-bulk]' );
		Array.prototype.forEach.call( bulk, function ( button ) {
			button.addEventListener( 'click', function () {
				var on = 'on' === button.getAttribute( 'data-wa-bulk' );
				var boxes = form.querySelectorAll( 'input[name*="[widgets]"]' );
				Array.prototype.forEach.call( boxes, function ( box ) {
					box.checked = on;
					sync( box );
				} );
			} );
		} );
	} );
}() );
