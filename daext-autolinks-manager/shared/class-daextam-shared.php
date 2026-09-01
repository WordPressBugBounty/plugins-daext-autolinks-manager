<?php
/**
 * This class stores properties and methods shared by the admin and public side of WordPress.
 *
 * @package daext-autolinks-manager
 */

class Daextam_Shared {

	/**
	 * Regex used to validate a number with a maximum of 10 digits.
	 *
	 * @var string
	 */
	public $regex_number_ten_digits = '/^\s*\d{1,10}\s*$/';

	/**
	 * Regex used to validate a URL.
	 *
	 * @var string
	 */
	public $url_regex = '/^(http|https):\/\/[-A-Za-z0-9+&@#\/%?=~_|$!:,.;]+$/i';

	/**
	 * The singleton instance of the class.
	 *
	 * @var Daextam_Shared|null
	 */
	protected static $instance = null;

	/**
	 * Plugin data.
	 *
	 * @var array
	 */
	private $data = array();

	/** @var Daextam_Data_Import_Export */
	private $data_import_export;

	/** @var Daextam_Term_Helpers */
	private $term_helpers;

	/** @var Daextam_Statistics */
	private $statistics;

	/** @var Daextam_Autolink_Engine */
	private $autolink_engine;

	/** @var Daextam_Content_Helpers */
	private $content_helpers;

	/** @var Daextam_Notices */
	private $notices;

	/** @var Daextam_Admin_Helper */
	private $admin_helper;

