<?php
/**
 * This file contains the function that will enqueue the Gutenberg block assets for the backend.
 *
 * @package daext-autolinks-manager
 */

// Prevent direct access to this file.
if ( ! defined( 'WPINC' ) ) {
	die();
}

/**
 * Enqueue the Gutenberg block assets for the backend.
 *
 * This function should be used for:
 *
 * - Hooks into editor only
 * - For main block JS
 * - For editor only block CSS overrides
 */
function daextam_editor_assets() {

	$shared = Daextam_Shared::get_instance();

	// Do not enqueue the sidebar files if the user doesn't have the proper capability.
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;}

	// Do not enqueue the sidebar files if this post type doesn't support the metadata.
	if ( ! post_type_supports( get_post_type(), 'custom-fields' ) ) {
		return;}

	// Styles ---------------------------------------------------------------------------------------------------------.
	wp_enqueue_style(
		'dagp-editor-css',
		$shared->get( 'url' ) . 'blocks/build/index.css',
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		filemtime( $shared->get( 'dir' ) . 'blocks/build/index.css' )
	);

	// Scripts --------------------------------------------------------------------------------------------------------.
	wp_enqueue_script(
		'daextam-editor-js', // Handle.
		$shared->get( 'url' ) . 'blocks/build/index.js', // We register the block here.
		array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
		filemtime( $shared->get( 'dir' ) . 'blocks/build/index.js' ),
		true // Enqueue the script in the footer.
	);

	// Store the JavaScript parameters in the window.DAEXTAM_PARAMETERS object.
	$automatic_links_panel_post_types_a   = maybe_unserialize( get_option( $shared->get( 'slug' ) . '_automatic_links_panel_post_types' ) );
	$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );

	$interlinks_options_is_active_in_post_type      = ( is_array( $automatic_links_panel_post_types_a ) && in_array( get_post_type(), $automatic_links_panel_post_types_a, true ) ) ? 1 : 0;
	$interlinks_optimization_is_active_in_post_type = ( is_array( $interlinks_optimization_post_types_a ) && in_array( get_post_type(), $interlinks_optimization_post_types_a, true ) ) ? 1 : 0;

	$initialization_script  = 'window.DAEXTAM_PARAMETERS = {';
	$initialization_script .= 'user_has_interlinks_options_mb_required_capability: "' . ( current_user_can( 'edit_others_posts' ) ? '1' : '0' ) . '",';
	$initialization_script .= 'user_has_interlinks_optimization_mb_required_capability: "' . ( current_user_can( 'edit_posts' ) ? '1' : '0' ) . '",';
	$initialization_script .= 'interlinks_options_is_active_in_post_type: "' . $interlinks_options_is_active_in_post_type . '",';
	$initialization_script .= 'interlinks_optimization_is_active_in_post_type: "' . $interlinks_optimization_is_active_in_post_type . '",';
	$initialization_script .= 'advanced_enable_autolinks: "' . esc_js( get_option( $shared->get( 'slug' ) . '_advanced_enable_autolinks' ) ) . '",';
	$initialization_script .= '};';

	wp_add_inline_script( 'daextam-editor-js', $initialization_script, 'before' );

	/*
	 * Add the translations associated with this script in the JED/json format.
	 *
	 * Reference: https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
	 *
	 * Argument 1: Handler
	 * Argument 2: Domain
	 * Argument 3: Location where the JED/json file is located.
	 *
	 * Note that:
	 *
	 * - The JED/json file should be named [domain]-[locale]-[handle].json to be actually detected by WordPress.
	 * - The JED/json file is generated with https://github.com/mikeedwards/po2json from the .po file
	 */
	wp_set_script_translations( 'daextam-editor-js', 'daext-autolinks-manager', $shared->get( 'dir' ) . 'blocks/lang' );
}
add_action( 'enqueue_block_editor_assets', 'daextam_editor_assets' );
