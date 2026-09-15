<?php
/**
 * Adapter determinístico para HTML legado / texto editorial estático.
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
			$fallback['warnings'] = self::unique_preserve_order( array_merge( $warnings, array( 'HTML_DOM_UNAVAILABLE' ), $fallback['warnings'] ) );
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
			$fallback['warnings'] = self::unique_preserve_order( array_merge( $warnings, array( 'HTML_PARSE_RECOVERED' ), $fallback['warnings'] ) );
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
			$warnings[] = 'HTML_PARSE_RECOVERED';
			return array(
				'fragments' => array(),
				'structure' => $structure,
				'warnings'  => self::unique_preserve_order( $warnings ),
			);
		}

		self::remove_excluded_nodes( $xpath, $root );
		self::collect_structure( $xpath, $root, $structure );
		self::walk_children( $root, $source, $fragments );

		foreach ( $fragments as $index => &$fragment ) {
			$fragment['ordinal'] = $index;
		}
		unset( $fragment );

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
			'lists'       => 0,
			'tables'      => 0,
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
			'lists'       => './/ul|.//ol',
			'tables'      => './/table',
			'images'      => './/img',
			'links'       => './/a[@href]',
			'code_blocks' => './/pre|.//code[not(ancestor::pre)]',
		);

		foreach ( $queries as $key => $query ) {
			$nodes = $xpath->query( $query, $root );
			$structure[ $key ] = $nodes ? $nodes->length : 0;
		}
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function walk_children( \DOMNode $parent, string $source, array &$fragments ): void {
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

			if ( self::is_boundary_tag( $tag ) ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::append_boundary_fragment( $child, $tag, $source, $fragments );
				continue;
			}

			if ( 'br' === $tag ) {
				$inline_buffer .= "\n";
				continue;
			}

			if ( 'img' === $tag ) {
				continue;
			}

			if ( self::is_container_tag( $tag ) ) {
				self::flush_inline_buffer( $inline_buffer, $source, $fragments );
				self::walk_children( $child, $source, $fragments );
				continue;
			}

			$inline_buffer .= self::visible_text( $child );
		}

		self::flush_inline_buffer( $inline_buffer, $source, $fragments );
	}

	private static function is_boundary_tag( string $tag ): bool {
		return in_array(
			$tag,
			array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'li', 'tr', 'pre', 'code', 'blockquote' ),
			true
		);
	}

	private static function is_container_tag( string $tag ): bool {
		return in_array(
			$tag,
			array( 'div', 'section', 'article', 'main', 'header', 'footer', 'aside', 'nav', 'ul', 'ol', 'table', 'thead', 'tbody', 'tfoot' ),
			true
		);
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function append_boundary_fragment( \DOMElement $node, string $tag, string $source, array &$fragments ): void {
		$kind = 'paragraph';
		$meta = array();
		$preserve = false;
		$text = '';

		if ( preg_match( '/^h([1-6])$/', $tag, $match ) ) {
			$kind = 'heading';
			$meta['level'] = (int) $match[1];
			$text = self::visible_text( $node );
		} elseif ( 'li' === $tag ) {
			$kind = 'list_item';
			$text = self::visible_text( $node );
		} elseif ( 'tr' === $tag ) {
			$kind = 'table_row';
			$cells = array();
			foreach ( $node->childNodes as $cell ) {
				if ( $cell instanceof \DOMElement && in_array( strtolower( $cell->tagName ), array( 'th', 'td' ), true ) ) {
					$value = Content_Normalizer::text( self::visible_text( $cell ) );
					if ( '' !== $value ) {
						$cells[] = $value;
					}
				}
			}
			$text = implode( ' | ', $cells );
		} elseif ( 'pre' === $tag || 'code' === $tag ) {
			$kind = 'code';
			$preserve = true;
			$text = $node->textContent ?? '';
		} elseif ( 'blockquote' === $tag ) {
			$kind = 'quote';
			$text = self::visible_text( $node );
		} else {
			$text = self::visible_text( $node );
		}

		$fragment = Content_Normalizer::fragment(
			$kind,
			$text,
			$source,
			count( $fragments ),
			$meta,
			$preserve
		);
		if ( null !== $fragment ) {
			$fragments[] = $fragment;
		}
	}

	private static function visible_text( \DOMNode $node ): string {
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
			if ( in_array( $tag, array( 'script', 'style', 'noscript' ), true ) ) {
				continue;
			}
			if ( 'br' === $tag ) {
				$out .= "\n";
				continue;
			}
			$out .= self::visible_text( $child );
		}
		return html_entity_decode( $out, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

	/** @param array<int,array<string,mixed>> $fragments */
	private static function flush_inline_buffer( string &$buffer, string $source, array &$fragments ): void {
		$fragment = Content_Normalizer::fragment( 'paragraph', $buffer, $source, count( $fragments ) );
		if ( null !== $fragment ) {
			$fragments[] = $fragment;
		}
		$buffer = '';
	}

	/**
	 * Fallback estrutural determinístico quando ext-dom não estiver disponível.
	 * Não renderiza tema, shortcode ou widget.
	 *
	 * @return array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>}
	 */
	private static function extract_without_dom( string $content, string $source ): array {
		$structure = self::empty_structure();
		$warnings  = array();
		$fragments = array();

		$content = preg_replace( '#<(script|style|noscript)\b[^>]*>.*?</\1>#is', '', $content ) ?? $content;

		$counts = array(
			'headings' => '#<h[1-6]\b#i',
			'lists'    => '#<(ul|ol)\b#i',
			'tables'   => '#<table\b#i',
			'images'   => '#<img\b#i',
			'links'    => '#<a\b[^>]*\bhref\s*=#i',
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
			$text = self::fallback_visible_text( $content );
			$fragment = Content_Normalizer::fragment( 'paragraph', $text, $source, 0 );
			if ( null !== $fragment ) {
				$fragments[] = $fragment;
			}
			return array( 'fragments' => $fragments, 'structure' => $structure, 'warnings' => $warnings );
		}

		$cursor = 0;
		foreach ( $matches as $match ) {
			$whole  = (string) $match[0][0];
			$offset = (int) $match[0][1];
			$tag    = strtolower( (string) $match[1][0] );
			$inner  = (string) $match[2][0];

			if ( $offset > $cursor ) {
				$between = substr( $content, $cursor, $offset - $cursor );
				$fragment = Content_Normalizer::fragment( 'paragraph', self::fallback_visible_text( $between ), $source, count( $fragments ) );
				if ( null !== $fragment ) {
					$fragments[] = $fragment;
				}
			}

			$kind = 'paragraph';
			$meta = array();
			$preserve = false;
			$text = '';

			if ( preg_match( '/^h([1-6])$/', $tag, $level ) ) {
				$kind = 'heading';
				$meta['level'] = (int) $level[1];
				$text = self::fallback_visible_text( $inner );
			} elseif ( 'li' === $tag ) {
				$kind = 'list_item';
				$text = self::fallback_visible_text( $inner );
			} elseif ( 'tr' === $tag ) {
				$kind = 'table_row';
				$cells = array();
				$cell_count = preg_match_all( '#<(th|td)\b[^>]*>(.*?)</\1\s*>#is', $inner, $cell_matches, PREG_SET_ORDER );
				if ( false !== $cell_count ) {
					foreach ( $cell_matches as $cell ) {
						$value = Content_Normalizer::text( self::fallback_visible_text( (string) $cell[2] ) );
						if ( '' !== $value ) {
							$cells[] = $value;
						}
					}
				}
				$text = implode( ' | ', $cells );
			} elseif ( 'pre' === $tag || 'code' === $tag ) {
				$kind = 'code';
				$preserve = true;
				$text = html_entity_decode( strip_tags( preg_replace( '#<br\s*/?>#i', "\n", $inner ) ?? $inner ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			} elseif ( 'blockquote' === $tag ) {
				$kind = 'quote';
				$text = self::fallback_visible_text( $inner );
			} else {
				$text = self::fallback_visible_text( $inner );
			}

			$fragment = Content_Normalizer::fragment( $kind, $text, $source, count( $fragments ), $meta, $preserve );
			if ( null !== $fragment ) {
				$fragments[] = $fragment;
			}
			$cursor = $offset + strlen( $whole );
		}

		if ( $cursor < strlen( $content ) ) {
			$tail = substr( $content, $cursor );
			$fragment = Content_Normalizer::fragment( 'paragraph', self::fallback_visible_text( $tail ), $source, count( $fragments ) );
			if ( null !== $fragment ) {
				$fragments[] = $fragment;
			}
		}

		return array( 'fragments' => $fragments, 'structure' => $structure, 'warnings' => $warnings );
	}

	private static function fallback_visible_text( string $html ): string {
		$html = preg_replace( '#<br\s*/?>#i', "\n", $html ) ?? $html;
		$html = preg_replace( '#</?(div|section|article|main|header|footer|aside|nav|ul|ol|table|thead|tbody|tfoot)>#i', "\n", $html ) ?? $html;
		$text = strip_tags( $html );
		return html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}

	private static function strip_tags_fallback( string $content ): string {
		if ( function_exists( 'wp_strip_all_tags' ) ) {
			return (string) wp_strip_all_tags( $content, true );
		}
		return strip_tags( $content );
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
