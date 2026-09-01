<?php
/**
 * Class used to implement the back-end functionalities of the "Link Equity" menu.
 *
 * @package daext-autolinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Link Equity" menu.
 */
class Daextam_Link_Equity_Menu_Elements extends Daextam_Menu_Elements {

	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'link-equity';
		$this->slug_plural        = 'link-equity';
		$this->label_singular     = 'Link Equity';
		$this->label_plural       = 'Link Equity';
		$this->primary_key        = 'category_id';
		$this->db_table           = 'category';
		$this->list_table_columns = array(
			array(
				'db_field' => 'name',
				'label'    => 'Name',
			),
			array(
				'db_field' => 'description',
				'label'    => 'Description',
			),
		);
		$this->searchable_fields  = array(
			'name',
			'description',
		);
	}

	/**
	 * Display the content of the body of the page.
	 *
	 * @return void
	 */
	function display_custom_content() {

		?>

		<div class="daextam-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->get_notices()->display_dismissible_notices();

			// Display the license activation notice.
			$this->shared->get_notices()->display_license_activation_notice();

			?>

			<div id="react-root"></div>

		</div>

		<?php

	}
}
