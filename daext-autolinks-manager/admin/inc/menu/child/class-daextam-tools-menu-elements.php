<?php
/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 *
 * @package daext-autolinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Tools" menu.
 */
class Daextam_Tools_Menu_Elements extends Daextam_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug      = 'tool';
		$this->slug_plural    = 'tools';
		$this->label_singular = __( 'Tool', 'daext-autolinks-manager' );
		$this->label_plural   = __( 'Tools', 'daext-autolinks-manager' );

	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 *  1. Sanitization
	 *  2. Validation
	 *  3. Database update
	 *
	 * @return false|void
	 */
	public function process_form() {
		$data_import_export = $this->shared->get_data_import_export();

		// process the export button click. (export) ------------------------------------------------------------------.

		/**
		 * Intercept requests that come from the "Export" button of the "Tools -> Export" menu and generate the
		 * downloadable XML file
		 */
		if ( isset( $_POST['daextam_export'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextam_tools_export', 'daextam_tools_export' );

			// Verify capability.
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'daext-autolinks-manager' ) );
			}

			// Generate the header of the XML file.
			header( 'Content-Encoding: UTF-8' );
			header( 'Content-type: text/xml; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename=link-manager-' . time() . '.xml' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// Generate initial part of the XML file.
			echo '<?xml version="1.0" encoding="UTF-8" ?>';
			echo '<root>';

			// Generate the XML of the various db tables.
			$data_import_export->convert_db_table_to_xml( 'autolink', 'autolink_id' );
			$data_import_export->convert_db_table_to_xml( 'category', 'category_id' );
			$data_import_export->convert_db_table_to_xml( 'term_group', 'term_group_id' );

			// Generate the final part of the XML file.
			echo '</root>';

			die();

		}
	}

	/**
	 * Display the form.
	 *
	 * @return void
	 */
	public function display_custom_content() {

		?>

		<div class="daextam-admin-body">

			<?php

			// Display the dismissible notices.
			$this->shared->get_notices()->display_dismissible_notices();


			?>

			<div class="daextam-tools-menu">

				<div class="daextam-main-form">

				<div class="daextam-main-form__wrapper-half">

					<div class="daextam-main-form__daext-form-section">

						<div class="daextam-main-form__section-header">
							<div class="daextam-main-form__section-header-title">
								<?php $this->shared->get_admin_helper()->echo_icon_svg( 'log-out-04' ); ?>
								<div class="daextam-main-form__section-header-title-text"><?php esc_html_e( 'Export', 'daext-autolinks-manager' ); ?></div>
							</div>
						</div>

						<div class="daextam-main-form__daext-form-section-body">

							<!-- Export form -->

							<p>
								<?php
								esc_html_e(
									'Click the Export button to generate an XML file that includes automatic link rules, categories and target groups.',
									'daext-autolinks-manager'
								);
								?>
							</p>
							<p>
								<?php
								printf(
									/* translators: %s: link to the Pro Version page */
									esc_html__( 'Note that you can import the resulting file in the Tools menu of the %s to quickly transition between the two plugin editions.', 'daext-autolinks-manager' ),
									'<a href="https://daext.com/link-manager/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Pro Version', 'daext-autolinks-manager' ) . '</a>'
								);
								?>
							</p>

							<!-- the data sent through this form are handled directly in process_form() -->
							<form method="POST" action="admin.php?page=<?php echo esc_attr( $this->shared->get( 'slug' ) ); ?>-<?php echo esc_attr( $this->slug_plural ); ?>">

								<div class="daext-widget-submit">
									<?php wp_nonce_field( 'daextam_tools_export', 'daextam_tools_export' ); ?>
									<input name="daextam_export" class="daextam-btn daextam-btn-primary" type="submit"
											value="<?php esc_attr_e( 'Export', 'daext-autolinks-manager' ); ?>"
										<?php
													if ( ! $this->shared->get_data_import_export()->exportable_data_exists() ) {
											echo 'disabled="disabled"';
										}
										?>
									>
								</div>

							</form>

						</div>

					</div>

				</div>

			</div>

			</div>

		</div>

		<?php
	}
}
