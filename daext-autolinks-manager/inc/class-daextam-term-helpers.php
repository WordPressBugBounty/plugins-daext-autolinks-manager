<?php
/**
 * Term helpers feature logic.
 *
 * This class encapsulates the helper methods related to plugin categories,
 * target groups and WordPress categories/tags, including the WordPress
 * "delete_term" action callback.
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Term helpers feature class.
 */
class Daextam_Term_Helpers {

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
	 * Returns True if the post has the categories required by the autolink or if the autolink doesn't require any
	 *  specific category.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $autolink An array with the autolink data.
	 *
	 * @return bool
	 */
	public function is_compliant_with_categories( $post_id, $autolink ) {

		$autolink_categories_a = maybe_unserialize( $autolink['categories'] );
		$post_categories       = get_the_terms( $post_id, 'category' );
		$category_found        = false;

		// If no categories are specified return true.
		if ( ! is_array( $autolink_categories_a ) ) {
			return true;
		}

		/*
		 * Do not proceed with the application of the autolink if in this post no categories included in
		 * $autolink_categories_a are available.
		 */
		foreach ( $post_categories as $key => $post_single_category ) {
			if ( in_array( $post_single_category->term_id, $autolink_categories_a, true ) ) {
				$category_found = true;
			}
		}

		if ( $category_found ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns True if the post has the tags required by the autolink or if the autolink doesn't require any specific
	 *  tag.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $autolink An array with the autolink data.
	 *
	 * @return bool
	 */
	public function is_compliant_with_tags( $post_id, $autolink ) {

		$autolink_tags_a = maybe_unserialize( $autolink['tags'] );
		$post_tags       = get_the_terms( $post_id, 'post_tag' );
		$tag_found       = false;

		// If no tags are specified return true.
		if ( ! is_array( $autolink_tags_a ) ) {
			return true;
		}

		if ( false !== $post_tags ) {

			/**
			 * Do not proceed with the application of the autolink if this post has at least one tag but no tags
			 * included in $autolink_tags_a are available.
			 */
			foreach ( $post_tags as $key => $post_single_tag ) {
				if ( in_array( $post_single_tag->term_id, $autolink_tags_a, true ) ) {
					$tag_found = true;
				}
			}
			if ( ! $tag_found ) {
				return false;
			}
		} else {

			// Do not proceed with the application of the autolink if this post has no tags associated.
			return false;

		}

		return true;
	}

	/**
	 * Verifies if the post includes at least one term included in the target group associated with the autolink.
	 *
	 *  In the following conditions True is returned:
	 *
	 *  - When a target group is not set
	 *  - When the post has at least one term present in the target group
	 *
	 * @param int    $post_id          The ID of the post.
	 * @param array  $autolink         The array with the autolink data.
	 * @param string $parsed_post_type The post type of the post currently being parsed.
	 *
	 * @return bool
	 */
	public function is_compliant_with_term_group( $post_id, $autolink, $parsed_post_type ) {

		$supported_terms = intval( get_option( $this->shared->get( 'slug' ) . '_advanced_supported_terms' ), 10 );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_group_obj = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}daextam_term_group WHERE term_group_id = %d ",
				$autolink['term_group_id']
			)
		);

		if ( null !== $term_group_obj ) {

			for ( $i = 1; $i <= $supported_terms; $i++ ) {

				$post_type = $term_group_obj->{'post_type_' . $i};
				$taxonomy  = $term_group_obj->{'taxonomy_' . $i};
				$term      = $term_group_obj->{'term_' . $i};

				// Verify post type, taxonomy and term as specified in the target group.
				if ( $post_type === $parsed_post_type && has_term( $term, $taxonomy, $post_id ) ) {
					return true;
				}
			}

			return false;

		}

