<?php
/**
 * Content helpers feature logic.
 *
 * This class encapsulates helper methods for content analysis and manipulation,
 * link counting, URL utilities, interlink analysis, and post-type registration.
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content helpers class.
 */
class Daextam_Content_Helpers {

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
	 * Returns true if the site is using HTTPS, false otherwise.
	 *
	 * @return bool
	 */
	public function is_site_using_https() {
		return is_ssl();
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
	 * Set the max execution time and memory limit based on the plugin options.
	 *
	 * This method checks the plugin options to determine if the max execution time and memory limit should be set.
	 * If the corresponding options are enabled, it sets the values accordingly using set_time_limit()
	 * and wp_raise_memory_limit().
	 */
	public function set_met_and_ml() {

		/**
		 * Set the custom "Max Execution Time Value" defined in the options if the 'Set Max Execution Time' option is
		 * set to "Yes".
		 *
		 * Note that set_time_limit() is the PHP-native, Plugin-Check-compliant replacement for
		 * ini_set('max_execution_time', ...).
		 */
		if ( intval( get_option( $this->shared->get( 'slug' ) . '_analysis_set_max_execution_time' ), 10 ) === 1 ) {
			set_time_limit( intval( get_option( $this->shared->get( 'slug' ) . '_analysis_max_execution_time_value' ), 10 ) );
		}

		/*
		 * Set the custom "Memory Limit Value" (in megabytes) defined in the options if the 'Set Memory Limit' option is
		 * set to "Yes".
		 *
		 * wp_raise_memory_limit() is the WordPress-native, Plugin-Check-compliant replacement for
		 * ini_set( 'memory_limit', ... ). The desired value is injected via the
		 * 'admin_memory_limit' filter immediately before the call so WordPress
		 * applies exactly the limit configured in the plugin options.
		 */
		if ( intval( get_option( $this->shared->get( 'slug' ) . '_analysis_set_memory_limit' ), 10 ) === 1 ) {
			$memory_limit = intval( get_option( $this->shared->get( 'slug' ) . '_analysis_memory_limit_value' ), 10 ) . 'M';
			add_filter(
				'admin_memory_limit',
				static function() use ( $memory_limit ) {
					return $memory_limit;
				}
			);
			wp_raise_memory_limit( 'admin' );
		}
	}

	/**
	 * Callback of the usort() function.
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * usort() function for PHP backward compatibility.
	 *
	 * Look for uses of usort_callback_1 to find which usort() function is
	 * actually using this callback.
	 *
	 * @param array $a The first array to compare.
	 * @param array $b The second array to compare.
	 */
	public function usort_callback_1( $a, $b ) {

		return $b['score'] - $a['score'];
	}

	/**
	 * The optimal number of interlinks is calculated by dividing the content
	 * length for the value in the "Characters per Interlink" option and
	 * converting the result to an integer.
	 *
	 * @param int $number_of_interlinks The overall number of interlinks ( manual interlinks + auto interlinks ).
	 * @param int $content_length The content length.
	 * @return int The number of recommended interlinks
	 */
	public function calculate_recommended_interlinks( $number_of_interlinks, $content_length ) {

		// Get the values of the options.
		$optimization_num_of_characters = 1000;
		$optimization_delta             = 2;

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		return intval( $optimal_number_of_interlinks, 10 );
	}

	/**
	 * The minimum number of interlinks suggestion is calculated by subtracting
	 * half of the optimization delta from the optimal number of interlinks.
	 *
	 * @param int $post_id The post id.
	 * @return int The minimum number of interlinks suggestion
	 */
	public function get_suggested_min_number_of_interlinks( $post_id ) {

		// Get the content length of the raw post.
		$content_length = mb_strlen( $this->get_raw_post_content( $post_id ) );

		// Get the values of the options.
		$optimization_num_of_characters = 1000;
		$optimization_delta             = 2;

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		// Get the minimum number of interlinks.
		$min_number_of_interlinks = intval( ( $optimal_number_of_interlinks - ( $optimization_delta / 2 ) ), 10 );

		// Set to zero negative values.
		if ( $min_number_of_interlinks < 0 ) {
			$min_number_of_interlinks = 0; }

		return $min_number_of_interlinks;
	}

	/**
	 * The maximum number of interlinks suggestion is calculated by adding
	 * half of the optimization delta to the optimal number of interlinks.
	 *
	 * @param int $post_id The post id.
	 * @return int The maximum number of interlinks suggestion.
	 */
	public function get_suggested_max_number_of_interlinks( $post_id ) {

		// Get the content length of the raw post.
		$content_length = mb_strlen( $this->get_raw_post_content( $post_id ) );

		// Get the values of the options.
		$optimization_num_of_characters = 1000;
		$optimization_delta             = 2;

		// Determines the optimal number of interlinks.
		$optimal_number_of_interlinks = $content_length / $optimization_num_of_characters;

		return intval( ( $optimal_number_of_interlinks + ( $optimization_delta / 2 ) ), 10 );
	}

	/**
	 * Get the raw post_content of the specified post.
	 *
	 * @param int $post_id The ID of the post.
	 * @return string The raw post content.
	 */
	public function get_raw_post_content( $post_id ) {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$post_obj = $wpdb->get_row(
				$wpdb->prepare( "SELECT post_content FROM {$wpdb->prefix}posts WHERE ID = %d", $post_id )
		);

		return $post_obj->post_content;
	}

	/**
	 * Get the number of manual interlinks in a given string
	 *
	 * @param string $text The string in which the search should be performed.
	 * @return int The number of internal links in the string
	 */
	public function get_manual_interlinks( $text ) {

		// Remove the HTML comments.
		$text = $this->remove_html_comments( $text );

		// Remove script tags.
		$text = $this->remove_script_tags( $text );

		// Working regex.
		$num_matches = preg_match_all(
				$this->manual_and_auto_internal_links_regex(),
				$text,
				$matches
		);

		return $num_matches;
	}

	/**
	 * Count the number of auto interlinks in the string.
	 *
	 * @param string $string The string in which the search should be performed.
	 * @return int The number of automatic links
	 */
	public function get_autolinks_number( $string ) {

		// Remove the HTML comments.
		$string = $this->remove_html_comments( $string );

		// Remove script tags.
		$string = $this->remove_script_tags( $string );

		/**
		 * Get the website url and quote and escape the regex character. # and
		 * whitespace ( used with the 'x' modifier ) are not escaped, thus
		 * should not be included in the $site_url string.
		 */
		$site_url = preg_quote( get_home_url() );

		$num_matches = preg_match_all(
				'{
            <a\s+                   #1 The element a start-tag followed by one or more whitespace character
            data-autolink-id="[\d]+"\s+     #2 The data-ail attribute followed by one or more whitespace character
            target="_[\w]+"\s+      #3 The target attribute followed by one or more whitespace character
            (?:rel="nofollow"\s+)?  #4 The rel="nofollow" attribute followed by one or more whitespace character, all is made optional by the trailing ? that works on the non-captured group ?:
            href\s*=\s*             #5 Equal may have whitespaces on both sides
            ([\'"]?)                #6 Match double quotes, single quote or no quote ( captured for the backreference \1 )
            ' . $site_url . '       #7 The site URL ( Scheme and Domain )
            [^\'">\s]*              #8 The rest of the URL ( Path and/or File )
            (\1)                    #9 Backreference that matches the href value delimiter matched at line 5
            [^>]*                   #10 Any character except > zero or more times
            >                       #11 End of the start-tag
            .+?                     #12 Any character one or more time with the quantifier lazy
            <\/a\s*>                #13 Element a end-tag with optional white-spaces characters before the >
            }ix',
				$string,
				$matches
		);

		return $num_matches;
	}

	/**
	 * A regex to match manual and automatic internal links.
	 *
	 * Note that relative URLs are also supported.
	 *
	 * @return string The regex to match manual and automatic internal links.
	 */
	public function manual_and_auto_internal_links_regex() {

		/**
		 * Get the website URL and escape the regex character. # and
		 * whitespace ( used with the 'x' modifier ) are not escaped, thus
		 * should not be included in the $site_url string
		 */
		$site_url = preg_quote( get_home_url(), '/' );

		// Get the website URL without the protocol part.
		$site_url_without_protocol_part = preg_quote( wp_parse_url( get_home_url(), PHP_URL_HOST ), '/' );

		return '{<a                                                     #1 Begin the element a start-tag
            [^>]+                                                       #2 Any character except > at least one time
            href\s*=\s*                                                 #3 Equal may have whitespaces on both sides
            ([\'"]?)                                                    #4 Match double quotes, single quote or no quote ( captured for the backreference \1 )
	        (                                                           #5 Capture group for both full and relative URLs
	            (?:' . $site_url . '[^\'">\s]*)                         #5a Match full URL starting with $site_url ( captured )
	            |                                                       # OR
	            (?!//)(?:\/|\.{1,2}\/)[^\'">\s]*                        #5b Match relative URLs (must start with /, ./, or ../) ( captured )
	                        |                                           # OR
                \#[^\'">\s]*                                            #5c Match fragment-only URLs (e.g., #section2) ( captured )
                            |                                           # OR
				(?!//)[^\'"\s<>:]+                                      #5d Match page-relative URLs (must not contain "://") (captured)
				|                                                       # OR
                (?://' . $site_url_without_protocol_part . '[^\'">\s]*) #5e Match protocol-relative URLs with $site_url (captured)
	        )    
            \1                                                          #6 Backreference that matches the href value delimiter matched at line 4
            [^>]*                                                       #7 Any character except > zero or more times
            >                                                           #8 End of the start-tag
            (.*?)                                                       #9 Link text or nested tags. After the dot ( enclose in parenthesis ) negative lookbehinds can be applied to avoid specific stuff inside the link text or nested tags. Example with single negative lookbehind (.(?<!word1))*? Example with multiple negative lookbehind (.(?<!word1)(?<!word2)(?<!word3))*?
            <\/a\s*>                                                    #10 Element a end-tag with optional white-spaces characters before the >
            }ix';
	}

	/**
	 * Register the support of the 'custom-fields' to all the post type with UI.
	 *
	 * The 'custom-fields' support is required by the sidebar components that use meta data. Without the
	 * 'custom-fields' support associated with the posts, the following meta data can't be used by the sidebar
	 * components and a JavaScript error breaks the editor:
	 *
	 * - _daextam_default_seo_power
	 * - _daextam_enable_ail
	 *
	 * Note that the problem solved by this method occurs only when a post type is registered and the "supports" array
	 * doesn't include the 'custom-fields' value.
	 *
	 * See: https://developer.wordpress.org/reference/functions/add_post_type_support/
	 */
	public function register_support_on_post_types() {

		// Get the post types with UI.
		$available_post_types_a = get_post_types(
				array(
						'show_ui' => true,
				)
		);

		// Remove the 'attachment' post type.
		$available_post_types_a = array_diff( $available_post_types_a, array( 'attachment' ) );

		// Add the 'custom-fields' support to the post types with UI.
		foreach ( $available_post_types_a as $available_post_type ) {
			add_post_type_support( $available_post_type, 'custom-fields' );
		}
	}

	/**
	 * Converts relative URLs to absolute URLs.
	 *
	 * The following type of URLs are supported:
	 *
	 * - Absolute URLs | E.g., "https://example.com/post/"
	 * - Protocol-relative URLs | E.g., "//localhost/image.jpg".
	 * - Root-relative URLs | E.g., "/post/".
	 * - Fragment-only URLs | E.g., "#section1".
	 * - Relative URLs with relative paths. | E.g., "./post/", "../post", "../../post".
	 * - Page-relative URLs | E.g., "post/".
	 *
	 * @param String $relative_url The relative URL that should be converted.
	 * @param Int $post_id The ID of the post.
	 *
	 * @return mixed|string
	 */
	public function relative_to_absolute_url( $relative_url, $post_id ) {

		$post_permalink = get_permalink( $post_id );

		/**
		 * If already an absolute URL, return as is.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( empty( $relative_url ) || wp_parse_url( $relative_url, PHP_URL_SCHEME ) ) {
			return $relative_url;
		}

		// Get the site URL. Ensure trailing slash for proper resolution.
		$base_url = home_url( '/' );

		// Parse base URL.
		$base_parts = wp_parse_url( $base_url );

		/**
		 * Protocol-relative URL | If it's a protocol-relative URL (e.g., //example.com/image.jpg), add "https:" as
		 * default.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '//' ) ) {
			if ( $this->is_site_using_https() ) {
				return 'https:' . $relative_url;
			} else {
				return 'http:' . $relative_url;
			}
		}

		/**
		 * Root-relative URLs | Handle root-relative URLs (e.g., "/some-page/").
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '/' ) ) {
			return $base_parts['scheme'] . '://' . $base_parts['host'] . $relative_url;
		}

		/**
		 * Fragment identifier | Handle fragment-only URLs (e.g., "#section").
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */
		if ( str_starts_with( $relative_url, '#' ) ) {
			return $post_permalink . $relative_url;
		}

		/**
		 * Relative URLs with relative paths.
		 *
		 * Handles the relative URLs with relative paths like "./page", "../page", and "../../page'.
		 *
		 * Check if the relative URLs starts with "./", or "../", or subsequent levels like "../../".
		 * If it does, use the exact relative URL to retrieve and return the absolute URL.
		 *
		 * -------------------------------------------------------------------------------------------------------------
		 */

		// This conditional supports all the levels like '../../', etc.
		if ( str_starts_with( $relative_url, './' ) || str_starts_with( $relative_url, '../' ) ) {

			/**
			 * Here, based on the type of relative URL, we move up one or more levels in the directory tree
			 * to create the correct absolute URL.
			 *
			 * Note that the URL on which we should move levels is stored in the $current_url variable.
			 */
			$post_permalink_parts = wp_parse_url( $post_permalink );

			// Ensure we have a valid base URL.
			if ( ! isset( $post_permalink_parts['scheme'], $post_permalink_parts['host'], $post_permalink_parts['path'] ) ) {
				return $relative_url; // Return as-is if current URL is invalid.
			}

			// Get the directory of the current URL.
			$base_path = rtrim( $post_permalink_parts['path'], '/' );

			// Split the base path into segments.
			$base_parts = explode( '/', $base_path );

			// Split the relative URL into segments.
			$relative_parts = explode( '/', $relative_url );

			// Process the relative path.
			foreach ( $relative_parts as $part ) {
				if ( '..' === $part ) {
					// Move up one directory level.
					if ( count( $base_parts ) > 1 ) {
						array_pop( $base_parts );
					}
				} elseif ( '.' !== $part && '' !== $part ) {
					// Append valid segments.
					$base_parts[] = $part;
				}
			}

			// If there is a trailing slash in the permalink add it to the $trailing_slash string.
			$trailing_slash = str_ends_with( $relative_url, '/' ) ? '/' : '';

			// Construct the final absolute URL and return it.
			return $post_permalink_parts['scheme'] . '://' . $post_permalink_parts['host'] . implode( '/', $base_parts ) . $trailing_slash;

		}

		/**
		 * Page-relative URLs.
		 *
		 * Handle relative URLs without a leading slash (page-relative URLs like "example-post/").
		 */
		$base_parts = wp_parse_url( $post_permalink );
		return $base_parts['scheme'] . '://' . $base_parts['host'] . $base_parts['path'] . $relative_url;

	}

	/**
	 * Given a link returns it with the anchor link removed.
	 *
	 * @param string $s The link that should be analyzed.
	 * @return string The link with the link anchor removed.
	 */
	public function remove_link_to_anchor( $s ) {

		$s = preg_replace_callback(
				'/([^#]+)               #Everything except # one or more times ( captured )
            \#.*                    #The # with anything the follows zero or more times
            /ux',
				array( $this, 'preg_replace_callback_4' ),
				$s
		);

		return $s;
	}

	/**
	 * Callback of the preg_replace_callback() function
	 *
	 * This callback is used to avoid an anonimus function as a parameter of the
	 * preg_replace_callback() function for PHP backward compatibility
	 *
	 * Look for uses of preg_replace_callback_4 to find which
	 * preg_replace_callback() function is actually using this callback
	 *
	 * @param array $m Todo.
	 */
	public function preg_replace_callback_4( $m ) {

		return $m[1];
	}

}
