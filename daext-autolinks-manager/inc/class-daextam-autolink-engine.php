<?php
/**
 * Autolink engine feature logic.
 *
 * This class encapsulates all methods and properties related to the core
 * autolink processing: applying automatic links to content, managing protected
 * blocks, preg_replace callbacks, and helper utilities used by the engine.
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autolink engine class.
 */
class Daextam_Autolink_Engine {

	/**
	 * Shared plugin instance.
	 *
	 * @var Daextam_Shared
	 */
	private $shared;

	// Properties used in add_autolinks() ----------------------------------------

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
	 * Declared public so that the closure passed to
	 * Daextam_Html_Text_Replacer::replace_in_text_nodes() can update it
	 * (via the $engine reference) before each preg_replace_callback_1() call.
	 *
	 * @var object
	 */
	public $parsed_autolink = null;

	/**
	 * The parsed post type.
	 *
	 * @var string
	 */
	private $parsed_post_type = null;

	/**
	 * The max number of automatic links allowed per post.
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
	 * An array with included the data of the automatic links used for performance reasons.
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
	 * The post ID.
	 *
	 * @var null
	 */
	private $post_id = null;

	/**
	 * The number of replacements.
	 *
	 * @var int
	 */
	public $number_of_replacements = 0;

	/**
	 * Constructor.
	 *
	 * @param Daextam_Shared $shared Shared plugin instance.
	 */
	public function __construct( $shared ) {
		$this->shared = $shared;
	}

	/**
	 * Add automatic links to the content based on the keyword created with the auto link rules menu:
	 *
	 *  1 - The protected blocks are applied with apply_protected_blocks()
	 *  2 - The words to be converted as a link are temporarily replaced with [al]ID[/al] tokens.
	 *      This is done via Daextam_Html_Text_Replacer::replace_in_text_nodes() which walks only
	 *      the DOM text nodes of the content, making it structurally impossible for any replacement
	 *      to land inside an HTML attribute, tag name, or other non-text markup.
	 *  3 - The [al]ID[/al] identifiers are replaced with the actual links
	 *  4 - The protected blocks are removed with remove_protected_blocks()
	 *  5 - The content with applied automatic links is returned
	 *
	 * @param string $content The content on which the automatic links should be applied.
	 * @param bool   $check_query This parameter is set to True when the method is called inside the loop and is used to
	 *    verify if we are in a single post.
	 * @param string $post_type If the automatic links are added from the back-end this parameter is used to determine the post type
	 *  of the content.
	 * @param int    $post_id This parameter is used if the method has been called outside the loop.
	 * @param bool $only_internal_links When true, only auto link rules whose URL starts with the site
	 * home URL (i.e. internal links) are applied. Default false applies rules with internal and external links.
	 *
	 * @return string The content with applied the automatic links.
	 */
	public function add_autolinks( $content, $check_query = true, $post_type = '', $post_id = false, $only_internal_links = false ) {

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
		 * Verify with the "Enable Auto Links" post meta data or (if the meta data is not present) verify with the
		 * "Enable Auto Links" option if the automatic links should be applied to this post.
		 */
		$enable_autolinks = get_post_meta( $this->post_id, '_daextam_enable_autolinks', true );
		if ( strlen( trim( $enable_autolinks ) ) === 0 ) {
			$enable_autolinks = get_option( $this->shared->get( 'slug' ) . '_advanced_enable_autolinks' );
		}
		if ( intval( $enable_autolinks, 10 ) === 0 ) {
			$this->number_of_replacements = 0;
			return $content;
		}

		// Protect the tags and the commented HTML with the protected blocks.
		$content = $this->apply_protected_blocks( $content );

		// Get the maximum number of automatic links allowed per post.
		$this->max_number_autolinks_per_post = $this->get_max_number_autolinks_per_post( $this->post_id );

		// Save the "Same URL Limit" as a class property.
		$this->same_url_limit = intval( get_option( $this->shared->get( 'slug' ) . '_advanced_same_url_limit' ), 10 );

		// Get an array with the automatic links from the db table.
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$autolinks = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}daextam_autolink ORDER BY priority DESC", ARRAY_A );