		return true;
	}

	/**
	 * Returns true if the category with the specified $category_id exists.
	 *
	 * @param int $category_id The category ID.
	 *
	 * @return bool
	 */
	public function category_exists( $category_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daextam_category WHERE category_id = %d", $category_id )
		);

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if one or more automatic links are using the specified category.
	 *
	 * @param int $category_id The category ID.
	 * @return bool
	 */
	public function category_is_used( $category_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daextam_autolink WHERE category_id = %d", $category_id )
		);

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if the target group with the specified $term_group_id exists.
	 *
	 * @param int $term_group_id The target group ID.
	 * @return bool
	 */
	public function term_group_exists( $term_group_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daextam_term_group WHERE term_group_id = %d", $term_group_id )
		);

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if one or more automatic links are using the specified target group.
	 *
	 * @param int $term_group_id The term group ID.
	 * @return bool
	 */
	public function term_group_is_used( $term_group_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daextam_autolink WHERE term_group_id = %d", $term_group_id )
		);

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Given the category ID the category name is returned.
	 *
	 * @param int $category_id The ID of the category.
	 * @return String
	 */
	public function get_category_name( $category_id ) {

		if ( intval( $category_id, 10 ) === 0 ) {
			return esc_html__( 'None', 'daext-autolinks-manager' );
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$category_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextam_category WHERE category_id = %d ", $category_id )
		);

		return $category_obj->name;
	}

	/**
	 * Fires after a term is deleted from the database and the cache is cleaned.
	 *
	 *  The following tasks are performed:
	 *
	 *  Part 1 - Deletes the $term_id found in the categories field of the automatic links
	 *  Part 2 - Deletes the $term_id found in the tags field of the automatic links
	 *  Part 3 - Deletes the $term_id found in the 50 term_[n] fields of the term groups
	 *
	 * @param int $term_id The term ID.
	 */
	public function delete_term_action( $term_id ) {

		// Part 1-2 ---------------------------------------------------------------------------------------------------.

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$autolink_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextam_autolink ORDER BY autolink_id ASC", ARRAY_A );

		if ( null !== $autolink_a && count( $autolink_a ) > 0 ) {

			foreach ( $autolink_a as $key1 => $autolink ) {

				// Delete the term in the categories field of the automatic links.
				$category_term_a = maybe_unserialize( $autolink['categories'] );
				if ( is_array( $category_term_a ) && count( $category_term_a ) > 0 ) {
					foreach ( $category_term_a as $key2 => $category_term ) {
						if ( intval( $category_term, 10 ) === $term_id ) {
							unset( $category_term_a[ $key2 ] );
						}
					}
				}
				$category_term_a_serialized = maybe_serialize( $category_term_a );

				// Delete the term in the tags field of the automatic links.
				$tag_term_a = maybe_unserialize( $autolink['tags'] );
				if ( is_array( $tag_term_a ) && count( $tag_term_a ) > 0 ) {
					foreach ( $tag_term_a as $key2 => $tag_term ) {
						if ( intval( $tag_term, 10 ) === $term_id ) {
							unset( $tag_term_a[ $key2 ] );
						}
					}
				}
				$tag_term_a_serialized = maybe_serialize( $tag_term_a );

				// Update the record of the database if $categories or $tags are changed.
				if ( $autolink['categories'] !== $category_term_a_serialized ||
					$autolink['tags'] !== $tag_term_a_serialized ) {

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}daextam_autolink SET 
                        categories = %s,
                        tags = %s
                        WHERE autolink_id = %d",
							$category_term_a_serialized,
							$tag_term_a_serialized,
							$autolink['autolink_id']
						)
					);

				}
			}
		}

		// Part 3 -----------------------------------------------------------------------------------------------------.

		// Delete the term in all the 50 term_[n] field of the term groups.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_group_a = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextam_term_group ORDER BY term_group_id ASC", ARRAY_A );

		if ( null !== $term_group_a && count( $term_group_a ) > 0 ) {

			foreach ( $term_group_a as $key => $term_group ) {

				$no_terms = true;
				for ( $i = 1; $i <= 50; $i++ ) {

					if ( intval( $term_group[ 'term_' . $i ], 10 ) === $term_id ) {
						$term_group[ 'post_type_' . $i ] = '';
						$term_group[ 'taxonomy_' . $i ]  = '';
						$term_group[ 'term_' . $i ]      = 0;
					}

					if ( intval( $term_group[ 'term_' . $i ], 10 ) !== 0 ) {
						$no_terms = false;
					}
				}

				/*
				 * If all the terms of the term group are empty delete the term group and reset the association between
				 * automatic links and this term group. If there are terms in the term group update the term group.
				 */
				if ( $no_terms ) {

					// Delete the term group.

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$query_result = $wpdb->query(
						$wpdb->prepare(
							"DELETE FROM {$wpdb->prefix}daextam_term_group WHERE term_group_id = %d ",
							$term_group['term_group_id']
						)
					);

					// If the term group is used reset the association between the automatic links and this term group.
					if ( $this->term_group_is_used( $term_group['term_group_id'] ) ) {

						// Reset the association between the automatic links and this term group.
						$safe_sql = $wpdb->prepare(
							"UPDATE {$wpdb->prefix}daextam_term_group SET 
                                    term_group_id = 0,
                                    WHERE term_group_id = %d",
							$term_group['term_group_id']
						);

					}
				} else {

					// Update the term group.

					$query_part = '';
					for ( $i = 1; $i <= 50; $i++ ) {
						$query_part .= $wpdb->prepare( '%i = %s,', 'post_type_' . $i, $term_group[ 'post_type_' . $i ] );
						$query_part .= $wpdb->prepare( '%i = %s,', 'taxonomy_' . $i, $term_group[ 'taxonomy_' . $i ] );
						$query_part .= $wpdb->prepare( '%i = %s', 'term_' . $i, $term_group[ 'term_' . $i ] );
						if ( 50 !== $i ) {
							$query_part .= ',';
						}
					}

					// Update the database.
					global $wpdb;

					// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $query_part is already sanitized.
					// phpcs:disable WordPress.DB.DirectDatabaseQuery
					$query_result = $wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}daextam_term_group SET
                        $query_part
                        WHERE term_group_id = %d",
							$term_group['term_group_id']
						)
					);
					// phpcs:enable

				}
			}
		}
	}
}

