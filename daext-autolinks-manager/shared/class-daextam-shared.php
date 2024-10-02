<?php
/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 *
 * @package daext-autolinks-manager
 */

/**
 * This class should be used to stores properties and methods shared by the
 * admin and public side of WordPress.
 */
class Daextam_Shared {

	// Properties used in add_autolinks() -----------------------------------------------------------------------------.

	/**
	 * The ID of the autolink.
	 *
	 * @var int
	 */
	private $autolink_id = 0;

	/**
	 * The autolink array.
	 *
	 * @var array
	 */
	private $autolink_a = array();

	/**
	 * The object of the parsed autolink.
	 *
	 * @var object
	 */
	private $parsed_autolink = null;

	/**
	 * The parsed post type.
	 *
	 * @var string
	 */
	private $parsed_post_type = null;

	/**
	 * The max number of autolinks allowed per post.
	 *
	 * @var int
	 */
	private $max_number_autolinks_per_post = null;

	/**
	 * The same URL limit.
	 *
	 * @var int
	 */
	private $same_url_limit = null;

	/**
	 * An array with included the data of the autolinks used for performance reasons.
	 *
	 * @var array
	 */
	private $autolinks_ca = null;

	/**
	 * The ID of the protected block.
	 *
	 * @var null
	 */
	private $pb_id = null;

	/**
	 * The protected block array.
	 *
	 * @var null
	 */
	private $pb_a = null;

	/**
	 * The post ID of the protected block.
	 *
	 * @var null
	 */
	private $post_id = null;

	// Regex ----------------------------------------------------------------------------------------------------------.

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
	 * The number of replacements.
	 *
	 * @var int The number of replacements.
	 */
	public $number_of_replacements = 0;

	/**
	 * The singleton instance of the class.
	 *
	 * @var Daextam_Shared
	 */
	protected static $instance = null;

	/**
	 * The data of the plugin.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 */
	private function __construct() {

		// Set plugin textdomain.
		load_plugin_textdomain( 'daext-autolinks-manager', false, 'daext-autolinks-manager/lang/' );

		$this->data['slug'] = 'daextam';
		$this->data['ver']  = '1.10.08';
		$this->data['dir']  = substr( plugin_dir_path( __FILE__ ), 0, -7 );
		$this->data['url']  = substr( plugin_dir_url( __FILE__ ), 0, -7 );

		// Here are stored the plugin option with the related default values.
		$this->data['options'] = array(

			// Database Version (not available in the options UI).
			$this->get( 'slug' ) . '_database_version'     => '0',

			// Options version. (not available in the options UI).
			$this->get( 'slug' ) . '_options_version'      => '0',

			/**
			 * Statistics data and HTTP Status data last update. Used for the automatic data update in the Dashboard and
			 * HTTP Status menus. (not available in the options UI)
			 */
			$this->get( 'slug' ) . '_statistics_data_last_update' => '',

			// Defaults -----------------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_defaults_category_id' => '0',
			$this->get( 'slug' ) . '_defaults_open_new_tab' => '0',
			$this->get( 'slug' ) . '_defaults_use_nofollow' => '0',
			$this->get( 'slug' ) . '_defaults_post_types'  => '',
			$this->get( 'slug' ) . '_defaults_categories'  => '',
			$this->get( 'slug' ) . '_defaults_tags'        => '',
			$this->get( 'slug' ) . '_defaults_term_group_id' => '0',
			$this->get( 'slug' ) . '_defaults_case_sensitive_search' => '1',
			$this->get( 'slug' ) . '_defaults_left_boundary' => '0',
			$this->get( 'slug' ) . '_defaults_right_boundary' => '0',
			$this->get( 'slug' ) . '_defaults_limit'       => '100',
			$this->get( 'slug' ) . '_defaults_priority'    => '0',

			// Analysis -----------------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_analysis_set_max_execution_time' => '1',
			$this->get( 'slug' ) . '_analysis_max_execution_time_value' => '300',
			$this->get( 'slug' ) . '_analysis_set_memory_limit' => '1',
			$this->get( 'slug' ) . '_analysis_memory_limit_value' => '512',
			$this->get( 'slug' ) . '_analysis_limit_posts_analysis' => '1000',
			$this->get( 'slug' ) . '_analysis_post_types'  => '',
			$this->get( 'slug' ) . '_statistics_data_update_frequency' => 'hourly',

			// Advanced -----------------------------------------------------------------------------------------------.
			$this->get( 'slug' ) . '_advanced_enable_autolinks' => '1',
			$this->get( 'slug' ) . '_advanced_filter_priority' => '2147483646',
			$this->get( 'slug' ) . '_advanced_enable_test_mode' => '0',
			$this->get( 'slug' ) . '_advanced_random_prioritization' => '1',
			$this->get( 'slug' ) . '_advanced_ignore_self_autolinks' => '1',
			$this->get( 'slug' ) . '_advanced_categories_and_tags_verification' => 'post',
			$this->get( 'slug' ) . '_advanced_general_limit_mode' => '1',
			$this->get( 'slug' ) . '_advanced_general_limit_characters_per_autolink' => '200',
			$this->get( 'slug' ) . '_advanced_general_limit_amount' => '100',
			$this->get( 'slug' ) . '_advanced_same_url_limit' => '100',
			$this->get( 'slug' ) . '_advanced_protect_attributes' => '0',

			/*
			 * By default the following HTML tags are protected:
			 *
			 * - h1
			 * - h2
			 * - h3
			 * - h4
			 * - h5
			 * - h6
			 * - a
			 * - img
			 * - pre
			 * - code
			 * - table
			 * - iframe
			 * - script
			 */
			$this->get( 'slug' ) . '_advanced_protected_tags' => array(
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
				'a',
				'img',
				'ul',
				'ol',
				'span',
				'pre',
				'code',
				'table',
				'iframe',
				'script',
			),

			/*
			 * By default all the Gutenberg Blocks except the following are protected:
			 *
			 * - Paragraph
			 * - List
			 * - Text Columns
			 */
			$this->get( 'slug' ) . '_advanced_protected_gutenberg_blocks' => array(
				// 'paragraph',
				'image',
				'heading',
				'gallery',
				// 'list',
				'quote',
				'audio',
				'cover-image',
				'subhead',
				'video',
				'code',
				'html',
				'preformatted',
				'pullquote',
				'table',
				'verse',
				'button',
				'columns',
				'more',
				'nextpage',
				'separator',
				'spacer',
				// 'text-columns',
				'shortcode',
				'categories',
				'latest-posts',
				'embed',
				'core-embed/twitter',
				'core-embed/youtube',
				'core-embed/facebook',
				'core-embed/instagram',
				'core-embed/wordpress',
				'core-embed/soundcloud',
				'core-embed/spotify',
				'core-embed/flickr',
				'core-embed/vimeo',
				'core-embed/animoto',
				'core-embed/cloudup',
				'core-embed/collegehumor',
				'core-embed/dailymotion',
				'core-embed/funnyordie',
				'core-embed/hulu',
				'core-embed/imgur',
				'core-embed/issuu',
				'core-embed/kickstarter',
				'core-embed/meetup-com',
				'core-embed/mixcloud',
				'core-embed/photobucket',
				'core-embed/polldaddy',
				'core-embed/reddit',
				'core-embed/reverbnation',
				'core-embed/screencast',
				'core-embed/scribd',
				'core-embed/slideshare',
				'core-embed/smugmug',
				'core-embed/speaker',
				'core-embed/ted',
				'core-embed/tumblr',
				'core-embed/videopress',
				'core-embed/wordpress-tv',
			),

			$this->get( 'slug' ) . '_advanced_protected_gutenberg_custom_blocks' => '',
			$this->get( 'slug' ) . '_advanced_protected_gutenberg_custom_void_blocks' => '',
			$this->get( 'slug' ) . '_advanced_supported_terms' => '10',

		);

		add_action( 'delete_term', array( $this, 'delete_term_action' ), 10, 3 );
	}

	/**
	 * Get the singleton instance of the class.
	 *
	 * @return Daextam_Shared|self|null
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieve data.
	 *
	 * @param string $index The index of the data to retrieve.
	 *
	 * @return mixed
	 */
	public function get( $index ) {
		return $this->data[ $index ];
	}

