<?php
/**
 * Calcula expectativas estruturais semânticas diretamente do DOM bruto.
 * Mantém a validação independente dos fragments/blocks emitidos.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Semantic_DOM_Expectation {

	/**
	 * Refina apenas contagens semanticamente materializáveis de headings/lists/tables.
	 * Demais métricas permanecem as produzidas pelo adapter.
	 *
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $result
	 * @return array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>}
	 */
	public static function apply( string $html, array $result ): array {
		if ( '' === trim( $html ) || ! class_exists( '\\DOMDocument' ) ) {
			return $result;
		}

		$previous = libxml_use_internal_errors( true );
		libxml_clear_errors();
		$dom = new \DOMDocument( '1.0', 'UTF-8' );
		$wrapped = '<!DOCTYPE html><html><body><div id="bdc-kb-semantic-root">' . $html . '</div></body></html>';
		$loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_NONET | LIBXML_COMPACT );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		if ( ! $loaded ) {
			return $result;
		}

		$xpath = new \DOMXPath( $dom );
		$nodes = $xpath->query( '//*[@id="bdc-kb-semantic-root"]' );
		$root = ( $nodes && $nodes->length > 0 ) ? $nodes->item( 0 ) : null;
		if ( ! $root instanceof \DOMElement ) {
			return $result;
		}

		self::remove_excluded_nodes( $xpath, $root );
		$warnings = is_array( $result['warnings'] ?? null ) ? $result['warnings'] : array();
		$structure = is_array( $result['structure'] ?? null ) ? $result['structure'] : Legacy_HTML_Adapter::empty_structure();

		$heading = self::heading_expectation( $xpath, $root );
		$structure['headings'] = $heading['semantic'];
		if ( $heading['empty'] > 0 ) {
			$warnings[] = 'HTML_SEMANTIC_EMPTY_HEADING_IGNORED:' . $heading['empty'];
		}
		if ( $heading['local'] > 0 ) {
			$warnings[] = 'HTML_LOCAL_HEADING_FLATTENED:' . $heading['local'];
		}

		$list = self::list_expectation( $xpath, $root );
		$structure['lists'] = $list['semantic'];
		if ( $list['empty'] > 0 ) {
			$warnings[] = 'HTML_SEMANTIC_EMPTY_LIST_IGNORED:' . $list['empty'];
		}
		if ( $list['in_table'] > 0 ) {
			$warnings[] = 'HTML_NESTED_LIST_IN_TABLE_FLATTENED:' . $list['in_table'];
		}

		$table = self::table_expectation( $xpath, $root );
		$structure['tables'] = $table['semantic'];
		if ( $table['empty'] > 0 ) {
			$warnings[] = 'HTML_SEMANTIC_EMPTY_TABLE_IGNORED:' . $table['empty'];
		}
		if ( $table['nested'] > 0 ) {
			$warnings[] = 'HTML_NESTED_TABLE_UNREPRESENTED:' . $table['nested'];
		}

		$result['structure'] = $structure;
		$result['warnings']  = self::unique_preserve_order( $warnings );
		return $result;
	}

	private static function remove_excluded_nodes( \DOMXPath $xpath, \DOMElement $root ): void {
		$nodes = $xpath->query( './/script|.//style|.//noscript', $root );
		if ( ! $nodes ) {
			return;
		}
		$remove = array();
		foreach ( $nodes as $node ) {
			$remove[] = $node;
		}
		foreach ( $remove as $node ) {
			if ( $node->parentNode ) {
				$node->parentNode->removeChild( $node );
			}
		}
	}

	/** @return array{semantic:int,empty:int,local:int} */
	private static function heading_expectation( \DOMXPath $xpath, \DOMElement $root ): array {
		$out = array( 'semantic' => 0, 'empty' => 0, 'local' => 0 );
		$nodes = $xpath->query( './/h1|.//h2|.//h3|.//h4|.//h5|.//h6', $root );
		if ( ! $nodes ) {
			return $out;
		}
		foreach ( $nodes as $node ) {
			if ( ! $node instanceof \DOMElement ) {
				continue;
			}
			$text = Content_Normalizer::text( (string) $node->textContent );
			if ( '' === $text ) {
				++$out['empty'];
				continue;
			}
			if ( self::has_ancestor_tag( $node, $root, array( 'p', 'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'table' ) ) ) {
				++$out['local'];
				continue;
			}
			++$out['semantic'];
		}
		return $out;
	}

	/** @return array{semantic:int,empty:int,in_table:int} */
	private static function list_expectation( \DOMXPath $xpath, \DOMElement $root ): array {
		$out = array( 'semantic' => 0, 'empty' => 0, 'in_table' => 0 );
		$nodes = $xpath->query( './/ul|.//ol', $root );
		if ( ! $nodes ) {
			return $out;
		}
		foreach ( $nodes as $node ) {
			if ( ! $node instanceof \DOMElement ) {
				continue;
			}
			if ( self::has_ancestor_tag( $node, $root, array( 'table' ) ) ) {
				++$out['in_table'];
				continue;
			}
			if ( ! self::has_direct_child_tag( $node, 'li' ) ) {
				++$out['empty'];
				continue;
			}
			++$out['semantic'];
		}
		return $out;
	}

	/** @return array{semantic:int,empty:int,nested:int} */
	private static function table_expectation( \DOMXPath $xpath, \DOMElement $root ): array {
		$out = array( 'semantic' => 0, 'empty' => 0, 'nested' => 0 );
		$nodes = $xpath->query( './/table', $root );
		if ( ! $nodes ) {
			return $out;
		}
		foreach ( $nodes as $node ) {
			if ( ! $node instanceof \DOMElement ) {
				continue;
			}
			if ( self::has_ancestor_tag( $node, $root, array( 'table' ) ) ) {
				++$out['nested'];
				continue;
			}
			if ( 0 === self::direct_table_row_count( $node ) ) {
				++$out['empty'];
				continue;
			}
			++$out['semantic'];
		}
		return $out;
	}

	/** @param array<int,string> $tags */
	private static function has_ancestor_tag( \DOMNode $node, \DOMElement $root, array $tags ): bool {
		$ancestor = $node->parentNode;
		while ( $ancestor instanceof \DOMElement && $ancestor !== $root ) {
			if ( in_array( strtolower( $ancestor->tagName ), $tags, true ) ) {
				return true;
			}
			$ancestor = $ancestor->parentNode;
		}
		return false;
	}

	private static function has_direct_child_tag( \DOMElement $node, string $tag ): bool {
		foreach ( $node->childNodes as $child ) {
			if ( $child instanceof \DOMElement && $tag === strtolower( $child->tagName ) ) {
				return true;
			}
		}
		return false;
	}

	private static function direct_table_row_count( \DOMElement $table ): int {
		$count = 0;
		foreach ( $table->childNodes as $child ) {
			if ( ! $child instanceof \DOMElement ) {
				continue;
			}
			$tag = strtolower( $child->tagName );
			if ( 'tr' === $tag ) {
				++$count;
				continue;
			}
			if ( in_array( $tag, array( 'thead', 'tbody', 'tfoot' ), true ) ) {
				foreach ( $child->childNodes as $row ) {
					if ( $row instanceof \DOMElement && 'tr' === strtolower( $row->tagName ) ) {
						++$count;
					}
				}
			}
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
