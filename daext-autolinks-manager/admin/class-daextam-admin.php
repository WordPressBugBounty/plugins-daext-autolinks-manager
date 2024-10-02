<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package daext-autolinks-manager
 */

/**
 * This class should be used to work with the administrative side of WordPress.
 */
class Daextam_Admin {

	/**
	 * The instance of this class.
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * The instance of the shared class.
	 *
	 * @var Daextam_Shared|null
	 */
	private $shared = null;

	/**
	 * The screen id of the "Dashboard" menu.
	 *
	 * @var null
	 */
	private $screen_id_dashboard = null;

	/**
	 * The screen id of the "Autolinks" menu.
	 *
	 * @var null
	 */
	private $screen_id_autolinks = null;

	/**
	 * The screen id of the "Categories" menu.
	 *
	 * @var null
	 */
	private $screen_id_categories = null;

	/**
	 * The screen id of the "Term Groups" menu.
	 *
	 * @var null
	 */
	private $screen_id_term_groups = null;

	/**
	 * The screen id of the "Tools" menu.
	 *
	 * @var null
	 */
	private $screen_id_tools = null;

	/**
	 * The screen id of the "Options" menu.
	 *
	 * @var null
	 */
	private $screen_id_options = null;

	/**
	 * Instance of the class used to generate the back-end menus.
	 *
	 * @var null
	 */
	private $menu_elements = null;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Assign an instance of the plugin info.
		$this->shared = Daextam_Shared::get_instance();

		// Load admin stylesheets and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the admin menu.
		add_action( 'admin_menu', array( $this, 'me_add_admin_menu' ) );

