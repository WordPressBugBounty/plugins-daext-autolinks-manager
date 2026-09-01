<?php
/**
 * Statistics feature logic.
 *
 * @package daext-autolinks-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

class Daextam_Statistics {

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
 * Generate the automatic-links statistics table used by the free dashboard.
 *
 * @return void
 */
public function update_statistics() {
$this->shared->get_content_helpers()->set_met_and_ml();

global $wpdb;
$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_statistic" );

$post_types_query      = '';
$analysis_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_analysis_post_types' ) );
if ( ! is_array( $analysis_post_types_a ) || 0 === count( $analysis_post_types_a ) ) {
$analysis_post_types_a = $this->shared->get_content_helpers()->get_post_types_with_ui();
}

if ( is_array( $analysis_post_types_a ) ) {
foreach ( $analysis_post_types_a as $key => $value ) {
$post_types_query .= $wpdb->prepare( 'post_type = %s', $value );
if ( ( count( $analysis_post_types_a ) - 1 ) !== $key ) {
$post_types_query .= ' OR ';
}
}
$post_types_query = '(' . $post_types_query . ') AND';
}

$limit_posts_analysis = intval( get_option( $this->shared->get( 'slug' ) . '_analysis_limit_posts_analysis' ), 10 );
$posts_a              = $wpdb->get_results(
$wpdb->prepare( "SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE $post_types_query post_status = 'publish' ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis ),
ARRAY_A
);

$statistic_a = array();
foreach ( $posts_a as $single_post ) {
$post_id = $single_post['ID'];
$this->shared->get_autolink_engine()->add_autolinks( $single_post['post_content'], false, $single_post['post_type'], $post_id );
$statistic_a[] = array(
'post_id'           => $post_id,
'post_title'        => $single_post['post_title'],
'post_permalink'    => get_the_permalink( $post_id ),
'post_edit_link'    => get_edit_post_link( $post_id, 'url' ),
'post_type'         => $single_post['post_type'],
'post_date'         => $single_post['post_date'],
'content_length'    => mb_strlen( trim( $single_post['post_content'] ) ),
'auto_links'        => $this->shared->get_autolink_engine()->number_of_replacements,
'auto_links_visits' => 0,
);
}

$query_groups = array();
foreach ( $statistic_a as $key => $single_statistic ) {
$query_index = intval( $key / 100, 10 );
$query_groups[ $query_index ][] = $wpdb->prepare(
'( %d, %s, %s, %s, %s, %s, %d, %d, %d )',
$single_statistic['post_id'],
$single_statistic['post_title'],
$single_statistic['post_permalink'],
$single_statistic['post_edit_link'],
$single_statistic['post_type'],
$single_statistic['post_date'],
$single_statistic['content_length'],
$single_statistic['auto_links'],
$single_statistic['auto_links_visits']
);
}

$query_start = "INSERT INTO {$wpdb->prefix}daextam_statistic (post_id, post_title, post_permalink, post_edit_link, post_type, post_date, content_length, auto_links, auto_links_visits) VALUES ";
foreach ( $query_groups as $query_values ) {
$wpdb->query( $query_start . implode( ',', $query_values ) );
}

update_option( $this->shared->get( 'slug' ) . '_statistics_data_last_update', current_time( 'mysql' ) );
}

/**
 * Escape a CSV value.
 *
 * @param string $content Content to escape.
 *
 * @return string
 */
public function esc_csv( $content ) {
return str_replace( '"', '""', $content );
}

/**
 * Delete plugin statistics data.
 *
 * @return void
 */
public function delete_statistics() {
global $wpdb;
$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_statistic" );
$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_il_statistic" );
$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_link_equity" );
$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_anchors" );
update_option( $this->shared->get( 'slug' ) . '_statistics_data_last_update', '' );
}

/**
 * Generate the link equity archive.
 *
 * @return string
 */
public function update_link_equity_archive() {
$this->shared->get_content_helpers()->set_met_and_ml();

global $wpdb;
$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_link_equity" );
$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_anchors" );

$link_equity_a  = array();
$link_equity_id = 0;

/**
 * Create a query used to consider in the analysis only the post types
 * selected with the 'link_equity_post_types' option.
 */
$link_equity_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_link_equity_post_types' ) );

// If the option is empty or not an array, fall back to all post types with a UI.
if ( ! is_array( $link_equity_post_types_a ) || 0 === count( $link_equity_post_types_a ) ) {
	$link_equity_post_types_a = $this->shared->get_content_helpers()->get_post_types_with_ui();
}

$post_types_query = '';
if ( is_array( $link_equity_post_types_a ) ) {
	foreach ( $link_equity_post_types_a as $key => $value ) {
		$post_types_query .= $wpdb->prepare( 'post_type = %s', $value );
		if ( ( count( $link_equity_post_types_a ) - 1 ) !== $key ) {
			$post_types_query .= ' OR ';
		}
	}
}

$limit_posts_analysis = intval( get_option( $this->shared->get( 'slug' ) . '_analysis_limit_posts_analysis' ), 10 );
$posts_a = $wpdb->get_results(
$wpdb->prepare( "SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE ($post_types_query) AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis ),
ARRAY_A
);

foreach ( $posts_a as $single_post ) {
$post_content = $single_post['post_content'];
$post_content = $this->shared->get_content_helpers()->remove_html_comments( $post_content );
$post_content = $this->shared->get_content_helpers()->remove_script_tags( $post_content );
$post_content_with_autolinks = $this->shared->get_autolink_engine()->add_autolinks( $post_content, false, $single_post['post_type'], $single_post['ID'] );

preg_match_all(
$this->shared->get_content_helpers()->manual_and_auto_internal_links_regex(),
$post_content_with_autolinks,
$matches,
PREG_OFFSET_CAPTURE
);

$captures = $matches[2];
foreach ( $captures as $key => $single_capture ) {
$link_position = $matches[0][ $key ][1];
$url           = $this->shared->get_content_helpers()->relative_to_absolute_url( $single_capture[0], $single_post['ID'] );
if ( intval( get_option( $this->shared->get( 'slug' ) . '_remove_link_to_anchor' ), 10 ) === 1 ) {
$url = $this->shared->get_content_helpers()->remove_link_to_anchor( $url );
}
if ( 1 === intval( get_option( $this->shared->get( 'slug' ) . '_remove_url_parameters' ), 10 ) ) {
$url = $this->shared->remove_url_parameters( $url );
}

$link_equity_a[ $link_equity_id ]['url']             = $url;
$link_equity_a[ $link_equity_id ]['link_equity']     = $this->calculate_link_link_equity( $post_content_with_autolinks, $single_post['ID'], $link_position );
$link_equity_a[ $link_equity_id ]['anchor']          = $matches[3][ $key ][0];
$link_equity_a[ $link_equity_id ]['post_id']         = $single_post['ID'];
$link_equity_a[ $link_equity_id ]['post_title']      = $single_post['post_title'];
$link_equity_a[ $link_equity_id ]['post_permalink']  = get_the_permalink( $single_post['ID'] );
$link_equity_a[ $link_equity_id ]['post_edit_link']  = get_edit_post_link( $single_post['ID'], 'url' );
++$link_equity_id;
}
}

$query_groups = array();
foreach ( $link_equity_a as $key => $single_link_equity ) {
$query_index = intval( $key / 100, 10 );
$query_groups[ $query_index ][] = $wpdb->prepare(
'( %s, %s, %d, %d, %s, %s, %s )',
$single_link_equity['url'],
$single_link_equity['anchor'],
$single_link_equity['post_id'],
$single_link_equity['link_equity'],
$single_link_equity['post_title'],
$single_link_equity['post_permalink'],
$single_link_equity['post_edit_link']
);
}

$query_start = "INSERT INTO {$wpdb->prefix}daextam_anchors (url, anchor, post_id, link_equity, post_title, post_permalink, post_edit_link) VALUES ";
foreach ( $query_groups as $query_values ) {
$wpdb->query( $query_start . implode( ',', $query_values ) );
}

$link_equity_a_no_duplicates    = array();
$link_equity_a_no_duplicates_id = 0;
foreach ( $link_equity_a as $single_link_equity ) {
$duplicate_found = false;
foreach ( $link_equity_a_no_duplicates as $key => $single_url ) {
if ( $single_url['url'] === $single_link_equity['url'] ) {
++$link_equity_a_no_duplicates[ $key ]['iil'];
$link_equity_a_no_duplicates[ $key ]['link_equity'] += $single_link_equity['link_equity'];
$duplicate_found = true;
}
}
if ( ! $duplicate_found ) {
$link_equity_a_no_duplicates[ $link_equity_a_no_duplicates_id ]['url']                  = $single_link_equity['url'];
$link_equity_a_no_duplicates[ $link_equity_a_no_duplicates_id ]['iil']                  = 1;
$link_equity_a_no_duplicates[ $link_equity_a_no_duplicates_id ]['link_equity']          = $single_link_equity['link_equity'];
++$link_equity_a_no_duplicates_id;
}
}

$max_value = 0;
foreach ( $link_equity_a_no_duplicates as $single_item ) {
if ( $single_item['link_equity'] > $max_value ) {
$max_value = $single_item['link_equity'];
}
}
foreach ( $link_equity_a_no_duplicates as $key => $single_item ) {
$link_equity_a_no_duplicates[ $key ]['link_equity_relative'] = $max_value > 0 ? ( 100 * $single_item['link_equity'] ) / $max_value : 0;
}

$query_groups = array();
foreach ( $link_equity_a_no_duplicates as $key => $value ) {
$query_index = intval( $key / 100, 10 );
$query_groups[ $query_index ][] = $wpdb->prepare(
'( %s, %d, %d, %d )',
$value['url'],
$value['iil'],
$value['link_equity'],
$value['link_equity_relative']
);
}

$query_start = "INSERT INTO {$wpdb->prefix}daextam_link_equity (url, iil, link_equity, link_equity_relative) VALUES ";
foreach ( $query_groups as $query_values ) {
$wpdb->query( $query_start . implode( ',', $query_values ) );
}

set_transient( $this->shared->get( 'slug' ) . '_link_equity_data_last_update', current_time( 'mysql' ), 0 );
return 'success';
}

/**
 * Calculate the link equity value passed by a single internal link.
 *
 * @param string $post_content_with_autolinks Post content with autolinks applied.
 * @param int    $post_id Post ID.
 * @param int    $link_position Link position.
 *
 * @return int|float
 */
public function calculate_link_link_equity( $post_content_with_autolinks, $post_id, $link_position ) {
$seo_power = get_post_meta( $post_id, '_daextam_seo_power', true );
if ( 0 === strlen( trim( $seo_power ) ) ) {
$seo_power = (int) get_option( $this->shared->get( 'slug' ) . '_default_seo_power' );
}

$link_equity_per_link = $seo_power / max( $this->get_number_of_links( $post_content_with_autolinks ), 1 );
$post_content_before_the_link = substr( $post_content_with_autolinks, 0, $link_position );
$number_of_links_before       = $this->get_number_of_links( $post_content_before_the_link );
$penality_per_position_percentage = (int) get_option( $this->shared->get( 'slug' ) . '_penality_per_position_percentage' );
$link_link_equity             = $link_equity_per_link - ( ( $link_equity_per_link / 100 * $penality_per_position_percentage ) * $number_of_links_before );

if ( $link_link_equity < 0 ) {
$link_link_equity = 0;
}

return $link_link_equity;
}

/**
 * Get the total number of links available in the provided string.
 *
 * @param string $content Content to inspect.
 *
 * @return int
 */
public function get_number_of_links( $content ) {
$content = $this->shared->get_content_helpers()->remove_html_comments( $content );
$content = $this->shared->get_content_helpers()->remove_script_tags( $content );

return preg_match_all(
'{<a[^>]+href\s*=\s*([\'"]?)[^\'">\s]+\1[^>]*>.*?<\/a\s*>}ix',
$content,
$matches
);
}

/**
 * Get the average number of automatic links.
 *
 * @param array $results Result rows.
 *
 * @return int|float
 */
public function get_average_automatic_links( $results ) {
$total = 0;
foreach ( $results as $result ) {
$total += $result->auto_links;
}

return round( $total / count( $results ), 1 );
}

/**
 * Get the average inbound internal links count.
 *
 * @param array $results Result rows.
 *
 * @return int|float
 */
public function get_average_iil( $results ) {
$total = 0;
foreach ( $results as $result ) {
$total += $result->iil;
}

return round( $total / count( $results ), 1 );
}

/**
 * Get the average link equity value.
 *
 * @param array $results Result rows.
 *
 * @return int|float
 */
public function get_average_link_equity( $results ) {
$total = 0;
foreach ( $results as $result ) {
$total += $result->link_equity;
}

return round( $total / count( $results ), 1 );
}

/**
 * Determine if the number of internal links is within the recommended range.
 *
 * @param int $number_of_interlinks Number of internal links.
 * @param int $content_length Content length.
 *
 * @return bool
 */
public function calculate_optimization( $number_of_interlinks, $content_length ) {
$optimization_num_of_characters = 1000;
$optimization_delta             = 2;
$optimal_number_of_interlinks   = (int) $content_length / $optimization_num_of_characters;

return $number_of_interlinks >= ( $optimal_number_of_interlinks - $optimization_delta ) && $number_of_interlinks <= ( $optimal_number_of_interlinks + $optimization_delta );
}

/**
 * Generate the internal-links statistics table used by the "Internal Links" dashboard tab.
 *
 * Analyses every published post of the configured post types and stores per-post metrics
 * (manual/auto interlinks, IIL, recommended interlinks, optimization flag) in the
 * daextam_il_statistic table. Click-tracking columns are set to 0 (feature not available
 * in the free version).
 *
 * @return void
 */
public function update_internal_links_statistics() {

	$this->shared->get_content_helpers()->set_met_and_ml();

	// Regenerate the link equity data so IIL values are fresh.
	$this->update_link_equity_archive();

	global $wpdb;
	$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_il_statistic" );

	// Determine which post types to analyse.
	$post_types_query      = '';
	$analysis_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_analysis_post_types' ) );
	if ( ! is_array( $analysis_post_types_a ) || 0 === count( $analysis_post_types_a ) ) {
		$analysis_post_types_a = $this->shared->get_content_helpers()->get_post_types_with_ui();
	}
	if ( is_array( $analysis_post_types_a ) ) {
		foreach ( $analysis_post_types_a as $key => $value ) {
			$post_types_query .= $wpdb->prepare( 'post_type = %s', $value );
			if ( ( count( $analysis_post_types_a ) - 1 ) !== $key ) {
				$post_types_query .= ' OR ';
			}
		}
		$post_types_query = '(' . $post_types_query . ') AND';
	}

	$limit_posts_analysis = intval( get_option( $this->shared->get( 'slug' ) . '_analysis_limit_posts_analysis' ), 10 );

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	$posts_a = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE $post_types_query post_status = 'publish' ORDER BY post_date DESC LIMIT %d",
			$limit_posts_analysis
		),
		ARRAY_A
	);
	// phpcs:enable

	$statistic_a = array();
	foreach ( $posts_a as $single_post ) {
		$post_id        = $single_post['ID'];
		$post_content   = $single_post['post_content'];
		$content_length = mb_strlen( trim( $post_content ) );

		// IIL: look up the permalink in the link equity table.
		$permalink       = get_the_permalink( $post_id );
		$link_equity_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT iil FROM {$wpdb->prefix}daextam_link_equity WHERE url = %s", $permalink )
		);
		$iil = ( null !== $link_equity_obj ) ? (int) $link_equity_obj->iil : 0;

		// Manual and automatic interlinks.
		$manual_interlinks = $this->shared->get_content_helpers()->get_manual_interlinks( $post_content );
		$post_content_with_autolinks = $this->shared->get_autolink_engine()->add_autolinks( $post_content, false, $single_post['post_type'], $post_id );
		$auto_interlinks   = $this->shared->get_content_helpers()->get_autolinks_number( $post_content_with_autolinks );

		$recommended_interlinks = $this->shared->get_content_helpers()->calculate_recommended_interlinks(
			$manual_interlinks + $auto_interlinks,
			$content_length
		);
		$optimization = $this->calculate_optimization( $manual_interlinks + $auto_interlinks, $content_length );

		$statistic_a[] = array(
			'post_id'                => $post_id,
			'post_title'             => $single_post['post_title'],
			'post_permalink'         => $permalink,
			'post_edit_link'         => get_edit_post_link( $post_id, 'url' ),
			'post_type'              => $single_post['post_type'],
			'post_date'              => $single_post['post_date'],
			'content_length'         => $content_length,
			'manual_interlinks'      => $manual_interlinks,
			'auto_interlinks'        => $auto_interlinks,
			'iil'                    => $iil,
			'recommended_interlinks' => $recommended_interlinks,
			'num_il_clicks'          => 0, // Click tracking is a pro-only feature.
			'optimization'           => $optimization ? 1 : 0,
		);
	}

	// Batch insert in groups of 100.
	$query_start = "INSERT INTO {$wpdb->prefix}daextam_il_statistic (post_id, post_title, post_permalink, post_edit_link, post_type, post_date, content_length, manual_interlinks, auto_interlinks, iil, recommended_interlinks, num_il_clicks, optimization) VALUES ";
	$query_groups = array();
	foreach ( $statistic_a as $key => $s ) {
		$query_index = intval( $key / 100, 10 );
		$query_groups[ $query_index ][] = $wpdb->prepare(
			'( %d, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d, %d )',
			$s['post_id'],
			$s['post_title'],
			$s['post_permalink'],
			$s['post_edit_link'],
			$s['post_type'],
			$s['post_date'],
			$s['content_length'],
			$s['manual_interlinks'],
			$s['auto_interlinks'],
			$s['iil'],
			$s['recommended_interlinks'],
			$s['num_il_clicks'],
			$s['optimization']
		);
	}
	foreach ( $query_groups as $query_values ) {
		$wpdb->query( $query_start . implode( ',', $query_values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	set_transient( $this->shared->get( 'slug' ) . '_statistics_il_data_last_update', current_time( 'mysql' ), 0 );
}

/**
 * Get the average number of manual internal links.
 *
 * @param array $results Result rows from daextam_il_statistic.
 *
 * @return int|float
 */
public function get_average_manual_internal_links( $results ) {
	$total = 0;
	foreach ( $results as $result ) {
		$total += $result->manual_interlinks;
	}
	return round( $total / count( $results ), 1 );
}

/**
 * Get the average number of automatic internal links.
 *
 * @param array $results Result rows from daextam_il_statistic.
 *
 * @return int|float
 */
public function get_average_automatic_internal_links( $results ) {
	$total = 0;
	foreach ( $results as $result ) {
		$total += $result->auto_interlinks;
	}
	return round( $total / count( $results ), 1 );
}

/**
 * Get the average number of inbound internal links.
 *
 * @param array $results Result rows from daextam_il_statistic.
 *
 * @return int|float
 */
public function get_average_inbound_internal_links( $results ) {
	$total = 0;
	foreach ( $results as $result ) {
		$total += $result->iil;
	}
	return round( $total / count( $results ), 1 );
}
}
