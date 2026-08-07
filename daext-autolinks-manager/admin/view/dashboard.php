<?php
/**
 * The file used to display the "Dashboard" menu in the admin area.
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$this->menu_elements->capability = 'publish_posts';
$this->menu_elements->context    = null;
$this->menu_elements->display_menu_content();