		/*
		 * To avoid additional database requests for each autolink in preg_replace_callback_2() save the data of the
		 * autolink in an array that uses the "autolink_id" as its index.
		 */
		$this->autolinks_ca = $this->save_autolinks_in_custom_array( $autolinks );

		// Apply the Random Prioritization if enabled.
		if ( intval( get_option( $this->shared->get( 'slug' ) . '_advanced_random_prioritization' ), 10 ) === 1 ) {
			$autolinks = $this->apply_random_prioritization( $autolinks, $this->post_id );
		}

		// Retrieve the site home URL once, outside the loop, for the internal-link filter below.
		$home_url = home_url();

		/*
		 * Build the list of automatic links that are eligible for this post, together
		 * with their precomputed regex parameters.  We separate eligibility
		 * checking from replacement so that the DOM is walked only once, with
		 * all eligible rules applied per text node inside a single callback.
		 */
		$eligible_autolinks = array();

		foreach ( $autolinks as $key => $autolink ) {

			/*
			 * When $only_internal_links is true, skip any autolink whose URL does not start with
			 * the site home URL (i.e. skip external links).
			 */
			if ( $only_internal_links && strncmp( $autolink['url'], $home_url, strlen( $home_url ) ) !== 0 ) {
				continue;
			}

			/*
			 * If $post_type is not empty means that we are adding the automatic links through the back-end, in this case set
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
			 * If the "Ignore Self Auto Links" option is set to true, do not apply the automatic links that have, as a target,
			 * the post where they should be applied.
			 */
			if ( intval( get_option( $this->shared->get( 'slug' ) . '_advanced_ignore_self_autolinks' ), 10 ) === 1 ) {
				if ( $autolink['url'] === $post_permalink ) {
					continue;
				}
			}

			// Get the list of post types where the automatic links should be applied.
			$post_types_a = maybe_unserialize( $autolink['post_types'] );

			// If $post_types_a is not an array fill $post_types_a with the posts available in the website.
			if ( ! is_array( $post_types_a ) ) {
				$post_types_a = $this->shared->get_content_helpers()->get_post_types_with_ui();
			}

			// Verify the post type.
			if ( false === in_array( $this->parsed_post_type, $post_types_a, true ) ) {
				continue;
			}

			/*
			 * If the target group is not set:
			 *
			 * - Check if the post is compliant by verifying categories and tags
			 *
			 * If the target group is set:
			 *
			 * - Check if the post is compliant by verifying the target group
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
				$categories_and_tags_verification = get_option( $this->shared->get( 'slug' ) . '_advanced_categories_and_tags_verification' );
			if ( ( 'any' === $categories_and_tags_verification || 'post' === get_post_type() ) &&
				( ! $this->shared->get_term_helpers()->is_compliant_with_categories( $this->post_id, $autolink ) ||
					! $this->shared->get_term_helpers()->is_compliant_with_tags( $this->post_id, $autolink ) ) ) {
				continue;
			}
		} elseif ( ! $this->shared->get_term_helpers()->is_compliant_with_term_group( $this->post_id, $autolink, $this->parsed_post_type ) ) {

				/**
				 * Do not proceed with the application of the autolink if this post is not compliant with the term
				 * group.
				 */
				continue;

			}

			// Get the max number of automatic links per keyword.
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

