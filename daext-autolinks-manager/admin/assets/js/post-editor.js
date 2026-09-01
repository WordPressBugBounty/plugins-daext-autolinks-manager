/**
 * This file is used to handle the JavaScript related operations in the post editor.
 *
 * Note that this file runs only if the block editor is disabled.
 *
 * @package daext-autolinks-manager
 */

/**
 * This file should run only if in the post editor the block editor is disabled.
 */
(function () {

	const { __ } = wp.i18n; // Import the __ function for translations

	jQuery(document).ready(function ($) {
		'use strict';

		/**
		 * Populate the Interlinks Optimization section on page load
		 */
		const postId = parseInt($('#post_ID').val(), 10);
		if (postId) {
			updateInterlinksOptimizationMetaBox(postId);
		}

		/**
		 * Here the wp.data API is used to detect when a post is modified and the Interlinks Optimization meta-box needs to be
		 * updated.
		 *
		 * Note that the update of the Interlinks Optimization meta-box is performed only if:
		 *
		 * - The Gutenberg editor is available. (wp.blocks is checked against undefined)
		 * - The Interlinks Optimization meta-box is present in the DOM (because in specific post types or when the user
		 *   doesn't have the proper capability too see it it's not available)
		 *
		 * References:
		 *
		 * - https://github.com/WordPress/gutenberg/issues/4674#issuecomment-404587928
		 * - https://wordpress.org/gutenberg/handbook/packages/packages-data/
		 * - https://www.npmjs.com/package/@wordpress/data
		 */
		if (typeof wp.blocks !== 'undefined' && $('#daextam-meta-optimization').length > 0) {
			let objectIsEmpty = true;
			let obj = wp.data.select('core/editor');
			for (let key in obj) {
				if (obj.hasOwnProperty(key)) {
					objectIsEmpty = false;
				}
			}
			if (objectIsEmpty) {
				return;
			}

			let lastModified = '';

			const unsubscribe = wp.data.subscribe(function () {
				'use strict';

				const postId = wp.data.select('core/editor').getCurrentPost().id;
				let postModifiedIsChanged = false;

				if (
					typeof wp.data.select('core/editor').getCurrentPost().modified !== 'undefined' &&
					wp.data.select('core/editor').getCurrentPost().modified !== lastModified
				) {
					lastModified = wp.data.select('core/editor').getCurrentPost().modified;
					postModifiedIsChanged = true;
				}

				/**
				 * Update the Interlinks Optimization meta-box if:
				 *
				 * - The post has been saved.
				 * - This is not an autosave.
				 * - The "lastModified" flag used to detect if the post "modified" date has changed is set to true.
				 */
				if (
					wp.data.select('core/editor').isSavingPost() &&
					!wp.data.select('core/editor').isAutosavingPost() &&
					postModifiedIsChanged === true
				) {
					updateInterlinksOptimizationMetaBox(postId);
				}
			});
		}

		/**
		 * Updates the Interlinks Optimization meta-box content.
		 *
		 * @param post_id The id of the current post
		 */
		function updateInterlinksOptimizationMetaBox(post_id) {
			'use strict';

			// Use wp.apiFetch to call the REST API endpoint.
			wp.apiFetch({
				path: '/daext-autolinks-manager/v1/generate-interlinks-optimization',
				method: 'POST',
				data: { id: post_id },
			})
				.then((response) => {
					if (response) {
							// Extract data from the response.
							const totalNumberOfInterlinks = response['total_number_of_interlinks'];
							const numberOfManualInterlinks = response['number_of_manual_interlinks'];
							const numberOfAutoInterlinks = response['number_of_autolinks'];
							const suggestedMin = response['suggested_min_number_of_interlinks'];
							const suggestedMax = response['suggested_max_number_of_interlinks'];

							// Generate the HTML content based on the response.
							let htmlContent = '';
							if (totalNumberOfInterlinks >= suggestedMin && totalNumberOfInterlinks <= suggestedMax) {
								htmlContent += `<p>${__('The number of internal links in this post is within the recommended range.', 'daext-autolinks-manager')}</p>`;
							} else {
								htmlContent += `<p>${__('This post currently contains', 'daext-autolinks-manager')} ${totalNumberOfInterlinks} ${totalNumberOfInterlinks === 1 ? __('internal link', 'daext-autolinks-manager') : __('internal links', 'daext-autolinks-manager')}. (${numberOfManualInterlinks} ${numberOfManualInterlinks === 1 ? __('manual internal link', 'daext-autolinks-manager') : __('manual internal links', 'daext-autolinks-manager')} ${__('and', 'daext-autolinks-manager')} ${numberOfAutoInterlinks} ${numberOfAutoInterlinks === 1 ? __('automatic internal link', 'daext-autolinks-manager') : __('auto internal links', 'daext-autolinks-manager')})</p>`;

								if (suggestedMin === suggestedMax) {
									htmlContent += `<p>${__('Based on the content length and your settings, the recommended number is', 'daext-autolinks-manager')} ${suggestedMin}.</p>`;
								} else {
									htmlContent += `<p>${__('Based on the content length and your settings, the recommended number is between', 'daext-autolinks-manager')} ${suggestedMin} ${__('and', 'daext-autolinks-manager')} ${suggestedMax}.</p>`;
								}
							}

							// Update the content of the meta-box.
							const metaBoxInside = document.querySelector('#daextam-meta-optimization .inside');
							if (metaBoxInside) {
								metaBoxInside.innerHTML = htmlContent;
							}
					}
				})
				.catch((error) => {
					console.error('Error fetching interlinks optimization data:', error);
				});
		}
	});
})();