	/**
	 * Add autolinks to the content based on the keyword created with the autolinks menu:
	 *
	 *  1 - The protected blocks are applied with apply_protected_blocks()
	 *  2 - The words to be converted as a link are temporarely replaced with [ail]ID[/ail]
	 *  3 - The [al]ID[/al] identifiers are replaced with the actual links
	 *  4 - The protected block are removed with the remove_protected_blocks()
	 *  5 - The content with applied the autolinks is returned
	 *
	 * @param string $content The content on which the autolinks should be applied.
	 * @param bool   $check_query This parameter is set to True when the method is called inside the loop and is used to
	 *    verify if we are in a single post.
	 * @param string $post_type If the autolinks are added from the back-end this parameter is used to determine the post type
	 *  of the content.
	 * @param int    $post_id This parameter is used if the method has been called outside the loop.
	 *
	 * @return string The content with applied the autolinks.
	 */
	public function add_autolinks( $content, $check_query = true, $post_type = '', $post_id = false ) {

		// Verify that we are inside a post, page or cpt.
		if ( $check_query ) {
			if ( ! is_singular() || is_attachment() || is_feed() ) {
				return $content;
			}
		}

		// If the $post_id is not set means that we are in the loop and can be retrieved with get_the_ID().
		if ( false === $post_id ) {
			$this->post_id = get_the_ID();
		} else {
			$this->post_id = $post_id;
		}

		// Get the permalink.
		$post_permalink = get_permalink( $this->post_id );

		/*
		 * Verify with the "Enable Autolinks" post meta data or (if the meta data is not present) verify with the
		 * "Enable Autolinks" option if the autolinks should be applied to this post.
		 */
		$enable_autolinks = get_post_meta( $this->post_id, '_daextam_enable_autolinks', true );
		if ( strlen( trim( $enable_autolinks ) ) === 0 ) {
			$enable_autolinks = get_option( $this->get( 'slug' ) . '_advanced_enable_autolinks' );
		}
		if ( intval( $enable_autolinks, 10 ) === 0 ) {
			$this->number_of_replacements = 0;
			return $content;
		}

		// Protect the tags and the commented HTML with the protected blocks.
		$content = $this->apply_protected_blocks( $content );

		// Get the maximum number of autolinks allowed per post.
		$this->max_number_autolinks_per_post = $this->get_max_number_autolinks_per_post( $this->post_id );

		// Save the "Same URL Limit" as a class property.
		$this->same_url_limit = intval( get_option( $this->get( 'slug' ) . '_advanced_same_url_limit' ), 10 );

		// Get an array with the autolinks from the db table.
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$autolinks = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextam_autolink ORDER BY priority DESC", ARRAY_A );

		/*
		 * To avoid additional database requests for each autolink in preg_replace_callback_2() save the data of the
		 * autolink in an array that uses the "autolink_id" as its index.
		 */
		$this->autolinks_ca = $this->save_autolinks_in_custom_array( $autolinks );

		// Apply the Random Prioritization if enabled.
		if ( intval( get_option( $this->get( 'slug' ) . '_advanced_random_prioritization' ), 10 ) === 1 ) {
			$autolinks = $this->apply_random_prioritization( $autolinks, $this->post_id );
		}

		// Iterate through all the defined autolinks.
		foreach ( $autolinks as $key => $autolink ) {

			// Save this autolink as a class property.
			$this->parsed_autolink = $autolink;

			/*
			 * If $post_type is not empty means that we are adding the autolinks through the back-end, in this case set
			 * the $this->parsed_post_type property with the $post_type variable.
			 *
			 * If $post_type is empty means that we are in the loop and the post type can be retrieved with the
			 * get_post_type() function.
			 */
			if ( '' !== $post_type ) {
				$this->parsed_post_type = $post_type;
			} else {
				$this->parsed_post_type = get_post_type();
			}

			/*
			 * If the "Ignore Self Autolinks" option is set to true, do not apply the autolinks that have, as a target,
			 * the post where they should be applied.
			 */
			if ( intval( get_option( $this->get( 'slug' ) . '_advanced_ignore_self_autolinks' ), 10 ) === 1 ) {
				if ( $autolink['url'] === $post_permalink ) {
					continue;
				}
			}

			// Get the list of post types where the autolinks should be applied.
			$post_types_a = maybe_unserialize( $autolink['post_types'] );

			// If $post_types_a is not an array fill $post_types_a with the posts available in the website.
			if ( ! is_array( $post_types_a ) ) {
				$post_types_a = $this->get_post_types_with_ui();
			}

			// Verify the post type.
			if ( false === in_array( $this->parsed_post_type, $post_types_a, true ) ) {
				continue;
			}

			/*
			 * If the term group is not set:
			 *
			 * - Check if the post is compliant by verifying categories and tags
			 *
			 * If the term group is set:
			 *
			 * - Check if the post is compliant by verifying the term group
			 */
			if ( intval( $autolink['term_group_id'], 10 ) === 0 ) {

				/**
				 * Verify categories and tags only in the "post" post type or in all the posts. This verification is based
				 *  on the value of the $categories_and_tags_verification option.
				 *
				 *  - If $categories_and_tags_verification is equal to "any" verify the presence of the selected categories
				 *  and tags in any post type.
				 *  - If $categories_and_tags_verification is equal to "post" verify the presence of the selected categories
				 *  and tags only in the "post" post type.
				 */
				$categories_and_tags_verification = get_option( $this->get( 'slug' ) . '_advanced_categories_and_tags_verification' );
				if ( ( 'any' === $categories_and_tags_verification || 'post' === get_post_type() ) &&
					( ! $this->is_compliant_with_categories( $this->post_id, $autolink ) ||
						! $this->is_compliant_with_tags( $this->post_id, $autolink ) ) ) {
					continue;
				}
			} elseif ( ! $this->is_compliant_with_term_group( $this->post_id, $autolink ) ) {

				/**
				 * Do not proceed with the application of the autolink if this post is not compliant with the term
				 * group.
				 */
				continue;

			}

			// Get the max number of autolinks per keyword.
			$max_number_autolinks_per_keyword = $autolink['limit'];

			// Apply a case-sensitive search if the case_sensitive_flag is set to True.
			if ( $autolink['case_sensitive_search'] ) {
				$modifier = 'u';// Enable unicode modifier.
			} else {
				$modifier = 'iu';// Enable case insensitive and unicode modifier.
			}

			// Find the left boundary.
			switch ( $autolink['left_boundary'] ) {
				case 0:
					$left_boundary = '\b';
					break;

				case 1:
					$left_boundary = ' ';
					break;

				case 2:
					$left_boundary = ',';
					break;

				case 3:
					$left_boundary = '\.';
					break;

				case 4:
					$left_boundary = '';
					break;
			}

			// Find the right boundary.
			switch ( $autolink['right_boundary'] ) {
				case 0:
					$right_boundary = '\b';
					break;

				case 1:
					$right_boundary = ' ';
					break;

				case 2:
					$right_boundary = ',';
					break;

				case 3:
					$right_boundary = '\.';
					break;

				case 4:
					$right_boundary = '';
					break;
			}

			// Escape regex characters and the '/' regex delimiter.
			$autolink_keyword        = preg_quote( $autolink['keyword'], '/' );
			$autolink_keyword_before = preg_quote( $autolink['keyword_before'], '/' );
			$autolink_keyword_after  = preg_quote( $autolink['keyword_after'], '/' );

			/*
			 * Step 1: "The creation of temporary identifiers of the substitutions"
			 *
			 * Replaces all the matches with the [al]ID[/al] string, where the ID is the identifier of the substitution.
			 * The ID is also used as the index of the $this->autolink_a temporary array used to store information about
			 * all the substutions. This array will be later used in "Step 2" to replace the [al]ID[/al] string with the
			 * actual links.
			 */
			$content = preg_replace_callback(
				'/(' . $autolink_keyword_before . ')(' . ( $left_boundary ) . ')(' . $autolink_keyword . ')(' . ( $right_boundary ) . ')(' . $autolink_keyword_after . ')/' . $modifier,
				array( $this, 'preg_replace_callback_1' ),
				$content,
				$max_number_autolinks_per_keyword
			);

		}

		/*
		 * Step 2: "The replacement of the temporary string [ail]ID[/ail]"
		 *
		 * Replaces the [al]ID[/al] matches found in the $content with the actual links by using the $this->autolink_a
		 * array to find the identifier of the substitutions and by retrieving in the db table "autolinks" (with the
		 * "autolink_id") additional information about the substitution.
		 */
		$content = preg_replace_callback(
			'/\[al\](\d+)\[\/al\]/',
			array( $this, 'preg_replace_callback_2' ),
			$content,
			-1,
			$this->number_of_replacements
		);

		// Remove the protected blocks.
		$content = $this->remove_protected_blocks( $content );

		// Reset the id of the autolink.
		$this->autolink_id = 0;

		// Reset the array that includes the data of the autolinks already applied.
		$this->autolink_a = array();

		return $content;
	}

	/**
	 * Replaces the following elements with [pr]ID[/pr]:
	 *
	 *  - HTML Attributes
	 *  - Protected Gutenberg Blocks
	 *  - Protected Gutenberg Custom Blocks
	 *  - Protected Gutenberg Custom Void Blocks
	 *  - The sections enclosed in HTML comments
	 *  - The Protected Tags
	 *
	 *  The replaced tags and URLs are saved in the property $pr_a, an array with the ID used in the block as the index.
	 *
	 * @param string $content The unprotected $content.
	 *
	 * @return string The $content with applied the protected block
	 */
	private function apply_protected_blocks( $content ) {

		$this->pb_id = 0;
		$this->pb_a  = array();

		// Protect all the HTML attributes if the "Protect Attributes" option is enabled.
		if ( intval( get_option( $this->get( 'slug' ) . '_advanced_protect_attributes' ), 10 ) === 1 ) {

			// Match all the HTML attributes that use double quotes as the attribute value delimiter.
			$content = preg_replace_callback(
				'{
					<[a-z0-9]+    #1 The beginning of any HTML element
					\s+           #2 Optional whitespaces 
					(             #3 Begin a group
					(?:           #4 Begin a non-capturing group	
					\s*           #5 Optional whitespaces
					[a-z0-9-_]+   #6 Match the name of the attribute
					\s*=\s*       #7 Equal may have whitespaces on both sides
					"             #8 Match double quotes
					[^"]*         #9 Any character except double quotes zero or more times
					"             #10 Match double quotes
					\s*           #11 Optional whitespaces 
					|			  #12 Provide an alternative to match attributes without values like for example "itemscope"
					\s*           #13 Optional whitespaces
					[a-z0-9-_]    #14 Match the name of the attribute
					\s*           #15 Optional whitespaces 
					)*            #16 Close the group that matches the complete attribute (attribute name + equal sign + attribute value) and use the * to match multiple groups
					)             #17 Close the main capturing group
					\/?           #18 Match an optional / (used in void elements)
					>             #19 Match the end of any HTML element
                    }ixs',
				array( $this, 'apply_single_protected_block_attributes' ),
				$content
			);

			// Match all the HTML attributes that use single quotes as the attribute value delimiter.
			$content = preg_replace_callback(
				'{
					<[a-z0-9]+    #1 The beginning of any HTML element
					\s+           #2 Optional whitespaces 
					(             #3 Begin a group
					(?:           #4 Begin a non-capturing group
					\s*           #5 Optional whitespaces
					[a-z0-9-_]+   #6 Match the name of the attribute
					\s*=\s*       #7 Equal may have whitespaces on both sides
					\'            #8 Match single quote
					[^\']*        #9 Any character except single quote zero or more times
					\'            #10 Match single quote
					\s*           #11 Optional whitespaces 
					|			  #12 Provide an alternative to match attributes without values like for example "itemscope"
					\s*           #13 Optional whitespaces
					[a-z0-9-_]    #14 Match the name of the attribute
					\s*           #15 Optional whitespaces 
					)*            #16 Close the group that matches the complete attribute (attribute name + equal sign + attribute value) and use the * to match multiple groups
					)             #17 Close the main capturing group
					\/?           #18 Match an optional / (used in void elements)
					>             #19 Match the end of any HTML element
                    }ixs',
				array( $this, 'apply_single_protected_block_attributes' ),
				$content
			);

		}

		// Get the Gutenberg Protected Blocks.
		$protected_gutenberg_blocks   = get_option( $this->get( 'slug' ) . '_advanced_protected_gutenberg_blocks' );
		$protected_gutenberg_blocks_a = maybe_unserialize( $protected_gutenberg_blocks );
		if ( ! is_array( $protected_gutenberg_blocks_a ) ) {
			$protected_gutenberg_blocks_a = array();
		}

		// Get the Protected Gutenberg Custom Blocks.
		$protected_gutenberg_custom_blocks   = get_option( $this->get( 'slug' ) . '_advanced_protected_gutenberg_custom_blocks' );
		$protected_gutenberg_custom_blocks_a = array_filter(
			explode(
				',',
				str_replace( ' ', '', trim( $protected_gutenberg_custom_blocks ) )
			)
		);

		// Get the Protected Gutenberg Custom Void Blocks.
		$protected_gutenberg_custom_void_blocks   = get_option( $this->get( 'slug' ) . '_advanced_protected_gutenberg_custom_void_blocks' );
		$protected_gutenberg_custom_void_blocks_a = array_filter(
			explode(
				',',
				str_replace( ' ', '', trim( $protected_gutenberg_custom_void_blocks ) )
			)
		);

		$protected_gutenberg_blocks_comprehensive_list_a = array_merge(
			$protected_gutenberg_blocks_a,
			$protected_gutenberg_custom_blocks_a,
			$protected_gutenberg_custom_void_blocks_a
		);

		if ( is_array( $protected_gutenberg_blocks_comprehensive_list_a ) ) {

			foreach ( $protected_gutenberg_blocks_comprehensive_list_a as $key => $block ) {

				// Non-Void Blocks.
				if ( 'paragraph' === $block ||
					'image' === $block ||
					'heading' === $block ||
					'gallery' === $block ||
					'list' === $block ||
					'quote' === $block ||
					'audio' === $block ||
					'cover-image' === $block ||
					'subhead' === $block ||
					'video' === $block ||
					'code' === $block ||
					'preformatted' === $block ||
					'pullquote' === $block ||
					'table' === $block ||
					'verse' === $block ||
					'button' === $block ||
					'columns' === $block ||
					'more' === $block ||
					'nextpage' === $block ||
					'separator' === $block ||
					'spacer' === $block ||
					'text-columns' === $block ||
					'shortcode' === $block ||
					'embed' === $block ||
					'core-embed/twitter' === $block ||
					'core-embed/youtube' === $block ||
					'core-embed/facebook' === $block ||
					'core-embed/instagram' === $block ||
					'core-embed/wordpress' === $block ||
					'core-embed/soundcloud' === $block ||
					'core-embed/spotify' === $block ||
					'core-embed/flickr' === $block ||
					'core-embed/vimeo' === $block ||
					'core-embed/animoto' === $block ||
					'core-embed/cloudup' === $block ||
					'core-embed/collegehumor' === $block ||
					'core-embed/dailymotion' === $block ||
					'core-embed/funnyordie' === $block ||
					'core-embed/hulu' === $block ||
					'core-embed/imgur' === $block ||
					'core-embed/issuu' === $block ||
					'core-embed/kickstarter' === $block ||
					'core-embed/meetup-com' === $block ||
					'core-embed/mixcloud' === $block ||
					'core-embed/photobucket' === $block ||
					'core-embed/polldaddy' === $block ||
					'core-embed/reddit' === $block ||
					'core-embed/reverbnation' === $block ||
					'core-embed/screencast' === $block ||
					'core-embed/scribd' === $block ||
					'core-embed/slideshare' === $block ||
					'core-embed/smugmug' === $block ||
					'core-embed/speaker' === $block ||
					'core-embed/ted' === $block ||
					'core-embed/tumblr' === $block ||
					'core-embed/videopress' === $block ||
					'core-embed/wordpress-tv' === $block ||
					in_array( $block, $protected_gutenberg_custom_blocks_a, true )
				) {

					// Escape regex characters and the '/' regex delimiter.
					$block = preg_quote( $block, '/' );

					// Non-Void Blocks Regex.
					$content = preg_replace_callback(
						'/
                    <!--\s+(wp:' . $block . ').*?-->        #1 Gutenberg Block Start
                    .*?                                     #2 Gutenberg Content
                    <!--\s+\/\1\s+-->                       #3 Gutenberg Block End
                    /ixs',
						array( $this, 'apply_single_protected_block' ),
						$content
					);

					// Void Blocks.
				} elseif ( 'html' === $block ||
							'categories' === $block ||
							'latest-posts' === $block ||
							in_array( $block, $protected_gutenberg_custom_void_blocks_a, true )
				) {

					// Escape regex characters and the '/' regex delimiter.
					$block = preg_quote( $block, '/' );

					// Void Blocks Regex.
					$content = preg_replace_callback(
						'/
                    <!--\s+wp:' . $block . '.*?\/-->        #1 Void Block
                    /ix',
						array( $this, 'apply_single_protected_block' ),
						$content
					);

				}
			}
		}

		/**
		 * Protect the commented sections, enclosed between <!-- and -->
		 */
		$content = preg_replace_callback(
			'/
            <!--                                #1 Comment Start
            .*?                                 #2 Any character zero or more time with a lazy quantifier
            -->                                 #3 Comment End
            /ix',
			array( $this, 'apply_single_protected_block' ),
			$content
		);

		/**
		 * Get the list of the protected tags from the "Protected Tags" option.
		 */
		$protected_tags   = get_option( $this->get( 'slug' ) . '_advanced_protected_tags' );
		$protected_tags_a = maybe_unserialize( $protected_tags );

		if ( is_array( $protected_tags_a ) ) {

			foreach ( $protected_tags_a as $key => $single_protected_tag ) {

				/**
				 * Validate the tag. HTML elements all have names that only use
				 *  characters in the range 0–9, a–z, and A–Z.
				 */
				if ( preg_match( '/^[0-9a-zA-Z]+$/', $single_protected_tag ) === 1 ) {

					// Make the tag lowercase.
					$single_protected_tag = strtolower( $single_protected_tag );

					// Apply different treatment if the tag is a void tag or a non-void tag.
					if ( 'area' === $single_protected_tag ||
						'base' === $single_protected_tag ||
						'br' === $single_protected_tag ||
						'col' === $single_protected_tag ||
						'embed' === $single_protected_tag ||
						'hr' === $single_protected_tag ||
						'img' === $single_protected_tag ||
						'input' === $single_protected_tag ||
						'keygen' === $single_protected_tag ||
						'link' === $single_protected_tag ||
						'meta' === $single_protected_tag ||
						'param' === $single_protected_tag ||
						'source' === $single_protected_tag ||
						'track' === $single_protected_tag ||
						'wbr' === $single_protected_tag
					) {

						// Apply the protected block on void tags.
						$content = preg_replace_callback(
							'/                                  
                            <                                   #1 Begin the start-tag
                            (' . $single_protected_tag . ')     #2 The tag name (captured for the backreference)
                            (\s+[^>]*)?                         #3 Match the rest of the start-tag
                            >                                   #4 End the start-tag
                            /ix',
							array( $this, 'apply_single_protected_block' ),
							$content
						);

					} else {

						// Apply the protected block on non-void tags.
						$content = preg_replace_callback(
							'/
                            <                                   #1 Begin the start-tag
                            (' . $single_protected_tag . ')     #2 The tag name (captured for the backreference)
                            (\s+[^>]*)?                         #3 Match the rest of the start-tag
                            >                                   #4 End the start-tag
                            .*?                                 #5 The element content (with the "s" modifier the dot matches also the new lines)
                            <\/\1\s*>                           #6 The end-tag with a backreference to the tag name (\1) and optional white-spaces before the closing >
                            /ixs',
							array( $this, 'apply_single_protected_block' ),
							$content
						);

					}
				}
			}
		}

		return $content;
	}

