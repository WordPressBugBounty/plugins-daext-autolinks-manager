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
	 * The screen id of the "Link Equity" menu.
	 *
	 * @var null
	 */
	private $screen_id_link_equity = null;

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

		// Hide the submenu items that are accessed via the toolbar only.
		add_action(
			'admin_head',
			function () {
				echo '<style>#adminmenu a[href="admin.php?page=daextam-categories"],#adminmenu a[href="admin.php?page=daextam-term-groups"]{display:none!important;}</style>';
			}
		);

		// Add the meta box.
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );

		// Save the meta box.
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

		// This hook is triggered during the creation of a new blog.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog_create_options_and_tables' ), 10, 6 );

		// This hook is triggered during the deletion of a blog.
		add_action( 'delete_blog', array( $this, 'delete_blog_delete_options_and_tables' ), 10, 1 );

		// Require and instantiate the classes used to handle the menus.
		add_action( 'init', array( $this, 'handle_menus' ) );

		// Replace the WordPress admin footer text on the plugin pages.
		add_filter( 'admin_footer_text', array( $this, 'custom_admin_footer_text' ) );

		// Remove the WordPress version from the admin footer on the plugin pages.
		add_filter( 'update_footer', array( $this, 'custom_admin_footer_version' ), 11 );

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
	 * If we are in one of the plugin back-end menus require and instantiate the class used to handle the specific menu.
	 *
	 * @return void
	 */
	public function handle_menus() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce non-necessary for menu selection.
		$page_query_param = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : null;

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
								'link_text' => __( 'Link Equity', 'daext-autolinks-manager' ),
								'link_url'  => admin_url( 'admin.php?page=daextam-link-equity' ),
								'icon'      => 'link-03',
								'menu_slug' => 'daextam-link-equity',
						),
						array(
							'link_text' => __( 'Auto Link Rules', 'daext-autolinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextam-autolinks' ),
							'icon'      => 'repeat-01',
							'menu_slug' => 'daextam-autolink',
						),
					),
					'more_items' => array(
						array(
								'link_text' => __( 'Export', 'daext-autolinks-manager' ),
								'link_url'  => admin_url( 'admin.php?page=daextam-tools' ),
								'pro_badge' => false,
						),
						array(
							'link_text' => __( 'Settings', 'daext-autolinks-manager' ),
							'link_url'  => admin_url( 'admin.php?page=daextam-options' ),
							'pro_badge' => false,
						),
						array(
							'link_text' => __( 'Broken Links', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/link-manager/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Click Tracking', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/link-manager/#features',
							'pro_badge' => true,
						),
						array(
								'link_text' => __( 'Link Suggestions', 'daext-autolinks-manager' ),
								'link_url'  => 'https://daext.com/link-manager/#features',
								'pro_badge' => true,
						),
						array(
								'link_text' => __( 'Import Data', 'daext-autolinks-manager' ),
								'link_url'  => 'https://daext.com/link-manager/#features',
								'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Bulk Import Keywords', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/link-manager/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Reports in CSV Format', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/link-manager/#features',
							'pro_badge' => true,
						),
						array(
							'link_text' => __( 'Maintenance Tasks', 'daext-autolinks-manager' ),
							'link_url'  => 'https://daext.com/link-manager/#features',
							'pro_badge' => true,
						),
					),
				),
			);

			require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/class-daextam-menu-elements.php';

			if ( 'daextam-dashboard' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextam-dashboard-menu-elements.php';
				$this->menu_elements = new Daextam_Dashboard_Menu_Elements( $this->shared, $page_query_param, $config );
			}
			if ( 'daextam-link-equity' === $page_query_param ) {
				require_once $this->shared->get( 'dir' ) . 'admin/inc/menu/child/class-daextam-link-equity-menu-elements.php';
				$this->menu_elements = new Daextam_Link_Equity_Menu_Elements( $this->shared, $page_query_param, $config );
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
	 * Enqueue admin specific styles.
	 *
	 * @return void
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();

		if ( in_array( $screen->id, array( $this->screen_id_dashboard, $this->screen_id_link_equity, $this->screen_id_autolinks, $this->screen_id_categories, $this->screen_id_term_groups, $this->screen_id_tools, $this->screen_id_options ), true ) ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-framework-menu', $this->shared->get( 'url' ) . 'admin/assets/css/framework-menu/main.css', array(), $this->shared->get( 'ver' ) );
		}

		if ( in_array( $screen->id, array( $this->screen_id_autolinks, $this->screen_id_categories, $this->screen_id_term_groups ), true ) ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-jquery-ui-dialog', $this->shared->get( 'url' ) . 'admin/assets/css/jquery-ui-dialog.css', array(), $this->shared->get( 'ver' ) );
		}

		if ( in_array( $screen->id, array( $this->screen_id_autolinks, $this->screen_id_term_groups ), true ) ) {
			wp_enqueue_style( $this->shared->get( 'slug' ) . '-select2', $this->shared->get( 'url' ) . 'admin/assets/inc/select2/css/select2.min.css', array(), $this->shared->get( 'ver' ) );
		}

		$meta_box_post_types_a = $this->shared->get_post_types_with_ui();
		if ( in_array( $screen->id, $meta_box_post_types_a, true ) ) {
			if ( ! method_exists( $screen, 'is_block_editor' ) || ! $screen->is_block_editor() ) {
				wp_enqueue_style( $this->shared->get( 'slug' ) . '-meta-box', $this->shared->get( 'url' ) . 'admin/assets/css/post-editor.css', array(), $this->shared->get( 'ver' ) );
			}
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
		);

		$screen = get_current_screen();
		wp_enqueue_script( $this->shared->get( 'slug' ) . '-general', $this->shared->get( 'url' ) . 'admin/assets/js/general.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );

		if ( $screen->id === $this->screen_id_dashboard ) {
			$initialization_script  = 'window.DAEXTAM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'items_per_page: ' . intval( get_option( $this->shared->get( 'slug' ) . '_pagination_statistics_menu' ), 10 ) . ',';
			$initialization_script .= 'statistics_al_data_last_update: "' . get_option( $this->shared->get( 'slug' ) . '_statistics_data_last_update' ) . '",';
			$initialization_script .= 'statistics_il_data_last_update: "' . get_transient( $this->shared->get( 'slug' ) . '_statistics_il_data_last_update' ) . '",';
			$initialization_script .= 'statistics_data_update_frequency: "' . get_option( $this->shared->get( 'slug' ) . '_statistics_data_update_frequency' ) . '",';
			$initialization_script .= 'current_time: "' . current_time( 'mysql' ) . '"';
			$initialization_script .= '};';
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-dashboard-menu', $this->shared->get( 'url' ) . 'admin/react/dashboard-menu/build/index.js', array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ), $this->shared->get( 'ver' ), true );
			wp_add_inline_script( $this->shared->get( 'slug' ) . '-dashboard-menu', $initialization_script, 'before' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
		}

		if ( $screen->id === $this->screen_id_link_equity ) {
			$initialization_script  = 'window.DAEXTAM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'read_requests_nonce: "' . wp_create_nonce( 'daextrevop_read_requests_nonce' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'items_per_page: ' . intval( get_option( $this->shared->get( 'slug' ) . '_pagination_link_equity_menu' ), 10 ) . ',';
			$initialization_script .= 'link_equity_data_last_update: "' . get_transient( $this->shared->get( 'slug' ) . '_link_equity_data_last_update' ) . '",';
			$initialization_script .= 'link_equity_data_update_frequency: "' . get_option( $this->shared->get( 'slug' ) . '_link_equity_data_update_frequency' ) . '",';
			$initialization_script .= 'current_time: "' . current_time( 'mysql' ) . '"';
			$initialization_script .= '};';
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-link-equity-menu', $this->shared->get( 'url' ) . 'admin/react/link-equity-menu/build/index.js', array( 'wp-element', 'wp-api-fetch', 'wp-i18n' ), $this->shared->get( 'ver' ), true );
			wp_add_inline_script( $this->shared->get( 'slug' ) . '-link-equity-menu', $initialization_script, 'before' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
		}

		if ( $screen->id === $this->screen_id_autolinks ) {
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-select2', $this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-autolinks', $this->shared->get( 'url' ) . 'admin/assets/js/menu-autolinks.js', array( 'jquery', $this->shared->get( 'slug' ) . '-select2', 'jquery-ui-dialog' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-autolinks', 'objectL10n', $wp_localize_script_data );
		}

		if ( $screen->id === $this->screen_id_categories ) {
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
		}

		if ( $screen->id === $this->screen_id_term_groups ) {
			$initialization_script  = 'window.DAEXTAM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'daextam_nonce: "' . wp_create_nonce( 'daextam' ) . '"';
			$initialization_script .= '};';
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-select2', $this->shared->get( 'url' ) . 'admin/assets/inc/select2/js/select2.min.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-term-groups', $this->shared->get( 'url' ) . 'admin/assets/js/menu-term-groups.js', array( 'jquery', 'jquery-ui-dialog', $this->shared->get( 'slug' ) . '-select2' ), $this->shared->get( 'ver' ), true );
			wp_localize_script( $this->shared->get( 'slug' ) . '-menu-term-groups', 'objectL10n', $wp_localize_script_data );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-term-groups', $initialization_script, 'before' );
		}

		if ( $screen->id === $this->screen_id_tools ) {
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
		}

		if ( $screen->id === $this->screen_id_options ) {
			$initialization_script  = 'window.DAEXTAM_PARAMETERS = {';
			$initialization_script .= 'ajax_url: "' . admin_url( 'admin-ajax.php' ) . '",';
			$initialization_script .= 'admin_url: "' . get_admin_url() . '",';
			$initialization_script .= 'site_url: "' . get_site_url() . '",';
			$initialization_script .= 'plugin_url: "' . $this->shared->get( 'url' ) . '",';
			$initialization_script .= 'options_configuration_pages: ' . wp_json_encode( $this->shared->menu_options_configuration() );
			$initialization_script .= '};';
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu-options', $this->shared->get( 'url' ) . 'admin/react/options-menu/build/index.js', array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components' ), $this->shared->get( 'ver' ), true );
			wp_add_inline_script( $this->shared->get( 'slug' ) . '-menu-options', $initialization_script, 'before' );
			wp_enqueue_script( $this->shared->get( 'slug' ) . '-menu', $this->shared->get( 'url' ) . 'admin/assets/js/framework-menu/menu.js', array( 'jquery' ), $this->shared->get( 'ver' ), true );
		}

		// Enqueue post-editor.js for the classic post editor when at least one meta box is active for this screen.
		if ( ! method_exists( $screen, 'is_block_editor' ) || ! $screen->is_block_editor() ) {
			$load_post_editor_js = false;

			$automatic_links_panel_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_automatic_links_panel_post_types' ) );
			if ( is_array( $automatic_links_panel_post_types_a ) && in_array( $screen->id, $automatic_links_panel_post_types_a, true ) ) {
				$load_post_editor_js = true;
			}

			$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
			if ( is_array( $interlinks_optimization_post_types_a ) && in_array( $screen->id, $interlinks_optimization_post_types_a, true ) ) {
				$load_post_editor_js = true;
			}

			if ( $load_post_editor_js ) {
				wp_enqueue_script(
					$this->shared->get( 'slug' ) . '-post-editor',
					$this->shared->get( 'url' ) . 'admin/assets/js/post-editor.js',
					array( 'jquery', 'wp-api-fetch' ),
					$this->shared->get( 'ver' ),
					true
				);
			}
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

		if ( intval( get_option( 'daextam_options_version' ), 10 ) < 2 ) {

			// Assign an instance of Daexthrmal_Shared.
			$shared = Daextam_Shared::get_instance();

			foreach ( $shared->get( 'options' ) as $key => $value ) {
				add_option( $key, $value );
			}

			// Update options version.
			update_option( 'daextam_options_version', '2' );

		}
	}


	/**
	 * Create the plugin database tables.
	 *
	 * @return void
	 */
	public static function ac_create_database_tables() {

		$shared = Daextam_Shared::get_instance();
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		self::rename_db_tables_if_needed();

		if ( intval( get_option( 'daextam_database_version' ), 10 ) < 6 ) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$table_name = $wpdb->prefix . 'daextam_statistic';
			$sql        = "CREATE TABLE $table_name (
                statistic_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                post_id BIGINT,
                post_title text NOT NULL,
                post_permalink text NOT NULL,
                post_edit_link text NOT NULL,
                post_type varchar(20) NOT NULL DEFAULT '',
                post_date datetime DEFAULT NULL,
                content_length BIGINT,
                auto_links BIGINT,
                auto_links_visits BIGINT
            ) $charset_collate";
			dbDelta( $sql );

			$table_name = $wpdb->prefix . 'daextam_il_statistic';
			$sql        = "CREATE TABLE $table_name (
                statistic_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                post_id BIGINT,
                post_title text NOT NULL,
                post_permalink text NOT NULL,
                post_edit_link text NOT NULL,
                post_type varchar(20) NOT NULL DEFAULT '',
                post_date datetime DEFAULT NULL,
                content_length BIGINT,
                manual_interlinks bigint(20) NOT NULL DEFAULT '0',
                auto_interlinks bigint(20) NOT NULL DEFAULT '0',
                iil bigint(20) NOT NULL DEFAULT '0',
                recommended_interlinks bigint(20) NOT NULL DEFAULT '0',
                num_il_clicks bigint(20) NOT NULL DEFAULT '0',
                optimization tinyint(1) NOT NULL DEFAULT '0'
            ) $charset_collate";
			dbDelta( $sql );

			$sql = "CREATE TABLE {$wpdb->prefix}daextam_link_equity (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                url varchar(2083) NOT NULL DEFAULT '',
                iil bigint(20) NOT NULL DEFAULT '0',
                link_equity bigint(20) NOT NULL DEFAULT '0',
                link_equity_relative bigint(20) NOT NULL DEFAULT '0'
            ) $charset_collate";
			dbDelta( $sql );

			$sql = "CREATE TABLE {$wpdb->prefix}daextam_anchors (
                id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                url varchar(2083) NOT NULL DEFAULT '',
                anchor longtext NOT NULL,
                post_id bigint(20) NOT NULL DEFAULT '0',
                post_title text NOT NULL,
                post_permalink text NOT NULL,
                post_edit_link text NOT NULL,
                link_equity bigint(20) NOT NULL DEFAULT '0'
            ) $charset_collate";
			dbDelta( $sql );

			$table_name = $wpdb->prefix . 'daextam_autolink';
			$sql        = "CREATE TABLE $table_name (
                autolink_id BIGINT AUTO_INCREMENT PRIMARY KEY,
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
                term_group_id BIGINT
            ) $charset_collate";
			dbDelta( $sql );

			$table_name = $wpdb->prefix . 'daextam_category';
			$sql        = "CREATE TABLE $table_name (
                category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100),
                description VARCHAR(255)
            ) $charset_collate";
			dbDelta( $sql );

			$table_name = $wpdb->prefix . 'daextam_term_group';
			$query_part = '';
			for ( $i = 1; $i <= 50; $i++ ) {
				$query_part .= 'post_type_' . $i . ' TEXT,';
				$query_part .= 'taxonomy_' . $i . ' TEXT,';
				$query_part .= 'term_' . $i . ' BIGINT';
				if ( 50 !== $i ) {
					$query_part .= ',';
				}
			}
			$sql = "CREATE TABLE $table_name (
                term_group_id BIGINT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100),
                $query_part
            ) $charset_collate";
			dbDelta( $sql );

			$shared->delete_statistics();
			update_option( 'daextam_database_version', '6' );
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
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_statistic" );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_il_statistic" );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_link_equity" );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_anchors" );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_autolink" );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_category" );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}daextam_term_group" );
	}


	/**
	 * Determine whether the current screen is one of the plugin's admin pages.
	 *
	 * @return bool
	 */
	private function is_plugin_admin_page() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}
		return in_array(
			$screen->id,
			array(
				$this->screen_id_dashboard,
				$this->screen_id_link_equity,
				$this->screen_id_autolinks,
				$this->screen_id_categories,
				$this->screen_id_term_groups,
				$this->screen_id_tools,
				$this->screen_id_options,
			),
			true
		);
	}

	/**
	 * Replace the admin footer text with a custom message on the plugin pages.
	 *
	 * @param string $text The default footer text.
	 *
	 * @return string
	 */
	public function custom_admin_footer_text( $text ) {
		if ( ! $this->is_plugin_admin_page() ) {
			return $text;
		}
		return sprintf(
			/* translators: %s: plugin name link */
			esc_html__( 'Thank you for using %s', 'daext-autolinks-manager' ),
			'<a href="https://daext.com/link-manager/" target="_blank" rel="noopener noreferrer">Link Manager</a>'
		);
	}

	/**
	 * Replace the WordPress version string with the plugin version in the admin footer on the plugin pages.
	 *
	 * @param string $text The default footer version text.
	 *
	 * @return string
	 */
	public function custom_admin_footer_version( $text ) {
		if ( ! $this->is_plugin_admin_page() ) {
			return $text;
		}
		return sprintf(
			/* translators: %s: plugin version */
			esc_html__( 'Version %s', 'daext-autolinks-manager' ),
			esc_html( $this->shared->get( 'ver' ) )
		);
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function me_add_admin_menu() {

		$icon_svg = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDI1NiAyNTYiPgogIDxkZWZzPgogICAgPHN0eWxlPgogICAgICAuY2xzLTEgewogICAgICAgIGZpbGw6ICNmZmY7CiAgICAgICAgc3Ryb2tlLXdpZHRoOiAwcHg7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgPC9kZWZzPgogIDxnIGlkPSJMYXllcl8xIiBkYXRhLW5hbWU9IkxheWVyIDEiPgogICAgPHBhdGggY2xhc3M9ImNscy0xIiBkPSJNMTI4LDE2YzI5LjkyLDAsNTguMDQsMTEuNjUsNzkuMiwzMi44LDIxLjE1LDIxLjE1LDMyLjgsNDkuMjgsMzIuOCw3OS4ycy0xMS42NSw1OC4wNC0zMi44LDc5LjJjLTIxLjE1LDIxLjE1LTQ5LjI4LDMyLjgtNzkuMiwzMi44cy01OC4wNC0xMS42NS03OS4yLTMyLjhjLTIxLjE1LTIxLjE1LTMyLjgtNDkuMjgtMzIuOC03OS4yczExLjY1LTU4LjA0LDMyLjgtNzkuMmMyMS4xNS0yMS4xNSw0OS4yOC0zMi44LDc5LjItMzIuOE0xMjgsMEM1Ny4zMSwwLDAsNTcuMzEsMCwxMjhzNTcuMzEsMTI4LDEyOCwxMjgsMTI4LTU3LjMxLDEyOC0xMjhTMTk4LjY5LDAsMTI4LDBoMFoiLz4KICA8L2c+CiAgPGcgaWQ9IkxheWVyXzIiIGRhdGEtbmFtZT0iTGF5ZXIgMiI+CiAgICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xMjgsNTZjLTE3LjY3LDAtMzIsMTQuMzMtMzIsMzJ2OGgxNnYtOGMwLTguODIsNy4xOC0xNiwxNi0xNnMxNiw3LjE4LDE2LDE2djMyYzAsOC44Mi03LjE4LDE2LTE2LDE2djE2YzE3LjY3LDAsMzItMTQuMzMsMzItMzJ2LTMyYzAtMTcuNjctMTQuMzMtMzItMzItMzJaIi8+CiAgICA8cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xNDQsMTYwdjhjMCw4LjgyLTcuMTgsMTYtMTYsMTZzLTE2LTcuMTgtMTYtMTZ2LTMyYzAtOC44Miw3LjE4LTE2LDE2LTE2di0xNmMtMTcuNjcsMC0zMiwxNC4zMy0zMiwzMnYzMmMwLDE3LjY3LDE0LjMzLDMyLDMyLDMyczMyLTE0LjMzLDMyLTMydi04aC0xNloiLz4KICA8L2c+Cjwvc3ZnPg==';

		add_menu_page(
			esc_html__( 'LM', 'daext-autolinks-manager' ),
			esc_html__( 'Link Manager', 'daext-autolinks-manager' ),
			'publish_posts',
			$this->shared->get( 'slug' ) . '-dashboard',
			array( $this, 'me_display_menu_dashboard' ),
			$icon_svg
		);

		$this->screen_id_dashboard = add_submenu_page( $this->shared->get( 'slug' ) . '-dashboard', esc_html__( 'LM - Dashboard', 'daext-autolinks-manager' ), esc_html__( 'Dashboard', 'daext-autolinks-manager' ), 'publish_posts', $this->shared->get( 'slug' ) . '-dashboard', array( $this, 'me_display_menu_dashboard' ) );
		$this->screen_id_link_equity = add_submenu_page( $this->shared->get( 'slug' ) . '-dashboard', esc_html__( 'LM - Link Equity', 'daext-autolinks-manager' ), esc_html__( 'Link Equity', 'daext-autolinks-manager' ), 'publish_posts', $this->shared->get( 'slug' ) . '-link-equity', array( $this, 'me_display_menu_link_equity' ) );
		$this->screen_id_autolinks = add_submenu_page( $this->shared->get( 'slug' ) . '-dashboard', esc_html__( 'LM - Auto Link Rules', 'daext-autolinks-manager' ), esc_html__( 'Auto Link Rules', 'daext-autolinks-manager' ), 'edit_others_posts', $this->shared->get( 'slug' ) . '-autolinks', array( $this, 'me_display_menu_autolinks' ) );
		$this->screen_id_categories = add_submenu_page( $this->shared->get( 'slug' ) . '-dashboard', esc_html__( 'LM - Categories', 'daext-autolinks-manager' ), esc_html__( 'Categories', 'daext-autolinks-manager' ), 'edit_others_posts', $this->shared->get( 'slug' ) . '-categories', array( $this, 'me_display_menu_categories' ) );
		$this->screen_id_term_groups = add_submenu_page( $this->shared->get( 'slug' ) . '-dashboard', esc_html__( 'LM - Target Groups', 'daext-autolinks-manager' ), esc_html__( 'Target Groups', 'daext-autolinks-manager' ), 'edit_others_posts', $this->shared->get( 'slug' ) . '-term-groups', array( $this, 'me_display_menu_term_groups' ) );
		$this->screen_id_tools = add_submenu_page( $this->shared->get( 'slug' ) . '-dashboard', esc_html__( 'LM - Export', 'daext-autolinks-manager' ), esc_html__( 'Export', 'daext-autolinks-manager' ), 'manage_options', $this->shared->get( 'slug' ) . '-tools', array( $this, 'me_display_menu_tools' ) );
		$this->screen_id_options = add_submenu_page( $this->shared->get( 'slug' ) . '-dashboard', esc_html__( 'LM - Settings', 'daext-autolinks-manager' ), esc_html__( 'Settings', 'daext-autolinks-manager' ), 'manage_options', $this->shared->get( 'slug' ) . '-options', array( $this, 'me_display_menu_options' ) );

		add_submenu_page( $this->shared->get( 'slug' ) . '-dashboard', esc_html__( 'Documentation', 'daext-autolinks-manager' ), esc_html__( 'Documentation', 'daext-autolinks-manager' ) . '<i class="dashicons dashicons-external" style="font-size:12px;vertical-align:-2px;height:10px;"></i>', 'manage_options', 'https://daext.com/kb/link-manager/' );
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
	 * Includes the link equity view.
	 *
	 * @return void
	 */
	public function me_display_menu_link_equity() {
		include_once 'view/link-equity.php';
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

		// Register the "Automatic Links" meta box for the post types defined in the option.
		if ( current_user_can( 'edit_others_posts' ) ) {
			$automatic_links_panel_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_automatic_links_panel_post_types' ) );
			if ( is_array( $automatic_links_panel_post_types_a ) ) {
				foreach ( $automatic_links_panel_post_types_a as $post_type ) {
					add_meta_box(
						'daextam-autolinks-manager',
						esc_html__( 'Automatic Links', 'daext-autolinks-manager' ),
						array( $this, 'autolinks_manager_meta_box_callback' ),
						$post_type,
						'side',
						'default',
						array(
							'__block_editor_compatible_meta_box' => false,
							'__back_compat_meta_box'             => true,
						)
					);
				}
			}
		}

		// Register the "Internal Links Optimization" meta box for the post types defined in the option.
		if ( current_user_can( 'edit_posts' ) ) {
			$interlinks_optimization_post_types_a = maybe_unserialize( get_option( $this->shared->get( 'slug' ) . '_interlinks_optimization_post_types' ) );
			if ( is_array( $interlinks_optimization_post_types_a ) ) {
				foreach ( $interlinks_optimization_post_types_a as $post_type ) {
					add_meta_box(
						'daextam-meta-optimization',
						esc_html__( 'Internal Links Optimization', 'daext-autolinks-manager' ),
						array( $this, 'create_optimization_meta_box_callback' ),
						$post_type,
						'side',
						'default',
						array(
							'__block_editor_compatible_meta_box' => false,
							'__back_compat_meta_box'             => true,
						)
					);
				}
			}
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

		<div class="daextam-field">
			<div class="daextam-label">
				<label for="daextam-enable-autolinks"><?php esc_html_e( 'Enable', 'daext-autolinks-manager' ); ?></label>
			</div>
			<div class="daextam-input">
				<select id="daextam-enable-autolinks" name="daextam_enable_autolinks">
					<option <?php selected( intval( $enable_autolinks, 10 ), 0 ); ?> value="0"><?php esc_html_e( 'No', 'daext-autolinks-manager' ); ?></option>
					<option <?php selected( intval( $enable_autolinks, 10 ), 1 ); ?> value="1"><?php esc_html_e( 'Yes', 'daext-autolinks-manager' ); ?></option>
				</select>
				<p class="daextam-description">
					<?php esc_html_e( 'Automatically add links based on the configured keywords.', 'daext-autolinks-manager' ); ?>
				</p>
			</div>
		</div>

		<?php

		// Use nonce for verification.
		wp_nonce_field( plugin_basename( __FILE__ ), 'daextam_nonce' );
	}


	/**
	 * Display the Internal Links Optimization meta box content.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function create_optimization_meta_box_callback( $post ) {

		return;

	}


	/**
	 * Save the Autolinks Options metadata.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function save_meta_box( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce = isset( $_POST['daextam_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['daextam_nonce'] ) ) : '';
		if ( is_null( $nonce ) || ! wp_verify_nonce( $nonce, plugin_basename( __FILE__ ) ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return;
		}

		$enable_autolinks = isset( $_POST['daextam_enable_autolinks'] ) ? intval( $_POST['daextam_enable_autolinks'], 10 ) : 0;
		update_post_meta( $post_id, '_daextam_enable_autolinks', $enable_autolinks );
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function dc_deactivate() {
		wp_clear_scheduled_hook( 'daextam_cron_hook' );
	}

	/**
	 * Rename legacy database tables when required.
	 *
	 * @return void
	 */
	public static function rename_db_tables_if_needed() {

		global $wpdb;
		$old_table = $wpdb->prefix . 'daextam_juice';
		$new_table = $wpdb->prefix . 'daextam_link_equity';
		$old_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $old_table ) );
		$new_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $new_table ) );

		if ( $old_exists && ! $new_exists ) {
			$wpdb->query( 'RENAME TABLE ' . $old_table . ' TO ' . $new_table );
		} elseif ( $old_exists && $new_exists ) {
			$wpdb->query( 'DROP TABLE ' . $old_table );
		}
	}
}
