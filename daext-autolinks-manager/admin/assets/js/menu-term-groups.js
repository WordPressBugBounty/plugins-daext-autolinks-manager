/**
 * This file is used to load taxonomies and terms dynamically in the menu term groups page.
 *
 * @package daext-autolinks-manager
 */

(function ($) {

	'use strict';

	$( document ).ready(
		function () {

			'use strict';

			initSelect2();

			// Handle Post Type Change --------------------------------------------------------------------------------.

			// Generate the selector from 1 to 50.
			let all_post_types_selector = '';
			for (let i = 1; i <= 50; i++) {
				all_post_types_selector += '#post_type_' + i;
				if (i < 50) {
					all_post_types_selector += ', ';
				}
			}

			$( all_post_types_selector ).on(
				'change',
				function () {

					'use strict';

					// Get the post type.
					const postType = $( this ).val();

					// Get the id.
					const id = parseInt( $( this ).attr( 'id' ).match( /\d+/ )[0], 10 );

					// Delete the options of taxonomy with the same id.
					$( '#taxonomy_' + id + ' option:not(:first-of-type)' ).remove();

					// Delete the options of terms with the same id.
					$( '#term_' + id + ' option:not(:first-of-type)' ).remove();

					// Prepare ajax request.
					const data = {
						'action': 'daextam_get_taxonomies',
						'security': window.DAEXTAM_PARAMETERS.daextam_nonce,
						'post_type': postType,
					};

					// Send ajax request.
					$.post(
						window.DAEXTAM_PARAMETERS.ajax_url,
						data,
						function (data) {

							'use strict';

							let isJson = true,
							taxonomies = null;

							try {
								taxonomies = $.parseJSON( data );
							} catch (e) {
								isJson = false;
							}

							if (isJson) {

								// Add the taxonomies.
								$.each(
									taxonomies,
									function (index, taxonomy) {

										'use strict';

										$( '#taxonomy_' + id ).append( '<option value="' + taxonomy.name + '">' + taxonomy.label + '</option>' );

									}
								);

							}

						}
					);

				}
			);

			// Handle Taxonomy Change ---------------------------------------------------------------------------------.

			// Generate the selector from 1 to 50.
			let all_taxonomies_selector = '';
			for (let i = 1; i <= 50; i++) {
				all_taxonomies_selector += '#taxonomy_' + i;
				if (i < 50) {
					all_taxonomies_selector += ', ';
				}
			}

			$( all_taxonomies_selector ).on(
				'change',
				function () {

					'use strict';

					// Get the taxonomy.
					const taxonomy = $( this ).val();

					// Get the id.
					const id = parseInt( $( this ).attr( 'id' ).match( /\d+/ )[0], 10 );

					// Delete the options of terms with the same id.
					$( '#term_' + id + ' option:not(:first-of-type)' ).remove();

					// Prepare ajax request.
					const data = {
						'action': 'daextam_get_terms',
						'security': window.DAEXTAM_PARAMETERS.daextam_nonce,
						'taxonomy': taxonomy,
					};

					// Send ajax request.
					$.post(
						window.DAEXTAM_PARAMETERS.ajax_url,
						data,
						function (data) {

							'use strict';

							let isJson = true,
							terms      = null;

							try {
								terms = $.parseJSON( data );
							} catch (e) {
								isJson = false;
							}

							if (parseInt( data, 10 ) !== 0 && isJson) {

								// Add the taxonomies.
								$.each(
									terms,
									function (index, termObj) {

										'use strict';

										$( '#term_' + id ).append( '<option value="' + termObj.term_id + '">' + termObj.name + '</option>' );

									}
								);

							}

						}
					);

				}
			);

			// Dialog Confirm -----------------------------------------------------------------------------------------.
			window.DAEXTAM = {};
			$( document.body ).on(
				'click',
				'.daextam-crud-table__row-actions-single-action-delete button' ,
				function (event) {

					'use strict';

					event.preventDefault();
					window.DAEXTAM.termGroupToDelete = $( this ).prev().val();
					$( '#dialog-confirm' ).dialog( 'open' );

				}
			);

		}
	);

	/**
	 * Dialog confirm initialization.
	 */
	$(
		function () {

			'use strict';

			$( '#dialog-confirm' ).dialog(
				{
					autoOpen: false,
					resizable: false,
					height: 'auto',
					width: 340,
					modal: true,
					buttons: {
						[objectL10n.deleteText]: function () {
							$( '#delete-item-' + window.DAEXTAM.termGroupToDelete ).submit();
						},
						[objectL10n.cancelText]: function () {
							$( this ).dialog( 'close' );
						},
					},
				}
			);

		}
	);

	/**
	 * Initialize the select2 fields.
	 */
	function initSelect2() {

		'use strict';

		for (let i = 1; i <= 10; i++) {
			$( '#post_type_' + i ).select2();
			$( '#taxonomy_' + i ).select2();
			$( '#term_' + i ).select2();
		}

	}

}(window.jQuery));