	/**
	 * This method is used inside all the preg_replace_callback located in the apply_protected_blocks() method.
	 *
	 *  What it does is:
	 *
	 *  1 - Saves the match in the $pb_a array
	 *  2 - Returns the protected block with the related identifier ([pb]ID[/pb])
	 *
	 * @param array $m An array with at index 0 the complete match and at index 1 the capture group.
	 *
	 * @return string
	 */
	private function apply_single_protected_block( $m ) {

		// Save the match in the $pb_a array.
		++$this->pb_id;
		$this->pb_a[ $this->pb_id ] = $m[0];

		// Replaces the portion of post with the protected block and the index of the $pb_a array as the identifier.
		return '[pb]' . $this->pb_id . '[/pb]';
	}

	/**
	 * This method is used by a preg_replace_callback located in the apply_protected_blocks() method.
	 *
	 *  Specifically, this method is used to apply a protected block on the matched HTML attributes.
	 *
	 *  What it does:
	 *
	 *  1 - Saves the match in the $pb_a array
	 *  2 - Replaces the matched HTML attributes with a protected blocks
	 *  2 - Returns the modified HTML
	 *
	 * @param array $m An array with at index 0 the complete match and at index 1 the first capturing group (one or more HTML
	 *  attributes).
	 *
	 * @return string
	 */
	private function apply_single_protected_block_attributes( $m ) {

		// Save the match in the $pb_a array.
		++$this->pb_id;
		$this->pb_a[ $this->pb_id ] = $m[1];

		// Replace the matched attribute with the protected block and return it.
		return str_replace( $m[1], '[pb]' . $this->pb_id . '[/pb]', $m[0] );
	}

	/**
	 * Replaces the block [pr]ID[/pr] with the related portion of post found in the $pb_a property.
	 *
	 * @param $content string The $content with applied the protected block.
	 * return array|string|string[]|null The unprotected content.
	 */
	private function remove_protected_blocks( $content ) {

		$content = preg_replace_callback(
			'/\[pb\](\d+)\[\/pb\]/',
			array( $this, 'preg_replace_callback_3' ),
			$content
		);

		return $content;
	}

	/**
	 * Callback of the preg_replace_callback() function.
	 *
	 *  This callback is used to avoid an anonymous function as a parameter of the preg_replace_callback() function for
	 *  PHP backward compatibility.
	 *
	 *  Look for uses of preg_replace_callback_1 to find which preg_replace_callback() function is actually using this
	 *  callback.
	 *
	 * @param array $m Todo.
	 *
	 * @return string
	 */
	public function preg_replace_callback_1( $m ) {

		/**
		 * Do not apply the replacement and return the matched string in the following cases:
		 *
		 *  - If the max number of autolinks per post has been reached
		 *  - If the "Same URL Limit" has been reached
		 */
		if ( $this->max_number_autolinks_per_post === $this->autolink_id ||
			$this->same_url_limit_reached() ) {

			return $m[1] . $m[2] . $m[3] . $m[4] . $m[5];

		} else {

			/**
			 * Increases the $autolink_id property and stores the information related to this autolink and match in the
			 * $autolink_a property. These information will be later used to replace the temporary identifiers of the
			 * autolinks with the related data, and also in this method to verify the "Same URL Limit" option.
			 */
			++$this->autolink_id;
			$this->autolink_a[ $this->autolink_id ]['autolink_id']    = $this->parsed_autolink['autolink_id'];
			$this->autolink_a[ $this->autolink_id ]['url']            = $this->parsed_autolink['url'];
			$this->autolink_a[ $this->autolink_id ]['text']           = $m[3];
			$this->autolink_a[ $this->autolink_id ]['left_boundary']  = $m[2];
			$this->autolink_a[ $this->autolink_id ]['right_boundary'] = $m[4];
			$this->autolink_a[ $this->autolink_id ]['keyword_before'] = $m[1];
			$this->autolink_a[ $this->autolink_id ]['keyword_after']  = $m[5];

			// Replaces the match with the temporary identifier of the autolink.
			return '[al]' . $this->autolink_id . '[/al]';

		}
	}

	/**
	 * Callback of the preg_replace_callback() function
	 *
	 *  This callback is used to avoid an anonymous function as a parameter of the preg_replace_callback() function for
	 *  PHP backward compatibility.
	 *
	 *  Look for uses of preg_replace_callback_2 to find which preg_replace_callback() function is actually using this
	 *  callback.
	 *
	 * @param array $m Todo.
	 */
	public function preg_replace_callback_2( $m ) {

		/**
		 * Find the related text of the link from the $this->autolink_a multidimensional array by using the match as
		 *  the index.
		 */
		$link_text = $this->autolink_a[ $m[1] ]['text'];

		// Get the left and right boundaries.
		$left_boundary  = $this->autolink_a[ $m[1] ]['left_boundary'];
		$right_boundary = $this->autolink_a[ $m[1] ]['right_boundary'];

		// Get the keyword_before and keyword_after.
		$keyword_before = $this->autolink_a[ $m[1] ]['keyword_before'];
		$keyword_after  = $this->autolink_a[ $m[1] ]['keyword_after'];

		// Get the autolink_id.
		$autolink_id = $this->autolink_a[ $m[1] ]['autolink_id'];

		// Generates the title attribute HTML if the "title" field is not empty.
		if ( mb_strlen( trim( $this->autolinks_ca[ $autolink_id ]['title'] ) ) > 0 ) {
			$title_attribute = 'title="' . esc_attr( stripslashes( $this->autolinks_ca[ $autolink_id ]['title'] ) ) . '"';
		} else {
			$title_attribute = '';
		}

		// Get the "open_new_tab" value.
		if ( 1 === intval( $this->autolinks_ca[ $autolink_id ]['open_new_tab'], 10 ) ) {
			$open_new_tab = 'target="_blank"';
		} else {
			$open_new_tab = 'target="_self"';
		}

		// Get the "use_nofollow" value.
		if ( 1 === intval( $this->autolinks_ca[ $autolink_id ]['use_nofollow'], 10 ) ) {
			$use_nofollow = 'rel="nofollow"';
		} else {
			$use_nofollow = '';
		}

		// Return the actual link.
		return $keyword_before . $left_boundary . '<a data-autolink-id="' . $autolink_id . '" ' . $open_new_tab . ' ' . $use_nofollow . ' href="' . esc_url( $this->autolinks_ca[ $autolink_id ]['url'] ) . '" ' . $title_attribute . '>' . $link_text . '</a>' . $right_boundary . $keyword_after;
	}

