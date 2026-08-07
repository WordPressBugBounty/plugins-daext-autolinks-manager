<?php
/**
 * The file used to display the "Auto Link Rules" menu in the admin area.
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$this->menu_elements->capability = 'edit_others_posts';
$this->menu_elements->context    = 'crud';
$this->menu_elements->display_menu_content();
