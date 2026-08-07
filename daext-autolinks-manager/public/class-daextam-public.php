<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package daext-autolinks-manager
 */

/**
 * This class should be used to work with the public side of WordPress.
 */
class Daextam_Public {

/**
 * The singleton instance of the class.
 *
 * @var Daextam_Public|null
 */
protected static $instance = null;

/**
 * An instance of the shared class.
 *
 * @var Daextam_Shared|null
 */
private $shared = null;

/**
 * Constructor.
 */
private function __construct() {

$this->shared = Daextam_Shared::get_instance();

if (
intval( get_option( $this->shared->get( 'slug' ) . '_advanced_enable_test_mode' ), 10 ) === 0 ||
current_user_can( 'manage_options' )
) {
add_filter(
'the_content',
array( $this->shared->get_autolink_engine(), 'add_autolinks' ),
intval( get_option( $this->shared->get( 'slug' ) . '_advanced_filter_priority' ), 10 )
);
add_filter( 'the_content', array( $this->shared->get_autolink_engine(), 'add_hidden_input' ), 2147483647 );
}
}

/**
 * Create an instance of this class.
 *
 * @return self|null
 */
public static function get_instance() {

if ( null === self::$instance ) {
self::$instance = new self();
}

return self::$instance;
}
}
