<?php
/**
 * Uninstall plugin.
 *
 * @package daext-autolinks-manager
 */

// Exit if this file is called outside WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}

require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextam-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextam-admin.php';

// Delete options and tables.
Daextam_Admin::un_delete();
