<?php
/**
 * Here the REST API endpoint of the plugin are registered.
 *
 * @package daext-autolinks-manager
 */

/**
 * This class should be used to work with the REST API endpoints of the plugin.
 */
class Daextam_Rest {

	/**
	 * The singleton instance of the class.
	 *
	 * @var null
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

		// Assign an instance of the shared class.
		$this->shared = Daextam_Shared::get_instance();

		/**
		 * Register specific meta fields to the Rest API
		 */
		add_action( 'init', array( $this, 'rest_api_register_meta' ) );

		/**
		 * Add custom routes to the Rest API.
		 */
		add_action( 'rest_api_init', array( $this, 'rest_api_register_route' ) );
	}

	/**
	 * Create a singleton instance of the class.
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
	 * Register specific meta fields to the Rest API.
	 *
	 * @return void
	 */
	public function rest_api_register_meta() {

		register_meta(
			'post',
			'_daextam_enable_autolinks',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return true;},
			)
		);
	}

	/**
	 * Add custom routes to the Rest API.
	 *
	 * @return void
	 */
	public function rest_api_register_route() {

		// Add the POST 'daext-autolinks-manager/v1/statistics/' endpoint to the Rest API.
		register_rest_route(
			'daext-autolinks-manager/v1',
			'/statistics/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_autolinks_manager_pro_read_statistics_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_autolinks_manager_pro_read_statistics_callback_permission_check' ),
			)
		);

		// Add the POST 'daext-autolinks-manager/v1/read-options/' endpoint to the Rest API.
		register_rest_route(
			'daext-autolinks-manager/v1',
			'/read-options/',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_autolinks_manager_pro_read_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_autolinks_manager_pro_read_options_callback_permission_check' ),
			)
		);

		// Add the POST 'daext-autolinks-manager/v1/options/' endpoint to the Rest API.
		register_rest_route(
			'daext-autolinks-manager/v1',
			'/options',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_daext_autolinks_manager_pro_update_options_callback' ),
				'permission_callback' => array( $this, 'rest_api_daext_autolinks_manager_pro_update_options_callback_permission_check' ),
			)
		);
	}

	/**
	 * Callback for the POST 'daext-autolinks-manager/v1/statistics' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 * - In the "Dashboard" menu to retrieve the statistics of the links on the posts.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_daext_autolinks_manager_pro_read_statistics_callback( $request ) {

		$data_update_required = intval( $request->get_param( 'data_update_required' ), 10 );

		if ( 0 === $data_update_required ) {

			// Use the provided form data.
			$search_string  = sanitize_text_field( $request->get_param( 'search_string' ) );
			$sorting_column = sanitize_text_field( $request->get_param( 'sorting_column' ) );
			$sorting_order  = sanitize_text_field( $request->get_param( 'sorting_order' ) );

		} else {

			// Set the default values of the form data.
			$search_string  = '';
			$sorting_column = 'post_date';
			$sorting_order  = 'desc';

			// Run update_statistics() to update the archive with the statistics.
			$this->shared->update_statistics();

		}

		// Create the WHERE part of the query based on the $optimization_status value.
		global $wpdb;
		$filter = '';

		// Create the WHERE part of the string based on the $search_string value.
		if ( '' !== $search_string ) {
			if ( strlen( $filter ) === 0 ) {
				$filter .= $wpdb->prepare( 'WHERE (post_title LIKE %s)', '%' . $search_string . '%' );
			} else {
				$filter .= $wpdb->prepare( ' AND (post_title LIKE %s)', '%' . $search_string . '%' );

			}
		}

		// Create the ORDER BY part of the query based on the $sorting_column and $sorting_order values.
		if ( '' !== $sorting_column ) {
			$filter .= ' ORDER BY ' . sanitize_key( $sorting_column );
		} else {
			$filter .= ' ORDER BY post_date';
		}

		if ( 'desc' === $sorting_order ) {
			$filter .= ' DESC';
		} else {
			$filter .= ' ASC';
		}

		// Get the data from the "_archive" db table using $wpdb and put them in the $response array.

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $filter is prepared.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$requests = $wpdb->get_results(
			"
			SELECT *
			FROM {$wpdb->prefix}daextam_statistic $filter"
		);
		// phpcs:enable

		if ( is_array( $requests ) && count( $requests ) > 0 ) {

			/**
			 * Add the formatted date (based on the date format defined in the WordPress settings) to the $requests
			 * array.
			 */
			foreach ( $requests as $key => $request ) {
				$requests[ $key ]->formatted_post_date = mysql2date( get_option( 'date_format' ), $request->post_date );
			}

			$response = array(
				'statistics' => array(
					'all_posts'  => count( $requests ),
					'average_al' => $this->shared->get_average_automatic_links( $requests ),
				),
				'table'      => $requests,
			);

		} else {

			$response = array(
				'statistics' => array(
					'all_posts'  => 0,
					'average_al' => 'N/A',
				),
				'table'      => array(),
			);

		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_autolinks_manager_pro_read_statistics_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to read the Autolinks Manager statistics.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the GET 'daext-autolinks-manager/v1/options' endpoint of the Rest API.
	 *
	 *   This method is in the following contexts:
	 *
	 *  - To retrieve the plugin options in the "Options" menu.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function rest_api_daext_autolinks_manager_pro_read_options_callback() {

		// Generate the response.
		$response = array();
		foreach ( $this->shared->get( 'options' ) as $key => $value ) {
			$response[ $key ] = get_option( $key );
		}

		// Prepare the response.
		$response = new WP_REST_Response( $response );

		return $response;
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_autolinks_manager_pro_read_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_read_error',
				'Sorry, you are not allowed to read the Autolinks Manager options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Callback for the POST 'daext-autolinks-manager/v1/options' endpoint of the Rest API.
	 *
	 * This method is in the following contexts:
	 *
	 *  - To update the plugin options in the "Options" menu.
	 *
	 * @param object $request The request data.
	 *
	 * @return WP_REST_Response
	 */
	public function rest_api_daext_autolinks_manager_pro_update_options_callback( $request ) {

		// get and sanitize data --------------------------------------------------------------------------------------.

		$options = array();

		// Tab - Automatic Links --------------------------------------------------------------------------------------.

		// Section - Options ------------------------------------------------------------------------------------------.

		$options['daextam_advanced_enable_autolinks']                      = $request->get_param( 'daextam_advanced_enable_autolinks' ) !== null ? intval( $request->get_param( 'daextam_advanced_enable_autolinks' ), 10 ) : null;
		$options['daextam_advanced_filter_priority']                       = $request->get_param( 'daextam_advanced_filter_priority' ) !== null ? intval( $request->get_param( 'daextam_advanced_filter_priority' ), 10 ) : null;
		$options['daextam_advanced_enable_test_mode']                      = $request->get_param( 'daextam_advanced_enable_test_mode' ) !== null ? intval( $request->get_param( 'daextam_advanced_enable_test_mode' ), 10 ) : null;
		$options['daextam_advanced_random_prioritization']                 = $request->get_param( 'daextam_advanced_random_prioritization' ) !== null ? intval( $request->get_param( 'daextam_advanced_random_prioritization' ), 10 ) : null;
		$options['daextam_advanced_ignore_self_autolinks']                 = $request->get_param( 'daextam_advanced_ignore_self_autolinks' ) !== null ? intval( $request->get_param( 'daextam_advanced_ignore_self_autolinks' ), 10 ) : null;
		$options['daextam_advanced_categories_and_tags_verification']      = $request->get_param( 'daextam_advanced_categories_and_tags_verification' ) !== null ? sanitize_key( $request->get_param( 'daextam_advanced_categories_and_tags_verification' ) ) : null;
		$options['daextam_advanced_general_limit_mode']                    = $request->get_param( 'daextam_advanced_general_limit_mode' ) !== null ? intval( $request->get_param( 'daextam_advanced_general_limit_mode' ), 10 ) : null;
		$options['daextam_advanced_general_limit_characters_per_autolink'] = $request->get_param( 'daextam_advanced_general_limit_characters_per_autolink' ) !== null ? intval( $request->get_param( 'daextam_advanced_general_limit_characters_per_autolink' ), 10 ) : null;
		$options['daextam_advanced_general_limit_amount']                  = $request->get_param( 'daextam_advanced_general_limit_amount' ) !== null ? intval( $request->get_param( 'daextam_advanced_general_limit_amount' ), 10 ) : null;
		$options['daextam_advanced_same_url_limit']                        = $request->get_param( 'daextam_advanced_same_url_limit' ) !== null ? intval( $request->get_param( 'daextam_advanced_same_url_limit' ), 10 ) : null;
		$options['daextam_advanced_protect_attributes']                    = $request->get_param( 'daextam_advanced_protect_attributes' ) !== null ? intval( $request->get_param( 'daextam_advanced_protect_attributes' ), 10 ) : null;

		// Section - Protected Elements -------------------------------------------------------------------------------.

		$options['daextam_advanced_protected_tags']                         = $request->get_param( 'daextam_advanced_protected_tags' ) !== null && is_array( $request->get_param( 'daextam_advanced_protected_tags' ) ) ? array_map( 'sanitize_text_field', $request->get_param( 'daextam_advanced_protected_tags' ) ) : null;
		$options['daextam_advanced_protected_gutenberg_blocks']             = $request->get_param( 'daextam_advanced_protected_gutenberg_blocks' ) !== null && is_array( $request->get_param( 'daextam_advanced_protected_gutenberg_blocks' ) ) ? array_map( 'sanitize_text_field', $request->get_param( 'daextam_advanced_protected_gutenberg_blocks' ) ) : null;
		$options['daextam_advanced_protected_gutenberg_custom_blocks']      = $request->get_param( 'daextam_advanced_protected_gutenberg_custom_blocks' ) !== null ? sanitize_text_field( $request->get_param( 'daextam_advanced_protected_gutenberg_custom_blocks' ) ) : null;
		$options['daextam_advanced_protected_gutenberg_custom_void_blocks'] = $request->get_param( 'daextam_advanced_protected_gutenberg_custom_void_blocks' ) !== null ? sanitize_text_field( $request->get_param( 'daextam_advanced_protected_gutenberg_custom_void_blocks' ) ) : null;

		// Section - Defaults -----------------------------------------------------------------------------------------.

		$options['daextam_defaults_category_id']           = $request->get_param( 'daextam_defaults_category_id' ) !== null ? intval( $request->get_param( 'daextam_defaults_category_id' ), 10 ) : null;
		$options['daextam_defaults_open_new_tab']          = $request->get_param( 'daextam_defaults_open_new_tab' ) !== null ? intval( $request->get_param( 'daextam_defaults_open_new_tab' ), 10 ) : null;
		$options['daextam_defaults_use_nofollow']          = $request->get_param( 'daextam_defaults_use_nofollow' ) !== null ? intval( $request->get_param( 'daextam_defaults_use_nofollow' ), 10 ) : null;
		$options['daextam_defaults_post_types']            = $request->get_param( 'daextam_defaults_post_types' ) !== null && is_array( $request->get_param( 'daextam_defaults_post_types' ) ) ? array_map( 'sanitize_text_field', $request->get_param( 'daextam_defaults_post_types' ) ) : null;
		$options['daextam_defaults_categories']            = $request->get_param( 'daextam_defaults_categories' ) !== null && is_array( $request->get_param( 'daextam_defaults_categories' ) ) ? array_map( 'sanitize_text_field', $request->get_param( 'daextam_defaults_categories' ) ) : null;
		$options['daextam_defaults_tags']                  = $request->get_param( 'daextam_defaults_tags' ) !== null && is_array( $request->get_param( 'daextam_defaults_tags' ) ) ? array_map( 'sanitize_text_field', $request->get_param( 'daextam_defaults_tags' ) ) : null;
		$options['daextam_defaults_term_group_id']         = $request->get_param( 'daextam_defaults_term_group_id' ) !== null ? intval( $request->get_param( 'daextam_defaults_term_group_id' ), 10 ) : null;
		$options['daextam_defaults_case_sensitive_search'] = $request->get_param( 'daextam_defaults_case_sensitive_search' ) !== null ? intval( $request->get_param( 'daextam_defaults_case_sensitive_search' ), 10 ) : null;
		$options['daextam_defaults_left_boundary']         = $request->get_param( 'daextam_defaults_left_boundary' ) !== null ? intval( $request->get_param( 'daextam_defaults_left_boundary' ), 10 ) : null;
		$options['daextam_defaults_right_boundary']        = $request->get_param( 'daextam_defaults_right_boundary' ) !== null ? intval( $request->get_param( 'daextam_defaults_right_boundary' ), 10 ) : null;
		$options['daextam_defaults_limit']                 = $request->get_param( 'daextam_defaults_limit' ) !== null ? intval( $request->get_param( 'daextam_defaults_limit' ), 10 ) : null;
		$options['daextam_defaults_priority']              = $request->get_param( 'daextam_defaults_priority' ) !== null ? intval( $request->get_param( 'daextam_defaults_priority' ), 10 ) : null;

		// Tab - Link Analysis ----------------------------------------------------------------------------------------.

		// Section - Technical Options --------------------------------------------------------------------------------.

		$options['daext_analysis_set_max_execution_time']     = $request->get_param( 'daext_analysis_set_max_execution_time' ) !== null ? intval( $request->get_param( 'daext_analysis_set_max_execution_time' ), 10 ) : null;
		$options['daextam_analysis_max_execution_time_value'] = $request->get_param( 'daextam_analysis_max_execution_time_value' ) !== null ? intval( $request->get_param( 'daextam_analysis_max_execution_time_value' ), 10 ) : null;
		$options['daextam_analysis_set_memory_limit']         = $request->get_param( 'daextam_analysis_set_memory_limit' ) !== null ? intval( $request->get_param( 'daextam_analysis_set_memory_limit' ), 10 ) : null;
		$options['daextam_analysis_memory_limit_value']       = $request->get_param( 'daextam_analysis_memory_limit_value' ) !== null ? intval( $request->get_param( 'daextam_analysis_memory_limit_value' ), 10 ) : null;
		$options['daextam_analysis_limit_posts_analysis']     = $request->get_param( 'daextam_analysis_limit_posts_analysis' ) !== null ? intval( $request->get_param( 'daextam_analysis_limit_posts_analysis' ), 10 ) : null;
		$options['daextam_analysis_post_types']               = $request->get_param( 'daextam_analysis_post_types' ) !== null && is_array( $request->get_param( 'daextam_analysis_post_types' ) ) ? array_map( 'sanitize_text_field', $request->get_param( 'daextam_analysis_post_types' ) ) : null;
		$options['daextam_statistics_data_update_frequency']  = $request->get_param( 'daextam_statistics_data_update_frequency' ) !== null ? sanitize_key( $request->get_param( 'daextam_statistics_data_update_frequency' ) ) : null;

		// Tab - Advanced ---------------------------------------------------------------------------------------------.

		// Section - Misc ---------------------------------------------------------------------------------------------.

		$options['daextam_advanced_supported_terms'] = $request->get_param( 'daextam_advanced_supported_terms' ) !== null ? intval( $request->get_param( 'daextam_advanced_supported_terms' ), 10 ) : null;

		foreach ( $options as $key => $option ) {
			if ( null !== $option ) {
				update_option( $key, $option );
			}
		}

		return new WP_REST_Response( 'Data successfully added.', '200' );
	}

	/**
	 * Check the user capability.
	 *
	 * @return true|WP_Error
	 */
	public function rest_api_daext_autolinks_manager_pro_update_options_callback_permission_check() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_update_error',
				'Sorry, you are not allowed to update the Autolinks Manager options.',
				array( 'status' => 403 )
			);
		}

		return true;
	}

}