		// Add the meta box.
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );

		// Save the meta box.
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

		// This hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// This hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

	    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for menu selection.
		$page_query_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;

		// Require and instantiate the class used to register the menu options.
		if ( null !== $page_query_param ) {

			$config = array(
				'admin_toolbar' => array(
					'items'      => array(
						array(
							'link_text' => __( 'Dashboard', 'daext-autolinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextam-dashboard' ),
							'icon'      => 'line-chart-up-03',
							'menu_slug' => 'daextam-dashboard',
						),
						array(
							'link_text' => __( 'Autolinks', 'daext-autolinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextam-autolinks' ),
							'icon'      => 'link-03',
							'menu_slug' => 'daextam-autolink',
						),
						array(
							'link_text' => __( 'Categories', 'daext-autolinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextam-categories' ),
							'icon'      => 'list',
							'menu_slug' => 'daextam-category',
						),
					),
					'more_items' => array(
						array(
							'link_text' => __( 'Term Groups', 'daext-autolinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextam-term-groups' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __( 'Tools', 'daext-autolinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextam-tools' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __( 'Options', 'daext-autolinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextam-options' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __( 'HTTP Status Checker', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/autolinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Click Tracking', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/autolinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Bulk Import Keywords', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/autolinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Reports in CSV Format', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/autolinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Import & Export XML', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/autolinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Maintenance Tasks', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/autolinks-manager/',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Configure Capabilities', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/autolinks-manager/',
							'pro_badge' => true,
						),
					),
				),
			);

			// The parent class.
			require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/class-daextam-menu-elements.php';

			// Use the correct child class based on the page query parameter.
			if ( 'daextam-dashboard' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextam-dashboard-menu-elements.php';
				$this->menu_elements = new Daextam_Dashboard_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextam-autolinks' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextam-autolink-menu-elements.php';
				$this->menu_elements = new Daextam_Autolink_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextam-categories' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextam-category-menu-elements.php';
				$this->menu_elements = new Daextam_Category_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextam-term-groups' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextam-term-groups-menu-elements.php';
				$this->menu_elements = new Daextam_Term_Groups_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextam-tools' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextam-tools-menu-elements.php';
				$this->menu_elements = new Daextam_Tools_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextam-options' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextam-options-menu-elements.php';
				$this->menu_elements = new Daextam_Options_Menu_Elements( $this->shared, $page_query_param, $config );
			}
		}
	}

	/**
	 * Return an instance of this class.
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
	 * Enqueue admin specific styles.
	 *
	 * @return void
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		// Menu Dashboard.
		if ( $screen->id === $this->screen_id_dashboard ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Autolinks.
		if ( $screen->id === $this->screen_id_autolinks ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu categories.
		if ( $screen->id === $this->screen_id_categories ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu Term Groups.
		if ( $screen->id === $this->screen_id_term_groups ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			// jQuery UI Dialog.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-jquery-ui-dialog',
				$this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css',
				array(),
				$this->shared->get( 'ver' )
			);

		}

		// Menu Tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );

		}

		// Menu Options.
		if ( $screen->id === $this->screen_id_options ) {

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array( 'wp-components' ), $this->shared->get( 'ver' ) );

		}

		$meta_box_post_types_a = $this->shared->get_post_types_with_ui();
		if ( in_array( $screen->id, $meta_box_post_types_a, true ) ) {

			// Post Editor.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-meta-box',
				$this->shared->get( 'url' ) . 'admin/assets/css/post-editor.css',
				array(),
				$this->shared->get( 'ver' )
			);

			// Select2.
			wp_enqueue_style(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css',
				array(),
				$this->shared->get( 'ver' )
			);

			wp_enqueue_style( $this->shared->get( 'slug' ) . '-select2-custom', $this->shared->get( 'url' ) . 'admin/assets/css/select2-custom.css', array(), $this->shared->get( 'ver' ) );

		}
	}

	/**
	 * Enqueue admin-specific JavaScript.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		$wp_localize_script_data = array(
			'deleteText'         => esc_html__( 'Delete', 'daext-autolinks-manager' ),
			'cancelText'         => esc_html__( 'Cancel', 'daext-autolinks-manager' ),
			'chooseAnOptionText' => esc_html__( 'Choose an Option ...', 'daext-autolinks-manager' ),
			'bulkImportRows'     => intval( get_option( $this->shared->get( 'slug' ) . '_advanced_bulk_import_rows' ), 10 ),
		);

		$screen = get_current_screen();

		// General.
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'admin/assets/js/general.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		// Menu Dashboard.
		if ( $screen->id === $this->screen_id_dashboard ) {

			// Store the JavaScript parameters in the window.DAEXTAM_PARAMETERS object.
			$initialization_script  = 'window.DAEXTAM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'items_per_page: ' . intval( get_option( $this->shared->get( 'slug' ) . '_pagination_statistics_menu' ), 10 ) . ',';
			$initialization_script .= 'statistics_data_last_update: "' . get_option( $this->shared->get( 'slug' ) . '_statistics_data_last_update' ) . '",';
			$initialization_script .= 'statistics_data_update_frequency: "' . get_option( $this->shared->get( 'slug' ) . '_statistics_data_update_frequency' ) . '",';
			$initialization_script .= 'current_time: "' . current_time( 'mysql' ) . '"';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-dashboard-menu',
				$this->shared->get( 'url' ) . 'admin/react/dashboard-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-dashboard-menu', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Autolinks.
		if ( $screen->id === $this->screen_id_autolinks ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-autolinks', $this->shared->get( 'url' ) . 'admin/assets/js/menu-autolinks.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2', 'jquery-ui-dialog' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-autolinks', 'objectL10n', $wp_localize_script_data );

		}

		// Menu categories.
		if ( $screen->id === $this->screen_id_categories ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Term Groups.
		if ( $screen->id === $this->screen_id_term_groups ) {

			// Store the JavaScript parameters in the window.DAEXTAM_PARAMETERS object.
			$initialization_script  = 'window.DAEXTAM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'daextam_nonce: "' . wp_create_nonce( 'daextam' ) . '",';
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-term-groups', $this->shared->get( 'url' ) . 'admin/assets/js/menu-term-groups.js', array( 'jquery', 'jquery-ui-dialog', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-term-groups', 'objectL10n', $wp_localize_script_data );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-term-groups', $initialization_script, 'before' );

		}

		// Menu Tools.
		if ( $screen->id === $this->screen_id_tools ) {

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		// Menu Options.
		if ( $screen->id === $this->screen_id_options ) {

			// Store the JavaScript parameters in the window.DAEXTAM_PARAMETERS object.
			$initialization_script  = 'window.DAEXTAM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'options_configuration_pages: ' . wp_json_encode( $this->shared->menu_options_configuration() );
			$initialization_script .= '};';

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-menu-options',
				$this->shared->get( 'url' ) . 'admin/react/options-menu/build/index.js',
				array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-options', $initialization_script, 'before' );

			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		}

		$meta_box_post_types_a = $this->shared->get_post_types_with_ui();
		if ( in_array( $screen->id, $meta_box_post_types_a, true ) ) {

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-select2',
				$this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js',
				array( 'jquery' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_enqueue_script(
				$this->shared->get( 'slug' ) . '-post-editor',
				$this->shared->get( 'url' ) . 'admin/assets/js/post-editor.js',
				array( 'jquery', $this->shared->get( 'slug' ) . '-select2' ),
				$this->shared->get( 'ver' ),
				true
			);

			wp_localize_script( $this->shared->get( 'slug' ) . '-post-editor', 'objectL10n', $wp_localize_script_data );

		}
	}

	/**
	 * Plugin activation.
	 *
	 * @param bool $networkwide True if the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public static function ac_activate( $networkwide ) {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			/**
			 * Uf this is a "Network Activation" create the options and tables
			 * for each blog.
			 */
			if ( $networkwide ) {

				// Get the current blog id.
				global $wpdb;
				$current_blog = $wpdb->blogid;

				// Create an array with all the blog ids.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				// Iterate through all the blogs.
				foreach ( $blogids as $blog_id ) {

					// Switch to the iterated blog.
					switch_to_blog( $blog_id );

					// Create options and tables for the iterated blog.
					self::ac_initialize_options();
					self::ac_create_database_tables();

				}

				// Switch to the current blog.
				switch_to_blog( $current_blog );

			} else {

				/**
				 * If this is not a "Network Activation" create options and
				 * tables only for the current blog.
				 */
				self::ac_initialize_options();
				self::ac_create_database_tables();

			}
		} else {

			/**
			 * If this is not a multisite installation create options and
			 * tables only for the current blog.
			 */
			self::ac_initialize_options();
			self::ac_create_database_tables();

		}
	}

	/**
	 * Create the options and tables for the newly created blog.
	 *
	 * @param int $blog_id The id of the blog that was created.
	 *
	 * @return void
	 */
	public function new_blog_create_options_and_tables( $blog_id ) {

		global $wpdb;

		/*
		 * if the plugin is "Network Active" create the options and tables for
		 * this new blog
		 */
		if ( is_plugin_active_for_network( 'daext-autolinks-manager/init.php' ) ) {

			// Get the id of the current blog.
			$current_blog = $wpdb->blogid;

			// Switch to the blog that is being activated.
			switch_to_blog( $blog_id );

			// Create options and database tables for the new blog.
			$this->ac_initialize_options();
			$this->ac_create_database_tables();

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		}
	}

	/**
	 * Delete options and tables for the deleted blog.
	 *
	 * @param int $blog_id The id of the blog.
	 *
	 * @return void
	 */
	public function delete_blog_delete_options_and_tables( $blog_id ) {

		global $wpdb;

		// Get the id of the current blog.
		$current_blog = $wpdb->blogid;

		// Switch to the blog that is being activated.
		switch_to_blog( $blog_id );

		// Create options and database tables for the new blog.
		$this->un_delete_options();
		$this->un_delete_database_tables();

		// Switch to the current blog.
		switch_to_blog( $current_blog );
	}

	/**
	 * Initialize plugin options.
	 *
	 * @return void
	 */
	public static function ac_initialize_options() {

		if ( intval( get_option( 'daextam_options_version' ), 10 ) < 1 ) {

			// Assign an instance of Daexthrmal_Shared.
			$shared = Daextam_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Update options version.
			update_option( 'daextam_options_version', '1' );

		}
	}

	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	public static function ac_create_database_tables() {

		// assign an instance of Daextam_Shared.
		$shared = Daextam_Shared::get_instance();

		global $wpdb;

		// Get the database character collate that will be appended at the end of each query.
		$charset_collate = $wpdb->get_charset_collate();

		// check database version and create the database.
		if ( intval( get_option( 'daextam_database_version' ), 10 ) < 2 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Create *prefix*_statistic.
			$table_name = $wpdb->prefix . 'daextam_statistic';
			$sql        = "CREATE TABLE $table_name (
                statistic_id BIGINT AUTO_INCREMENT,
                post_id BIGINT,
                post_title text NOT NULL DEFAULT '',
                post_permalink text NOT NULL DEFAULT '',
                post_edit_link text NOT NULL DEFAULT '',
                post_type varchar(20) NOT NULL DEFAULT '',
                post_date datetime DEFAULT NULL,
                content_length BIGINT,
                auto_links BIGINT,
                PRIMARY KEY  (statistic_id)
            ) $charset_collate";
			dbDelta( $sql );

			// Create *prefix*_autolink.
			$table_name = $wpdb->prefix . 'daextam_autolink';
			$sql        = "CREATE TABLE $table_name (
                autolink_id BIGINT AUTO_INCREMENT,
                name VARCHAR(100),
                category_id BIGINT,
                keyword VARCHAR(255),
                url VARCHAR(2083),
                title VARCHAR(255),
                open_new_tab TINYINT(1),
                use_nofollow TINYINT(1),
                case_sensitive_search TINYINT(1),
                `limit` INT,
                priority INT,
                left_boundary SMALLINT,
                right_boundary SMALLINT,
                keyword_before VARCHAR(255),
                keyword_after VARCHAR(255),
                post_types TEXT,
                categories TEXT,
                tags TEXT,
                term_group_id BIGINT,
                PRIMARY KEY  (autolink_id)
            ) $charset_collate";
			dbDelta( $sql );

			// Create *prefix*_category.
			$table_name = $wpdb->prefix . 'daextam_category';
			$sql        = "CREATE TABLE $table_name (
                category_id BIGINT AUTO_INCREMENT,
                name VARCHAR(100),
                description VARCHAR(255),
                PRIMARY KEY  (category_id)
            ) $charset_collate";
			dbDelta( $sql );

			// Create *prefix*_term.
			$table_name = $wpdb->prefix . 'daextam_term_group';
			$query_part = '';
			for ( $i = 1; $i <= 50; $i++ ) {
				$query_part .= 'post_type_' . $i . ' TEXT,
				';
				$query_part .= 'taxonomy_' . $i . ' TEXT,
				';
				$query_part .= 'term_' . $i . ' BIGINT';
				if ( 50 !== $i ) {
					$query_part .= ',
					';
				}
			}
			$sql = "CREATE TABLE $table_name (
                term_group_id BIGINT AUTO_INCREMENT,
                name VARCHAR(100),
                $query_part,
                PRIMARY KEY  (term_group_id)
            ) $charset_collate";
			dbDelta( $sql );

			/**
			 * Delete the statistics. This is done to avoid the statistics with
			 * previous db fields to be displayed in latest UI.
			 */
			$shared->delete_statistics();

			// Update database version.
			update_option( 'daextam_database_version', '2' );

		}
	}

	/**
	 * Plugin delete.
	 *
	 * @return void
	 */
	public static function un_delete() {

		/**
		 * Delete options and tables for all the sites in the network.
		 */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// Get the current blog id.
			global $wpdb;
			$current_blog = $wpdb->blogid;

			// Create an array with all the blog ids.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			// Iterate through all the blogs.
			foreach ( $blogids as $blog_id ) {

				// Switch to the iterated blog.
				switch_to_blog( $blog_id );

				// Create options and tables for the iterated blog.
				self::un_delete_options();
				self::un_delete_database_tables();

			}

			// Switch to the current blog.
			switch_to_blog( $current_blog );

		} else {

			/**
			 * If this is not a multisite installation delete options and tables only for the current blog.
			 */
			self::un_delete_options();
			self::un_delete_database_tables();

		}
	}

	/**
	 * Delete plugin options.
	 *
	 * @return void
	 */
	public static function un_delete_options() {

		// Assign an instance of Daextam_Shared.
		$shared = Daextam_Shared::get_instance();

		foreach ( $shared->get( 'options' ) as $key => $value ) {
			delete_option( $key );
		}
	}

	/**
	 * Delete plugin database tables.
	 *
	 * @return void
	 */
	public static function un_delete_database_tables() {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_statistic" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_autolink" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_category" );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_term_group" );
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function me_add_admin_menu() {

		$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDI1NiAyNTYiPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuY2xzLTEgewogICAgICAgIGZpbGw6ICNmZmY7CiAgICAgICAgc3Ryb2tlLXdpZHRoOiAwcHg7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgPC9kZWZzPgogIDxnIGlkPSJMYXllcl8xIiBkYXRhLW5hbWU9IkxheWVyIDEiPgogICAgPHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMTI4LDE2YzI5LjkyLDAsNTguMDQsMTEuNjUsNzkuMiwzMi44LDIxLjE1LDIxLjE1LDMyLjgsNDkuMjgsMzIuOCw3OS4ycy0xMS42NSw1OC4wNC0zMi44LDc5LjJjLTIxLjE1LDIxLjE1LTQ5LjI4LDMyLjgtNzkuMiwzMi44cy01OC4wNC0xMS42NS03OS4yLTMyLjhjLTIxLjE1LTIxLjE1LTMyLjgtNDkuMjgtMzIuOC03OS4yczExLjY1LTU4LjA0LDMyLjgtNzkuMmMyMS4xNS0yMS4xNSw0OS4yOC0zMi44LDc5LjItMzIuOE0xMjgsMEM1Ny4zMSwwLDAsNTcuMzEsMCwxMjhzNTcuMzEsMTI4LDEyOCwxMjgsMTI4LTU3LjMxLDEyOC0xMjhTMTk4LjY5LDAsMTI4LDBoMFoiLz4KICA8L2c+CiAgPGcgaWQ9IkxheWVyXzIiIGRhdGEtbmFtZT0iTGF5ZXIgMiI+CiAgICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xMjgsNTZjLTE3LjY3LDAtMzIsMTQuMzMtMzIsMzJ2OGgxNnYtOGMwLTguODIsNy4xOC0xNiwxNi0xNnMxNiw3LjE4LDE2LDE2djMyYzAsOC44Mi03LjE4LDE2LTE2LDE2djE2YzE3LjY3LDAsMzItMTQuMzMsMzItMzJ2LTMyYzAtMTcuNjctMTQuMzMtMzItMzItMzJaIi8+CiAgICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xNDQsMTYwdjhjMCw4LjgyLTcuMTgsMTYtMTYsMTZzLTE2LTcuMTgtMTYtMTZ2LTMyYzAtOC44Miw3LjE4LTE2LDE2LTE2di0xNmMtMTcuNjcsMC0zMiwxNC4zMy0zMiwzMnYzMmMwLDE3LjY3LDE0LjMzLDMyLDMyLDMyczMyLTE0LjMzLDMyLTMydi04aC0xNloiLz4KICA8L2c+Cjwvc3ZnPg==';

		add_menu_page(
			esc_html__( 'AM', 'daext-autolinks-manager' ),
			esc_html__( 'Autolinks', 'daext-autolinks-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-dashboard',
			array( $this, 'me_display_menu_dashboard' ),
			$icon_svg
		);

		$this->screen_id_dashboard = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'AM - Dashboard', 'daext-autolinks-manager' ),
			esc_html__( 'Dashboard', 'daext-autolinks-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-dashboard',
			array( $this, 'me_display_menu_dashboard' )
		);

		$this->screen_id_autolinks = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'AM - Autolinks', 'daext-autolinks-manager' ),
			esc_html__( 'Autolinks', 'daext-autolinks-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-autolinks',
			array( $this, 'me_display_menu_autolinks' )
		);

		$this->screen_id_categories = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'AM - Categories', 'daext-autolinks-manager' ),
			esc_html__( 'Categories', 'daext-autolinks-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-categories',
			array( $this, 'me_display_menu_categories' )
		);

		$this->screen_id_term_groups = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'AM - Term Groups', 'daext-autolinks-manager' ),
			esc_html__( 'Term Groups', 'daext-autolinks-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-term-groups',
			array( $this, 'me_display_menu_term_groups' )
		);

		$this->screen_id_tools = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'HM - Tools', 'daext-autolinks-manager' ),
			esc_html__( 'Tools', 'daext-autolinks-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-tools',
			array( $this, 'me_display_menu_tools' )
		);

		$this->screen_id_options = add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'AM - Options', 'daext-autolinks-manager' ),
			esc_html__( 'Options', 'daext-autolinks-manager' ),
			'manage_options',
			$this->shared->get( 'slug' ) . '-options',
			array( $this, 'me_display_menu_options' )
		);

		add_submenu_page(
			$this->shared->get( 'slug' ) . '-dashboard',
			esc_html__( 'Help & Support', 'daext-autolinks-manager' ),
			esc_html__( 'Help & Support', 'daext-autolinks-manager' ) . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>',
			'manage_options',
			'https://daext.com/doc/autolinks-manager/',
		);
	}

	/**
	 * Includes the dashboard view.
	 *
	 * @return void
	 */
	public function me_display_menu_dashboard() {
		include_once 'view/dashboard.php';
	}

	/**
	 * Includes the autolinks view.
	 *
	 * @return void
	 */
	public function me_display_menu_autolinks() {
		include_once 'view/autolinks.php';
	}

	/**
	 * Includes the categories view.
	 *
	 * @return void
	 */
	public function me_display_menu_categories() {
		include_once 'view/categories.php';
	}

	/**
	 * Includes the term groups view.
	 *
	 * @return void
	 */
	public function me_display_menu_term_groups() {
		include_once 'view/term-groups.php';
	}

	/**
	 * Includes the Tools view.
	 *
	 * @return void
	 */
	public function me_display_menu_tools() {
		include_once 'view/tools.php';
	}

	/**
	 * Includes the options view.
	 *
	 * @return void
	 */
	public function me_display_menu_options() {
		include_once 'view/options.php';
	}

	// meta box -------------------------------------------------------------------------------------------------------.

	/**
	 * Add the meta boxes.
	 *
	 * @return void
	 */
	public function create_meta_box() {

		if ( current_user_can( 'manage_options' ) ) {

			add_meta_box(
				'daextam-autolinks-manager',
				esc_html__( 'Autolinks Manager', 'daext-autolinks-manager' ),
				array( $this, 'autolinks_manager_meta_box_callback' ),
				null,
				'normal',
				'high',
				/**
				 * Reference: https://make.wordpress.org/core/2018/11/07/meta-box-compatibility-flags/
				 */
				array(

					/*
					 * It's not confirmed that this meta box works in the block editor.
					 */
					'__block_editor_compatible_meta_box' => false,

					/*
					 * This meta box should only be loaded in the classic editor interface, and the block editor
					 * should not display it.
					 */
					'__back_compat_meta_box'             => true,

				)
			);

		}
	}

	/**
	 * Display the Autolinks Manager meta box content.
	 *
	 * @param object $post The post object.
	 *
	 * @return void
	 */
	public function autolinks_manager_meta_box_callback( $post ) {

		$enable_autolinks = get_post_meta( $post->ID, '_daextam_enable_autolinks', true );

		// if the $enable_autolinks is empty use the Enable Autolinks option as a default value.
		if ( mb_strlen( trim( $enable_autolinks ) ) === 0 ) {
			$enable_autolinks = get_option( $this->shared->get( 'slug' ) . '_advanced_enable_autolinks' );
		}

		?>

		<table class="form-table table-autolinks-manager">
			<tbody>

			<tr>
				<th scope="row"><label><?php esc_html_e( 'Enable Autolinks', 'daext-autolinks-manager' ); ?></label></th>
				<td>
					<select id="daextam-enable-autolinks" name="daextam_enable_autolinks">
						<option <?php selected( intval( $enable_autolinks, 10 ), 0 ); ?> value="0"><?php esc_html_e( 'No', 'daext-autolinks-manager' ); ?></option>
						<option <?php selected( intval( $enable_autolinks, 10 ), 1 ); ?> value="1"><?php esc_html_e( 'Yes', 'daext-autolinks-manager' ); ?></option>
					</select>
				</td>
			</tr>

			</tbody>
		</table>

		<?php

		// Use nonce for verification.
		wp_nonce_field( plugin_basename( __FILE__ ), 'daextam_nonce' );
	}

	/**
	 * Save the Autolinks Options metadata.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function save_meta_box( $post_id ) {

		// Security Verifications Start -------------------------------------------------------------------------------.

		// Verify if this is an auto save routine. Don't do anything if our form has not been submitted.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		/*
		 * Verify if this came from our screen and with proper authorization, because save_post can be triggered at
		 * other times/
		 */
		$nonce = isset( $_POST['daextam_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['daextam_nonce'] ) ) : '';
		if ( is_null( $nonce ) || ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// Verify the capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Security Verifications End ---------------------------------------------------------------------------------.

		// Save the "Enable Autolinks".
		$enable_autolinks = isset( $_POST['daextam_enable_autolinks'] ) ? intval( $_POST['daextam_enable_autolinks'], 10 ) : 0;
		update_post_meta( $post_id, '_daextam_enable_autolinks', $enable_autolinks );
	}
}