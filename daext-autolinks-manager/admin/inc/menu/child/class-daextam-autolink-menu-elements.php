<?php
/**
 * Class used to implement the back-end functionalities of the "Autolinks" menu.
 *
 * @package daext-autolinks-manager
 */

/**
 * Class used to implement the back-end functionalities of the "Autolinks" menu.
 */
class Daextam_Autolink_Menu_Elements extends Daextam_Menu_Elements {

	/**
	 * Constructor.
	 *
	 * @param object $shared The shared class.
	 * @param string $page_query_param The page query parameter.
	 * @param string $config The config parameter.
	 */
	public function __construct( $shared, $page_query_param, $config ) {

		parent::__construct( $shared, $page_query_param, $config );

		$this->menu_slug          = 'autolink';
		$this->slug_plural        = 'autolinks';
		$this->label_singular     = __( 'Autolink', 'daext-autolinks-manager' );
		$this->label_plural       = __( 'Autolinks', 'daext-autolinks-manager' );
		$this->primary_key        = 'autolink_id';
		$this->db_table           = 'autolink';
		$this->list_table_columns = array(
			array(
				'db_field' => 'name',
				'label'    => __( 'Name', 'daext-autolinks-manager' ),
			),
			array(
				'db_field'                => 'category_id',
				'label'                   => 'Category',
				'prepare_displayed_value' => array( $shared, 'get_category_name' ),
			),
			array(
				'db_field' => 'keyword',
				'label'    => __( 'Keyword', 'daext-autolinks-manager' ),
			),
			array(
				'db_field' => 'url',
				'label'    => __( 'URL', 'daext-autolinks-manager' ),
			),
		);
		$this->searchable_fields  = array(
			'name',
			'keyword',
		);

		$this->default_values = array(
			'name'                  => '',
			'category_id'           => intval( get_option( $this->shared->get( 'slug' ) . '_defaults_category_id' ), 10 ),
			'keyword'               => '',
			'url'                   => '',
			'title'                 => '',
			'left_boundary'         => get_option( $this->shared->get( 'slug' ) . '_defaults_left_boundary' ),
			'right_boundary'        => get_option( $this->shared->get( 'slug' ) . '_defaults_right_boundary' ),
			'keyword_before'        => '',
			'keyword_after'         => '',
			'post_types'            => get_option( $this->shared->get( 'slug' ) . '_defaults_post_types' ),
			'categories'            => get_option( $this->shared->get( 'slug' ) . '_defaults_categories' ),
			'tags'                  => get_option( $this->shared->get( 'slug' ) . '_defaults_tags' ),
			'term_group_id'         => intval( get_option( $this->shared->get( 'slug' ) . '_defaults_term_group_id' ), 10 ),
			'limit'                 => intval( get_option( $this->shared->get( 'slug' ) . '_defaults_limit', 10 ) ),
			'case_sensitive_search' => intval( get_option( $this->shared->get( 'slug' ) . '_defaults_case_sensitive_search' ), 10 ),
			'open_new_tab'          => intval( get_option( $this->shared->get( 'slug' ) . '_defaults_open_new_tab' ), 10 ),
			'use_nofollow'          => intval( get_option( $this->shared->get( 'slug' ) . '_defaults_use_nofollow' ), 10 ),
			'priority'              => intval( get_option( $this->shared->get( 'slug' ) . '_defaults_priority' ), 10 ),
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

		// Preliminary operations ---------------------------------------------------------------------------------------------.
		global $wpdb;

		// Sanitization -------------------------------------------------------------------------------------------------------.

		$data = array();

		// Actions.
		$data['update_id']      = isset( $_POST['update_id'] ) ? intval( $_POST['update_id'], 10 ) : null;
		$data['form_submitted'] = isset( $_POST['form_submitted'] ) ? intval( $_POST['form_submitted'], 10 ) : null;

		// Sanitization.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			// Main Form data.
			$data['name']         = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : null;
			$data['category_id']  = isset( $_POST['category_id'] ) ? intval( wp_unslash( $_POST['category_id'] ), 10 ) : null;
			$data['keyword']      = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : null;
			$data['url']          = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : null;
			$data['title']        = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : null;
			$data['open_new_tab'] = isset( $_POST['open_new_tab'] ) ? 1 : 0;
			$data['use_nofollow'] = isset( $_POST['use_nofollow'] ) ? 1 : 0;

			if ( isset( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ) {

				// Sanitize all the post types in the array.
				$data['post_types'] = array_map( 'sanitize_key', $_POST['post_types'] );

			} else {
				$data['post_types'] = '';
			}

			if ( isset( $_POST['categories'] ) && is_array( $_POST['categories'] ) ) {

				// Sanitize all the categories in the array.
				$data['categories'] = array_map( 'sanitize_key', $_POST['categories'] );

				// Convert to integer base 10 all the category id in the array.
				$data['categories'] = array_map(
					function ( $value ) {
						return intval( $value, 10 );
					},
					$data['categories']
				);

			} else {
				$data['categories'] = '';
			}

			if ( isset( $_POST['tags'] ) && is_array( $_POST['tags'] ) ) {

				// Sanitize all the categories in the array.
				$data['tags'] = array_map( 'sanitize_key', $_POST['tags'] );

				// Convert to integer base 10 all the tag id in the array.
				$data['tags'] = array_map(
					function ( $value ) {
						return intval( $value, 10 );
					},
					$data['tags']
				);

			} else {
				$data['tags'] = '';
			}

			$data['term_group_id']         = isset( $_POST['term_group_id'] ) ? intval( $_POST['term_group_id'], 10 ) : null;
			$data['case_sensitive_search'] = isset( $_POST['case_sensitive_search'] ) ? 1 : 0;
			$data['left_boundary']         = isset( $_POST['left_boundary'] ) ? intval( $_POST['left_boundary'], 10 ) : null;
			$data['right_boundary']        = isset( $_POST['right_boundary'] ) ? intval( $_POST['right_boundary'], 10 ) : null;
			$data['keyword_before']        = isset( $_POST['keyword_before'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword_before'] ) ) : null;
			$data['keyword_after']         = isset( $_POST['keyword_after'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword_after'] ) ) : null;
			$data['limit']                 = isset( $_POST['limit'] ) ? intval( $_POST['limit'], 10 ) : null;
			$data['priority']              = isset( $_POST['priority'] ) ? intval( $_POST['priority'], 10 ) : null;

		}

		// Validation.
		if ( ! is_null( $data['update_id'] ) || ! is_null( $data['form_submitted'] ) ) {

			$invalid_data_message = '';

			// Validation on "name".
			if ( mb_strlen( trim( $data['name'] ) ) === 0 || mb_strlen( trim( $data['name'] ) ) > 100 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Name" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Keyword".
			if ( 0 === strlen( trim( $data['keyword'] ) ) || strlen( $data['keyword'] ) > 255 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Keyword" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			/**
			 * Do not allow only numbers as a keyword. Only numbers in a keyword would cause the index of the protected block to
			 * be replaced. For example the keyword "1" would cause the "1" present in the index of the following protected
			 * blocks to be replaced with an autolink:
			 *
			 * - [pb]1[/pb]
			 * - [pb]31[/pb]
			 * - [pb]812[/pb]
			 */
			if ( preg_match( '/^\d+$/', $data['keyword'] ) === 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'The specified keyword is not allowed.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data                  = true;
				$specified_keyword_not_allowed = true;
			}

			/**
			 * Do not allow to create specific keywords that would be able to replace the start delimiter of the
			 * protected block [pb], part of the start delimiter, the end delimited [/pb] or part of the end delimiter.
			 */
			if ( preg_match( '/^\[$|^\[p$|^\[pb$|^\[pb]$|^\[\/$|^\[\/p$|^\[\/pb$|^\[\/pb\]$|^\]$|^b\]$|^pb\]$|^\/pb\]$|^p$|^pb$|^pb\]$|^\/$|^\/p$|^\/pb$|^\/pb]$|^b$|^b\$/i', $data['keyword'] ) === 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'The specified keyword is not allowed.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data                  = true;
				$specified_keyword_not_allowed = true;
			}

			/**
			 * Do not allow to create specific keyword that would be able to replace the start delimiter of the autolink [al],
			 * part of the start delimiter, the end delimited [/al] or part of the end delimiter.
			 */
			if ( ! isset( $specified_keyword_not_allowed ) && preg_match( '/^\[$|^\[a$|^\[al$|^\[al]$|^\[\/$|^\[\/a$|^\[\/al$|^\[\/al\]$|^\]$|^l\]$|^al\]$|^\/al\]$|^a$|^al$|^al\]$|^\/$|^\/a$|^\/al$|^\/al]$|^l$|^l\$]/i', $data['keyword'] ) === 1 ) {
				$this->shared->save_dismissible_notice(
					__( 'The specified keyword is not allowed.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "URL".
			if ( mb_strlen( trim( $data['url'] ) ) === 0 || mb_strlen( trim( $data['url'] ) ) > 2083 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "URL" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Title".
			if ( strlen( $data['title'] ) > 1024 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Title" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			// validation on "keyword_before".
			if ( mb_strlen( trim( $data['keyword_before'] ) ) > 255 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Keyword Before" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "keyword_after".
			if ( mb_strlen( trim( $data['keyword_after'] ) ) > 255 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a valid value in the "Keyword After" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			// Validation on "Max Number Autolinks".
			if ( ! preg_match( $this->shared->regex_number_ten_digits, $data['limit'] ) || intval( $data['limit'], 10 ) < 1 || intval( $data['limit'], 10 ) > 1000000 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a number from 1 to 1000000 in the "Limit" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}

			// validation on "Priority".
			if ( ! preg_match( $this->shared->regex_number_ten_digits, $data['priority'] ) || intval( $data['priority'], 10 ) > 100 ) {
				$this->shared->save_dismissible_notice(
					__( 'Please enter a number from 0 to 100 in the "Priority" field.', 'daext-autolinks-manager' ),
					'error'
				);
				$invalid_data = true;
			}
		}

		// update ---------------------------------------------------------------.
		if ( ! is_null( $data['update_id'] ) && ! isset( $invalid_data ) ) {

			// Update the database.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}daextam_autolink SET 
                name = %s,
                category_id = %d,
                keyword = %s,
                url = %s,
                title = %s,
                left_boundary = %d,
                right_boundary = %d,
                keyword_before = %s,
                keyword_after = %s,
                post_types = %s,
                categories = %s,
                tags = %s,
                term_group_id = %d,
                `limit` = %d,
                case_sensitive_search = %d,
                open_new_tab = %d,
                use_nofollow = %d,
                priority = %d
                WHERE autolink_id = %d",
					$data['name'],
					$data['category_id'],
					$data['keyword'],
					$data['url'],
					$data['title'],
					$data['left_boundary'],
					$data['right_boundary'],
					$data['keyword_before'],
					$data['keyword_after'],
					maybe_serialize( $data['post_types'] ),
					maybe_serialize( $data['categories'] ),
					maybe_serialize( $data['tags'] ),
					$data['term_group_id'],
					$data['limit'],
					$data['case_sensitive_search'],
					$data['open_new_tab'],
					$data['use_nofollow'],
					$data['priority'],
					$data['update_id']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The automatic link has been successfully updated.', 'daext-autolinks-manager' ),
					'updated'
				);
			}
		} elseif ( ! is_null( $data['form_submitted'] ) && ! isset( $invalid_data ) ) {

			// Add record to database ------------------------------------------------------------------.

			// Insert into the database.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$query_result = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}daextam_autolink SET 
                name = %s,
                category_id = %d,
                keyword = %s,
                url = %s,
                title = %s,
                left_boundary = %d,
                right_boundary = %d,
                keyword_before = %s,
                keyword_after = %s,
                post_types = %s,
                categories = %s,
                tags = %s,
                term_group_id = %d,
                `limit` = %d,
                case_sensitive_search = %d,
                open_new_tab = %d,
                use_nofollow = %d,
                priority = %d",
					$data['name'],
					$data['category_id'],
					$data['keyword'],
					$data['url'],
					$data['title'],
					$data['left_boundary'],
					$data['right_boundary'],
					$data['keyword_before'],
					$data['keyword_after'],
					maybe_serialize( $data['post_types'] ),
					maybe_serialize( $data['categories'] ),
					maybe_serialize( $data['tags'] ),
					$data['term_group_id'],
					$data['limit'],
					$data['case_sensitive_search'],
					$data['open_new_tab'],
					$data['use_nofollow'],
					$data['priority']
				)
			);

			if ( false !== $query_result ) {
				$this->shared->save_dismissible_notice(
					__( 'The automatic link has been successfully added.', 'daext-autolinks-manager' ),
					'updated'
				);
			}
		}
	}

	/**
	 * Defines the form fields present in the add/edit form and call the method to print them.
	 *
	 * @param object $item_obj The item object.
	 *
	 * @return void
	 */
	public function print_form_fields( $item_obj = null ) {

		// Get the categories.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$category_a = $wpdb->get_results(
			"SELECT category_id, name FROM {$wpdb->prefix}daextam_category ORDER BY category_id DESC",
			ARRAY_A
		);

		$category_a_option_value      = array();
		$category_a_option_value['0'] = __( 'None', 'daext-autolinks-manager' );
		foreach ( $category_a as $key => $value ) {
			$category_a_option_value[ $value['category_id'] ] = $value['name'];
		}

		// Get the available post types.
		$available_post_types_a = get_post_types(
			array(
				'public'  => true,
				'show_ui' => true,
			)
		);

		// Remove the "attachment" post type.
		$available_post_types_a = array_diff( $available_post_types_a, array( 'attachment' ) );

		// Get the term groups.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_group_a = $wpdb->get_results(
			"SELECT term_group_id, name FROM {$wpdb->prefix}daextam_term_group ORDER BY term_group_id DESC",
			ARRAY_A
		);

		$term_group_a_option_value      = array();
		$term_group_a_option_value['0'] = __( 'None', 'daext-autolinks-manager' );
		foreach ( $term_group_a as $key => $value ) {
			$term_group_a_option_value[ $value['term_group_id'] ] = $value['name'];
		}

		// Get the categories.
		$categories = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
			)
		);

		$categories_option = array();
		foreach ( $categories as $key => $category ) {
			$categories_option[ $category->term_id ] = $category->name;
		}

		// Get the tags.
		$tags = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
				'taxonomy'   => 'post_tag',
			)
		);

		$tags_option = array();
		foreach ( $tags as $key => $tag ) {
			$tags_option[ $tag->term_id ] = $tag->name;
		}

		// Boundary options.
		$boundary_options = array(
			'0' => __( 'Generic', 'daext-autolinks-manager' ),
			'1' => __( 'White Space', 'daext-autolinks-manager' ),
			'2' => __( 'Comma', 'daext-autolinks-manager' ),
			'3' => __( 'Point', 'daext-autolinks-manager' ),
			'4' => __( 'None', 'daext-autolinks-manager' ),
		);

		// Add the form data in the $sections array.
		$sections = array(
			array(
				'label'          => 'Main',
				'section_id'     => 'main',
				'icon_id'        => 'dots-grid',
				'display_header' => false,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'name',
						'label'       => __( 'Name', 'daext-autolinks-manager' ),
						'description' => __( 'The name of the automatic link.', 'daext-autolinks-manager' ),
						'placeholder' => __( 'T-Shirts Automatic Link', 'daext-autolinks-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['name'] : null,
						'maxlength'   => 100,
						'required'    => true,
					),
					array(
						'type'        => 'select',
						'name'        => 'category_id',
						'label'       => __( 'Category', 'daext-autolinks-manager' ),
						'description' => __( 'The category of the automatic link.', 'daext-autolinks-manager' ),
						'options'     => $category_a_option_value,
						'value'       => isset( $item_obj ) ? $item_obj['category_id'] : $this->default_values['category_id'],
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'keyword',
						'label'       => __( 'Keyword', 'daext-autolinks-manager' ),
						'description' => __( 'The keyword that will be converted to a link.', 'daext-autolinks-manager' ),
						'placeholder' => __( 't-shirts', 'daext-autolinks-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['keyword'] : null,
						'maxlength'   => 255,
						'required'    => true,
					),
					array(
						'type'        => 'text',
						'name'        => 'url',
						'label'       => __( 'URL', 'daext-autolinks-manager' ),
						'description' => __( 'The destination address of the link automatically generated on the keyword.', 'daext-autolinks-manager' ),
						'placeholder' => __( 'https://example.com/t-shirts/', 'daext-autolinks-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['url'] : null,
						'maxlength'   => 2083,
						'required'    => true,
					),
				),
			),
			array(
				'label'          => 'HTML',
				'section_id'     => 'html-options',
				'icon_id'        => 'code-browser',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'text',
						'name'        => 'title',
						'label'       => __( 'Title', 'daext-autolinks-manager' ),
						'description' => __( 'The title attribute of the link automatically generated on the keyword.', 'daext-autolinks-manager' ),
						'placeholder' => __('Shop Classic T-Shirts for Everyday Comfort', 'daext-autolinks-manager'),
						'value'       => isset( $item_obj ) ? $item_obj['title'] : null,
						'maxlength'   => 255,
						'required'    => false,
					),
					array(
						'type'        => 'toggle',
						'name'        => 'open_new_tab',
						'label'       => __( 'Open in New Tab', 'daext-autolinks-manager' ),
						'description' => __( 'Open the linked document in a new tab.', 'daext-autolinks-manager' ),
						'options'     => array(
							'0' => 'No',
							'1' => 'Yes',
						),
						'value'       => isset( $item_obj ) ? $item_obj['open_new_tab'] : $this->default_values['open_new_tab'],
					),
					array(
						'type'        => 'toggle',
						'name'        => 'use_nofollow',
						'label'       => __( 'Use Nofollow', 'daext-autolinks-manager' ),
						'description' => __( 'Add the rel="nofollow" attribute to the link.', 'daext-autolinks-manager' ),
						'options'     => array(
							'0' => 'No',
							'1' => 'Yes',
						),
						'value'       => isset( $item_obj ) ? $item_obj['use_nofollow'] : $this->default_values['use_nofollow'],
					),
				),
			),
			array(
				'label'          => 'Affected Posts',
				'section_id'     => 'affected-posts',
				'icon_id'        => 'layout-alt-03',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'select_multiple',
						'name'        => 'post_types',
						'label'       => __( 'Post Types', 'daext-autolinks-manager' ),
						'description' => __( 'With this option you are able to determine in which post types the defined keywords will be automatically converted to a link. Leave this field empty to convert the keyword in any post type.', 'daext-autolinks-manager' ),
						'options'     => $available_post_types_a,
						'value'       => isset( $item_obj ) ? $item_obj['post_types'] : $this->default_values['post_types'],
					),
					array(
						'type'        => 'select_multiple',
						'name'        => 'categories',
						'label'       => __( 'Categories', 'daext-autolinks-manager' ),
						'description' => __( 'With this option you are able to determine in which categories the defined keywords will be automatically converted to a link. Leave this field empty to convert the keyword in any category.', 'daext-autolinks-manager' ),
						'options'     => $categories_option,
						'value'       => isset( $item_obj ) ? $item_obj['categories'] : $this->default_values['categories'],
					),
					array(
						'type'        => 'select_multiple',
						'name'        => 'tags',
						'label'       => __( 'Tags', 'daext-autolinks-manager' ),
						'description' => __( 'With this option you are able to determine in which tags the defined keywords will be automatically converted to a link. Leave this field empty to convert the keyword in any tag.', 'daext-autolinks-manager' ),
						'options'     => $tags_option,
						'value'       => isset( $item_obj ) ? $item_obj['tags'] : $this->default_values['tags'],
					),
					array(
						'type'        => 'select',
						'name'        => 'term_group_id',
						'label'       => __( 'Term Group', 'daext-autolinks-manager' ),
						'description' => __( 'The terms that will be compared with the ones available on the posts where the autolinks are applied. Please note that when a term group is selected the "Categories" and "Tags" options will be ignored.', 'daext-autolinks-manager' ),
						'options'     => $term_group_a_option_value,
						'value'       => isset( $item_obj ) ? $item_obj['term_group_id'] : $this->default_values['term_group_id'],
					),
				),
			),
			array(
				'label'          => 'Advanced Match',
				'section_id'     => 'advanced-match',
				'icon_id'        => 'settings-01',
				'display_header' => true,
				'fields'         => array(
					array(
						'type'        => 'toggle',
						'name'        => 'case_sensitive_search',
						'label'       => __( 'Case Sensitive Search', 'daext-autolinks-manager' ),
						'description' => __( 'Enable the case-sensitive search.', 'daext-autolinks-manager' ),
						'options'     => array(
							'0' => 'No',
							'1' => 'Yes',
						),
						'value'       => isset( $item_obj ) ? $item_obj['case_sensitive_search'] : $this->default_values['case_sensitive_search'],
					),
					array(
						'type'        => 'select',
						'name'        => 'left_boundary',
						'label'       => __( 'Left Boundary', 'daext-autolinks-manager' ),
						'description' => __( 'Use this option to match keywords preceded by a generic boundary or by a specific character.', 'daext-autolinks-manager' ),
						'options'     => $boundary_options,
						'value'       => isset( $item_obj ) ? $item_obj['left_boundary'] : $this->default_values['left_boundary'],
					),
					array(
						'type'        => 'select',
						'name'        => 'right_boundary',
						'label'       => __( 'Right Boundary', 'daext-autolinks-manager' ),
						'description' => __( 'Use this option to match keywords followed by a generic boundary or by a specific character.', 'daext-autolinks-manager' ),
						'options'     => $boundary_options,
						'value'       => isset( $item_obj ) ? $item_obj['right_boundary'] : $this->default_values['right_boundary'],
					),
					array(
						'type'        => 'text',
						'name'        => 'keyword_before',
						'label'       => __( 'Keyword Before', 'daext-autolinks-manager' ),
						'description' => __( 'Use this option to match occurrences preceded by a specific string.', 'daext-autolinks-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['keyword_before'] : null,
						'maxlength'   => 255,
						'required'    => false,
					),
					array(
						'type'        => 'text',
						'name'        => 'keyword_after',
						'label'       => __( 'Keyword After', 'daext-autolinks-manager' ),
						'description' => __( 'Use this option to match occurrences followed by a specific string.', 'daext-autolinks-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['keyword_after'] : null,
						'maxlength'   => 255,
						'required'    => false,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'limit',
						'label'       => __( 'Limit', 'daext-autolinks-manager' ),
						'description' => __( 'With this option you can determine the maximum number of matches of the defined keyword automatically converted to a link.', 'daext-autolinks-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['limit'] : $this->default_values['limit'],
						'min'         => 1,
						'max'         => 1000,
					),
					array(
						'type'        => 'input_range',
						'name'        => 'priority',
						'label'       => __( 'Priority', 'daext-autolinks-manager' ),
						'description' => __( 'The priority value determines the order used to apply the autolinks on the post.', 'daext-autolinks-manager' ),
						'value'       => isset( $item_obj ) ? $item_obj['priority'] : $this->default_values['priority'],
						'min'         => 0,
						'max'         => 100,
					),
				),
			),
		);

		$this->print_form_fields_from_array( $sections );
	}

	/**
	 * Check if the item is deletable. If not, return the message to be displayed.
	 *
	 * @param int $item_id The item id.
	 *
	 * @return array
	 */
	public function item_is_deletable( $item_id ) {

		return array(
			'is_deletable'               => true,
			'dismissible_notice_message' => null,
		);
	}
}
