<?php
/**
 * Relationship fidelity for Knowledge Document 2.1.0.
 *
 * Compares structural relationships observable in raw HTML against the
 * materialized fragment graph. No editorial content is persisted or emitted.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Hierarchy_Relationships {

	/**
	 * @param array<string,mixed>              $source Content_Source::inspect() result.
	 * @param array<int,array<string,mixed>>   $fragments Extracted fragments.
	 * @param array<int,string>                $strategies Extraction strategies.
	 * @return array<string,mixed>
	 */
	public static function evaluate( array $source, array $fragments, array $strategies ): array {
		$expected_forests = array();
		$actual_fragments = array();
		$enforce_headings = false;
		$source_count = 0;

		if ( in_array( 'legacy_html', $strategies, true ) ) {
			$html = isset( $source['post_content'] ) ? (string) $source['post_content'] : '';
			if ( '' !== trim( $html ) ) {
				$forest = self::html_forest( $html );
				if ( null !== $forest ) {
					$expected_forests[] = $forest;
					++$source_count;
					$enforce_headings = true;
				}
			}
			$actual_fragments = array_values(
				array_filter(
					$fragments,
					static fn ( array $fragment ): bool => 'post_content' === (string) ( $fragment['source'] ?? '' )
				)
			);
		} elseif ( in_array( 'elementor', $strategies, true ) ) {
			$data = is_array( $source['elementor_data'] ?? null ) ? $source['elementor_data'] : array();
			$html_sources = self::elementor_html_sources( $data );
			foreach ( $html_sources as $html ) {
				$forest = self::html_forest( $html );
				if ( null !== $forest ) {
					$expected_forests[] = $forest;
					++$source_count;
				}
			}
			$enforce_headings = 1 === $source_count;
			$actual_fragments = array_values(
				array_filter(
					$fragments,
					static fn ( array $fragment ): bool => str_starts_with( (string) ( $fragment['source'] ?? '' ), 'elementor:' )
				)
			);
		}

		if ( 0 === $source_count ) {
			$empty = self::empty_metrics();
			return array(
				'applicable'       => false,
				'source_count'     => 0,
				'heading_enforced' => false,
				'expected'         => $empty,
				'actual'           => $empty,
				'complete'         => true,
				'reasons'          => array(),
			);
		}

		$expected = self::aggregate_forests( $expected_forests );
		$actual   = self::fragment_metrics( $actual_fragments );
		$reasons  = self::compare_metrics( $expected, $actual, $enforce_headings );

		return array(
			'applicable'       => true,
			'source_count'     => $source_count,
			'heading_enforced' => $enforce_headings,
			'expected'         => $expected,
			'actual'           => $actual,
			'complete'         => empty( $reasons ),
			'reasons'          => $reasons,
		);
	}

	/**
	 * Unit-testable single HTML comparison.
	 *
	 * @param array<int,array<string,mixed>> $fragments
	 * @return array<string,mixed>
	 */
	public static function compare_html( string $html, array $fragments, string $source_name = 'post_content' ): array {
		$forest = self::html_forest( $html );
		if ( null === $forest ) {
			$empty = self::empty_metrics();
			return array(
				'applicable'       => false,
				'source_count'     => 0,
				'heading_enforced' => false,
				'expected'         => $empty,
				'actual'           => $empty,
				'complete'         => true,
				'reasons'          => array(),
			);
		}
		$filtered = array_values(
			array_filter(
				$fragments,
				static fn ( array $fragment ): bool => $source_name === (string) ( $fragment['source'] ?? '' )
			)
		);
		$expected = self::aggregate_forests( array( $forest ) );
		$actual = self::fragment_metrics( $filtered );
		$reasons = self::compare_metrics( $expected, $actual, true );
		return array(
			'applicable'       => true,
			'source_count'     => 1,
			'heading_enforced' => true,
			'expected'         => $expected,
			'actual'           => $actual,
			'complete'         => empty( $reasons ),
			'reasons'          => $reasons,
		);
	}

	/** @return array<int,string> */
	private static function elementor_html_sources( array $data ): array {
		$out = array();
		self::walk_elementor( $data, $out );
		return $out;
	}

	/** @param array<int|string,mixed> $nodes @param array<int,string> $out */
	private static function walk_elementor( array $nodes, array &$out ): void {
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				continue;
			}
			$el_type = isset( $node['elType'] ) && is_string( $node['elType'] ) ? $node['elType'] : '';
			$widget_type = isset( $node['widgetType'] ) && is_string( $node['widgetType'] ) ? $node['widgetType'] : '';
			$settings = is_array( $node['settings'] ?? null ) ? $node['settings'] : array();
			if ( 'widget' === $el_type || '' !== $widget_type ) {
				if ( 'text-editor' === $widget_type ) {
					$editor = isset( $settings['editor'] ) && is_scalar( $settings['editor'] ) ? (string) $settings['editor'] : '';
					if ( '' !== trim( $editor ) ) {
						$out[] = $editor;
					}
				} elseif ( 'shortcode' === $widget_type ) {
					$shortcode = isset( $settings['shortcode'] ) && is_scalar( $settings['shortcode'] ) ? (string) $settings['shortcode'] : '';
					$inspection = Shortcode_Inspector::inspect( $shortcode );
					foreach ( (array) ( $inspection['matches'] ?? array() ) as $match ) {
						$inner = isset( $match['inner'] ) ? (string) $match['inner'] : '';
						if ( '' !== trim( $inner ) ) {
							$out[] = $inner;
						}
					}
				}
			}
			if ( isset( $node['elements'] ) && is_array( $node['elements'] ) ) {
				self::walk_elementor( $node['elements'], $out );
			}
		}
	}

	/**
	 * @return array{lists:array<int,array<string,mixed>>,heading_levels:array<int,int>}|null
	 */
	private static function html_forest( string $html ): ?array {
		if ( '' === trim( $html ) || ! class_exists( '\\DOMDocument' ) ) {
			return null;
		}
		$previous = libxml_use_internal_errors( true );
		libxml_clear_errors();
		$dom = new \DOMDocument( '1.0', 'UTF-8' );
		$wrapped = '<!DOCTYPE html><html><body><div id="bdc-kb-hierarchy-root">' . $html . '</div></body></html>';
		$loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_NONET | LIBXML_COMPACT );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		if ( ! $loaded ) {
			return null;
		}
		$xpath = new \DOMXPath( $dom );
		$nodes = $xpath->query( '//*[@id="bdc-kb-hierarchy-root"]' );
		$root = ( $nodes && $nodes->length > 0 ) ? $nodes->item( 0 ) : null;
		if ( ! $root instanceof \DOMElement ) {
			return null;
		}
		self::remove_excluded_nodes( $xpath, $root );

		$list_roots = array();
		$list_nodes = $xpath->query( './/ul|.//ol', $root );
		if ( $list_nodes ) {
			foreach ( $list_nodes as $list ) {
				if ( ! $list instanceof \DOMElement || self::has_ancestor_tag( $list, $root, array( 'table' ) ) ) {
					continue;
				}
				if ( ! self::has_direct_li( $list ) || self::has_ancestor_tag( $list, $root, array( 'ul', 'ol' ) ) ) {
					continue;
				}
				$list_roots[] = self::dom_list_shape( $list );
			}
		}

		$heading_levels = array();
		$heading_nodes = $xpath->query( './/h1|.//h2|.//h3|.//h4|.//h5|.//h6', $root );
		if ( $heading_nodes ) {
			foreach ( $heading_nodes as $heading ) {
				if ( ! $heading instanceof \DOMElement ) {
					continue;
				}
				$text = Content_Normalizer::text( (string) $heading->textContent );
				if ( '' === $text || self::has_ancestor_tag( $heading, $root, array( 'p', 'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'table' ) ) ) {
					continue;
				}
				if ( preg_match( '/^h([1-6])$/i', $heading->tagName, $match ) ) {
					$heading_levels[] = (int) $match[1];
				}
			}
		}
		return array( 'lists' => $list_roots, 'heading_levels' => $heading_levels );
	}

	/** @return array<string,mixed> */
	private static function dom_list_shape( \DOMElement $list ): array {
		$shape = array(
			'type'  => 'ol' === strtolower( $list->tagName ) ? 'ordered' : 'unordered',
			'items' => array(),
		);
		foreach ( $list->childNodes as $child ) {
			if ( ! $child instanceof \DOMElement || 'li' !== strtolower( $child->tagName ) ) {
				continue;
			}
			$item = array( 'children' => array() );
			self::collect_nested_lists( $child, $item['children'] );
			$shape['items'][] = $item;
		}
		return $shape;
	}

	/** @param array<int,array<string,mixed>> $out */
	private static function collect_nested_lists( \DOMElement $node, array &$out ): void {
		foreach ( $node->childNodes as $child ) {
			if ( ! $child instanceof \DOMElement ) {
				continue;
			}
			$tag = strtolower( $child->tagName );
			if ( 'table' === $tag ) {
				continue;
			}
			if ( in_array( $tag, array( 'ul', 'ol' ), true ) ) {
				if ( self::has_direct_li( $child ) ) {
					$out[] = self::dom_list_shape( $child );
				}
				continue;
			}
			self::collect_nested_lists( $child, $out );
		}
	}

	/** @param array<int,array{lists:array<int,array<string,mixed>>,heading_levels:array<int,int>}> $forests @return array<string,mixed> */
	private static function aggregate_forests( array $forests ): array {
		$all_lists = array();
		$heading_metrics = array();
		foreach ( $forests as $forest ) {
			foreach ( (array) ( $forest['lists'] ?? array() ) as $list ) {
				if ( is_array( $list ) ) {
					$all_lists[] = $list;
				}
			}
			$heading_metrics[] = self::heading_metrics( is_array( $forest['heading_levels'] ?? null ) ? $forest['heading_levels'] : array() );
		}
		$list_metrics = self::list_metrics( $all_lists );
		$heading_parent_edges = 0;
		$heading_path_transitions = 0;
		$heading_signatures = array();
		foreach ( $heading_metrics as $metrics ) {
			$heading_parent_edges += (int) $metrics['heading_parent_edges'];
			$heading_path_transitions += (int) $metrics['heading_path_transitions'];
			$heading_signatures[] = (string) $metrics['heading_tree_signature'];
		}
		$list_metrics['heading_parent_edges'] = $heading_parent_edges;
		$list_metrics['heading_path_transitions'] = $heading_path_transitions;
		$list_metrics['heading_tree_signature'] = 1 === count( $heading_metrics )
			? (string) $heading_metrics[0]['heading_tree_signature']
			: self::signature( $heading_signatures );
		return $list_metrics;
	}

	/** @param array<int,array<string,mixed>> $fragments @return array<string,mixed> */
	private static function fragment_metrics( array $fragments ): array {
		$lists = array();
		$list_order = array();
		$item_to_list = array();
		$heading_levels = array();

		foreach ( $fragments as $position => $fragment ) {
			$kind = (string) ( $fragment['kind'] ?? '' );
			$meta = is_array( $fragment['meta'] ?? null ) ? $fragment['meta'] : array();
			if ( 'heading' === $kind ) {
				$heading_levels[] = max( 1, min( 6, (int) ( $meta['level'] ?? 1 ) ) );
				continue;
			}
			if ( 'list_item' !== $kind ) {
				continue;
			}
			$list_id = (string) ( $meta['list_id'] ?? '' );
			if ( '' === $list_id ) {
				continue;
			}
			if ( ! isset( $lists[ $list_id ] ) ) {
				$lists[ $list_id ] = array(
					'type'           => (string) ( $meta['list_type'] ?? 'unknown' ),
					'parent_item_id' => (string) ( $meta['parent_item_id'] ?? '' ),
					'first_position' => (int) $position,
					'items'          => array(),
				);
				$list_order[] = $list_id;
			}
			$item_id = (string) ( $meta['item_id'] ?? '' );
			$lists[ $list_id ]['items'][] = array(
				'item_id'    => $item_id,
				'item_index' => max( 0, (int) ( $meta['item_index'] ?? 0 ) ),
				'position'   => (int) $position,
			);
			if ( '' !== $item_id ) {
				$item_to_list[ $item_id ] = $list_id;
			}
		}

		foreach ( $lists as &$list ) {
			usort(
				$list['items'],
				static function ( array $a, array $b ): int {
					$by_index = (int) $a['item_index'] <=> (int) $b['item_index'];
					return 0 !== $by_index ? $by_index : (int) $a['position'] <=> (int) $b['position'];
				}
			);
		}
		unset( $list );

		$roots = array();
		foreach ( $list_order as $list_id ) {
			$parent_item_id = (string) ( $lists[ $list_id ]['parent_item_id'] ?? '' );
			if ( '' === $parent_item_id || ! isset( $item_to_list[ $parent_item_id ] ) ) {
				$roots[] = self::fragment_list_shape( $list_id, $lists, array() );
			}
		}

		$metrics = self::list_metrics( $roots );
		$metrics = array_merge( $metrics, self::heading_metrics( $heading_levels ) );
		return $metrics;
	}

	/** @param array<string,array<string,mixed>> $lists @param array<string,bool> $visited @return array<string,mixed> */
	private static function fragment_list_shape( string $list_id, array $lists, array $visited ): array {
		if ( isset( $visited[ $list_id ] ) || ! isset( $lists[ $list_id ] ) ) {
			return array( 'type' => 'cycle', 'items' => array() );
		}
		$visited[ $list_id ] = true;
		$list = $lists[ $list_id ];
		$shape = array( 'type' => (string) ( $list['type'] ?? 'unknown' ), 'items' => array() );
		foreach ( (array) ( $list['items'] ?? array() ) as $item ) {
			$item_shape = array( 'children' => array() );
			$item_id = (string) ( $item['item_id'] ?? '' );
			foreach ( $lists as $child_id => $candidate ) {
				if ( '' !== $item_id && (string) ( $candidate['parent_item_id'] ?? '' ) === $item_id ) {
					$item_shape['children'][] = self::fragment_list_shape( (string) $child_id, $lists, $visited );
				}
			}
			$shape['items'][] = $item_shape;
		}
		return $shape;
	}

	/** @param array<int,array<string,mixed>> $roots @return array<string,mixed> */
	private static function list_metrics( array $roots ): array {
		$edges = 0;
		$max_depth = 0;
		$sibling_projection = array();
		foreach ( $roots as $root_index => $root ) {
			self::walk_list_metrics( $root, 0, 'r' . $root_index, $edges, $max_depth, $sibling_projection );
		}
		return array(
			'list_parent_edges'             => $edges,
			'list_root_count'               => count( $roots ),
			'list_max_depth'                => empty( $roots ) ? 0 : $max_depth,
			'list_sibling_order_signature'  => self::signature( $sibling_projection ),
			'list_tree_signature'           => self::signature( $roots ),
			'heading_path_transitions'      => 0,
			'heading_parent_edges'          => 0,
			'heading_tree_signature'        => self::signature( array() ),
		);
	}

	/** @param array<int,array<string,mixed>> $siblings */
	private static function walk_list_metrics( array $list, int $depth, string $path, int &$edges, int &$max_depth, array &$siblings ): void {
		$max_depth = max( $max_depth, $depth );
		$item_projection = array();
		foreach ( (array) ( $list['items'] ?? array() ) as $item_index => $item ) {
			$child_types = array();
			foreach ( (array) ( $item['children'] ?? array() ) as $child_index => $child ) {
				if ( ! is_array( $child ) ) {
					continue;
				}
				++$edges;
				$child_types[] = (string) ( $child['type'] ?? 'unknown' );
				self::walk_list_metrics( $child, $depth + 1, $path . '.i' . $item_index . '.c' . $child_index, $edges, $max_depth, $siblings );
			}
			$item_projection[] = $child_types;
		}
		$siblings[] = array( 'path' => $path, 'type' => (string) ( $list['type'] ?? 'unknown' ), 'items' => $item_projection );
	}

	/** @param array<int,int> $levels @return array<string,mixed> */
	private static function heading_metrics( array $levels ): array {
		$stack = array();
		$nodes = array();
		$edges = 0;
		$transitions = 0;
		$previous_parent_path = null;
		foreach ( $levels as $index => $level ) {
			while ( ! empty( $stack ) && (int) end( $stack )['level'] >= $level ) {
				array_pop( $stack );
			}
			$parent = empty( $stack ) ? null : (int) end( $stack )['index'];
			if ( null !== $parent ) {
				++$edges;
			}
			$parent_path = array_map( static fn ( array $node ): int => (int) $node['level'], $stack );
			if ( null !== $previous_parent_path && $previous_parent_path !== $parent_path ) {
				++$transitions;
			}
			$nodes[] = array( 'level' => $level, 'parent' => $parent );
			$stack[] = array( 'index' => $index, 'level' => $level );
			$previous_parent_path = $parent_path;
		}
		return array(
			'heading_path_transitions' => $transitions,
			'heading_parent_edges'     => $edges,
			'heading_tree_signature'   => self::signature( $nodes ),
		);
	}

	/** @return array<int,string> */
	private static function compare_metrics( array $expected, array $actual, bool $enforce_headings ): array {
		$reasons = array();
		foreach ( array( 'list_parent_edges', 'list_root_count' ) as $key ) {
			if ( (int) ( $expected[ $key ] ?? 0 ) !== (int) ( $actual[ $key ] ?? 0 ) ) {
				$reasons[] = 'HIERARCHY_EDGE_MISMATCH:' . $key . ':' . (int) ( $expected[ $key ] ?? 0 ) . ':' . (int) ( $actual[ $key ] ?? 0 );
			}
		}
		if ( (int) ( $expected['list_max_depth'] ?? 0 ) !== (int) ( $actual['list_max_depth'] ?? 0 ) ) {
			$reasons[] = 'HIERARCHY_DEPTH_MISMATCH:list_max_depth:' . (int) ( $expected['list_max_depth'] ?? 0 ) . ':' . (int) ( $actual['list_max_depth'] ?? 0 );
		}
		foreach ( array( 'list_sibling_order_signature', 'list_tree_signature' ) as $key ) {
			if ( (string) ( $expected[ $key ] ?? '' ) !== (string) ( $actual[ $key ] ?? '' ) ) {
				$reasons[] = 'HIERARCHY_ORDER_MISMATCH:' . $key . ':' . self::short_hash( (string) ( $expected[ $key ] ?? '' ) ) . ':' . self::short_hash( (string) ( $actual[ $key ] ?? '' ) );
			}
		}
		if ( $enforce_headings ) {
			if ( (int) ( $expected['heading_parent_edges'] ?? 0 ) !== (int) ( $actual['heading_parent_edges'] ?? 0 ) ) {
				$reasons[] = 'HIERARCHY_EDGE_MISMATCH:heading_parent_edges:' . (int) ( $expected['heading_parent_edges'] ?? 0 ) . ':' . (int) ( $actual['heading_parent_edges'] ?? 0 );
			}
			if ( (int) ( $expected['heading_path_transitions'] ?? 0 ) !== (int) ( $actual['heading_path_transitions'] ?? 0 )
				|| (string) ( $expected['heading_tree_signature'] ?? '' ) !== (string) ( $actual['heading_tree_signature'] ?? '' ) ) {
				$reasons[] = 'HIERARCHY_ORDER_MISMATCH:heading_tree_signature:' . self::short_hash( (string) ( $expected['heading_tree_signature'] ?? '' ) ) . ':' . self::short_hash( (string) ( $actual['heading_tree_signature'] ?? '' ) );
			}
		}
		return array_values( array_unique( $reasons ) );
	}

	/** @return array<string,mixed> */
	private static function empty_metrics(): array {
		return array(
			'list_parent_edges'            => 0,
			'list_root_count'              => 0,
			'list_max_depth'               => 0,
			'list_sibling_order_signature' => self::signature( array() ),
			'list_tree_signature'          => self::signature( array() ),
			'heading_path_transitions'     => 0,
			'heading_parent_edges'         => 0,
			'heading_tree_signature'       => self::signature( array() ),
		);
	}

	private static function signature( mixed $value ): string {
		$json = json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION );
		return hash( 'sha256', is_string( $json ) ? $json : '' );
	}

	private static function short_hash( string $hash ): string {
		return substr( $hash, 0, 12 );
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

	private static function has_direct_li( \DOMElement $list ): bool {
		foreach ( $list->childNodes as $child ) {
			if ( $child instanceof \DOMElement && 'li' === strtolower( $child->tagName ) ) {
				return true;
			}
		}
		return false;
	}

	/** @param array<int,string> $tags */
	private static function has_ancestor_tag( \DOMElement $node, \DOMElement $root, array $tags ): bool {
		$ancestor = $node->parentNode;
		while ( $ancestor instanceof \DOMElement && $ancestor !== $root ) {
			if ( in_array( strtolower( $ancestor->tagName ), $tags, true ) ) {
				return true;
			}
			$ancestor = $ancestor->parentNode;
		}
		return false;
	}
}
