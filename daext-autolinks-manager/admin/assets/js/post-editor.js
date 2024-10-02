/**
 * This file is used to initialize the select2 fields in the post editor.
 *
 * @package daext-autolinks-manager
 */

(function ($) {

	'use strict';

	$( document ).ready(
		function () {

			'use strict';

			initSelect2();

		}
	);

	/**
	 * Initialize the select2 fields.
	 */
	function initSelect2() {

		'use strict';

		let options = {
			placeholder: window.objectL10n.chooseAnOptionText,
		};

		$( '#daextam-enable-autolinks' ).select2( options );

	}

}(window.jQuery));
