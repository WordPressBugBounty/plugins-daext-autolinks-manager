<?php
/**
 * Plugin Name: Link Manager
 * Description: A complete link management solution that helps you analyze internal links, monitor link equity, automate keyword-based linking, and more.
 * Version: 1.10.12
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * Author: DAEXT
 * Author URI: https://daext.com
 * Text Domain: daext-autolinks-manager
 * License: GPLv3
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

// Set constants.
define( 'DAEXTAM_EDITION', 'FREE' );

// Shared feature classes.
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-data-import-export.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-term-helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-statistics.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-autolink-engine.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-html-text-replacer.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-content-helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-notices.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-admin-helper.php';

// Class shared across public and admin.
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextam-shared.php';

// Rest API.
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-rest.php';
add_action( 'plugins_loaded', array( 'Daextam_Rest', 'get_instance' ) );

// Public.
require_once plugin_dir_path( __FILE__ ) . 'public/class-daextam-public.php';
add_action( 'plugins_loaded', array( 'Daextam_Public', 'get_instance' ) );

// Perform the block editor related activities only if the block editor is present.
if ( function_exists( 'register_block_type' ) ) {
require_once plugin_dir_path( __FILE__ ) . 'blocks/src/init.php';
}

// Admin.
if ( is_admin() ) {

require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextam-admin.php';

// If this is not an AJAX request, create a new singleton instance of the admin class.
if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
add_action( 'plugins_loaded', array( 'Daextam_Admin', 'get_instance' ) );
}

// Activate the plugin using only the class static methods.
register_activation_hook( __FILE__, array( 'Daextam_Admin', 'ac_activate' ) );

// Deactivate the plugin only with static methods.
register_deactivation_hook( __FILE__, array( 'Daextam_Admin', 'dc_deactivate' ) );
}

// Ajax.
if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

// Admin.
require_once plugin_dir_path( __FILE__ ) . 'class-daextam-ajax.php';
add_action( 'plugins_loaded', array( 'Daextam_Ajax', 'get_instance' ) );
}

/**
 * Customize the action links in the Plugins screen.
 *
 * @param array $actions Plugin action links.
 *
 * @return array
 */
function daextam_customize_action_links( $actions ) {
$actions[] = '<a href="https://daext.com/link-manager/" target="_blank">' . esc_html__( 'Buy the Pro Version', 'daext-autolinks-manager' ) . '</a>';
return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'daextam_customize_action_links' );

/**
 * If we are in the admin area, update the plugin db tables and options if they are not up-to-date.
 */
if ( is_admin() ) {

require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextam-admin.php';

// If needed, create or update the database tables.
Daextam_Admin::ac_create_database_tables();

// If needed, create or update the plugin options.
Daextam_Admin::ac_initialize_options();
}

/**
 * Load the plugin text domain for translation.
 *
 * @return void
 */
function daextam_load_plugin_textdomain() {
load_plugin_textdomain( 'daext-autolinks-manager', false, 'daext-autolinks-manager/lang/' );
}
add_action( 'init', 'daextam_load_plugin_textdomain' );
