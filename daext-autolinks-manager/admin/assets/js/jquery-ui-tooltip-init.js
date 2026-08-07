/**
 * This file is used to initialize the jQuery UI Tooltip.
 *
 * @package daext-autolinks-manager
 */

(function ($) {

	'use strict';

	$( document ).ready(
		function () {

			'use strict';

			// Init jquery-ui-tooltip.
			$(
				function () {
					$( '.help-icon' ).tooltip( {show: false, hide: false} );
				}
			);

		}
	);

})( window.jQuery );