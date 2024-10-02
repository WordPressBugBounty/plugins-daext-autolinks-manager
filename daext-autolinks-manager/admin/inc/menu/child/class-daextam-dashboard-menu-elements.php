<?php
/**
 * Class used to implement the back-end functionalities of the "Dashboard" menu.
 *
 * @package daext-autolinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Dashboard" menu.
 */
class Daextam_Dashboard_Menu_Elements extends Daextam_Menu_Elements {

	/**
	 * Daextam_Dashboard_Menu_Elements constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug      = 'dashboard';
		$this->slug_plural    = 'dashboard';
		$this->label_singular = __( 'Dashboard', 'daext-autolinks-manager' );
		$this->label_plural   = __( 'Dashboard', 'daext-autolinks-manager' );
	}

	/**
	 * Display the content of the body.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div id="react-root"></div>

		<?php
	}
}
