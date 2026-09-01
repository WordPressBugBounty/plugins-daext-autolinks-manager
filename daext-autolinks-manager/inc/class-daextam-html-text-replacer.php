<?php
/**
 * DOM-based text-node replacement engine.
 *
 * This class walks the text nodes of an HTML fragment and applies a caller-
 * supplied callback only to those nodes, making it structurally impossible for
 * keyword replacements to land inside an HTML attribute, tag name, or any other
 * non-text part of the markup.
 *
 * It is used by Daextam_Autolink_Engine::add_autolinks() as a replacement for
 * the previous approach of running preg_replace_callback() directly on the raw
 * HTML string during Step 1 (the creation of temporary [al]ID[/al] identifiers).
 *
 * @package daext-autolinks-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DOM-based text-node replacement engine.
 */
class Daextam_Html_Text_Replacer {

	/**
	 * Walks every text node in $html that is not a descendant of an excluded
	 * element and passes its value through $callback.
	 *
	 * The callback receives the raw text-node string and must return the
	 * (possibly modified) string. The returned value may contain [al]ID[/al]
	 * tokens; because the result is inserted as a DocumentFragment those tokens
	 * are treated as plain text until Step 2 of the autolink engine expands them
	 * with preg_replace_callback_2().
	 *
	 * Nodes that are descendants of the following elements are excluded from
	 * replacement, mirroring the tags that apply_protected_blocks() already
	 * handles via the [pb] token system or that must never contain auto-links:
	 *
	 *   - <a>       – never nest a link inside a link
	 *   - <script>  – JavaScript source must not be mutated
	 *   - <style>   – CSS source must not be mutated
	 *   - <textarea>– raw user-editable text, not rendered as HTML
	 *   - <button>  – interactive controls
	 *   - <noscript>– fallback HTML rendered when scripting is off
	 *   - <select>  – form control; its text must not be mutated
	 *   - <option>  – individual select option; same reason as <select>
	 *
	 * All other protected regions (comments, editor blocks, protected tags
	 * like <code>, <pre>, etc.) are already collapsed to [pb]N[/pb] tokens by
	 * apply_protected_blocks() before this method is called, so they appear as
	 * opaque text nodes and are preserved verbatim.
	 *
	 * @param string   $html     The post HTML after apply_protected_blocks() has
	 *                           been applied (so [pb] tokens are already in place).
	 * @param callable $callback Signature: fn( string $text_node_value ): string.
	 *                           Called once per eligible text node. Must return a
	 *                           string; may contain literal '[al]N[/al]' tokens.
	 *
	 * @return string The HTML with replacements applied only inside text nodes.
	 */
	public function replace_in_text_nodes( string $html, callable $callback ): string {

		if ( '' === $html ) {
			return $html;
		}

		// ------------------------------------------------------------------ //
		// 1. Parse the HTML fragment into a DOMDocument.
		//
		//    Notes on the loading strategy:
		//
		//    a) Post content is a fragment, not a full document. Wrapping it in
		//       a <div> guarantees a single root that we can later serialize back
		//       via inner_html(), regardless of how many top-level siblings the
		//       fragment contains.
		//
		//    b) The '<?xml encoding="UTF-8">' processing instruction tells
		//       libxml to treat the byte stream as UTF-8 without requiring a
		//       <meta charset> tag. It is stripped from the output by
		//       inner_html() because we serialize only the children of the <div>
		//       wrapper, not the document root.
		//
		//    c) LIBXML_HTML_NODEFDTD suppresses the automatic insertion of a
		//       DOCTYPE declaration, keeping the output clean.
		//
		//    d) libxml_use_internal_errors() prevents malformed HTML (which is
		//       common in real-world post content) from triggering PHP warnings.
		// ------------------------------------------------------------------ //

		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML(
			'<?xml encoding="UTF-8"><div id="daextam-root">' . $html . '</div>',
			LIBXML_HTML_NODEFDTD
		);
		libxml_clear_errors();

		// ------------------------------------------------------------------ //
		// 2. Collect eligible text nodes via DOMXPath.
		//
		//    The XPath expression selects every text() node that is NOT a
		//    descendant of any of the excluded elements listed above.
		//
		//    We collect nodes into an array first because modifying the DOM
		//    while iterating a live DOMNodeList causes items to shift, leading
		//    to skipped or double-processed nodes.
		// ------------------------------------------------------------------ //

		$xpath      = new DOMXPath( $dom );
		$node_list  = $xpath->query(
			'//div[@id="daextam-root"]//text()
			[
				not( ancestor::a )
				and not( ancestor::script )
				and not( ancestor::style )
				and not( ancestor::textarea )
				and not( ancestor::button )
				and not( ancestor::noscript )
				and not( ancestor::select )
				and not( ancestor::option )
			]'
		);

		if ( false === $node_list || 0 === $node_list->length ) {
			return $html;
		}

		// Snapshot into a plain array to avoid live-NodeList mutation issues.
		$text_nodes = array();
		foreach ( $node_list as $node ) {
			$text_nodes[] = $node;
		}

		// ------------------------------------------------------------------ //
		// 3. Apply the callback to each text node.
		//
		//    If the callback returns a string that is identical to the original
		//    node value, nothing is changed (fast path).
		//
		//    If the callback introduces [al]N[/al] tokens the new string is
		//    re-inserted as a DocumentFragment so the surrounding DOM structure
		//    is preserved. The tokens themselves are plain text at this stage;
		//    they contain only alphanumeric characters and brackets, so
		//    DOMDocument will not try to parse them as markup.
		// ------------------------------------------------------------------ //

		foreach ( $text_nodes as $text_node ) {

			$original = $text_node->nodeValue;
			$replaced = $callback( $original );

			// Nothing changed – skip the (relatively expensive) fragment work.
			if ( $replaced === $original ) {
				continue;
			}

			$parent = $text_node->parentNode;
			if ( null === $parent ) {
				continue;
			}


			// Encode HTML special characters in the replacement string so the
			// result is a valid XML text value. Bracket characters used by the
			// [al]N[/al] tokens are not affected by htmlspecialchars(), so no
			// additional restore step is needed.
			$safe_replaced = htmlspecialchars( $replaced, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );

			// Build a DocumentFragment from the encoded string.
			// appendXML() expects a well-formed XML fragment; htmlspecialchars()
			// above guarantees that any stray '&' or '<' are already escaped.
			// If appendXML() still fails (e.g. due to an unrecoverable encoding
			// issue) leave the original text node untouched.
			$fragment = $dom->createDocumentFragment();
			libxml_use_internal_errors( true );
			$ok = $fragment->appendXML( $safe_replaced );
			libxml_clear_errors();

			if ( false === $ok ) {
				continue;
			}

			$parent->insertBefore( $fragment, $text_node );
			$parent->removeChild( $text_node );
		}

		// ------------------------------------------------------------------ //
		// 4. Serialize only the inner HTML of the wrapper <div>.
		// ------------------------------------------------------------------ //

		$root = $xpath->query( '//div[@id="daextam-root"]' )->item( 0 );

		if ( null === $root ) {
			// Fallback: return the original string unchanged.
			return $html;
		}

		return $this->inner_html( $root, $dom );
	}

	/**
	 * Returns the serialized inner HTML of a DOMNode (i.e. the HTML of all its
	 * children concatenated, without the node's own opening and closing tags).
	 *
	 * @param DOMNode    $node The node whose inner HTML is to be returned.
	 * @param DOMDocument $dom  The owning document, used to call saveHTML().
	 *
	 * @return string
	 */
	private function inner_html( DOMNode $node, DOMDocument $dom ): string {

		$html = '';

		foreach ( $node->childNodes as $child ) {
			$html .= $dom->saveHTML( $child );
		}

		return $html;
	}
}
