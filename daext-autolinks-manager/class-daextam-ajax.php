<?php
/**
 * This file contains the class Daextam_Ajax, used to include ajax actions.
 *
 * @package daext-autolinks-manager
 */

/**
 * This class should be used to include ajax actions.
 */
class Daextam_Ajax {

	/**
	 * The instance of the Daextam_Ajax class.
	 *
	 * @var Daextam_Ajax
	 */
	protected static $instance = null;

	/**
	 * The instance of the Daextam_Shared class.
	 *
	 * @var Daextam_Shared
	 */
	private $shared = null;

	/**
	 * The constructor of the Daextam_Ajax class.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextam_Shared::get_instance();

		// AJAX requests for logged-in users.
		add_action( 'wp_ajax_daextam_get_taxonomies', array( $this, 'daextam_get_taxonomies' ) );
		add_action( 'wp_ajax_daextam_get_terms', array( $this, 'daextam_get_terms' ) );
	}

	/**
	 * Return an istance of this class.
	 *
	 * @return self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the list of taxonomies associated with the provided post type.
	 *
	 * @return void
	 */
	public function daextam_get_taxonomies() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextam', 'security', false ) ) {
			esc_html_e( 'Invalid AJAX Request', 'daext-autolinks-manager' );
			die();
		}

		// check the capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			esc_html_e( 'Invalid Capability', 'daext-autolinks-manager' );
			die();
		}

		// Get the data.
		$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : null;

		$taxonomies = get_object_taxonomies( $post_type );

		$taxonomy_obj_a = array();
		if ( is_array( $taxonomies ) && count( $taxonomies ) > 0 ) {
			foreach ( $taxonomies as $key => $taxonomy ) {
				$taxonomy_obj_a[] = get_taxonomy( $taxonomy );
			}
		}

		echo wp_json_encode( $taxonomy_obj_a );
		die();
	}

	/**
	 * Get the list of terms associated with the provided taxonomy.
	 *
	 * @return string|void
	 */
	public function daextam_get_terms() {

		// Check the referer.
		if ( ! check_ajax_referer( 'daextam', 'security', false ) ) {
			esc_html_e( 'Invalid AJAX Request', 'deextamp' );
			die();
		}

		// Check the capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			esc_html_e( 'Invalid Capability', 'daext-autolinks-manager' );
			die();
		}

		// Get the data.
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( $_POST['taxonomy'] ) : null;

		$terms = get_terms(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
				'taxonomy'   => $taxonomy,
			)
		);

		if ( is_object( $terms ) && get_class( $terms ) === 'WP_Error' ) {
			return '0';
		} else {
			echo wp_json_encode( $terms );
		}

		die();
	}
}