	/**
	 *  Callback of the preg_replace_callback() function.
	 *
	 *  This callback is used to avoid an anonymous function as a parameter of the preg_replace_callback() function for
	 *  PHP backward compatibility.
	 *
	 *  Look for uses of preg_replace_callback_3 to find which preg_replace_callback() function is actually using this
	 *  callback.
	 *
	 * @param array $m Todo.
	 *
	 * @return array|mixed|string|string[]|null
	 */
	public function preg_replace_callback_3( $m ) {
		/**
		 * The presence of nested protected blocks is verified. If a protected block is inside the content of a
		 *  protected block the remove_protected_block() method is applied recursively until there are no protected
		 *  blocks.
		 */
		$html           = $this->pb_a[ $m[1] ];
		$recursion_ends = false;

		do {

			/**
			 * If there are no protected blocks in content of the protected block end the recursion, otherwise apply
			 * remove_protected_block() again.
			 */
			if ( 0 === preg_match( '/\[pb\](\d+)\[\/pb\]/', $html ) ) {
				$recursion_ends = true;
			} else {
				$html = $this->remove_protected_blocks( $html );
			}
		} while ( false === $recursion_ends );

		return $html;
	}

	/**
	 * Returns true if there are exportable data or false if here are no exportable data.
	 *
	 * @return bool
	 */
	public function exportable_data_exists() {

		$exportable_data = false;
		global $wpdb;

		$total_items = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}daextam_autolink" );
		if ( $total_items > 0 ) {
			$exportable_data = true;
		}