	/** @var Daextam_Html_Text_Replacer */
	private $html_text_replacer;

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->data['slug'] = 'daextam';
		$this->data['ver']  = '1.10.13';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, -7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, -7 );

		$this->data_import_export = new Daextam_Data_Import_Export( $this );
		$this->term_helpers       = new Daextam_Term_Helpers( $this );
		$this->statistics         = new Daextam_Statistics( $this );
		$this->autolink_engine    = new Daextam_Autolink_Engine( $this );
		$this->content_helpers    = new Daextam_Content_Helpers( $this );
		$this->notices            = new Daextam_Notices( $this );
		$this->admin_helper       = new Daextam_Admin_Helper( $this );
		$this->html_text_replacer = new Daextam_Html_Text_Replacer();

		$this->data['options'] = array(
			$this->get( 'slug' ) . '_database_version' => '0',
			$this->get( 'slug' ) . '_options_version' => '0',
			$this->get( 'slug' ) . '_statistics_data_last_update' => '',
			$this->get( 'slug' ) . '_defaults_category_id' => '0',
			$this->get( 'slug' ) . '_defaults_open_new_tab' => '0',
			$this->get( 'slug' ) . '_defaults_use_nofollow' => '0',
			$this->get( 'slug' ) . '_defaults_post_types' => '',
			$this->get( 'slug' ) . '_defaults_categories' => '',
			$this->get( 'slug' ) . '_defaults_tags' => '',
			$this->get( 'slug' ) . '_defaults_term_group_id' => '',
			$this->get( 'slug' ) . '_defaults_case_sensitive_search' => '0',
			$this->get( 'slug' ) . '_defaults_left_boundary' => '0',
			$this->get( 'slug' ) . '_defaults_right_boundary' => '0',
			$this->get( 'slug' ) . '_defaults_limit' => '2',
			$this->get( 'slug' ) . '_defaults_priority' => '10',
			$this->get( 'slug' ) . '_analysis_set_max_execution_time' => '1',
			$this->get( 'slug' ) . '_analysis_max_execution_time_value' => '300',
			$this->get( 'slug' ) . '_analysis_set_memory_limit' => '1',
			$this->get( 'slug' ) . '_analysis_memory_limit_value' => '512',
			$this->get( 'slug' ) . '_analysis_limit_posts_analysis' => '1000',
			$this->get( 'slug' ) . '_analysis_post_types' => '',
			$this->get( 'slug' ) . '_statistics_data_update_frequency' => 'weekly',
			$this->get( 'slug' ) . '_link_equity_data_update_frequency' => 'weekly',
			$this->get( 'slug' ) . '_advanced_enable_autolinks' => '1',
			$this->get( 'slug' ) . '_advanced_filter_priority' => '2147483646',
			$this->get( 'slug' ) . '_advanced_enable_test_mode' => '0',
			$this->get( 'slug' ) . '_advanced_random_prioritization' => '1',
			$this->get( 'slug' ) . '_advanced_ignore_self_autolinks' => '1',
			$this->get( 'slug' ) . '_advanced_categories_and_tags_verification' => 'post',
			$this->get( 'slug' ) . '_advanced_general_limit_mode' => '1',
			$this->get( 'slug' ) . '_advanced_general_limit_characters_per_autolink' => '200',
			$this->get( 'slug' ) . '_advanced_general_limit_amount' => '5',
			$this->get( 'slug' ) . '_advanced_same_url_limit' => '2',
			$this->get( 'slug' ) . '_advanced_protect_attributes' => '0',
			$this->get( 'slug' ) . '_advanced_protected_tags' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'img', 'ul', 'ol', 'span', 'pre', 'code', 'table', 'iframe', 'script' ),
			$this->get( 'slug' ) . '_advanced_protected_gutenberg_blocks' => array( 'image', 'heading', 'gallery', 'quote', 'audio', 'cover-image', 'subhead', 'video', 'code', 'html', 'preformatted', 'pullquote', 'table', 'verse', 'button', 'columns', 'more', 'nextpage', 'separator', 'spacer', 'shortcode', 'categories', 'latest-posts', 'embed', 'core-embed/twitter', 'core-embed/youtube', 'core-embed/facebook', 'core-embed/instagram', 'core-embed/wordpress', 'core-embed/soundcloud', 'core-embed/spotify', 'core-embed/flickr', 'core-embed/vimeo', 'core-embed/animoto', 'core-embed/cloudup', 'core-embed/collegehumor', 'core-embed/dailymotion', 'core-embed/funnyordie', 'core-embed/hulu', 'core-embed/imgur', 'core-embed/issuu', 'core-embed/kickstarter', 'core-embed/meetup-com', 'core-embed/mixcloud', 'core-embed/photobucket', 'core-embed/polldaddy', 'core-embed/reddit', 'core-embed/reverbnation', 'core-embed/screencast', 'core-embed/scribd', 'core-embed/slideshare', 'core-embed/smugmug', 'core-embed/speaker', 'core-embed/ted', 'core-embed/tumblr', 'core-embed/videopress', 'core-embed/wordpress-tv' ),
			$this->get( 'slug' ) . '_advanced_protected_gutenberg_custom_blocks' => '',
			$this->get( 'slug' ) . '_advanced_protected_gutenberg_custom_void_blocks' => '',
			$this->get( 'slug' ) . '_advanced_supported_terms' => '10',
			$this->get( 'slug' ) . '_default_seo_power'    => 1000,
			$this->get( 'slug' ) . '_penality_per_position_percentage' => '1',
			$this->get( 'slug' ) . '_remove_link_to_anchor' => '1',
			$this->get( 'slug' ) . '_remove_url_parameters' => '0',
			$this->get( 'slug' ) . '_pagination_statistics_menu' => '10',
			$this->get( 'slug' ) . '_pagination_link_equity_menu' => '10',
			$this->get( 'slug' ) . '_pagination_autolinks_menu' => '10',
			$this->get( 'slug' ) . '_link_equity_post_types' => array(),
			$this->get( 'slug' ) . '_automatic_links_panel_post_types' => array( 'post', 'page' ),
			$this->get( 'slug' ) . '_interlinks_optimization_post_types' => array( 'post', 'page' ),
		);

		add_action( 'delete_term', array( $this->term_helpers, 'delete_term_action' ), 10, 3 );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get( $index ) {
		return $this->data[ $index ];
	}

	public function get_data_import_export() {
		return $this->data_import_export;
	}

	public function get_term_helpers() {
		return $this->term_helpers;
	}

	public function get_statistics() {
		return $this->statistics;
	}

	public function get_autolink_engine() {
		return $this->autolink_engine;
	}

	public function get_content_helpers() {
		return $this->content_helpers;
	}

	public function get_notices() {
		return $this->notices;
	}

	public function get_admin_helper() {
		return $this->admin_helper;
	}

	public function get_html_text_replacer() {
		return $this->html_text_replacer;
	}

	public function get_post_types_with_ui() {
		return $this->content_helpers->get_post_types_with_ui();
	}

	/**
	 * Remove URL parameters (query string) from a URL.
	 *
	 * @param string $url The URL to process.
	 *
	 * @return string The URL without query parameters.
	 */
	public function remove_url_parameters( $url ) {
		$parsed = wp_parse_url( $url );
		if ( false === $parsed ) {
			return $url;
		}
		$result = '';
		if ( ! empty( $parsed['scheme'] ) ) {
			$result .= $parsed['scheme'] . '://';
		}
		if ( ! empty( $parsed['host'] ) ) {
			$result .= $parsed['host'];
		}
		if ( ! empty( $parsed['port'] ) ) {
			$result .= ':' . $parsed['port'];
		}
		if ( ! empty( $parsed['path'] ) ) {
			$result .= $parsed['path'];
		}
		// Intentionally omit query and fragment.
		return $result;
	}

	public function update_statistics() {
		$this->statistics->update_statistics();
	}

	public function delete_statistics() {
		$this->statistics->delete_statistics();
	}

	public function get_average_automatic_links( $results ) {
		return $this->statistics->get_average_automatic_links( $results );
	}

	public function menu_options_configuration() {

		// Get the public post types that have a UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );
		unset( $post_types_with_ui['attachment'] );
		$post_types_select_options = array();
		foreach ( $post_types_with_ui as $post_type ) {
			$post_types_select_options[] = array(
				'value' => $post_type,
				'text'  => $post_type,
			);
		}

		$protected_gutenberg_blocks_select_options = array(
			array(
				'value' => 'Paragraph',
				'text'  => 'paragraph',
			),
			array(
				'value' => 'image',
				'text'  => 'Image',
			),
			array(
				'value' => 'heading',
				'text'  => 'Heading',
			),
			array(
				'value' => 'gallery',
				'text'  => 'Gallery',
			),
			array(
				'value' => 'list',
				'text'  => 'List',
			),
			array(
				'value' => 'audio',
				'text'  => 'Audio',
			),
			array(
				'value' => 'cover-image',
				'text'  => 'Cover Image',
			),
			array(
				'value' => 'subhead',
				'text'  => 'Subhead',
			),
			array(
				'value' => 'video',
				'text'  => 'Video',
			),
			array(
				'value' => 'preformatted',
				'text'  => 'Preformatted',
			),
			array(
				'value' => 'pullquote',
				'text'  => 'Pullquote',
			),
			array(
				'value' => 'table',
				'text'  => 'Table',
			),
			array(
				'value' => 'button',
				'text'  => 'Button',
			),
			array(
				'value' => 'columns',
				'text'  => 'Columns',
			),
			array(
				'value' => 'more',
				'text'  => 'More',
			),
			array(
				'value' => 'nextpage',
				'text'  => 'Page Break',
			),
			array(
				'value' => 'separator',
				'text'  => 'Separator',
			),
			array(
				'value' => 'spacer',
				'text'  => 'Spacer',
			),
			array(
				'value' => 'shortcode',
				'text'  => 'Shortcode',
			),
			array(
				'value' => 'categories',
				'text'  => 'Categories',
			),
			array(
				'value' => 'latest-posts',
				'text'  => 'Latest Posts',
			),
			array(
				'value' => 'embed',
				'text'  => 'Embed',
			),
			array(
				'value' => 'core-embed/twitter',
				'text'  => 'Twitter',
			),
			array(
				'value' => 'core-embed/facebook',
				'text'  => 'Facebook',
			),
			array(
				'value' => 'core-embed/instagram',
				'text'  => 'Instagram',
			),
			array(
				'value' => 'core-embed/wordpress',
				'text'  => 'WordPress',
			),
			array(
				'value' => 'core-embed/soundcloud',
				'text'  => 'SoundCloud',
			),
			array(
				'value' => 'core-embed/spotify',
				'text'  => 'Spotify',
			),
			array(
				'value' => 'core-embed/flickr',
				'text'  => 'Flickr',
			),
			array(
				'value' => 'core-embed/vimeo',
				'text'  => 'Vimeo',
			),
			array(
				'value' => 'core-embed/animoto',
				'text'  => 'Animoto',
			),
			array(
				'value' => 'core-embed/cloudup',
				'text'  => 'Cloudup',
			),
			array(
				'value' => 'core-embed/collegehumor',
				'text'  => 'CollegeHumor',
			),
			array(
				'value' => 'core-embed/dailymotion',
				'text'  => 'DailyMotion',
			),
			array(
				'value' => 'core-embed/funnyordie',
				'text'  => 'Funny or Die',
			),
			array(
				'value' => 'core-embed/hulu',
				'text'  => 'Imgur',
			),
			array(
				'value' => 'core-embed/issuu',
				'text'  => 'Issuu',
			),
			array(
				'value' => 'core-embed/kickstarter',
				'text'  => 'Kickstarter',
			),
			array(
				'value' => 'core-embed/meetup-com',
				'text'  => 'Meetup.com',
			),
			array(
				'value' => 'core-embed/mixcloud',
				'text'  => 'Mixcloud',
			),
			array(
				'value' => 'core-embed/photobucket',
				'text'  => 'Photobucket',
			),
			array(
				'value' => 'core-embed/polldaddy',
				'text'  => 'Polldaddy',
			),
			array(
				'value' => 'core-embed/reddit',
				'text'  => 'Reddit',
			),
			array(
				'value' => 'core-embed/reverbnation',
				'text'  => 'ReverbNation',
			),
			array(
				'value' => 'core-embed/screencast',
				'text'  => 'Screencast',
			),
			array(
				'value' => 'core-embed/smugmug',
				'text'  => 'SmugMug',
			),
			array(
				'value' => 'core-embed/ted',
				'text'  => 'Ted',
			),
			array(
				'value' => 'core-embed/tumblr',
				'text'  => 'Tumblr',
			),
			array(
				'value' => 'core-embed/videopress',
				'text'  => 'VideoPress',
			),
			array(
				'value' => 'core-embed/wordpress-tv',
				'text'  => 'WordPress.tv',
			),
		);

		// The select multiple options of the "Protected Tags" option.
		$protected_tags_html_tags = array(
			'a',
			'abbr',
			'acronym',
			'address',
			'applet',
			'area',
			'article',
			'aside',
			'audio',
			'b',
			'base',
			'basefont',
			'bdi',
			'bdo',
			'big',
			'blockquote',
			'body',
			'br',
			'button',
			'canvas',
			'caption',
			'center',
			'cite',
			'code',
			'col',
			'colgroup',
			'datalist',
			'dd',
			'del',
			'details',
			'dfn',
			'dir',
			'div',
			'dl',
			'dt',
			'em',
			'embed',
			'fieldset',
			'figcaption',
			'figure',
			'font',
			'footer',
			'form',
			'frame',
			'frameset',
			'h1',
			'h2',
			'h3',
			'h4',
			'h5',
			'h6',
			'head',
			'header',
			'hgroup',
			'hr',
			'html',
			'i',
			'iframe',
			'img',
			'input',
			'ins',
			'kbd',
			'keygen',
			'label',
			'legend',
			'li',
			'link',
			'map',
			'mark',
			'menu',
			'meta',
			'meter',
			'nav',
			'noframes',
			'noscript',
			'object',
			'ol',
			'optgroup',
			'option',
			'output',
			'p',
			'param',
			'pre',
			'progress',
			'q',
			'rp',
			'rt',
			'ruby',
			's',
			'samp',
			'script',
			'section',
			'select',
			'small',
			'source',
			'span',
			'strike',
			'strong',
			'style',
			'sub',
			'summary',
			'sup',
			'table',
			'tbody',
			'td',
			'textarea',
			'tfoot',
			'th',
			'thead',
			'time',
			'title',
			'tr',
			'tt',
			'u',
			'ul',
			'var',
			'video',
			'wbr',
		);

		$protected_tags_html_tags_select_options = array();
		foreach ( $protected_tags_html_tags as $protected_tags_html_tag ) {
			$protected_tags_html_tags_select_options[] = array(
				'value' => $protected_tags_html_tag,
				'text'  => $protected_tags_html_tag,
			);
		}

		$default_category_id_select_options = array(
			array(
				'value' => '0',
				'text'  => __( 'None', 'daext-autolinks-manager' ),
			),
		);

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$category_a = $wpdb->get_results(
			"SELECT category_id, name FROM {$wpdb->prefix}daextam_category ORDER BY category_id DESC",
			ARRAY_A
		);

		foreach ( $category_a as $key => $category ) {
			$default_category_id_select_options[] = array(
				'value' => $category['category_id'],
				'text'  => $category['name'],
			);
		}

		// Categories select options.
		$categories                = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
			)
		);
		$categories_select_options = array();
		foreach ( $categories as $category ) {
			$categories_select_options[] = array(
				'value' => (string) $category->term_id,
				'text'  => $category->name,
			);
		}

		// Tags select options.
		$tags                = get_categories(
			array(
				'hide_empty' => 0,
				'orderby'    => 'term_id',
				'order'      => 'DESC',
				'taxonomy'   => 'post_tag',
			)
		);
		$tags_select_options = array();
		foreach ( $tags as $tag ) {
			$tags_select_options[] = array(
				'value' => (string) $tag->term_id,
				'text'  => $tag->name,
			);
		}

		// Term groups select options.
		$term_group_select_options = array();
		$term_group_select_options[] = array(
			'value' => '0',
			'text'  => __( 'None', 'daext-autolinks-manager' ),
		);

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_group_a              = $wpdb->get_results(
			"SELECT term_group_id, name FROM {$wpdb->prefix}daextam_term_group ORDER BY term_group_id DESC",
			ARRAY_A
		);

		foreach ( $term_group_a as $key => $term_group ) {
			$term_group_select_options[] = array(
				'value' => $term_group['term_group_id'],
				'text'  => stripslashes( $term_group['name'] ),
			);
		}

		$configuration = array(
			array(
				'title'       => __( 'Link Analysis', 'daext-autolinks-manager' ),
				'description' => __( 'Configure options and parameters used for the link analysis.', 'daext-autolinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Link Equity', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daextam_default_seo_power',
								'label'     => __( 'Total Link Equity (Default)', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'Define the default total link equity assigned to posts. This value represents the amount of link equity a post passes through its internal links and determines the equity assigned to linked pages in the plugin reports.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the default total link equity assigned to posts.', 'daext-autolinks-manager' ),
								'rangeMin'  => 100,
								'rangeMax'  => 1000000,
								'rangeStep' => 100,
							),
							array(
								'name'      => 'daextam_penality_per_position_percentage',
								'label'     => __( 'Penalty per Position (%)', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'With multiple links in an article, the algorithm that calculates the link equity passed by each link removes a percentage of the passed link equity based on the position of the link compared to the other links.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the penalty per position percentage.', 'daext-autolinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 100,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daextam_remove_link_to_anchor',
								'label'   => __( 'Remove Fragment Identifier', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Automatically remove anchors from every URL used to calculate link equity. With this option enabled, "http://example.com" and "http://example.com#myanchor" will both contribute to generate link equity for a single URL, that is "http://example.com".', 'daext-autolinks-manager' ),
								'help'    => __( 'Remove the fragment identifier from the URL.', 'daext-autolinks-manager' ),
							),
							array(
								'name'    => 'daextam_remove_url_parameters',
								'label'   => __( 'Remove URL Parameters', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Automatically remove URL parameters from every URL used to calculate link equity. With this option enabled, "http://example.com" and "http://example.com?param=1" will both contribute to generate link equity for a single URL, that is "http://example.com". This option should not be enabled if your website uses URL parameters to identify specific pages (for example when pretty permalinks are not enabled).', 'daext-autolinks-manager' ),
								'help'    => __( 'Remove the parameters from the URL.', 'daext-autolinks-manager' ),
							),
						),
					),
					array(
						'title'   => __( 'Technical Options', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'    => 'daextam_analysis_set_max_execution_time',
								'label'   => __( 'Set Max Execution Time', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to enable your custom "Max Execution Time Value" on long running scripts.', 'daext-autolinks-manager' ),
								'help'    => __(
									'Enable a custom max execution time value.',
									'daext-autolinks-manager'
								),
							),
							array(
								'name'      => 'daextam_analysis_max_execution_time_value',
								'label'     => __( 'Max Execution Time Value', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the maximum number of seconds allowed to execute long running scripts.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the max execution time value.', 'daext-autolinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 3600,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daextam_analysis_set_memory_limit',
								'label'   => __( 'Set Memory Limit', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Select "Yes" to enable your custom "Memory Limit Value" on long running scripts.', 'daext-autolinks-manager' ),
								'help'    => __(
									'Enable a custom memory limit.',
									'daext-autolinks-manager'
								),
							),
							array(
								'name'      => 'daextam_analysis_memory_limit_value',
								'label'     => __( 'Memory Limit Value', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This value determines the PHP memory limit in megabytes allowed to execute long running scripts.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the memory limit value.', 'daext-autolinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 16384,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daextam_analysis_limit_posts_analysis',
								'label'     => __( 'Limit Posts Analysis	', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'With this options you can determine the maximum number of posts analyzed to get information about your autolinks. If you select for example "1000", the analysis performed by the plugin will use your latest "1000" posts.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Limit the maximum number of analyzed posts.', 'daext-autolinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 100000,
								'rangeStep' => 1,
							),
							array(
								'name'          => 'daextam_analysis_post_types',
								'label'         => __( 'Dashboard Post Types', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine the post types analyzed in the Dashboard menu. Leave this field empty to perform the analysis in any post type.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types analyzed in the Dashboard menu.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_link_equity_post_types',
								'label'         => __( 'Link Equity Post Types', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine the post types analyzed in the Link Equity menu. Leave this field empty to perform the analysis in any post type.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types analyzed in the Link Equity menu.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_statistics_data_update_frequency',
								'label'         => __( 'Dashboard Data Update Frequency', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The frequency of the automatic data updates performed in the Dashboard menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => array(
									array(
										'value' => 'never',
										'text'  => __( 'Never', 'daext-autolinks-manager' ),
									),
									array(
										'value' => 'hourly',
										'text'  => __( 'Hourly', 'daext-autolinks-manager' ),
									),
									array(
										'value' => 'daily',
										'text'  => __( 'Daily', 'daext-autolinks-manager' ),
									),
									array(
										'value' => 'weekly',
										'text'  => __( 'Weekly', 'daext-autolinks-manager' ),
									),
									array(
										'value' => 'monthly',
										'text'  => __( 'Monthly', 'daext-autolinks-manager' ),
									),
								),
								'help'          => __( 'Select the frequency of the automatic data updates performed in the Dashboard menu.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_link_equity_data_update_frequency',
								'label'         => __( 'Link Equity Data Update Frequency', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __( 'Choose how often the link equity dataset should be refreshed automatically.', 'daext-autolinks-manager' ),
								'selectOptions' => array(
									array( 'value' => 'hourly', 'text' => __( 'Hourly', 'daext-autolinks-manager' ) ),
									array( 'value' => 'daily', 'text' => __( 'Daily', 'daext-autolinks-manager' ) ),
									array( 'value' => 'weekly', 'text' => __( 'Weekly', 'daext-autolinks-manager' ) ),
									array( 'value' => 'monthly', 'text' => __( 'Monthly', 'daext-autolinks-manager' ) ),
								),
								'help'          => __( 'Refresh the stored link equity data on the selected schedule.', 'daext-autolinks-manager' ),
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'Automatic Links', 'daext-autolinks-manager' ),
				'description' => __( 'Configure the application of the automatic links.', 'daext-autolinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Options', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'    => 'daextam_advanced_enable_autolinks',
								'label'   => __( 'Enable Autolinks', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'This option determines the default status of the "Enable" option in the "Automatic Links" block editor sidebar and meta box.',
									'daext-autolinks-manager'
								),
								'help'    => __( 'Enable the application of the automatic links.', 'daext-autolinks-manager' ),
							),
							array(
								'name'      => 'daextam_advanced_filter_priority',
								'label'     => __( 'Filter Priority', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This option determines the priority of the filter used to apply the automatic links. A lower number corresponds with an earlier execution.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the priority of the filter used to apply the automatic links.', 'daext-autolinks-manager' ),
								'rangeMin'  => - 2147483648,
								'rangeMax'  => 2147483646,
								'rangeStep' => 1,
							),
							array(
								'name'    => 'daextam_advanced_enable_test_mode',
								'label'   => __( 'Test Mode', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With the test mode enabled the automatic links will be applied to your posts, pages or custom post types only if the user that is requesting the posts, pages or custom post types has the capability defined with the "Autolinks Menu" option.',
									'daext-autolinks-manager'
								),
								'help'    => __( 'Apply the automatic links only when the site is viewed by privileged users.', 'daext-autolinks-manager' ),
							),
							array(
								'name'    => 'daextam_advanced_random_prioritization',
								'label'   => __( 'Random Prioritization', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									"With this option enabled the order used to apply the automatic links with the same priority is randomized on a per-post basis. With this option disabled the order used to apply the automatic links with the same priority is the order used to add them in the back-end. It's recommended to enable this option for a better distribution of the automatic links.",
									'daext-autolinks-manager'
								),
								'help'    => __( 'Improve the distribution of the automatic links.', 'daext-autolinks-manager' ),
							),
							array(
								'name'    => 'daextam_advanced_ignore_self_autolinks',
								'label'   => __( 'Ignore Self Autolinks', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __(
									'With this option enabled, the automatic links which have as a target the post where they should be applied, will be ignored.',
									'daext-autolinks-manager'
								),
								'help'    => __( 'Prevent the application of automatic links that targets the post where they should be applied.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_advanced_categories_and_tags_verification',
								'label'         => __( 'Categories & Tags Verification', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __( 'If "Post" is selected categories and tags will be verified only in the "post" post type, if "Any" is selected categories and tags will be verified in any post type.', 'daext-autolinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => 'post',
										'text'  => __( 'Post', 'daext-autolinks-manager' ),
									),
									array(
										'value' => 'any',
										'text'  => __( 'Any', 'daext-autolinks-manager' ),
									),
								),
								'help'          => __(
									'Select how to verify categories and tags.',
									'daext-autolinks-manager'
								),
							),
							array(
								'name'          => 'daextam_advanced_general_limit_mode',
								'label'         => __( 'General Limit Mode', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __( 'If "Auto" is selected the maximum number of automatic links per post is automatically generated based on the length of the post, in this case the "General Limit (Characters per Autolinks)" option is used. If "Manual" is selected the maximum number of automatic links per post is equal to the value of the "General Limit (Amount)" option.', 'daext-autolinks-manager' ),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Auto', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'Manual', 'daext-autolinks-manager' ),
									),
								),
								'help'          => __(
									'Select how the general limit of automatic links per post should be determined.',
									'daext-autolinks-manager'
								),
							),
							array(
								'name'      => 'daextam_advanced_general_limit_characters_per_autolink',
								'label'     => __( 'General Limit (Characters per Autolink)', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'This value is used to automatically determine the maximum number of autolinks per post when the "General Limit Mode" option is set to "Auto".', 'daext-autolinks-manager' ),
								'help'      => __(
									'Set the ideal number of characters per automatic links.',
									'daext-autolinks-manager'
								),
								'rangeMin'  => 1,
								'rangeMax'  => 50000,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daextam_advanced_general_limit_amount',
								'label'     => __( 'General Limit (Amount)', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'This value determines the maximum number of automatic links per post when the "General Limit Mode" option is set to "Manual".', 'daext-autolinks-manager' ),
								'help'      => __(
									'Set the maximum number of automatic links per post.',
									'daext-autolinks-manager'
								),
								'rangeMin'  => 1,
								'rangeMax'  => 500,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daextam_advanced_same_url_limit',
								'label'     => __( 'Same URL Limit', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'This option limits the number of autolinks with the same URL to a specific value.', 'daext-autolinks-manager' ),
								'help'      => __(
									'Set the maximum number of automatic links with the same URL.',
									'daext-autolinks-manager'
								),
								'rangeMin'  => 1,
								'rangeMax'  => 500,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daextam_advanced_protect_attributes',
								'label'     => __( 'Protect Attributes', 'daext-autolinks-manager' ),
								'type'      => 'toggle',
								'tooltip'   => __( 'With this option enabled, the automatic links will not be applied to HTML attributes.', 'daext-autolinks-manager' ),
								'help'      => __(
									'Do not apply the automatic links to HTML attributes.',
									'daext-autolinks-manager'
								),
							),
						),
					),
					array(
						'title'   => __( 'Protected Elements', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daextam_advanced_protected_tags',
								'label'         => __( 'Tags', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which HTML tags the autolinks should not be applied.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $protected_tags_html_tags_select_options,
								'help'          => __( 'Select the tags where the automatic links should not be applied.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_advanced_protected_gutenberg_blocks',
								'label'         => __( 'Gutenberg Blocks', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which Gutenberg blocks the automatic links should not be applied.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $protected_gutenberg_blocks_select_options,
								'help'          => __( 'Select the Gutenberg blocks where the automatic links should not be applied.', 'daext-autolinks-manager' ),
							),
							array(
								'name'    => 'daextam_advanced_protected_gutenberg_custom_blocks',
								'label'   => __( 'Gutenberg Custom Blocks', 'daext-autolinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'Enter a list of Gutenberg custom void blocks, separated by a comma.',
									'daext-autolinks-manager'
								),
								'help'    => __( 'Add the Gutenberg custom blocks where the automatic links should not be applied.', 'daext-autolinks-manager' ),
							),
							array(
								'name'    => 'daextam_advanced_protected_gutenberg_custom_void_blocks',
								'label'   => __( 'Gutenberg Custom Void Blocks', 'daext-autolinks-manager' ),
								'type'    => 'text',
								'tooltip' => __(
									'Enter a list of Gutenberg custom void blocks, separated by a comma.',
									'daext-autolinks-manager'
								),
								'help'    => __( 'Add the Gutenberg custom void blocks where the automatic links should not be applied.', 'daext-autolinks-manager' ),
							),
						),
					),
					array(
						'title'   => __( 'Defaults', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daextam_defaults_category_id',
								'label'         => __( 'Category', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The category of the autolink. This option determines the default value of the "Category" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $default_category_id_select_options,
								'help'          => __( 'Select the category of the automatic link.', 'daext-autolinks-manager' ),
							),
							array(
								'name'    => 'daextam_defaults_open_new_tab',
								'label'   => __( 'Open New Tab', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'If you enable this option, the link generated on the defined keyword opens the linked document in a new tab. This option determines the default value of the "Open New Tab" field available in the "Autolinks" menu.', 'daext-autolinks-manager' ),
								'help'    => __(
									'Open the linked document in a new tab.',
									'daext-autolinks-manager'
								),
							),
							array(
								'name'    => 'daextam_defaults_use_nofollow',
								'label'   => __( 'Use Nofollow', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'If you enable this option, the link generated on the defined keyword will include the rel="nofollow" attribute. This option determines the default value of the "Use Nofollow" field available in the "Autolinks" menu.', 'daext-autolinks-manager' ),
								'help'    => __(
									'Add the rel="nofollow" attribute to the link.',
									'daext-autolinks-manager'
								),
							),
							array(
								'name'          => 'daextam_defaults_post_types',
								'label'         => __( 'Post Types', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which post types the defined keywords will be automatically converted to a link. Leave this field empty to convert the keyword in any post type. This option determines the default value of the "Post Types" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the automatic links should be added.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_defaults_categories',
								'label'         => __( 'Categories', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which categories the defined keywords will be automatically converted to a link. Leave this field empty to convert the keyword in any category. This option determines the default value of the "Categories" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $categories_select_options,
								'help'          => __( 'Select the categories where the automatic links should be added.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_defaults_tags',
								'label'         => __( 'Tags', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine in which tags the defined keywords will be automatically converted to a link. Leave this field empty to convert the keyword in any tag. This option determines the default value of the "Tags" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $tags_select_options,
								'help'          => __( 'Select the tags where the automatic links should be added.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_defaults_term_group_id',
								'label'         => __( 'Target Group', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The terms that will be compared with the ones available on the posts where the automatic links are applied. Please note that when a target group is selected the "Categories" and "Tags" options will be ignored. This option determines the default value of the "Target Group" field available in the "Auto Link Rules" menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $term_group_select_options,
								'help'          => __( 'Select the target group where the automatic links should be added.', 'daext-autolinks-manager' ),
							),
							array(
								'name'    => 'daextam_defaults_case_sensitive_search',
								'label'   => __( 'Case Sensitive Search', 'daext-autolinks-manager' ),
								'type'    => 'toggle',
								'tooltip' => __( 'Use this option to turn the case-sensitive search on or off. If you disable this option, the defined keyword will match both lowercase and uppercase variations. This option determines the default value of the "Case Sensitive Search" field available in the "Autolinks" menu.', 'daext-autolinks-manager' ),
								'help'    => __(
									'Enable the case-sensitive search.',
									'daext-autolinks-manager'
								),
							),
							array(
								'name'          => 'daextam_defaults_left_boundary',
								'label'         => __( 'Left Boundary', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Use this option to match keywords preceded by a generic boundary or by a specific character. This option determines the default value of the "Left Boundary" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Generic', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'White Space', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '2',
										'text'  => __( 'Comma', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '3',
										'text'  => __( 'Point', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '4',
										'text'  => __( 'None', 'daext-autolinks-manager' ),
									),
								),
								'help'          => __( 'Select the boundary or character that should precede the keyword.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_defaults_right_boundary',
								'label'         => __( 'Right Boundary', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'Use this option to match keywords followed by a generic boundary or by a specific character. This option determines the default value of the "Right Boundary" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => array(
									array(
										'value' => '0',
										'text'  => __( 'Generic', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '1',
										'text'  => __( 'White Space', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '2',
										'text'  => __( 'Comma', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '3',
										'text'  => __( 'Point', 'daext-autolinks-manager' ),
									),
									array(
										'value' => '4',
										'text'  => __( 'None', 'daext-autolinks-manager' ),
									),
								),
								'help'          => __( 'Select the boundary or character that should follow the keyword.', 'daext-autolinks-manager' ),
							),
							array(
								'name'      => 'daextam_defaults_limit',
								'label'     => __( 'Limit', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'With this option you can determine the maximum number of matches of the defined keyword automatically converted to a link. This option determines the default value of the "Limit" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the maximum number of keywords automatically converted to links.', 'daext-autolinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 500,
								'rangeStep' => 1,
							),
							array(
								'name'      => 'daextam_defaults_priority',
								'label'     => __( 'Priority', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'The priority value determines the order used to apply the autolinks on the post. This option determines the default value of the "Priority" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the priority of the keyword.', 'daext-autolinks-manager' ),
								'rangeMin'  => 0,
								'rangeMax'  => 100,
								'rangeStep' => 1,
							),
						),
					),
				),
			),
			array(
				'title'       => __( 'Advanced', 'daext-autolinks-manager' ),
				'description' => __( 'Manage advanced plugin settings.', 'daext-autolinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Editor Panels', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'          => 'daextam_automatic_links_panel_post_types',
								'label'         => __( 'Automatic Links Post Types', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'Use this option to determine in which post types the "Automatic Links" panel should be displayed in the editor.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the "Automatic Links" editor panel should be available.', 'daext-autolinks-manager' ),
							),
							array(
								'name'          => 'daextam_interlinks_optimization_post_types',
								'label'         => __( 'Internal Links Optimization Post Types', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'Use this option to determine in which post types the "Internal Links Optimization" panel should be displayed in the editor.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types where the "Internal Links Optimization" editor panel should be available.', 'daext-autolinks-manager' ),
							),
						),
					),
					array(
						'title'   => __( 'Misc', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daextam_advanced_supported_terms',
								'label'     => __( 'Supported Terms', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This option determines the maximum number of terms supported in a single target group.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the maximum number of terms supported in a single target group.', 'daext-autolinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 50,
								'rangeStep' => 1,
							),
						),
					),
					array(
						'title'   => __( 'Pagination', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daextam_pagination_statistics_menu',
								'label'     => __( 'Dashboard Menu', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'This options determines the number of elements per page displayed in the "Dashboard" menu.', 'daext-autolinks-manager' ),
								'help'      => __( 'Set the number of elements per page displayed in the "Dashboard" menu.', 'daext-autolinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
							array(
								'name'      => 'daextam_pagination_link_equity_menu',
								'label'     => __( 'Link Equity Menu', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'This options determines the number of elements per page displayed in the "Link Equity" menu.', 'daext-autolinks-manager' ),
								'help'      => __( 'Set the number of elements per page displayed in the "Link Equity" menu.', 'daext-autolinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
							array(
								'name'      => 'daextam_pagination_autolinks_menu',
								'label'     => __( 'Auto Link Rules Menu', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __( 'This options determines the number of elements per page displayed in the "Auto Link Rules" menu.', 'daext-autolinks-manager' ),
								'help'      => __( 'Set the number of elements per page displayed in the "Auto Link Rules" menu.', 'daext-autolinks-manager' ),
								'rangeMin'  => 10,
								'rangeMax'  => 100,
								'rangeStep' => 10,
							),
						),
					),
				),
			),
		);

		return $configuration;
	}


}
