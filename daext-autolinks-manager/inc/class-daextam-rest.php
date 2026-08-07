<?php
/**
 * Here the REST API endpoints of the plugin are registered.
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
 * @var self|null
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
add_action( 'init', array( $this, 'rest_api_register_meta' ) );
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
 * Register specific meta fields to the REST API.
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
return true;
},
)
);
}

/**
 * Add custom routes to the REST API.
 *
 * @return void
 */
public function rest_api_register_route() {
register_rest_route( 'daext-autolinks-manager/v1', '/statistics/', array( 'methods' => 'POST', 'callback' => array( $this, 'rest_api_read_statistics' ), 'permission_callback' => array( $this, 'publish_posts_permission_check' ) ) );
register_rest_route( 'daext-autolinks-manager/v1', '/link-equity/', array( 'methods' => 'POST', 'callback' => array( $this, 'rest_api_read_link_equity' ), 'permission_callback' => array( $this, 'publish_posts_permission_check' ) ) );
register_rest_route( 'daext-autolinks-manager/v1', '/link-equity-url/', array( 'methods' => 'POST', 'callback' => array( $this, 'rest_api_read_link_equity_url' ), 'permission_callback' => array( $this, 'publish_posts_permission_check' ) ) );
register_rest_route( 'daext-autolinks-manager/v1', '/read-options/', array( 'methods' => 'POST', 'callback' => array( $this, 'rest_api_read_options' ), 'permission_callback' => array( $this, 'manage_options_permission_check' ) ) );
register_rest_route( 'daext-autolinks-manager/v1', '/options', array( 'methods' => 'POST', 'callback' => array( $this, 'rest_api_update_options' ), 'permission_callback' => array( $this, 'manage_options_permission_check' ) ) );
register_rest_route( 'daext-autolinks-manager/v1', '/options', array( 'methods' => 'GET', 'callback' => array( $this, 'rest_api_read_options_in_editor' ), 'permission_callback' => array( $this, 'edit_posts_permission_check' ) ) );
register_rest_route( 'daext-autolinks-manager/v1', '/generate-interlinks-optimization/', array( 'methods' => 'POST', 'callback' => array( $this, 'rest_api_generate_interlinks_optimization' ), 'permission_callback' => array( $this, 'edit_posts_permission_check' ) ) );
}

/**
 * Permission callback for manage_options screens.
 *
 * @return true|WP_Error
 */
public function manage_options_permission_check() {
if ( ! current_user_can( 'manage_options' ) ) {
return new WP_Error( 'rest_update_error', 'Sorry, you are not allowed to access this resource.', array( 'status' => 403 ) );
}

return true;
}

/**
 * Permission callback for editor tools.
 *
 * @return true|WP_Error
 */
public function edit_posts_permission_check() {
if ( ! current_user_can( 'edit_posts' ) ) {
return new WP_Error( 'rest_update_error', 'Sorry, you are not allowed to access this resource.', array( 'status' => 403 ) );
}

return true;
}

/**
 * Permission callback for publish_posts screens.
 *
 * @return true|WP_Error
 */
public function publish_posts_permission_check() {
if ( ! current_user_can( 'publish_posts' ) ) {
return new WP_Error( 'rest_update_error', 'Sorry, you are not allowed to access this resource.', array( 'status' => 403 ) );
}

return true;
}

/**
 * Read dashboard statistics — dispatches to the correct view handler.
 *
 * Supports two views via the `active_view` parameter:
 *   - 'internal'  → internal-links statistics (daextam_il_statistic)
 *   - 'automatic' → automatic-links statistics (daextam_statistic)  [default]
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 */
public function rest_api_read_statistics( $request ) {
	$active_view = sanitize_key( $request->get_param( 'active_view' ) );

	if ( 'internal' === $active_view ) {
		return $this->rest_api_dashboard_internal_links_view( $request );
	}

	// Default: 'automatic' view.
	return $this->rest_api_dashboard_automatic_links_view( $request );
}