		return $exportable_data;
	}

	/**
	 * Objects as a value are set to empty strings. This prevents to generate notices with the methods of the wpdb class.
	 *
	 * @param array $data An array which includes objects that should be converted to a empty strings.
	 *
	 * @return string An array where the objects have been replaced with empty strings.
	 */
	public function replace_objects_with_empty_strings( $data ) {

		foreach ( $data as $key => $value ) {
			if ( gettype( $value ) === 'object' ) {
				$data[ $key ] = '';
			}
		}

		return $data;
	}

	/**
	 * Returns the maximum number of autolinks allowed per post by using the method explained below.
	 *
	 *  If the "General Limit Mode" option is set to "Auto":
	 *
	 *  The maximum number of autolinks per post is calculated based on the content length of this post divided for the
	 *  value of the "General Limit (Characters per Autolink)" option.
	 *
	 *  If the "General Limit Mode" option is set to "Manual":
	 *
	 *  The maximum number of autolinks per post is equal to the value of "General Limit (Amount)".
	 *
	 * @param int $post_id The post ID for which the maximum number autolinks per post should be calculated.
	 *
	 * @return int The maximum number of autolinks allowed per post.
	 */
	private function get_max_number_autolinks_per_post( $post_id ) {

		if ( intval( get_option( $this->get( 'slug' ) . '_advanced_general_limit_mode' ), 10 ) === 0 ) {

			// Auto ---------------------------------------------------------------------------------------------------.
			$post_obj                = get_post( $post_id );
			$post_length             = mb_strlen( $post_obj->post_content );
			$characters_per_autolink = intval(
				get_option( $this->get( 'slug' ) . '_advanced_general_limit_characters_per_autolink' ),
				10
			);

			return intval( $post_length / $characters_per_autolink );

		} else {

			// Manual -------------------------------------------------------------------------------------------------.
			return intval( get_option( $this->get( 'slug' ) . '_advanced_general_limit_amount' ), 10 );

		}
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
	private function is_compliant_with_categories( $post_id, $autolink ) {

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
	private function is_compliant_with_tags( $post_id, $autolink ) {

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
	 * Verifies if the post includes at least one term included in the term group associated with the autolink.
	 *
	 *  In the following conditions True is returned:
	 *
	 *  - When a term group is not set
	 *  - When the post has at least one term present in the term group
	 *
	 * @param int   $post_id The ID of the post.
	 * @param array $autolink The array with the autolink data.
	 *
	 * @return bool
	 */
	private function is_compliant_with_term_group( $post_id, $autolink ) {

		$supported_terms = intval( get_option( $this->get( 'slug' ) . '_advanced_supported_terms' ), 10 );

		global $wpdb;
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

				// Verify post type, taxonomy and term as specified in the term group.
				if ( $post_type === $this->parsed_post_type && has_term( $term, $taxonomy, $post_id ) ) {
					return true;
				}
			}

			return false;

		}

		return true;
	}

	/**
	 * Remove the HTML comment ( comment enclosed between <!-- and --> )
	 *
	 * @param string $content The HTML with the comments.
	 *
	 * @return string The HTML without the comments
	 */
	public function remove_html_comments( $content ) {

		$content = preg_replace(
			'/
            <!--                                #1 Comment Start
            .*?                                 #2 Any character zero or more time with a lazy quantifier
            -->                                 #3 Comment End
            /ix',
			'',
			$content
		);

		return $content;
	}

	/**
	 * Remove the script tags
	 *
	 * @param string $content The HTML with the script tags.
	 *
	 * @return array|string|string[]|null The HTML without the script tags
	 */
	public function remove_script_tags( $content ) {

		$content = preg_replace(
			'/
            <                                   #1 Begin the start-tag
            script                              #2 The script tag name
            (\s+[^>]*)?                         #3 Match the rest of the start-tag
            >                                   #4 End the start-tag
            .*?                                 #5 The element content ( with the "s" modifier the dot matches also the new lines )
            <\/script\s*>                       #6 The script end-tag with optional white-spaces before the closing >
            /ixs',
			'',
			$content
		);

		return $content;
	}

	/**
	 * If the number of times that the parsed autolink ($this->parsed_autolink['url']) is present in the array that
	 *  includes the data of the autolinks already applied as temporary identifiers ($this->autolink_a) is equal or
	 *  higher than the limit estabilished with the "Same URL Limit" option ($this->same_url_limit) True is returned,
	 *  otherwise False is returned.
	 *
	 * @return Bool
	 */
	public function same_url_limit_reached() {

		$counter = 0;

		foreach ( $this->autolink_a as $key => $value ) {
			if ( $value['url'] === $this->parsed_autolink['url'] ) {
				++$counter;
			}
		}

		if ( $counter >= $this->same_url_limit ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Applies a random order (based on the hash of the post_id and autolink_id) to the autolinks that have the same
	 *  priority. This ensures a better distribution of the autolinks.
	 *
	 * @param array $autolinks The autolink.
	 * @param int   $post_id The post ID.
	 *
	 * @return Array
	 */
	public function apply_random_prioritization( $autolinks, $post_id ) {

		// Initialize variables.
		$autolinks_rp1 = array();
		$autolinks_rp2 = array();

		// Move the autolinks array in the new $autolinks_rp1 array, which uses the priority value as its index.
		foreach ( $autolinks as $key => $autolink ) {

			$autolinks_rp1[ $autolink['priority'] ][] = $autolink;

		}

		/*
		 * Apply a random order (based on the hash of the post_id and autolink_id) to the autolinks that have the same
		 * priority.
		 */
		foreach ( $autolinks_rp1 as $key => $autolinks_a ) {

			/**
			 * In each autolink create the new "hash" field which include a hash value based on the post_id and on the
			 * autolink_id.
			 */
			foreach ( $autolinks_a as $key2 => $autolink ) {

				/**
				 * Create the hased value. Note that the "-" character is used to avoid situations where the same input
				 *  is provided to the md5() function.
				 *
				 *  Without the "-" character for example with:
				 *
				 *  $post_id = 12 and $autolink['autolink_id'] = 34
				 *
				 *  We provide the same input of:
				 *
				 *  $post_id = 123 and $autolink['autolink_id'] = 4
				 *
				 *  etc.
				 */
				$hash = hexdec( md5( $post_id . '-' . $autolink['autolink_id'] ) );

				/*
				 * Convert all the non-digits to the character "1", this makes the comparison performed in the usort
				 * callback possible.
				 */
				$autolink['hash']     = preg_replace( '/\D/', '1', $hash, -1, $replacement_done );
				$autolinks_a[ $key2 ] = $autolink;

			}

			// Sort $autolinks_a based on the new value of the "hash" field.
			usort(
				$autolinks_a,
				function ( $a, $b ) {

					return $b['hash'] - $a['hash'];
				}
			);

			$autolinks_rp1[ $key ] = $autolinks_a;

		}

		/**
		 * Move the autolinks in the new $autolinks_rp2 array, which is structured like the original array, where the
		 * value of the priority field is stored in the autolink, and it's not used as the index of the array that
		 * includes all the autolinks with the same priority.
		 */
		foreach ( $autolinks_rp1 as $key => $autolinks_a ) {

			$autolinks_a_number = count( $autolinks_a );
			for ( $t = 0; $t < $autolinks_a_number; $t++ ) {

				$autolink        = $autolinks_a[ $t ];
				$autolinks_rp2[] = $autolink;

			}
		}

		return $autolinks_rp2;
	}

	/**
	 * To avoid additional database requests for each autolink in preg_replace_callback_2() save the data of the
	 *  autolink in an array that uses the "autolink_id" as its index.
	 *
	 * @param array $autolinks An array with the autolinks data.
	 *
	 * @return Array
	 */
	public function save_autolinks_in_custom_array( $autolinks ) {

		$autolinks_ca = array();

		foreach ( $autolinks as $key => $autolink ) {

			$autolinks_ca[ $autolink['autolink_id'] ] = $autolink;

		}

		return $autolinks_ca;
	}

	/**
	 * Given the Autolink ID the Autolink Object is returned.
	 *
	 * @param int $autolink_id The ID of the autolink.
	 *
	 * @return Object
	 */
	public function get_autolink_object( $autolink_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$autolink_obj = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}daextam_autolink WHERE autolink_id = %d ", $autolink_id )
		);

		return $autolink_obj;
	}

	/**
	 * Adds a hidden input used to store the post id at the end of the content.
	 *
	 * @param string $content The content.
	 * @return string The $content with added the hidden input
	 */
	public function add_hidden_input( $content ) {

		if ( ! is_singular() || is_attachment() || is_feed() ) {
			return $content;
		}

		$hidden_input = '<input id="daextam-post-id" type="hidden" value="' . $this->post_id . '">';

		return $content . $hidden_input;
	}

	/**
	 * Get an array with the post types with UI except the attachment post type.
	 *
	 * @return Array
	 */
	public function get_post_types_with_ui() {

		// Get all the post types with UI.
		$args               = array(
			'public'  => true,
			'show_ui' => true,
		);
		$post_types_with_ui = get_post_types( $args );

		// Remove the attachment post type.
		unset( $post_types_with_ui['attachment'] );

		// Replace the associative index with a numeric index.
		$temp_array = array();
		foreach ( $post_types_with_ui as $key => $value ) {
			$temp_array[] = $value;
		}
		$post_types_with_ui = $temp_array;

		return $post_types_with_ui;
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
	 * Returns true if one or more autolinks are using the specified category.
	 *
	 * @param int $category_id The category ID.
	 * @return bool
	 */
	public function category_is_used( $category_id ) {

		global $wpdb;

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
	 * Returns true if the term group with the specified $term_group_id exists.
	 *
	 * @param int $term_group_id The term group ID.
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
	 * Returns true if one or more autolinks are using the specified term group.
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
	 * Generates the XML version of the data of the table.
	 *
	 * @param string $db_table_name The name of the db table without the prefix.
	 * @param string $db_table_primary_key The name of the primary key of the table.
	 */
	public function convert_db_table_to_xml( $db_table_name, $db_table_primary_key ) {

		// Get the data from the db table.
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- $db_table_name is sanitized.
		$data_a = $wpdb->get_results(
			'SELECT * FROM ' . $wpdb->prefix . 'daextam_' . sanitize_key( $db_table_name ) . ' ORDER BY ' . sanitize_key( $db_table_primary_key ) . ' ASC',
			ARRAY_A
		);
		// phpcs:enable

		// Generate the data of the db table.
		foreach ( $data_a as $record ) {

			echo '<' . esc_attr( $db_table_name ) . '>';

			// Get all the indexes of the $data array.
			$record_keys = array_keys( $record );

			// Cycle through all the indexes of the single record and create all the XML tags.
			foreach ( $record_keys as $key ) {
				echo '<' . esc_attr( $key ) . '>' . esc_attr( $record[ $key ] ) . '</' . esc_attr( $key ) . '>';
			}

			echo '</' . esc_attr( $db_table_name ) . '>';

		}
	}

	/**
	 * Fires after a term is deleted from the database and the cache is cleaned.
	 *
	 *  The following tasks are performed:
	 *
	 *  Part 1 - Deletes the $term_id found in the categories field of the autolinks
	 *  Part 2 - Deletes the $term_id found in the tags field of the autolinks
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

				// Delete the term in the categories field of the autolinks.
				$category_term_a = maybe_unserialize( $autolink['categories'] );
				if ( is_array( $category_term_a ) && count( $category_term_a ) > 0 ) {
					foreach ( $category_term_a as $key2 => $category_term ) {
						if ( intval( $category_term, 10 ) === $term_id ) {
							unset( $category_term_a[ $key2 ] );
						}
					}
				}
				$category_term_a_serialized = maybe_serialize( $category_term_a );

				// Delete the term in the tags field of the autolinks.
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
				 * autolinks and this term group. If there are terms in the term group update the term group.
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

					// If the term group is used reset the association between the autolinks and this term group.
					if ( $this->term_group_is_used( $term_group['term_group_id'] ) ) {

						// Reset the association between the autolinks and this term group.
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
						$query_part .= $wpdb->prepare( 'post_type_' . intval( $i, 10 ) . ' = %s,', $term_group[ 'post_type_' . $i ] );
						$query_part .= $wpdb->prepare( 'taxonomy_' . intval( $i, 10 ) . ' = %s,', $term_group[ 'taxonomy_' . $i ] );
						$query_part .= $wpdb->prepare( 'term_' . intval( $i, 10 ) . ' = %s', $term_group[ 'term_' . $i ] );
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

	/**
	 * If $needle is present in the $haystack array echos 'selected="selected"'.
	 *
	 * @param array  $haystack The haystack.
	 * @param string $needle The needle.
	 *
	 * @return string|void
	 */
	public function selected_array( $haystack, $needle ) {

		if ( is_array( $haystack ) && in_array( $needle, $haystack, true ) ) {
			return 'selected="selected"';
		}
	}

	/**
	 * Set the PHP "Max Execution Time" and "Memory Limit" based on the values defined in the options.
	 */
	public function set_met_and_ml() {

		/**
		 * Set the custom "Max Execution Time Value" defined in the options if the 'Set Max Execution Time' option is
		 * set to "Yes".
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_analysis_set_max_execution_time' ), 10 ) === 1 ) {
			ini_set(
				'max_execution_time',
				intval( get_option( $this->get( 'slug' ) . '_analysis_max_execution_time_value' ), 10 )
			);
		}

		/*
		 * Set the custom "Memory Limit Value" (in megabytes) defined in the options if the 'Set Memory Limit' option is
		 * set to "Yes".
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_analysis_set_memory_limit' ), 10 ) === 1 ) {
			ini_set( 'memory_limit', intval( get_option( $this->get( 'slug' ) . '_analysis_memory_limit_value' ), 10 ) . 'M' );
		}
	}

	/**
	 * Utility function used to detect if a post exist.
	 *
	 * @param int $id The post id.
	 *
	 * @return bool True if the post exists, False if the post doesn't exist.
	 */
	public function post_exists( $id ) {
		return is_string( get_post_status( $id ) );
	}

	/**
	 * Returns true if the autolink with the specified id exists.
	 *
	 * @param int $id The category ID.
	 * @return bool
	 */
	public function autolink_exists( $id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_items = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}daextam_autolink WHERE autolink_id = %d", $id )
		);

		if ( $total_items > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns an array with the data used by React to initialize the options.
	 *
	 * @return array[]
	 */
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
									'This option determines the default status of the "Enable Autolinks" option available in the "Autolinks Manager" meta box.',
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
								'label'         => __( 'Term Group', 'daext-autolinks-manager' ),
								'type'          => 'select',
								'tooltip'       => __(
									'The terms that will be compared with the ones available on the posts where the autolinks are applied. Please note that when a term group is selected the "Categories" and "Tags" options will be ignored. This option determines the default value of the "Term Group" field available in the "Autolinks" menu.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $term_group_select_options,
								'help'          => __( 'Select the term group where the automatic links should be added.', 'daext-autolinks-manager' ),
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
				'title'       => __( 'Link Analysis', 'daext-autolinks-manager' ),
				'description' => __( 'Configure options and parameters used for the link analysis.', 'daext-autolinks-manager' ),
				'cards'       => array(
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
								'label'         => __( 'Post Types', 'daext-autolinks-manager' ),
								'type'          => 'select-multiple',
								'tooltip'       => __(
									'With this option you are able to determine the post types analyzed in the Dashboard menu. Leave this field empty to perform the analysis in any post type.',
									'daext-autolinks-manager'
								),
								'selectOptions' => $post_types_select_options,
								'help'          => __( 'Select the post types analyzed in the Dashboard menu.', 'daext-autolinks-manager' ),
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
						),
					),
				),
			),
			array(
				'title'       => __( 'Advanced', 'daext-autolinks-manager' ),
				'description' => __( 'Manage advanced plugin settings.', 'daext-autolinks-manager' ),
				'cards'       => array(
					array(
						'title'   => __( 'Misc', 'daext-autolinks-manager' ),
						'options' => array(
							array(
								'name'      => 'daextam_advanced_supported_terms',
								'label'     => __( 'Supported Terms', 'daext-autolinks-manager' ),
								'type'      => 'range',
								'tooltip'   => __(
									'This option determines the maximum number of terms supported in a single term group.',
									'daext-autolinks-manager'
								),
								'help'      => __( 'Set the maximum number of terms supported in a single term group.', 'daext-autolinks-manager' ),
								'rangeMin'  => 1,
								'rangeMax'  => 50,
								'rangeStep' => 1,
							),
						),
					),
				),
			),
		);

		return $configuration;
	}

	/**
	 * Echo the SVG icon specified by the $icon_name parameter.
	 *
	 * @param string $icon_name The name of the icon to echo.
	 *
	 * @return void
	 */
	public function echo_icon_svg( $icon_name ) {

		switch ( $icon_name ) {

			case 'link-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
	                    <path d="M9.99999 13C10.4294 13.5741 10.9773 14.0491 11.6065 14.3929C12.2357 14.7367 12.9315 14.9411 13.6466 14.9923C14.3618 15.0435 15.0796 14.9403 15.7513 14.6897C16.4231 14.4392 17.0331 14.047 17.54 13.54L20.54 10.54C21.4508 9.59695 21.9547 8.33394 21.9434 7.02296C21.932 5.71198 21.4061 4.45791 20.4791 3.53087C19.552 2.60383 18.298 2.07799 16.987 2.0666C15.676 2.0552 14.413 2.55918 13.47 3.46997L11.75 5.17997M14 11C13.5705 10.4258 13.0226 9.95078 12.3934 9.60703C11.7642 9.26327 11.0685 9.05885 10.3533 9.00763C9.63819 8.95641 8.9204 9.0596 8.24864 9.31018C7.57688 9.56077 6.96687 9.9529 6.45999 10.46L3.45999 13.46C2.5492 14.403 2.04522 15.666 2.05662 16.977C2.06801 18.288 2.59385 19.542 3.52089 20.4691C4.44793 21.3961 5.702 21.9219 7.01298 21.9333C8.32396 21.9447 9.58697 21.4408 10.53 20.53L12.24 18.82" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	                    </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'bar-chart-07':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 21H6.2C5.07989 21 4.51984 21 4.09202 20.782C3.71569 20.5903 3.40973 20.2843 3.21799 19.908C3 19.4802 3 18.9201 3 17.8V3M7 10.5V17.5M11.5 5.5V17.5M16 10.5V17.5M20.5 5.5V17.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'intersect-square':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 5.2C2 4.07989 2 3.51984 2.21799 3.09202C2.40973 2.71569 2.71569 2.40973 3.09202 2.21799C3.51984 2 4.0799 2 5.2 2H12.8C13.9201 2 14.4802 2 14.908 2.21799C15.2843 2.40973 15.5903 2.71569 15.782 3.09202C16 3.51984 16 4.0799 16 5.2V12.8C16 13.9201 16 14.4802 15.782 14.908C15.5903 15.2843 15.2843 15.5903 14.908 15.782C14.4802 16 13.9201 16 12.8 16H5.2C4.07989 16 3.51984 16 3.09202 15.782C2.71569 15.5903 2.40973 15.2843 2.21799 14.908C2 14.4802 2 13.9201 2 12.8V5.2Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 11.2C8 10.0799 8 9.51984 8.21799 9.09202C8.40973 8.71569 8.71569 8.40973 9.09202 8.21799C9.51984 8 10.0799 8 11.2 8H18.8C19.9201 8 20.4802 8 20.908 8.21799C21.2843 8.40973 21.5903 8.71569 21.782 9.09202C22 9.51984 22 10.0799 22 11.2V18.8C22 19.9201 22 20.4802 21.782 20.908C21.5903 21.2843 21.2843 21.5903 20.908 21.782C20.4802 22 19.9201 22 18.8 22H11.2C10.0799 22 9.51984 22 9.09202 21.782C8.71569 21.5903 8.40973 21.2843 8.21799 20.908C8 20.4802 8 19.9201 8 18.8V11.2Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'share-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 6H17.8C16.1198 6 15.2798 6 14.638 6.32698C14.0735 6.6146 13.6146 7.07354 13.327 7.63803C13 8.27976 13 9.11984 13 10.8V12M21 6L18 3M21 6L18 9M10 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V14" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-circle-broken':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'cursor-click-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 3.5V2M5.06066 5.06066L4 4M5.06066 13L4 14.0607M13 5.06066L14.0607 4M3.5 9H2M8.5 8.5L12.6111 21.2778L15.5 18.3889L19.1111 22L22 19.1111L18.3889 15.5L21.2778 12.6111L8.5 8.5Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'list':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M21 12L9 12M21 6L9 6M21 18L9 18M5 12C5 12.5523 4.55228 13 4 13C3.44772 13 3 12.5523 3 12C3 11.4477 3.44772 11 4 11C4.55228 11 5 11.4477 5 12ZM5 6C5 6.55228 4.55228 7 4 7C3.44772 7 3 6.55228 3 6C3 5.44772 3.44772 5 4 5C4.55228 5 5 5.44772 5 6ZM5 18C5 18.5523 4.55228 19 4 19C3.44772 19 3 18.5523 3 18C3 17.4477 3.44772 17 4 17C4.55228 17 5 17.4477 5 18Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'database-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M21 5C21 6.65685 16.9706 8 12 8C7.02944 8 3 6.65685 3 5M21 5C21 3.34315 16.9706 2 12 2C7.02944 2 3 3.34315 3 5M21 5V19C21 20.66 17 22 12 22C7 22 3 20.66 3 19V5M21 9.72021C21 11.3802 17 12.7202 12 12.7202C7 12.7202 3 11.3802 3 9.72021M21 14.44C21 16.1 17 17.44 12 17.44C7 17.44 3 16.1 3 14.44" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'tool-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 6L10.5 10.5M6 6H3L2 3L3 2L6 3V6ZM19.259 2.74101L16.6314 5.36863C16.2354 5.76465 16.0373 5.96265 15.9632 6.19098C15.8979 6.39183 15.8979 6.60817 15.9632 6.80902C16.0373 7.03735 16.2354 7.23535 16.6314 7.63137L16.8686 7.86863C17.2646 8.26465 17.4627 8.46265 17.691 8.53684C17.8918 8.6021 18.1082 8.6021 18.309 8.53684C18.5373 8.46265 18.7354 8.26465 19.1314 7.86863L21.5893 5.41072C21.854 6.05488 22 6.76039 22 7.5C22 10.5376 19.5376 13 16.5 13C16.1338 13 15.7759 12.9642 15.4298 12.8959C14.9436 12.8001 14.7005 12.7521 14.5532 12.7668C14.3965 12.7824 14.3193 12.8059 14.1805 12.8802C14.0499 12.9501 13.919 13.081 13.657 13.343L6.5 20.5C5.67157 21.3284 4.32843 21.3284 3.5 20.5C2.67157 19.6716 2.67157 18.3284 3.5 17.5L10.657 10.343C10.919 10.081 11.0499 9.95005 11.1198 9.81949C11.1941 9.68068 11.2176 9.60347 11.2332 9.44681C11.2479 9.29945 11.1999 9.05638 11.1041 8.57024C11.0358 8.22406 11 7.86621 11 7.5C11 4.46243 13.4624 2 16.5 2C17.5055 2 18.448 2.26982 19.259 2.74101ZM12.0001 14.9999L17.5 20.4999C18.3284 21.3283 19.6716 21.3283 20.5 20.4999C21.3284 19.6715 21.3284 18.3283 20.5 17.4999L15.9753 12.9753C15.655 12.945 15.3427 12.8872 15.0408 12.8043C14.6517 12.6975 14.2249 12.7751 13.9397 13.0603L12.0001 14.9999Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'refresh-ccw-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 18.8746C19.1213 17.329 20.5 14.8255 20.5 12C20.5 7.30555 16.6944 3.49998 12 3.49998H11.5M12 20.5C7.30558 20.5 3.5 16.6944 3.5 12C3.5 9.17444 4.87867 6.67091 7 5.12537M11 22.4L13 20.4L11 18.4M13 5.59998L11 3.59998L13 1.59998" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'file-code-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5 18.5C5 18.9644 5 19.1966 5.02567 19.3916C5.2029 20.7378 6.26222 21.7971 7.60842 21.9743C7.80337 22 8.03558 22 8.5 22H16.2C17.8802 22 18.7202 22 19.362 21.673C19.9265 21.3854 20.3854 20.9265 20.673 20.362C21 19.7202 21 18.8802 21 17.2V9.98822C21 9.25445 21 8.88757 20.9171 8.5423C20.8436 8.2362 20.7224 7.94356 20.5579 7.67515C20.3724 7.3724 20.113 7.11296 19.5941 6.59411L16.4059 3.40589C15.887 2.88703 15.6276 2.6276 15.3249 2.44208C15.0564 2.27759 14.7638 2.15638 14.4577 2.08289C14.1124 2 13.7455 2 13.0118 2H8.5C8.03558 2 7.80337 2 7.60842 2.02567C6.26222 2.2029 5.2029 3.26222 5.02567 4.60842C5 4.80337 5 5.03558 5 5.5M9 14.5L11.5 12L9 9.5M5 9.5L2.5 12L5 14.5" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'upload-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M16 12L12 8M12 8L8 12M12 8V17.2C12 18.5907 12 19.2861 12.5505 20.0646C12.9163 20.5819 13.9694 21.2203 14.5972 21.3054C15.5421 21.4334 15.9009 21.2462 16.6186 20.8719C19.8167 19.2036 22 15.8568 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 15.7014 4.01099 18.9331 7 20.6622" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'dataflow-02':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 4V15.2C12 16.8802 12 17.7202 12.327 18.362C12.6146 18.9265 13.0735 19.3854 13.638 19.673C14.2798 20 15.1198 20 16.8 20H17M17 20C17 21.1046 17.8954 22 19 22C20.1046 22 21 21.1046 21 20C21 18.8954 20.1046 18 19 18C17.8954 18 17 18.8954 17 20ZM7 4L17 4M7 4C7 5.10457 6.10457 6 5 6C3.89543 6 3 5.10457 3 4C3 2.89543 3.89543 2 5 2C6.10457 2 7 2.89543 7 4ZM17 4C17 5.10457 17.8954 6 19 6C20.1046 6 21 5.10457 21 4C21 2.89543 20.1046 2 19 2C17.8954 2 17 2.89543 17 4ZM12 12H17M17 12C17 13.1046 17.8954 14 19 14C20.1046 14 21 13.1046 21 12C21 10.8954 20.1046 10 19 10C17.8954 10 17 10.8954 17 12Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'zap-fast':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 17.5H3.5M6.5 12H2M9 6.5H4M17 3L10.4036 12.235C10.1116 12.6438 9.96562 12.8481 9.97194 13.0185C9.97744 13.1669 10.0486 13.3051 10.1661 13.3958C10.3011 13.5 10.5522 13.5 11.0546 13.5H16L15 21L21.5964 11.765C21.8884 11.3562 22.0344 11.1519 22.0281 10.9815C22.0226 10.8331 21.9514 10.6949 21.8339 10.6042C21.6989 10.5 21.4478 10.5 20.9454 10.5H16L17 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'server-05':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M19 9C19 12.866 15.866 16 12 16M19 9C19 5.13401 15.866 2 12 2M19 9H5M12 16C8.13401 16 5 12.866 5 9M12 16C13.7509 14.0832 14.7468 11.5956 14.8009 9C14.7468 6.40442 13.7509 3.91685 12 2M12 16C10.2491 14.0832 9.25498 11.5956 9.20091 9C9.25498 6.40442 10.2491 3.91685 12 2M12 16V18M5 9C5 5.13401 8.13401 2 12 2M14 20C14 21.1046 13.1046 22 12 22C10.8954 22 10 21.1046 10 20M14 20C14 18.8954 13.1046 18 12 18M14 20H21M10 20C10 18.8954 10.8954 18 12 18M10 20H3" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'settings-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M18.7273 14.7273C18.6063 15.0015 18.5702 15.3056 18.6236 15.6005C18.6771 15.8954 18.8177 16.1676 19.0273 16.3818L19.0818 16.4364C19.2509 16.6052 19.385 16.8057 19.4765 17.0265C19.568 17.2472 19.6151 17.4838 19.6151 17.7227C19.6151 17.9617 19.568 18.1983 19.4765 18.419C19.385 18.6397 19.2509 18.8402 19.0818 19.0091C18.913 19.1781 18.7124 19.3122 18.4917 19.4037C18.271 19.4952 18.0344 19.5423 17.7955 19.5423C17.5565 19.5423 17.3199 19.4952 17.0992 19.4037C16.8785 19.3122 16.678 19.1781 16.5091 19.0091L16.4545 18.9545C16.2403 18.745 15.9682 18.6044 15.6733 18.5509C15.3784 18.4974 15.0742 18.5335 14.8 18.6545C14.5311 18.7698 14.3018 18.9611 14.1403 19.205C13.9788 19.4489 13.8921 19.7347 13.8909 20.0273V20.1818C13.8909 20.664 13.6994 21.1265 13.3584 21.4675C13.0174 21.8084 12.5549 22 12.0727 22C11.5905 22 11.1281 21.8084 10.7871 21.4675C10.4461 21.1265 10.2545 20.664 10.2545 20.1818V20.1C10.2475 19.7991 10.1501 19.5073 9.97501 19.2625C9.79991 19.0176 9.55521 18.8312 9.27273 18.7273C8.99853 18.6063 8.69437 18.5702 8.39947 18.6236C8.10456 18.6771 7.83244 18.8177 7.61818 19.0273L7.56364 19.0818C7.39478 19.2509 7.19425 19.385 6.97353 19.4765C6.7528 19.568 6.51621 19.6151 6.27727 19.6151C6.03834 19.6151 5.80174 19.568 5.58102 19.4765C5.36029 19.385 5.15977 19.2509 4.99091 19.0818C4.82186 18.913 4.68775 18.7124 4.59626 18.4917C4.50476 18.271 4.45766 18.0344 4.45766 17.7955C4.45766 17.5565 4.50476 17.3199 4.59626 17.0992C4.68775 16.8785 4.82186 16.678 4.99091 16.5091L5.04545 16.4545C5.25503 16.2403 5.39562 15.9682 5.4491 15.6733C5.50257 15.3784 5.46647 15.0742 5.34545 14.8C5.23022 14.5311 5.03887 14.3018 4.79497 14.1403C4.55107 13.9788 4.26526 13.8921 3.97273 13.8909H3.81818C3.33597 13.8909 2.87351 13.6994 2.53253 13.3584C2.19156 13.0174 2 12.5549 2 12.0727C2 11.5905 2.19156 11.1281 2.53253 10.7871C2.87351 10.4461 3.33597 10.2545 3.81818 10.2545H3.9C4.2009 10.2475 4.49273 10.1501 4.73754 9.97501C4.98236 9.79991 5.16883 9.55521 5.27273 9.27273C5.39374 8.99853 5.42984 8.69437 5.37637 8.39947C5.3229 8.10456 5.18231 7.83244 4.97273 7.61818L4.91818 7.56364C4.74913 7.39478 4.61503 7.19425 4.52353 6.97353C4.43203 6.7528 4.38493 6.51621 4.38493 6.27727C4.38493 6.03834 4.43203 5.80174 4.52353 5.58102C4.61503 5.36029 4.74913 5.15977 4.91818 4.99091C5.08704 4.82186 5.28757 4.68775 5.50829 4.59626C5.72901 4.50476 5.96561 4.45766 6.20455 4.45766C6.44348 4.45766 6.68008 4.50476 6.9008 4.59626C7.12152 4.68775 7.32205 4.82186 7.49091 4.99091L7.54545 5.04545C7.75971 5.25503 8.03183 5.39562 8.32674 5.4491C8.62164 5.50257 8.9258 5.46647 9.2 5.34545H9.27273C9.54161 5.23022 9.77093 5.03887 9.93245 4.79497C10.094 4.55107 10.1807 4.26526 10.1818 3.97273V3.81818C10.1818 3.33597 10.3734 2.87351 10.7144 2.53253C11.0553 2.19156 11.5178 2 12 2C12.4822 2 12.9447 2.19156 13.2856 2.53253C13.6266 2.87351 13.8182 3.33597 13.8182 3.81818V3.9C13.8193 4.19253 13.906 4.47834 14.0676 4.72224C14.2291 4.96614 14.4584 5.15749 14.7273 5.27273C15.0015 5.39374 15.3056 5.42984 15.6005 5.37637C15.8954 5.3229 16.1676 5.18231 16.3818 4.97273L16.4364 4.91818C16.6052 4.74913 16.8057 4.61503 17.0265 4.52353C17.2472 4.43203 17.4838 4.38493 17.7227 4.38493C17.9617 4.38493 18.1983 4.43203 18.419 4.52353C18.6397 4.61503 18.8402 4.74913 19.0091 4.91818C19.1781 5.08704 19.3122 5.28757 19.4037 5.50829C19.4952 5.72901 19.5423 5.96561 19.5423 6.20455C19.5423 6.44348 19.4952 6.68008 19.4037 6.9008C19.3122 7.12152 19.1781 7.32205 19.0091 7.49091L18.9545 7.54545C18.745 7.75971 18.6044 8.03183 18.5509 8.32674C18.4974 8.62164 18.5335 8.9258 18.6545 9.2V9.27273C18.7698 9.54161 18.9611 9.77093 19.205 9.93245C19.4489 10.094 19.7347 10.1807 20.0273 10.1818H20.1818C20.664 10.1818 21.1265 10.3734 21.4675 10.7144C21.8084 11.0553 22 11.5178 22 12C22 12.4822 21.8084 12.9447 21.4675 13.2856C21.1265 13.6266 20.664 13.8182 20.1818 13.8182H20.1C19.8075 13.8193 19.5217 13.906 19.2778 14.0676C19.0339 14.2291 18.8425 14.4584 18.7273 14.7273Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'grid-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M8.4 3H4.6C4.03995 3 3.75992 3 3.54601 3.10899C3.35785 3.20487 3.20487 3.35785 3.10899 3.54601C3 3.75992 3 4.03995 3 4.6V8.4C3 8.96005 3 9.24008 3.10899 9.45399C3.20487 9.64215 3.35785 9.79513 3.54601 9.89101C3.75992 10 4.03995 10 4.6 10H8.4C8.96005 10 9.24008 10 9.45399 9.89101C9.64215 9.79513 9.79513 9.64215 9.89101 9.45399C10 9.24008 10 8.96005 10 8.4V4.6C10 4.03995 10 3.75992 9.89101 3.54601C9.79513 3.35785 9.64215 3.20487 9.45399 3.10899C9.24008 3 8.96005 3 8.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 3H15.6C15.0399 3 14.7599 3 14.546 3.10899C14.3578 3.20487 14.2049 3.35785 14.109 3.54601C14 3.75992 14 4.03995 14 4.6V8.4C14 8.96005 14 9.24008 14.109 9.45399C14.2049 9.64215 14.3578 9.79513 14.546 9.89101C14.7599 10 15.0399 10 15.6 10H19.4C19.9601 10 20.2401 10 20.454 9.89101C20.6422 9.79513 20.7951 9.64215 20.891 9.45399C21 9.24008 21 8.96005 21 8.4V4.6C21 4.03995 21 3.75992 20.891 3.54601C20.7951 3.35785 20.6422 3.20487 20.454 3.10899C20.2401 3 19.9601 3 19.4 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M19.4 14H15.6C15.0399 14 14.7599 14 14.546 14.109C14.3578 14.2049 14.2049 14.3578 14.109 14.546C14 14.7599 14 15.0399 14 15.6V19.4C14 19.9601 14 20.2401 14.109 20.454C14.2049 20.6422 14.3578 20.7951 14.546 20.891C14.7599 21 15.0399 21 15.6 21H19.4C19.9601 21 20.2401 21 20.454 20.891C20.6422 20.7951 20.7951 20.6422 20.891 20.454C21 20.2401 21 19.9601 21 19.4V15.6C21 15.0399 21 14.7599 20.891 14.546C20.7951 14.3578 20.6422 14.2049 20.454 14.109C20.2401 14 19.9601 14 19.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M8.4 14H4.6C4.03995 14 3.75992 14 3.54601 14.109C3.35785 14.2049 3.20487 14.3578 3.10899 14.546C3 14.7599 3 15.0399 3 15.6V19.4C3 19.9601 3 20.2401 3.10899 20.454C3.20487 20.6422 3.35785 20.7951 3.54601 20.891C3.75992 21 4.03995 21 4.6 21H8.4C8.96005 21 9.24008 21 9.45399 20.891C9.64215 20.7951 9.79513 20.6422 9.89101 20.454C10 20.2401 10 19.9601 10 19.4V15.6C10 15.0399 10 14.7599 9.89101 14.546C9.79513 14.3578 9.64215 14.2049 9.45399 14.109C9.24008 14 8.96005 14 8.4 14Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-up':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 15L12 9L6 15" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-down':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 9L12 15L18 9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M15 18L9 12L15 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-left-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 17L13 12L18 7M11 17L6 12L11 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 18L15 12L9 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'chevron-right-double':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6 17L11 12L6 7M13 17L18 12L13 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'arrow-up-right':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7 17L17 7M17 7H7M17 7V17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'plus':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 5V19M5 12H19" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-circle-broken':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572M22 4L12 14.01L9 11.01" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-in-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 8L16 12M16 12L12 16M16 12H3M3.33782 7C5.06687 4.01099 8.29859 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C8.29859 22 5.06687 19.989 3.33782 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'log-out-04':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 8L22 12M22 12L18 16M22 12H9M15 4.20404C13.7252 3.43827 12.2452 3 10.6667 3C5.8802 3 2 7.02944 2 12C2 16.9706 5.8802 21 10.6667 21C12.2452 21 13.7252 20.5617 15 19.796" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-asc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H9M3 18H21" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'order-desc':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M3 12H15M3 6H21M3 18H9" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'clipboard-icon-svg':
				$xml = '<?xml version="1.0" encoding="utf-8"?>
				<svg version="1.1" id="Layer_3" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
					 viewBox="0 0 20 20" style="enable-background:new 0 0 20 20;" xml:space="preserve">
				<path d="M14,18H8c-1.1,0-2-0.9-2-2V7c0-1.1,0.9-2,2-2h6c1.1,0,2,0.9,2,2v9C16,17.1,15.1,18,14,18z M8,7v9h6V7H8z"/>
				<path d="M5,4h6V2H5C3.9,2,3,2.9,3,4v9h2V4z"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'x':
				$xml = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 7L7 17M7 7L17 17" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'version' => array(),
						'id'      => array(),
						'xmlns'   => array(),
						'x'       => array(),
						'y'       => array(),
						'viewbox' => array(),
						'style'   => array(),
					),
					'path' => array(
						'd' => array(),
					),
				);

				break;

			case 'diamond-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M2.49954 9H21.4995M9.99954 3L7.99954 9L11.9995 20.5L15.9995 9L13.9995 3M12.6141 20.2625L21.5727 9.51215C21.7246 9.32995 21.8005 9.23885 21.8295 9.13717C21.8551 9.04751 21.8551 8.95249 21.8295 8.86283C21.8005 8.76114 21.7246 8.67005 21.5727 8.48785L17.2394 3.28785C17.1512 3.18204 17.1072 3.12914 17.0531 3.09111C17.0052 3.05741 16.9518 3.03238 16.8953 3.01717C16.8314 3 16.7626 3 16.6248 3H7.37424C7.2365 3 7.16764 3 7.10382 3.01717C7.04728 3.03238 6.99385 3.05741 6.94596 3.09111C6.89192 3.12914 6.84783 3.18204 6.75966 3.28785L2.42633 8.48785C2.2745 8.67004 2.19858 8.76114 2.16957 8.86283C2.144 8.95249 2.144 9.04751 2.16957 9.13716C2.19858 9.23885 2.2745 9.32995 2.42633 9.51215L11.385 20.2625C11.596 20.5158 11.7015 20.6424 11.8279 20.6886C11.9387 20.7291 12.0603 20.7291 12.1712 20.6886C12.2975 20.6424 12.4031 20.5158 12.6141 20.2625Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'check-verified-01':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M9 12L11 14L15.5 9.5M7.33377 3.8187C8.1376 3.75455 8.90071 3.43846 9.51447 2.91542C10.9467 1.69486 13.0533 1.69486 14.4855 2.91542C15.0993 3.43846 15.8624 3.75455 16.6662 3.8187C18.5421 3.96839 20.0316 5.45794 20.1813 7.33377C20.2455 8.1376 20.5615 8.90071 21.0846 9.51447C22.3051 10.9467 22.3051 13.0533 21.0846 14.4855C20.5615 15.0993 20.2455 15.8624 20.1813 16.6662C20.0316 18.5421 18.5421 20.0316 16.6662 20.1813C15.8624 20.2455 15.0993 20.5615 14.4855 21.0846C13.0533 22.3051 10.9467 22.3051 9.51447 21.0846C8.90071 20.5615 8.1376 20.2455 7.33377 20.1813C5.45794 20.0316 3.96839 18.5421 3.8187 16.6662C3.75455 15.8624 3.43846 15.0993 2.91542 14.4855C1.69486 13.0533 1.69486 10.9467 2.91542 9.51447C3.43846 8.90071 3.75455 8.1376 3.8187 7.33377C3.96839 5.45794 5.45794 3.96839 7.33377 3.8187Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'line-chart-up-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17 9L11.5657 14.4343C11.3677 14.6323 11.2687 14.7313 11.1545 14.7684C11.0541 14.8011 10.9459 14.8011 10.8455 14.7684C10.7313 14.7313 10.6323 14.6323 10.4343 14.4343L8.56569 12.5657C8.36768 12.3677 8.26867 12.2687 8.15451 12.2316C8.05409 12.1989 7.94591 12.1989 7.84549 12.2316C7.73133 12.2687 7.63232 12.3677 7.43431 12.5657L3 17M17 9H13M17 9V13M7.8 21H16.2C17.8802 21 18.7202 21 19.362 20.673C19.9265 20.3854 20.3854 19.9265 20.673 19.362C21 18.7202 21 17.8802 21 16.2V7.8C21 6.11984 21 5.27976 20.673 4.63803C20.3854 4.07354 19.9265 3.6146 19.362 3.32698C18.7202 3 17.8802 3 16.2 3H7.8C6.11984 3 5.27976 3 4.63803 3.32698C4.07354 3.6146 3.6146 4.07354 3.32698 4.63803C3 5.27976 3 6.11984 3 7.8V16.2C3 17.8802 3 18.7202 3.32698 19.362C3.6146 19.9265 4.07354 20.3854 4.63803 20.673C5.27976 21 6.11984 21 7.8 21Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'code-browser':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M22 9H2M14 17.5L16.5 15L14 12.5M10 12.5L7.5 15L10 17.5M2 7.8L2 16.2C2 17.8802 2 18.7202 2.32698 19.362C2.6146 19.9265 3.07354 20.3854 3.63803 20.673C4.27976 21 5.11984 21 6.8 21H17.2C18.8802 21 19.7202 21 20.362 20.673C20.9265 20.3854 21.3854 19.9265 21.673 19.362C22 18.7202 22 17.8802 22 16.2V7.8C22 6.11984 22 5.27977 21.673 4.63803C21.3854 4.07354 20.9265 3.6146 20.362 3.32698C19.7202 3 18.8802 3 17.2 3L6.8 3C5.11984 3 4.27976 3 3.63803 3.32698C3.07354 3.6146 2.6146 4.07354 2.32698 4.63803C2 5.27976 2 6.11984 2 7.8Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			case 'layout-alt-03':
				$xml = '<svg class="untitled-ui-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M17.5 17H6.5M17.5 13H6.5M3 9H21M7.8 3H16.2C17.8802 3 18.7202 3 19.362 3.32698C19.9265 3.6146 20.3854 4.07354 20.673 4.63803C21 5.27976 21 6.11984 21 7.8V16.2C21 17.8802 21 18.7202 20.673 19.362C20.3854 19.9265 19.9265 20.3854 19.362 20.673C18.7202 21 17.8802 21 16.2 21H7.8C6.11984 21 5.27976 21 4.63803 20.673C4.07354 20.3854 3.6146 19.9265 3.32698 19.362C3 18.7202 3 17.8802 3 16.2V7.8C3 6.11984 3 5.27976 3.32698 4.63803C3.6146 4.07354 4.07354 3.6146 4.63803 3.32698C5.27976 3 6.11984 3 7.8 3Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				';

				$allowed_html = array(
					'svg'  => array(
						'class'   => array(),
						'width'   => array(),
						'height'  => array(),
						'viewbox' => array(),
						'fill'    => array(),
						'xmlns'   => array(),
					),
					'path' => array(
						'd'               => array(),
						'stroke'          => array(),
						'stroke-width'    => array(),
						'stroke-linecap'  => array(),
						'stroke-linejoin' => array(),
					),
				);

				break;

			default:
				$xml = '';

				break;

		}

		echo wp_kses( $xml, $allowed_html );
	}

	/**
	 * Display the dismissible notices stored in the "daextam_dismissible_notice_a" option.
	 *
	 * Note that the dismissible notice will be displayed only once to the user.
	 *
	 * The dismissable notice is first displayed (only to the same user with which has been generated) and then it is
	 * removed from the "daextam_dismissible_notice_a" option.
	 *
	 * @return void
	 */
	public function display_dismissible_notices() {

		$dismissible_notice_a = get_option( 'daextam_dismissible_notice_a' );

		// Iterate over the dismissible notices with the user id of the same user.
		if ( is_array( $dismissible_notice_a ) ) {
			foreach ( $dismissible_notice_a as $key => $dismissible_notice ) {

				// If the user id of the dismissible notice is the same as the current user id, display the message.
				if ( get_current_user_id() === $dismissible_notice['user_id'] ) {

					$message = $dismissible_notice['message'];
					$class   = $dismissible_notice['class'];

					?>
					<div class="<?php echo esc_attr( $class ); ?> notice">
						<p><?php echo esc_html( $message ); ?></p>
						<div class="notice-dismiss-button"><?php $this->echo_icon_svg( 'x' ); ?></div>
					</div>

					<?php

					// Remove the echoed dismissible notice from the "daextam_dismissible_notice_a" WordPress option.
					unset( $dismissible_notice_a[ $key ] );

					update_option( 'daextam_dismissible_notice_a', $dismissible_notice_a );

				}
			}
		}
	}

	/**
	 * Save a dismissible notice in the "daextam_dismissible_notice_a" WordPress.
	 *
	 * @param string $message The message of the dismissible notice.
	 * @param string $element_class The class of the dismissible notice.
	 *
	 * @return void
	 */
	public function save_dismissible_notice( $message, $element_class ) {

		$dismissible_notice = array(
			'user_id' => get_current_user_id(),
			'message' => $message,
			'class'   => $element_class,
		);

		// Get the current option value.
		$dismissible_notice_a = get_option( 'daextam_dismissible_notice_a' );

		// If the option is not an array, initialize it as an array.
		if ( ! is_array( $dismissible_notice_a ) ) {
			$dismissible_notice_a = array();
		}

		// Add the dismissible notice to the array.
		$dismissible_notice_a[] = $dismissible_notice;

		// Save the dismissible notice in the "daextam_dismissible_notice_a" WordPress option.
		update_option( 'daextam_dismissible_notice_a', $dismissible_notice_a );
	}

	/**
	 * Sanitize the data of an uploaded file.
	 *
	 * @param array $file The data of the uploaded file.
	 *
	 * @return array
	 */
	public function sanitize_uploaded_file( $file ) {

		return array(
			'name'     => sanitize_file_name( $file['name'] ),
			'type'     => $file['type'],
			'tmp_name' => $file['tmp_name'],
			'error'    => intval( $file['error'], 10 ),
			'size'     => intval( $file['size'], 10 ),
		);
	}

	/**
	 * Generates the data of the "statistic" table.
	 *
	 * @return void
	 */
	public function update_statistics() {

		/**
		 * Set the custom "Max Execution Time Value" defined in the options if the "Set Max Execution Time" option is
		 * set to "Yes".
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_analysis_set_max_execution_time' ), 10 ) === 1 ) {
			ini_set(
				'max_execution_time',
				intval( get_option( $this->get( 'slug' ) . '_analysis_max_execution_time_value' ), 10 )
			);
		}

		/**
		 * Set the custom "Memory Limit Value" ( in megabytes ) defined in the options if the "Set Memory Limit" option
		 * is set to "Yes".
		 */
		if ( intval( get_option( $this->get( 'slug' ) . '_analysis_set_memory_limit' ), 10 ) === 1 ) {
			ini_set(
				'memory_limit',
				intval( get_option( $this->get( 'slug' ) . '_analysis_memory_limit_value' ), 10 ) . 'M'
			);
		}

		// Delete all the records in the "statistic" db table.
		global $wpdb;
		$result = $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_statistic" );

		// Get post types.
		$post_types_query      = '';
		$analysis_post_types_a = maybe_unserialize( get_option( $this->get( 'slug' ) . '_analysis_post_types' ) );

		// If $analysis_post_types_a is not an array fill $analysis_post_types_a with the posts available in the website.
		if ( ! is_array( $analysis_post_types_a ) || 0 === count( $analysis_post_types_a ) ) {
			$analysis_post_types_a = $this->get_post_types_with_ui();
		}

		// Generate the $post_types_query.
		if ( is_array( $analysis_post_types_a ) ) {

			foreach ( $analysis_post_types_a as $key => $value ) {

				$post_types_query .= $wpdb->prepare( 'post_type = %s', $value );
				if ( ( count( $analysis_post_types_a ) - 1 ) !== $key ) {
					$post_types_query .= ' OR ';
				}
			}

			$post_types_query = '(' . $post_types_query . ') AND';

		}

		// Generates the data of all the posts and save them in the $statistic_a array.
		global $wpdb;
		$table_name           = $wpdb->prefix . 'posts';
		$limit_posts_analysis = intval( get_option( $this->get( 'slug' ) . '_analysis_limit_posts_analysis' ), 10 );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_types_query is already sanitized.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$posts_a = $wpdb->get_results(
			$wpdb->prepare( "SELECT ID, post_title, post_type, post_date, post_content FROM {$wpdb->prefix}posts WHERE $post_types_query post_status = 'publish' ORDER BY post_date DESC LIMIT %d", $limit_posts_analysis ),
			ARRAY_A
		);
		// phpcs:enable

		// Init $statistic_a.
		$statistic_a = array();

		foreach ( $posts_a as $key => $single_post ) {

			// Post Id.
			$post_id = $single_post['ID'];

			// get the post title.
			$post_title = $single_post['post_title'];

			// Get the post permalink.
			$post_permalink = get_the_permalink( $single_post['ID'] );

			// Get the post edit link.
			$post_edit_link = get_edit_post_link( $single_post['ID'], 'url' );

			// Set the post type.
			$post_type = $single_post['post_type'];

			// Set the post date.
			$post_date = $single_post['post_date'];

			// Content Length.
			$content_length = mb_strlen( trim( $single_post['post_content'] ) );

			// Auto Links.
			$this->add_autolinks(
				$single_post['post_content'],
				false,
				$single_post['post_type'],
				$post_id
			);
			$auto_links = $this->number_of_replacements;

			/**
			 * Save data in the $statistic_a array (the data will be later saved into the statistic db table ).
			 */
			$statistic_a[] = array(
				'post_id'        => $post_id,
				'post_title'     => $post_title,
				'post_permalink' => $post_permalink,
				'post_edit_link' => $post_edit_link,
				'post_type'      => $post_type,
				'post_date'      => $post_date,
				'content_length' => $content_length,
				'auto_links'     => $auto_links,
			);

		}

		/*
		 * Save data into the statistic db table with multiple queries of 100 items each one.
		 *
		 * It's a compromise adopted for the following two reasons:
		 *
		 * 1 - For performance, too many queries slow down the process
		 * 2 - To avoid problem with queries too long
		 */
		$table_name         = $wpdb->prefix . $this->get( 'slug' ) . '_statistic';
		$statistic_a_length = count( $statistic_a );
		$query_groups       = array();
		$query_index        = 0;
		foreach ( $statistic_a as $key => $single_statistic ) {

			$query_index = intval( $key / 100, 10 );

			$query_groups[ $query_index ][] = $wpdb->prepare(
				'( %d, %s, %s, %s, %s, %s, %d, %d )',
				$single_statistic['post_id'],
				$single_statistic['post_title'],
				$single_statistic['post_permalink'],
				$single_statistic['post_edit_link'],
				$single_statistic['post_type'],
				$single_statistic['post_date'],
				$single_statistic['content_length'],
				$single_statistic['auto_links']
			);

		}

		/*
		 * Each item in the $query_groups array includes a maximum of 100 assigned records. Here each group creates a
		 * query and the query is executed.
		 */
		$query_start = "INSERT INTO {$wpdb->prefix}daextam_statistic (post_id, post_title, post_permalink, post_edit_link, post_type, post_date, content_length, auto_links) VALUES ";
		$query_end   = '';

		foreach ( $query_groups as $key => $query_values ) {

			$query_body = '';

			foreach ( $query_values as $single_query_value ) {

				$query_body .= $single_query_value . ',';

			}

			// Save data into the archive db table.

			// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_types_query is already sanitized.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery
			$wpdb->query(
				$query_start . substr( $query_body, 0, mb_strlen( $query_body ) - 1 ) . $query_end
			);
			// phpcs:enable

		}

		// Update the option that stores the last update date.
		update_option( $this->get( 'slug' ) . '_statistics_data_last_update', current_time( 'mysql' ) );
	}

	/**
	 * Iterate the $results array and find the average number of automatic links. Note that the auto
	 * links value is stored in the 'auto_links' key of the $results array.
	 *
	 * @param array $results The link statistics stored in the archive db table provided as an array.
	 *
	 * @return int The average number of automatic links.
	 */
	public function get_average_automatic_links( $results ) {

		// Init the $total_al variable.
		$total_al = 0;

		// Iterate the $results array and sum the automatic links.
		foreach ( $results as $key => $result ) {
			$total_al += $result->auto_links;
		}

		// Calculate the average number of manual links.
		$average_al = $total_al / count( $results );

		// Round the average number of manual links (no decimals).
		$average_al = round( $average_al, 1 );

		return $average_al;
	}

	/**
	 * Delete the statistics available in the following db tables:
	 *
	 * - daextam_statistic
	 *
	 * @return void
	 */
	public function delete_statistics() {

		global $wpdb;

		// Delete the anchors db table content.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}daextam_statistic" );
	}
}