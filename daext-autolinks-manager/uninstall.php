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

require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-data-import-export.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-term-helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-statistics.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-autolink-engine.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-html-text-replacer.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-content-helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-notices.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-daextam-admin-helper.php';
require_once plugin_dir_path( __FILE__ ) . 'shared/class-daextam-shared.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-daextam-admin.php';

Daextam_Admin::un_delete();
