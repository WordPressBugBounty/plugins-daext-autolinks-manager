<?php
/**
 * Class used to implement the back-end functionalities of the "Term Groups" menu.
 *
 * @package daext-autolinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Term Groups" menu.
 */
class Daextam_Term_Groups_Menu_Elements extends Daextam_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'term-groups';
		$this->slug_plural        = 'term-groups';
		$this->label_singular     = __( 'Term Group', 'daext-autolinks-manager' );
		$this->label_plural       = __( 'Term Groups', 'daext-autolinks-manager' );
		$this->primary_key        = 'term_group_id';
		$this->db_table           = 'term_group';
		$this->list_table_columns = array(
			array(
				'db_field' => 'name',
				'label'    => __( 'Name', 'daext-autolinks-manager' ),
			),
		);
		$this->searchable_fields  = array(
			'name',
		);
	}

	/**
	 * Process the add/edit form submission of the menu. Specifically the following tasks are performed:
	 *
	 * 1. Sanitization
	 * 2. Validation
	 * 3. Database update
	 *
	 * @return void
	 */
	public function process_form() {

		if ( isset( $_POST['update_id'] ) ||
			isset( $_POST['form_submitted'] ) ) {

			// Nonce verification.
			check_admin_referer( 'daextam_create_update_' . $this->menu_slug, 'daextam_create_update_' . $this->menu_slug . '_nonce' );

		}

		?>

		<!-- process data -->

		<?php

		// Initialize variables -----------------------------------------------------------------------------------------------.
		$dismissible_notice_a = array();

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		// Sanitization ---------------------------------------------------------------------------------------------.
		$data['name'] = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null;

		// Actions.
		$data['edit_id']        = isset( $_GET['edit_id'] ) ? intval( $_GET['edit_id'], 10 ) : null;
		$data['delete_id']      = isset( $_POST['delete_id'] ) ? intval( $_POST['delete_id'], 10 ) : null;
		$data['clone_id']       = isset( $_POST['clone_id'] ) ? intval( $_POST['clone_id'], 10 ) : null;
		$data['update_id']      = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;
		$data['form_submitted'] = isset( $_POST['form_submitted'] ) ? intval( $_POST['form_submitted'], 10 ) : null;

		// Filter and search data.
		$data['s']  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : null;
		$data['cf'] = isset( $_GET['cf'] ) ? sanitize_text_field( wp_unslash( $_GET['cf'] ) ) : null;

		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// prepare data ---------------------------------------------------------------------------------------------------.
			for ( $i = 1; $i <= 50; $i++ ) {

				// If the "Supported Terms" are less than 50 give a default value to the non-submitted fields.
				if ( ! isset( $_POST[ 'post_type_' . $i ] ) ) {
					$data[ 'post_type_' . $i ] = '';
				} else {
					$data[ 'post_type_' . $i ] = sanitize_key( $_POST[ 'post_type_' . $i ] );
				}
				if ( ! isset( $_POST[ 'taxonomy_' . $i ] ) ) {
					$data[ 'taxonomy_' . $i ] = '';
				} else {
					$data[ 'taxonomy_' . $i ] = sanitize_key( $_POST[ 'taxonomy_' . $i ] );
				}
				if ( ! isset( $_POST[ 'term_' . $i ] ) ) {
					$data[ 'term_' . $i ] = 0;
				} else {
					$data[ 'term_' . $i ] = intval( $_POST[ 'term_' . $i ], 10 );
				}

				// Set post type and taxonomy to an empty value if the related term is not set.
				if ( intval( $data[ 'term_' . $i ], 10 ) === 0 ) {
					$data[ 'post_type_' . $i ] = '';
					$data[ 'taxonomy_' . $i ]  = '';
				}
			}

			// validation -----------------------------------------------------------------------------------------------------.

			$invalid_data_message = '';

			// validation on "name".
			if ( mb_strlen( trim( $data['name'] ) ) === 0 || mb_strlen( trim( $data['name'] ) ) > 100 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Name" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			// Require that at least one term is set.
			$one_term_is_set = false;
			for ( $i = 1; $i <= 50; $i++ ) {
				if ( intval( $data[ 'term_' . $i ], 10 ) !== 0 ) {
					$one_term_is_set = true;
				}
			}
			if ( ! $one_term_is_set ) {
				$this->shared->save_dismissible_notice(
					__( 'Please specify at least one term.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}
		}

		// Update or add the record in the database.
		if ( ! is_null( $data['update_id'] ) && ! isset( $invalid_data ) ) {

			// Update.

			// Prepare the partial query.
			$query_part = '';
			for ( $i = 1; $i <= 50; $i++ ) {

				$query_part .= $wpdb->prepare( 'post_type_' . intval( $i, 10 ) . ' = %s,', $data[ 'post_type_' . $i ] );
				$query_part .= $wpdb->prepare( 'taxonomy_' . intval( $i, 10 ) . ' = %s,', $data[ 'taxonomy_' . $i ] );
				$query_part .= $wpdb->prepare( 'term_' . intval( $i, 10 ) . ' = %s', $data[ 'term_' . $i ] );

				if ( 50 !== $i ) {
					$query_part .= ',';
				}
			}

			// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $query_part is already prepared.
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daextam_term_group SET
                name = %s,
                $query_part
                WHERE term_group_id = %d",
					$data['name'],
					$data['update_id']
				)
			);
			// phpcs:enable

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The term group has been successfully updated.', 'daext-autolinks-manager' ),
					'updated'
				);
			}
		} elseif ( ! is_null( $data['form_submitted'] ) && ! isset( $invalid_data ) ) {

				// Add.

				// Prepare the partial query.
				$query_part = '';
			for ( $i = 1; $i <= 50; $i++ ) {

				$query_part .= $wpdb->prepare( 'post_type_' . intval( $i, 10 ) . ' = %s,', $data[ 'post_type_' . $i ] );
				$query_part .= $wpdb->prepare( 'taxonomy_' . intval( $i, 10 ) . ' = %s,', $data[ 'taxonomy_' . $i ] );
				$query_part .= $wpdb->prepare( 'term_' . intval( $i, 10 ) . ' = %s', $data[ 'term_' . $i ] );

				if ( 50 !== $i ) {
					$query_part .= ',';
				}
			}

				// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $query_part is already prepared.
				$query_result = $wpdb->query(
					$wpdb->prepare(
						"INSERT INTO {$wpdb->prefix}daextam_term_group SET
		            name = %s,
		            $query_part",
						$data['name']
					)
				);
				// phpcs:enable

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The term group has been successfully added.', 'daext-autolinks-manager' ),
					'updated'
				);
			}
		}
	}

	/**
	 * Defines the form fields present in the add/edit form and call the method to print them.
	 *
	 * @param object $item_obj The object containing the data of the item.
	 * @return void
	 */
	public function print_form_fields( $item_obj = null ) {

		// Get the number of supported terms.
		$supported_terms = intval( get_option( $this->shared->get( 'slug' ) . '_advanced_supported_terms' ), 10 );

		// Get the available post types.
		$available_post_types_a = get_post_types(
			array(
				'public'  => true,
				'show_ui' => true,
			)
		);

		// Remove the "attachment" post type.
		$available_post_types_a = array_diff( $available_post_types_a, array( 'attachment' ) );

		// Add the "None" option at the beginning of the array.
		$available_post_types_a = array( '' => 'None' ) + $available_post_types_a;

		// Add the form data in the $sections array.
		$sections = array(
			array(
				'label'          => 'Main',
				'section_id'     => 'main',
				'display_header' => false,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'name',
						'label'       => __( 'Name', 'daext-autolinks-manager' ),
						'description' => __( 'The name of the term group.', 'daext-autolinks-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['name'] : null,
						'maxlength'   => 100,
						'required'    => true,
					),
				),
			),
		);

		// for 1 to 50 where the post_type, taxonomy and term are added.
		for ( $i = 1; $i <= $supported_terms; $i++ ) {

			$sections[0]['fields'][] = array(
				'type'        => 'select',
				'name'        => 'post_type_' . $i,
				'label'       => __( 'Post Type', 'daext-autolinks-manager' ) . ' ' . $i,
				'description' => __( 'The post type for which you want to retrieve the taxonomies.', 'daext-autolinks-manager' ),
				'options'     => $available_post_types_a,
				'value'       => isset( $item_obj ) ? $item_obj[ 'post_type_' . $i ] : null,
			);

			// Get the taxonomies of the iterated post type.
			if ( null !== $item_obj ) {
				$taxonomies = get_object_taxonomies( $item_obj[ 'post_type_' . $i ] );
			} else {
				$taxonomies = array();
			}
			$available_taxonomies = array( '' => __( 'None', 'daext-autolinks-manager' ) );
			foreach ( $taxonomies as $key => $taxonomy ) {
				$taxonomy_obj                      = get_taxonomy( $taxonomy );
				$available_taxonomies[ $taxonomy ] = $taxonomy_obj->label;
			}

			$sections[0]['fields'][] = array(
				'type'        => 'select',
				'name'        => 'taxonomy_' . $i,
				'label'       => __( 'Taxonomy', 'daext-autolinks-manager' ) . ' ' . $i,
				'description' => __( 'The taxonomy for which you want to retrieve the terms.', 'daext-autolinks-manager' ),
				'options'     => $available_taxonomies,
				'value'       => isset( $item_obj ) ? $item_obj[ 'taxonomy_' . $i ] : null,
			);

			// Get the terms of the iterated taxonomy.
			if ( null !== $item_obj ) {
				$terms = get_terms(
					array(
						'hide_empty' => 0,
						'orderby'    => 'term_id',
						'order'      => 'DESC',
						'taxonomy'   => $item_obj[ 'taxonomy_' . $i ],
					)
				);
			} else {
				$terms = array();
			}

			$available_terms = array( '0' => __( 'None', 'daext-autolinks-manager' ) );
			if ( is_array( $terms ) ) {
				foreach ( $terms as $key => $term_obj ) {
					$available_terms[ $term_obj->term_id ] = $term_obj->name;
				}
			}

			$sections[0]['fields'][] = array(
				'type'        => 'select',
				'name'        => 'term_' . $i,
				'label'       => __( 'Term', 'daext-autolinks-manager' ) . ' ' . $i,
				'description' => __( 'The term that will be compared with the ones available on the posts where the automatic links are applied.', 'daext-autolinks-manager' ),
				'options'     => $available_terms,
				'value'       => isset( $item_obj ) ? $item_obj[ 'term_' . $i ] : null,
			);

		}

		$this->print_form_fields_from_array( $sections );
	}

	/**
	 * Check if the item is deletable. If not, return the message to be displayed.
	 *
	 * @param int $item_id The ID of the item.
	 *
	 * @return array
	 */
	public function item_is_deletable( $item_id ) {

		if ( $this->shared->term_group_is_used( $item_id ) ) {
			$is_deletable               = false;
			$dismissible_notice_message = __( "This term group is associated with one or more autolinks and can't be deleted.", 'daext-autolinks-manager' );
		} else {
			$is_deletable               = true;
			$dismissible_notice_message = null;
		}

		return array(
			'is_deletable'               => $is_deletable,
			'dismissible_notice_message' => $dismissible_notice_message,
		);
	}
}