			// Store the precomputed parameters alongside the autolink data.
			$eligible_autolinks[] = array(
				'autolink'                        => $autolink,
				'modifier'                        => $modifier,
				'left_boundary'                   => $left_boundary,
				'right_boundary'                  => $right_boundary,
				'autolink_keyword'                => $autolink_keyword,
				'autolink_keyword_before'         => $autolink_keyword_before,
				'autolink_keyword_after'          => $autolink_keyword_after,
				'max_number_autolinks_per_keyword' => $max_number_autolinks_per_keyword,
			);

		}

		/*
		 * Step 1: "The creation of temporary identifiers of the substitutions"
		 *
		 * Replaces all keyword matches with [al]ID[/al] tokens, where ID is the index of
		 * the $this->autolink_a array that stores the substitution data.  These tokens are
		 * later expanded into real <a> tags by Step 2.
		 *
		 * The replacement is delegated to Daextam_Html_Text_Replacer::replace_in_text_nodes()
		 * which walks only the DOM text nodes of the content.  This makes it structurally
		 * impossible for any replacement to land inside an HTML attribute, a tag name, or any
		 * other non-text part of the markup.  The [pb]N[/pb] tokens already placed by
		 * apply_protected_blocks() are preserved verbatim because they appear as opaque text
		 * node content and contain no characters that any keyword regex would match.
		 */
		$engine = $this; // Capture for use inside the closure below.

		/*
		 * Build a per-rule "remaining replacements" counter array, indexed in
		 * the same order as $eligible_autolinks.
		 *
		 * A value of -1 means "unlimited" (i.e. the rule has no keyword limit).
		 * Any positive value is decremented each time the corresponding rule
		 * makes one or more replacements on a text node, and the rule is skipped
		 * entirely once the counter reaches 0.
		 *
		 * The array is captured by reference (&$remaining_per_rule) so that
		 * every text-node visit inside replace_in_text_nodes() shares and
		 * mutates the same counters, enforcing a document-wide limit rather than
		 * a per-text-node limit.
		 */
		$remaining_per_rule = array();
		foreach ( $eligible_autolinks as $index => $params ) {
			$limit                        = (int) $params['max_number_autolinks_per_keyword'];
			$remaining_per_rule[ $index ] = ( $limit <= 0 ) ? -1 : $limit;
		}

		$content = $this->shared->get_html_text_replacer()->replace_in_text_nodes(
			$content,
			function ( $text ) use ( $engine, $eligible_autolinks, &$remaining_per_rule ) {

				foreach ( $eligible_autolinks as $index => $params ) {

					// Skip this rule if its document-wide limit has been exhausted.
					if ( 0 === $remaining_per_rule[ $index ] ) {
						continue;
					}

					$engine->parsed_autolink = $params['autolink'];

					// Pass the remaining budget as the preg_replace_callback limit so
					// the engine never exceeds it within this single text node either.
					// The 5th argument ($count) receives how many replacements were
					// actually made, which we subtract from the remaining budget.
					$count = 0;
					$text  = preg_replace_callback(
						'/(' . $params['autolink_keyword_before'] . ')(' . $params['left_boundary'] . ')(' . $params['autolink_keyword'] . ')(' . $params['right_boundary'] . ')(' . $params['autolink_keyword_after'] . ')/' . $params['modifier'],
						array( $engine, 'preg_replace_callback_1' ),
						$text,
						$remaining_per_rule[ $index ], // -1 = unlimited, otherwise the remaining budget.
						$count
					);

					// Decrement the document-wide budget by however many replacements
					// were made on this text node (only when a limit is in effect).
					if ( -1 !== $remaining_per_rule[ $index ] ) {
						$remaining_per_rule[ $index ] -= $count;
					}

				}

				return $text;
			}
		);

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

		// Reset the array that includes the data of the automatic links already applied.
		$this->autolink_a = array();

		return $content;
	}

	/**
	 * Replaces the following elements with [pr]ID[/pr]:
	 *
	 *  - HTML Attributes
	 *  - Protected Editor Blocks
	 *  - Protected Editor Custom Blocks
	 *  - Protected Editor Custom Void Blocks
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


		// Get the Editor Protected Blocks.
		$protected_gutenberg_blocks   = get_option( $this->shared->get( 'slug' ) . '_advanced_protected_gutenberg_blocks' );
		$protected_gutenberg_blocks_a = maybe_unserialize( $protected_gutenberg_blocks );
		if ( ! is_array( $protected_gutenberg_blocks_a ) ) {
			$protected_gutenberg_blocks_a = array();
		}

		// Get the Protected Editor Custom Blocks.
		$protected_gutenberg_custom_blocks   = get_option( $this->shared->get( 'slug' ) . '_advanced_protected_gutenberg_custom_blocks' );
		$protected_gutenberg_custom_blocks_a = array_filter(
			explode(
				',',
				str_replace( ' ', '', trim( $protected_gutenberg_custom_blocks ) )
			)
		);

		// Get the Protected Editor Custom Void Blocks.
		$protected_gutenberg_custom_void_blocks   = get_option( $this->shared->get( 'slug' ) . '_advanced_protected_gutenberg_custom_void_blocks' );
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
                    <!--\s+(wp:' . $block . ').*?-->        #1 Editor Block Start
                    .*?                                     #2 Block Content
                    <!--\s+\/\1\s+-->                       #3 Editor Block End
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
		$protected_tags   = get_option( $this->shared->get( 'slug' ) . '_advanced_protected_tags' );
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
		 *  - If the max number of automatic links per post has been reached
		 *  - If the "Same URL Limit" has been reached
		 */
		if ( $this->max_number_autolinks_per_post === $this->autolink_id ||
			$this->same_url_limit_reached() ) {

			return $m[1] . $m[2] . $m[3] . $m[4] . $m[5];

		} else {

			/**
			 * Increases the $autolink_id property and stores the information related to this autolink and match in the
			 * $autolink_a property. These information will be later used to replace the temporary identifiers of the
			 * automatic links with the related data, and also in this method to verify the "Same URL Limit" option.
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
	 * Returns the maximum number of automatic links allowed per post by using the method explained below.
	 *
	 *  If the "General Limit Mode" option is set to "Auto":
	 *
	 *  The maximum number of automatic links per post is calculated based on the content length of this post divided for the
	 *  value of the "General Limit (Characters per Autolink)" option.
	 *
	 *  If the "General Limit Mode" option is set to "Manual":
	 *
	 *  The maximum number of automatic links per post is equal to the value of "General Limit (Amount)".
	 *
	 * @param int $post_id The post ID for which the maximum number automatic links per post should be calculated.
	 *
	 * @return int The maximum number of automatic links allowed per post.
	 */
	private function get_max_number_autolinks_per_post( $post_id ) {

		if ( intval( get_option( $this->shared->get( 'slug' ) . '_advanced_general_limit_mode' ), 10 ) === 0 ) {

			// Auto ---------------------------------------------------------------------------------------------------.
			$post_obj                = get_post( $post_id );
			$post_length             = mb_strlen( $post_obj->post_content );
			$characters_per_autolink = intval(
				get_option( $this->shared->get( 'slug' ) . '_advanced_general_limit_characters_per_autolink' ),
				10
			);

			return intval( $post_length / $characters_per_autolink );

		} else {

			// Manual -------------------------------------------------------------------------------------------------.
			return intval( get_option( $this->shared->get( 'slug' ) . '_advanced_general_limit_amount' ), 10 );

		}
	}

	/**
	 * If the number of times that the parsed autolink ($this->parsed_autolink['url']) is present in the array that
	 *  includes the data of the automatic links already applied as temporary identifiers ($this->autolink_a) is equal or
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
	 * Applies a random order (based on the hash of the post_id and autolink_id) to the automatic links that have the same
	 *  priority. This ensures a better distribution of the automatic links.
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

		// Move the automatic links array in the new $autolinks_rp1 array, which uses the priority value as its index.
		foreach ( $autolinks as $key => $autolink ) {

			$autolinks_rp1[ $autolink['priority'] ][] = $autolink;

		}

		/*
		 * Apply a random order (based on the hash of the post_id and autolink_id) to the automatic links that have the same
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
		 * Move the automatic links in the new $autolinks_rp2 array, which is structured like the original array, where the
		 * value of the priority field is stored in the autolink, and it's not used as the index of the array that
		 * includes all the automatic links with the same priority.
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
	 * @param array $autolinks An array with the automatic links data.
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

}