/**
 * Build and return the data used by the "Automatic Links" tab in the Dashboard menu.
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 */
private function rest_api_dashboard_automatic_links_view( $request ) {

	$refresh_statistics = intval( $request->get_param( 'refresh_statistics' ), 10 );

	if ( 0 === $refresh_statistics ) {
		$search_string  = sanitize_text_field( $request->get_param( 'search_string' ) );
		$filter_column  = sanitize_key( $request->get_param( 'filter_column' ) );
		$sorting_column = sanitize_text_field( $request->get_param( 'sorting_column' ) );
		$sorting_order  = sanitize_text_field( $request->get_param( 'sorting_order' ) );
	} else {
		$search_string  = '';
		$filter_column  = 'post_title';
		$sorting_column = 'post_date';
		$sorting_order  = 'desc';
		$this->shared->get_statistics()->update_statistics();
	}

	global $wpdb;
	$filter = '';

	// Whitelist of filterable columns for the automatic-links view.
	$filterable_columns = array(
		'post_title'     => array( 'sql_expr' => 'post_title',     'type' => 'text'    ),
		'post_date'      => array( 'sql_expr' => 'post_date',      'type' => 'text'    ),
		'post_type'      => array( 'sql_expr' => 'post_type',      'type' => 'text'    ),
		'content_length' => array( 'sql_expr' => 'content_length', 'type' => 'numeric' ),
		'auto_links'     => array( 'sql_expr' => 'auto_links',     'type' => 'numeric' ),
	);

	if ( ! array_key_exists( $filter_column, $filterable_columns ) ) {
		$filter_column = 'post_title';
	}

	if ( ! array_key_exists( $sorting_column, $filterable_columns ) ) {
		$sorting_column = 'post_date';
	}

	if ( '' !== $search_string ) {
		$col_def  = $filterable_columns[ $filter_column ];
		$filter  .= 'WHERE ' . $this->build_column_filter_clause( $col_def['sql_expr'], $search_string, $col_def['type'] );
	}

	if ( '' !== $sorting_column ) {
		$filter .= $wpdb->prepare( ' ORDER BY %i', $sorting_column );
	} else {
		$filter .= ' ORDER BY post_date';
	}
	$filter .= ( 'asc' === strtolower( $sorting_order ) ) ? ' ASC' : ' DESC';

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery
	$requests = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextam_statistic $filter" );
	// phpcs:enable

	if ( is_array( $requests ) && count( $requests ) > 0 ) {
		foreach ( $requests as $key => $row ) {
			$requests[ $key ]->formatted_post_date = mysql2date( get_option( 'date_format' ), $row->post_date );
		}
		$response = array(
			'statistics' => array(
				'all_posts'  => count( $requests ),
				'average_al' => $this->shared->get_statistics()->get_average_automatic_links( $requests ),
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

	return new WP_REST_Response( $response );
}

/**
 * Build and return the data used by the "Internal Links" tab in the Dashboard menu.
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 */
private function rest_api_dashboard_internal_links_view( $request ) {

	$refresh_statistics = intval( $request->get_param( 'refresh_statistics' ), 10 );

	if ( 0 === $refresh_statistics ) {
		$search_string  = sanitize_text_field( $request->get_param( 'search_string' ) );
		$filter_column  = sanitize_key( $request->get_param( 'filter_column' ) );
		$sorting_column = sanitize_text_field( $request->get_param( 'sorting_column' ) );
		$sorting_order  = sanitize_text_field( $request->get_param( 'sorting_order' ) );
	} else {
		$search_string  = '';
		$filter_column  = 'post_title';
		$sorting_column = 'post_date';
		$sorting_order  = 'desc';
		$this->shared->get_statistics()->update_internal_links_statistics();
	}

	global $wpdb;
	$filter = '';

	// Whitelist of filterable columns for the internal-links view (no num_il_clicks — pro only).
	$filterable_columns = array(
		'post_title'        => array( 'sql_expr' => 'post_title',       'type' => 'text'    ),
		'post_date'         => array( 'sql_expr' => 'post_date',         'type' => 'text'    ),
		'post_type'         => array( 'sql_expr' => 'post_type',         'type' => 'text'    ),
		'content_length'    => array( 'sql_expr' => 'content_length',    'type' => 'numeric' ),
		'manual_interlinks' => array( 'sql_expr' => 'manual_interlinks', 'type' => 'numeric' ),
		'auto_interlinks'   => array( 'sql_expr' => 'auto_interlinks',   'type' => 'numeric' ),
		'iil'               => array( 'sql_expr' => 'iil',               'type' => 'numeric' ),
		'optimization'      => array( 'sql_expr' => 'optimization',      'type' => 'numeric' ),
	);

	if ( ! array_key_exists( $filter_column, $filterable_columns ) ) {
		$filter_column = 'post_title';
	}

	if ( ! array_key_exists( $sorting_column, $filterable_columns ) ) {
		$sorting_column = 'post_date';
	}

	if ( '' !== $search_string ) {
		$col_def  = $filterable_columns[ $filter_column ];
		$filter  .= 'WHERE ' . $this->build_column_filter_clause( $col_def['sql_expr'], $search_string, $col_def['type'] );
	}

	if ( '' !== $sorting_column ) {
		$filter .= $wpdb->prepare( ' ORDER BY %i', $sorting_column );
	} else {
		$filter .= ' ORDER BY post_date';
	}
	$filter .= ( 'asc' === strtolower( $sorting_order ) ) ? ' ASC' : ' DESC';

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery
	$requests = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextam_il_statistic $filter" );
	// phpcs:enable

	if ( is_array( $requests ) && count( $requests ) > 0 ) {
		foreach ( $requests as $key => $row ) {
			$requests[ $key ]->formatted_post_date = mysql2date( get_option( 'date_format' ), $row->post_date );
		}
		$response = array(
			'statistics' => array(
				'all_posts'             => count( $requests ),
				'average_mil'           => $this->shared->get_statistics()->get_average_manual_internal_links( $requests ),
				'average_ail'           => $this->shared->get_statistics()->get_average_automatic_internal_links( $requests ),
				'average_inbound_links' => $this->shared->get_statistics()->get_average_inbound_internal_links( $requests ),
			),
			'table'      => $requests,
		);
	} else {
		$response = array(
			'statistics' => array(
				'all_posts'             => 0,
				'average_mil'           => 'N/A',
				'average_ail'           => 'N/A',
				'average_inbound_links' => 'N/A',
			),
			'table'      => array(),
		);
	}

	return new WP_REST_Response( $response );
}

/**
 * Read link equity data.
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 */
public function rest_api_read_link_equity( $request ) {
$refresh_statistics = intval( $request->get_param( 'refresh_statistics' ), 10 );

if ( 0 === $refresh_statistics ) {
$search_string  = sanitize_text_field( $request->get_param( 'search_string' ) );
$filter_column  = sanitize_key( $request->get_param( 'filter_column' ) );
$sorting_column = sanitize_text_field( $request->get_param( 'sorting_column' ) );
$sorting_order  = sanitize_text_field( $request->get_param( 'sorting_order' ) );
} else {
$search_string  = '';
$filter_column  = 'url';
$sorting_column = 'link_equity';
$sorting_order  = 'desc';
$this->shared->get_statistics()->update_link_equity_archive();
}

global $wpdb;
$filter = '';
$filterable_columns = array(
'url'         => array( 'sql_expr' => 'url', 'type' => 'text' ),
'iil'         => array( 'sql_expr' => 'iil', 'type' => 'numeric' ),
'link_equity' => array( 'sql_expr' => 'link_equity', 'type' => 'numeric' ),
);

if ( ! array_key_exists( $filter_column, $filterable_columns ) ) {
$filter_column = 'url';
}

if ( '' !== $search_string ) {
$col_def = $filterable_columns[ $filter_column ];
$filter .= 'WHERE ' . $this->build_column_filter_clause( $col_def['sql_expr'], $search_string, $col_def['type'] );
}

$allowed_sort_columns = array( 'url', 'iil', 'link_equity', 'link_equity_relative' );
if ( in_array( $sorting_column, $allowed_sort_columns, true ) ) {
$filter .= $wpdb->prepare( ' ORDER BY %i', $sorting_column );
} else {
$filter .= ' ORDER BY link_equity';
}

$filter .= ( 'asc' === strtolower( $sorting_order ) ) ? ' ASC' : ' DESC';

$requests = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextam_link_equity $filter" );

if ( is_array( $requests ) && count( $requests ) > 0 ) {
$response = array(
'statistics' => array(
'all_urls'            => count( $requests ),
'average_iil'         => $this->shared->get_statistics()->get_average_iil( $requests ),
'average_link_equity' => $this->shared->get_statistics()->get_average_link_equity( $requests ),
),
'table'      => $requests,
);
} else {
$response = array(
'statistics' => array(
'all_urls'            => 0,
'average_iil'         => 'N/A',
'average_link_equity' => 'N/A',
),
'table'      => array(),
);
}

return new WP_REST_Response( $response );
}

/**
 * Read the anchors attached to a link equity row.
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return void
 */
public function rest_api_read_link_equity_url( $request ) {
$data            = array();
$link_equity_max = 0;
$link_equity_id  = sanitize_text_field( $request->get_param( 'id' ) );

global $wpdb;
$link_equity_obj = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextam_link_equity WHERE id = %d", $link_equity_id ), OBJECT );
$results         = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextam_anchors WHERE url = %s ORDER BY id ASC", $link_equity_obj->url ), ARRAY_A );

if ( count( $results ) > 0 ) {
foreach ( $results as $result ) {
if ( $result['link_equity'] > $link_equity_max ) {
$link_equity_max = $result['link_equity'];
}
}
} else {
echo 'no data';
die();
}

$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextam_anchors WHERE url = %s ORDER BY link_equity DESC", $link_equity_obj->url ), ARRAY_A );
if ( count( $results ) > 0 ) {
foreach ( $results as $result ) {
$data[] = array(
'id'               => $result['id'],
'postTitle'        => $result['post_title'],
'linkEquity'       => intval( $result['link_equity'], 10 ),
'linkEquityVisual' => $link_equity_max > 0 ? intval( 100 * $result['link_equity'] / $link_equity_max, 10 ) : 0,
'anchor'           => $result['anchor'],
'postId'           => intval( $result['post_id'], 10 ),
'postPermalink'    => $result['post_permalink'],
'postEditLink'     => $result['post_edit_link'],
);
}
} else {
echo 'no data';
die();
}

echo wp_json_encode( $data );
die();
}

/**
 * Return all option values.
 *
 * @return WP_REST_Response
 */
public function rest_api_read_options() {
$response = array();
foreach ( $this->shared->get( 'options' ) as $key => $value ) {
$response[ $key ] = get_option( $key );
}

return new WP_REST_Response( $response );
}

/**
 * Update the free option set.
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 */
public function rest_api_update_options( $request ) {
$text_keys = array(
'daextam_advanced_categories_and_tags_verification',
'daextam_statistics_data_update_frequency',
'daextam_link_equity_data_update_frequency',
'daextam_advanced_protected_gutenberg_custom_blocks',
'daextam_advanced_protected_gutenberg_custom_void_blocks',
);

foreach ( $this->shared->get( 'options' ) as $key => $default_value ) {
$param = $request->get_param( $key );
if ( null === $param ) {
continue;
}

if ( is_array( $default_value ) ) {
$value = is_array( $param ) ? array_map( 'sanitize_text_field', $param ) : array();
} elseif ( in_array( $key, $text_keys, true ) ) {
$value = sanitize_text_field( $param );
} else {
$value = intval( $param, 10 );
}

update_option( $key, $value );
}

return new WP_REST_Response( array( 'success' => true ) );
}

/**
 * Return option values used by the editor sidebar.
 *
 * @return WP_REST_Response
 */
public function rest_api_read_options_in_editor() {
$automatic_links_panel_post_types_a   = maybe_unserialize( get_option( 'daextam_automatic_links_panel_post_types' ) );
$interlinks_optimization_post_types_a = maybe_unserialize( get_option( 'daextam_interlinks_optimization_post_types' ) );

$response = array(
'daextam_advanced_enable_autolinks'                      => get_option( 'daextam_advanced_enable_autolinks' ),
'user_has_interlinks_optimization_mb_required_capability' => current_user_can( 'edit_posts' ) ? 1 : 0,
'interlinks_optimization_is_active_in_post_type'         => ( is_array( $interlinks_optimization_post_types_a ) && in_array( get_post_type(), $interlinks_optimization_post_types_a, true ) ) ? 1 : 0,
);

return new WP_REST_Response( $response );
}

/**
 * Generate optimization data for the editor sidebar.
 *
 * @param WP_REST_Request $request Request data.
 *
 * @return WP_REST_Response
 */
public function rest_api_generate_interlinks_optimization( $request ) {
$this->shared->get_content_helpers()->set_met_and_ml();
$post_id = $request->get_param( 'id' ) !== null ? intval( $request->get_param( 'id' ), 10 ) : null;
$post    = get_post( $post_id );

$post_content_with_autolinks = $this->shared->get_autolink_engine()->add_autolinks( $post->post_content, false, $post->post_type, $post->ID );
$data = array(
'suggested_min_number_of_interlinks' => $this->shared->get_content_helpers()->get_suggested_min_number_of_interlinks( $post->ID ),
'suggested_max_number_of_interlinks' => $this->shared->get_content_helpers()->get_suggested_max_number_of_interlinks( $post->ID ),
'post_content_with_autolinks'        => $post_content_with_autolinks,
'number_of_manual_interlinks'        => $this->shared->get_content_helpers()->get_manual_interlinks( $post->post_content ),
'number_of_autolinks'                => $this->shared->get_content_helpers()->get_autolinks_number( $post_content_with_autolinks ),
);
$data['total_number_of_interlinks'] = $data['number_of_manual_interlinks'] + $data['number_of_autolinks'];

return new WP_REST_Response( $data, 200 );
}

/**
 * Build a WHERE clause fragment for a single column filter.
 *
 * @param string $col_expr SQL column expression.
 * @param string $search_value Raw search value.
 * @param string $type Column type.
 *
 * @return string
 */
private function build_column_filter_clause( $col_expr, $search_value, $type ) {
global $wpdb;

if ( 'text' === $type ) {
return $wpdb->prepare( "($col_expr LIKE %s)", '%' . $wpdb->esc_like( $search_value ) . '%' );
}

$search_value = trim( $search_value );
$operator     = '=';
$numeric_val  = $search_value;

if ( preg_match( '/^(>=|<=|>|<|=)\s*(.+)$/', $search_value, $matches ) ) {
$operator    = $matches[1];
$numeric_val = trim( $matches[2] );
}

if ( ! is_numeric( $numeric_val ) ) {
return '(1 = 0)';
}

if ( false !== strpos( $numeric_val, '.' ) ) {
return $wpdb->prepare( "($col_expr $operator %f)", floatval( $numeric_val ) );
}

return $wpdb->prepare( "($col_expr $operator %d)", intval( $numeric_val ) );
}

}
