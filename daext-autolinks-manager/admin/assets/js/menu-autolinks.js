/**
 * This file is used to handle initialize Select2 in the Autolinks menu.
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

		$( '#category_id' ).select2();
		$( '#post_types' ).select2( options );
		$( '#categories' ).select2( options );
		$( '#tags' ).select2( options );
		$( '#term_group_id' ).select2();
		$( '#left_boundary' ).select2();
		$( '#right_boundary' ).select2();

	}

}(window.jQuery));