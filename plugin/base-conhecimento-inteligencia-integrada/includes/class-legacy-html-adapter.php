<?php
/**
 * Adapter determinístico para HTML legado / texto editorial estático.
 * Preserva relações semânticas necessárias para Knowledge Document v2.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Legacy_HTML_Adapter {

	/**
	 * @return array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>}
	 */
	public static function extract( string $content, string $source = 'post_content' ): array {
		$structure = self::empty_structure();
		$warnings  = array();
		$fragments = array();

		$shortcodes = Shortcode_Inspector::inspect( $content );
		$structure['shortcodes'] = $shortcodes['total'];
		$warnings = array_merge( $warnings, $shortcodes['warnings'] );
		$content = Shortcode_Inspector::unwrap_without_execution( $content );

		if ( '' === trim( $content ) ) {
			return array(
				'fragments' => array(),
				'structure' => $structure,
				'warnings'  => self::unique_preserve_order( $warnings ),
			);
		}

		if ( ! class_exists( '\DOMDocument' ) ) {
			$fallback = self::extract_without_dom( $content, $source );
			$fallback['structure']['shortcodes'] = $structure['shortcodes'];
			$fallback['warnings'] = self::unique_preserve_order(
				array_merge( $warnings, array( 'HTML_DOM_UNAVAILABLE', 'HTML_STRUCTURE_DEGRADED_NO_DOM' ), $fallback['warnings'] )
			);
			return $fallback;
		}

		$previous = libxml_use_internal_errors( true );
		libxml_clear_errors();
		$dom = new \DOMDocument( '1.0', 'UTF-8' );
		$wrapped = '<!DOCTYPE html><html><body><div id="bdc-kb-root">' . $content . '</div></body></html>';
		$loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_NONET | LIBXML_COMPACT );
		$errors = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( ! $loaded ) {
			$fallback = self::extract_without_dom( $content, $source );
			$fallback['structure']['shortcodes'] = $structure['shortcodes'];
			$fallback['warnings'] = self::unique_preserve_order(
				array_merge( $warnings, array( 'HTML_PARSE_RECOVERED', 'HTML_STRUCTURE_DEGRADED_NO_DOM' ), $fallback['warnings'] )
			);
			return $fallback;
		}

		if ( ! empty( $errors ) ) {
			$warnings[] = 'HTML_PARSE_RECOVERED';
		}

		$xpath = new \DOMXPath( $dom );
		$root = $dom->getElementById( 'bdc-kb-root' );
		if ( ! $root instanceof \DOMElement ) {
			$root_nodes = $xpath->query( '//*[@id="bdc-kb-root"]' );
			$root = ( $root_nodes && $root_nodes->length > 0 ) ? $root_nodes->item( 0 ) : null;
		}
		if ( ! $root instanceof \DOMElement ) {
			return array(
				'fragments' => array(),
				'structure' => $structure,
				'warnings'  => self::unique_preserve_order( array_merge( $warnings, array( 'HTML_PARSE_RECOVERED' ) ) ),
			);
		}

		self::remove_excluded_nodes( $xpath, $root );
		self::collect_structure( $xpath, $root, $structure );

		$context = array(
			'list_index'  => 0,
			'table_index' => 0,
			'image_index' => 0,
		);
		self::walk_children( $root, $source, $fragments, $context );
		self::reindex( $fragments );

		$structure['paragraphs'] = self::count_kind( $fragments, 'paragraph' );
		$structure['list_items'] = self::count_kind( $fragments, 'list_item' );
		$structure['table_rows'] = self::count_kind( $fragments, 'table_row' );
		$structure['table_cells'] = self::count_table_cells( $fragments );

		return array(
			'fragments' => array_values( $fragments ),
			'structure' => $structure,
			'warnings'  => self::unique_preserve_order( $warnings ),
		);
	}

	/** @return array<string,int> */
	public static function empty_structure(): array {
		return array(
			'headings'    => 0,
			'paragraphs'  => 0,
			'lists'       => 0,
			'list_items'  => 0,
			'tables'      => 0,
			'table_rows'  => 0,
			'table_cells' => 0,
			'images'      => 0,
			'links'       => 0,
			'code_blocks' => 0,
			'shortcodes'  => 0,
		);
	}

	private static function remove_excluded_nodes( \DOMXPath $xpath, \DOMElement $root ): void {
		$nodes = $xpath->query( './/script|.//style|.//noscript', $root );
		if ( ! $nodes ) {
			return;
		}
		$to_remove = array();
		foreach ( $nodes as $node ) {
			$to_remove[] = $node;
		}
		foreach ( $to_remove as $node ) {
			if ( $node->parentNode ) {
				$node->parentNode->removeChild( $node );
			}
		}
	}

	/** @param array<string,int> $structure */
	private static function collect_structure( \DOMXPath $xpath, \DOMElement $root, array &$structure ): void {
		$queries = array(
			'headings'    => './/h1|.//h2|.//h3|.//h4|.//h5|.//h6',
			'paragraphs'  => './/p',
			'lists'       => './/ul|.//ol',
			'list_items'  => './/li',
			'tables'      => './/table',
			'table_rows'  => './/tr',
			'table_cells' => './/th|.//td',
			'images'      => './/img',
			'links'       => './/a[@href]',
			'code_blocks' => './/pre|.//code[not(ancestor::pre)]',
		);
		foreach ( $queries as $key => $query ) {
			$nodes = $xpath->query( $query, $root );
			$structure[ $key ] = $nodes ? $nodes->length : 0;
		}
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,int> $context
	 */
	private static function walk_children( \DOMNode $parent, string $source, array &$fragments, array &$context ): void {
		$inline_buffer = '';
		foreach ( $parent->childNodes as $child ) {
			if ( $child instanceof \DOMText ) {
				$inline_buffer .= $child->nodeValue ?? '';
				continue;
			}
			if ( ! $child instanceof \DOMElement ) {
				continue;
			}

			$tag = strtolower( $child->tagName );
			if ( 'br' === $tag ) {
				$inline_buffer .= "\n";
				continue;
			}

			if ( in_array( $tag, array( 'ul', 'ol' ), true ) ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_list( $child, $source, $fragments, $context, 0, '' );
				continue;
			}
			if ( 'table' === $tag ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_table( $child, $source, $fragments, $context );
				continue;
			}
			if ( 'img' === $tag ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_image( $child, $source, $fragments, $context );
				continue;
			}
			if ( preg_match( '/^h([1-6])$/', $tag, $match ) ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_fragment( 'heading', self::visible_text( $child ), $source, $fragments, array( 'level' => (int) $match[1] ) );
				continue;
			}
			if ( 'p' === $tag ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_fragment( 'paragraph', self::visible_text_excluding_structural_children( $child ), $source, $fragments );
				self::walk_nested_structures( $child, $source, $fragments, $context );
				continue;
			}
			if ( 'blockquote' === $tag ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_fragment( 'quote', self::visible_text_excluding_structural_children( $child ), $source, $fragments );
				self::walk_nested_structures( $child, $source, $fragments, $context );
				continue;
			}
			if ( 'pre' === $tag ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_fragment( 'code', $child->textContent ?? '', $source, $fragments, array(), true );
				continue;
			}
			if ( 'code' === $tag ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_fragment( 'code', $child->textContent ?? '', $source, $fragments, array(), true );
				continue;
			}

			if ( self::is_container_tag( $tag ) ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::walk_children( $child, $source, $fragments, $context );
				continue;
			}

			$inline_buffer .= self::visible_text( $child );
		}
		self::flush_inline_buffer( $inline_buffer, $source, $fragments );
	}

	private static function is_container_tag( string $tag ): bool {
		return in_array( $tag, array( 'div', 'section', 'article', 'main', 'header', 'footer', 'aside', 'nav', 'figure', 'figcaption' ), true );
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,int> $context
	 */
	private static function append_list( \DOMElement $list, string $source, array &$fragments, array &$context, int $depth, string $parent_item_id ): void {
		$list_id = 'list-' . $context['list_index']++;
		$list_type = 'ol' === strtolower( $list->tagName ) ? 'ordered' : 'unordered';
		$item_index = 0;

		foreach ( $list->childNodes as $child ) {
			if ( ! $child instanceof \DOMElement || 'li' !== strtolower( $child->tagName ) ) {
				continue;
			}
			$item_id = $list_id . '-item-' . $item_index;
			$text = self::visible_text_excluding_tags( $child, array( 'ul', 'ol', 'table' ) );
			self::append_fragment(
				'list_item',
				$text,
				$source,
				$fragments,
				array(
					'list_id'        => $list_id,
					'list_type'      => $list_type,
					'depth'          => $depth,
					'item_index'     => $item_index,
					'item_id'        => $item_id,
					'parent_item_id' => $parent_item_id,
				)
			);

			foreach ( $child->childNodes as $nested ) {
				if ( ! $nested instanceof \DOMElement ) {
					continue;
				}
				$nested_tag = strtolower( $nested->tagName );
				if ( in_array( $nested_tag, array( 'ul', 'ol' ), true ) ) {
					self::append_list( $nested, $source, $fragments, $context, $depth + 1, $item_id );
				} elseif ( 'table' === $nested_tag ) {
					self::append_table( $nested, $source, $fragments, $context );
				}
			}
			++$item_index;
		}
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,int> $context
	 */
	private static function append_table( \DOMElement $table, string $source, array &$fragments, array &$context ): void {
		$table_id = 'table-' . $context['table_index']++;
		$caption_index = 0;
		foreach ( $table->childNodes as $child ) {
			if ( $child instanceof \DOMElement && 'caption' === strtolower( $child->tagName ) ) {
				self::append_fragment( 'table_caption', self::visible_text( $child ), $source, $fragments, array( 'table_id' => $table_id, 'caption_index' => $caption_index++ ) );
			}
		}

		$rows = self::direct_table_rows( $table );
		foreach ( $rows as $row_index => $row ) {
			$cells = array();
			$cell_index = 0;
			foreach ( $row->childNodes as $cell ) {
				if ( ! $cell instanceof \DOMElement ) {
					continue;
				}
				$cell_tag = strtolower( $cell->tagName );
				if ( ! in_array( $cell_tag, array( 'th', 'td' ), true ) ) {
					continue;
				}
				$text = Content_Normalizer::text( self::visible_text_excluding_tags( $cell, array( 'table' ) ) );
				$cells[] = array(
					'cell_index' => $cell_index++,
					'kind'       => 'th' === $cell_tag ? 'header' : 'data',
					'text'       => $text,
					'colspan'    => max( 1, (int) $cell->getAttribute( 'colspan' ) ),
					'rowspan'    => max( 1, (int) $cell->getAttribute( 'rowspan' ) ),
				);
			}
			$display = implode( ' | ', array_map( static fn ( array $cell ): string => (string) $cell['text'], $cells ) );
			self::append_fragment( 'table_row', $display, $source, $fragments, array( 'table_id' => $table_id, 'row_index' => $row_index, 'cells' => $cells ) );
		}
	}

	/** @return array<int,\DOMElement> */
	private static function direct_table_rows( \DOMElement $table ): array {
		$rows = array();
		foreach ( $table->childNodes as $child ) {
			if ( ! $child instanceof \DOMElement ) {
				continue;
			}
			$tag = strtolower( $child->tagName );
			if ( 'tr' === $tag ) {
				$rows[] = $child;
				continue;
			}
			if ( in_array( $tag, array( 'thead', 'tbody', 'tfoot' ), true ) ) {
				foreach ( $child->childNodes as $row ) {
					if ( $row instanceof \DOMElement && 'tr' === strtolower( $row->tagName ) ) {
						$rows[] = $row;
					}
				}
			}
		}
		return $rows;
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,int> $context
	 */
	private static function append_image( \DOMElement $image, string $source, array &$fragments, array &$context ): void {
		$alt = Content_Normalizer::text( $image->getAttribute( 'alt' ) );
		if ( '' === $alt ) {
			return;
		}
		self::append_fragment( 'image', $alt, $source, $fragments, array( 'image_id' => 'image-' . $context['image_index']++ ) );
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,int> $context
	 */
	private static function walk_nested_structures( \DOMElement $parent, string $source, array &$fragments, array &$context ): void {
		foreach ( $parent->childNodes as $child ) {
			if ( ! $child instanceof \DOMElement ) {
				continue;
			}
			$tag = strtolower( $child->tagName );
			if ( in_array( $tag, array( 'ul', 'ol' ), true ) ) {
				self::append_list( $child, $source, $fragments, $context, 0, '' );
			} elseif ( 'table' === $tag ) {
				self::append_table( $child, $source, $fragments, $context );
			}
		}
	}

	/** @param array<int,string> $excluded_tags */
	private static function visible_text_excluding_tags( \DOMNode $node, array $excluded_tags ): string {
		$out = '';
		foreach ( $node->childNodes as $child ) {
			if ( $child instanceof \DOMText ) {
				$out .= $child->nodeValue ?? '';
				continue;
			}
			if ( ! $child instanceof \DOMElement ) {
				continue;
			}
			$tag = strtolower( $child->tagName );
			if ( in_array( $tag, array( 'script', 'style', 'noscript' ), true ) || in_array( $tag, $excluded_tags, true ) ) {
				continue;
			}
			if ( 'br' === $tag ) {
				$out .= "\n";
				continue;
			}
			$out .= self::visible_text_excluding_tags( $child, $excluded_tags );
		}
		return html_entity_decode( $out, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

	private static function visible_text_excluding_structural_children( \DOMNode $node ): string {
		return self::visible_text_excluding_tags( $node, array( 'ul', 'ol', 'table' ) );
	}

	private static function visible_text( \DOMNode $node ): string {
		return self::visible_text_excluding_tags( $node, array() );
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function append_fragment( string $kind, string $text, string $source, array &$fragments, array $meta = array(), bool $preserve = false ): void {
		$fragment = Content_Normalizer::fragment( $kind, $text, $source, count( $fragments ), $meta, $preserve );
		if ( null !== $fragment ) {
			$fragments[] = $fragment;
		}
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function flush_inline_buffer( string &$buffer, string $source, array &$fragments ): void {
		self::append_fragment( 'paragraph', $buffer, $source, $fragments );
		$buffer = '';
	}

	/**
	 * Best-effort fallback quando ext-dom não estiver disponível.
	 * Relações estruturais são marcadas como degradadas para impedir AI readiness READY.
	 *
	 * @return array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>}
	 */
	private static function extract_without_dom( string $content, string $source ): array {
		$structure = self::empty_structure();
		$warnings  = array( 'HTML_STRUCTURE_DEGRADED_NO_DOM' );
		$fragments = array();
		$content = preg_replace( '#<(script|style|noscript)\b[^>]*>.*?</\1>#is', '', $content ) ?? $content;

		$counts = array(
			'headings'    => '#<h[1-6]\b#i',
			'paragraphs'  => '#<p\b#i',
			'lists'       => '#<(ul|ol)\b#i',
			'list_items'  => '#<li\b#i',
			'tables'      => '#<table\b#i',
			'table_rows'  => '#<tr\b#i',
			'table_cells' => '#<(th|td)\b#i',
			'images'      => '#<img\b#i',
			'links'       => '#<a\b[^>]*\bhref\s*=#i',
		);
		foreach ( $counts as $key => $pattern ) {
			$count = preg_match_all( $pattern, $content, $unused );
			$structure[ $key ] = false === $count ? 0 : $count;
		}
		$pre_count = preg_match_all( '#<pre\b[^>]*>.*?</pre\s*>#is', $content, $pre_matches );
		$without_pre = preg_replace( '#<pre\b[^>]*>.*?</pre\s*>#is', '', $content ) ?? $content;
		$code_count = preg_match_all( '#<code\b#i', $without_pre, $unused );
		$structure['code_blocks'] = ( false === $pre_count ? 0 : $pre_count ) + ( false === $code_count ? 0 : $code_count );

		$pattern = '#<(h[1-6]|p|li|tr|pre|code|blockquote)\b[^>]*>(.*?)</\1\s*>#is';
		$found = preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
		if ( false === $found || 0 === $found ) {
			self::append_fragment( 'paragraph', self::fallback_visible_text( $content ), $source, $fragments );
			return array( 'fragments' => $fragments, 'structure' => $structure, 'warnings' => $warnings );
		}

		$cursor = 0;
		$list_index = 0;
		$table_index = 0;
		foreach ( $matches as $match ) {
			$whole  = (string) $match[0][0];
			$offset = (int) $match[0][1];
			$tag    = strtolower( (string) $match[1][0] );
			$inner  = (string) $match[2][0];
			if ( $offset > $cursor ) {
				self::append_fragment( 'paragraph', self::fallback_visible_text( substr( $content, $cursor, $offset - $cursor ) ), $source, $fragments );
			}

			$meta = array();
			$kind = 'paragraph';
			$text = self::fallback_visible_text( $inner );
			$preserve = false;
			if ( preg_match( '/^h([1-6])$/', $tag, $level ) ) {
				$kind = 'heading';
				$meta['level'] = (int) $level[1];
			} elseif ( 'li' === $tag ) {
				$kind = 'list_item';
				$meta = array( 'list_id' => 'fallback-list-' . $list_index, 'list_type' => 'unknown', 'depth' => 0, 'item_index' => 0, 'item_id' => 'fallback-list-' . $list_index . '-item-0', 'parent_item_id' => '' );
				++$list_index;
			} elseif ( 'tr' === $tag ) {
				$kind = 'table_row';
				$cells = array();
				$cell_count = preg_match_all( '#<(th|td)\b([^>]*)>(.*?)</\1\s*>#is', $inner, $cell_matches, PREG_SET_ORDER );
				if ( false !== $cell_count ) {
					foreach ( $cell_matches as $cell_index => $cell ) {
						$cells[] = array( 'cell_index' => $cell_index, 'kind' => 'th' === strtolower( (string) $cell[1] ) ? 'header' : 'data', 'text' => Content_Normalizer::text( self::fallback_visible_text( (string) $cell[3] ) ), 'colspan' => 1, 'rowspan' => 1 );
					}
				}
				$meta = array( 'table_id' => 'fallback-table-' . $table_index++, 'row_index' => 0, 'cells' => $cells );
				$text = implode( ' | ', array_map( static fn ( array $cell ): string => (string) $cell['text'], $cells ) );
			} elseif ( 'pre' === $tag || 'code' === $tag ) {
				$kind = 'code';
				$preserve = true;
				$text = html_entity_decode( strip_tags( preg_replace( '#<br\s*/?>#i', "\n", $inner ) ?? $inner ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			} elseif ( 'blockquote' === $tag ) {
				$kind = 'quote';
			}
			self::append_fragment( $kind, $text, $source, $fragments, $meta, $preserve );
			$cursor = $offset + strlen( $whole );
		}
		if ( $cursor < strlen( $content ) ) {
			self::append_fragment( 'paragraph', self::fallback_visible_text( substr( $content, $cursor ) ), $source, $fragments );
		}
		return array( 'fragments' => $fragments, 'structure' => $structure, 'warnings' => $warnings );
	}

	private static function fallback_visible_text( string $html ): string {
		$html = preg_replace( '#<br\s*/?>#i', "\n", $html ) ?? $html;
		$html = preg_replace( '#</?(div|section|article|main|header|footer|aside|nav|ul|ol|table|thead|tbody|tfoot)>#i', "\n", $html ) ?? $html;
		return html_entity_decode( strip_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function reindex( array &$fragments ): void {
		foreach ( $fragments as $index => &$fragment ) {
			$fragment['ordinal'] = $index;
		}
		unset( $fragment );
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function count_kind( array $fragments, string $kind ): int {
		$count = 0;
		foreach ( $fragments as $fragment ) {
			if ( (string) ( $fragment['kind'] ?? '' ) === $kind ) {
				++$count;
			}
		}
		return $count;
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function count_table_cells( array $fragments ): int {
		$count = 0;
		foreach ( $fragments as $fragment ) {
			if ( 'table_row' !== (string) ( $fragment['kind'] ?? '' ) ) {
				continue;
			}
			$meta = is_array( $fragment['meta'] ?? null ) ? $fragment['meta'] : array();
			$count += is_array( $meta['cells'] ?? null ) ? count( $meta['cells'] ) : 0;
		}
		return $count;
	}

	/** @param array<int,string> $values @return array<int,string> */
	private static function unique_preserve_order( array $values ): array {
		$out = array();
		$seen = array();
		foreach ( $values as $value ) {
			if ( isset( $seen[ $value ] ) ) {
				continue;
			}
			$seen[ $value ] = true;
			$out[] = $value;
		}
		return $out;
	}
}
