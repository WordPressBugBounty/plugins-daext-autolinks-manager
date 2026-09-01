<?php
/**
 * Data import/export feature logic.
 *
 * This class encapsulates the XML import/export routines used by the
 * "Import & Export" admin menu.
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data import/export feature class.
 */
class Daextam_Data_Import_Export {

	/**
	 * Shared plugin instance.
	 *
	 * @var Daextam_Shared
	 */
	private $shared;

	/**
	 * Constructor.
	 *
	 * @param Daextam_Shared $shared Shared plugin instance.
	 */
	public function __construct( $shared ) {
		$this->shared = $shared;
	}

	/**
	 * Returns true if there are exportable data or false if there are no exportable data.
	 *
	 * @return bool
	 */
	public function exportable_data_exists() {

		$exportable_data = false;
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daextam_autolink" );
		if ( $total_items > 0 ) {
			$exportable_data = true;
		}

		return $exportable_data;
	}

	/**
	 * Generates the XML version of the data of the table.
	 *
	 * @param string $db_table_name The name of the db table without the prefix.
	 * @param string $db_table_primary_key The name of the primary key of the table.
	 *
	 * @return void
	 */
	public function convert_db_table_to_xml( $db_table_name, $db_table_primary_key ) {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$data_a = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i ORDER BY %i ASC',
				$wpdb->prefix . 'daextam_' . $db_table_name,
				$db_table_primary_key
			),
			ARRAY_A
		);
		// phpcs:enable

		foreach ( $data_a as $record ) {

			echo '<' . esc_attr( $db_table_name ) . '>';

			$record_keys = array_keys( $record );
			foreach ( $record_keys as $key ) {
				echo '<' . esc_attr( $key ) . '>' . esc_attr( $record[ $key ] ) . '</' . esc_attr( $key ) . '>';
			}

			echo '</' . esc_attr( $db_table_name ) . '>';
		}
	}

}
