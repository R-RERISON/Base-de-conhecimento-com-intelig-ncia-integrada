<?php
/**
 * Modelo semântico intermediário do Knowledge Document v2.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Semantic_Structure {

	/**
	 * Enriquece fragments com contexto de headings sem alterar a ordem editorial.
	 *
	 * @param array<int,array<string,mixed>> $fragments
	 * @return array<int,array<string,mixed>>
	 */
	public static function sections( array $fragments ): array {
		$out = array();
		$heading_stack = array();

		foreach ( $fragments as $fragment ) {
			if ( ! is_array( $fragment ) ) {
				continue;
			}
			$kind = isset( $fragment['kind'] ) ? (string) $fragment['kind'] : 'paragraph';
			$meta = is_array( $fragment['meta'] ?? null ) ? $fragment['meta'] : array();
			$text = Content_Normalizer::text(
				isset( $fragment['text'] ) ? (string) $fragment['text'] : '',
				'code' === $kind
			);
			$structural_anchor = 'list_item' === $kind && true === ( $meta['structural_anchor'] ?? false );
			if ( '' === $text && ! $structural_anchor ) {
				continue;
			}

			if ( 'heading' === $kind ) {
				$level = max( 1, min( 6, (int) ( $meta['level'] ?? 1 ) ) );
				foreach ( array_keys( $heading_stack ) as $existing_level ) {
					if ( (int) $existing_level >= $level ) {
						unset( $heading_stack[ $existing_level ] );
					}
				}
				$heading_stack[ $level ] = $text;
				ksort( $heading_stack, SORT_NUMERIC );
				$meta['level'] = $level;
			}

			$section = array(
				'kind'         => $kind,
				'heading'      => 'heading' === $kind ? $text : '',
				'text'         => $text,
				'ordinal'      => count( $out ),
				'source'       => isset( $fragment['source'] ) ? (string) $fragment['source'] : '',
				'heading_path' => self::heading_path( $heading_stack ),
			);
			if ( ! empty( $meta ) ) {
				$section['meta'] = $meta;
			}
			$out[] = $section;
		}
		return $out;
	}

	/**
	 * Constrói blocos semanticamente estruturados. Listas aninhadas viram árvore;
	 * tabelas preservam linhas/células, tipos e spans.
	 *
	 * @param array<int,array<string,mixed>> $sections
	 * @return array<int,array<string,mixed>>
	 */
	public static function blocks( array $sections ): array {
		$lists  = self::collect_lists( $sections );
		$tables = self::collect_tables( $sections );
		$out = array();
		$emitted_lists = array();
		$emitted_tables = array();

		foreach ( $sections as $section ) {
			$kind = (string) ( $section['kind'] ?? 'paragraph' );
			$meta = is_array( $section['meta'] ?? null ) ? $section['meta'] : array();

			if ( 'list_item' === $kind ) {
				$list_id = (string) ( $meta['list_id'] ?? '' );
				if ( '' === $list_id || isset( $emitted_lists[ $list_id ] ) ) {
					continue;
				}
				$list = $lists[ $list_id ] ?? null;
				if ( ! is_array( $list ) ) {
					continue;
				}
				// Listas filhas são embutidas no item pai e não emitidas como top-level.
				if ( '' !== (string) ( $list['parent_item_id'] ?? '' ) ) {
					$emitted_lists[ $list_id ] = true;
					continue;
				}
				$block = self::render_list_tree( $list_id, $lists );
				$block['ordinal'] = count( $out );
				$out[] = $block;
				self::mark_list_tree_emitted( $list_id, $lists, $emitted_lists );
				continue;
			}

			if ( in_array( $kind, array( 'table_row', 'table_caption' ), true ) ) {
				$table_id = (string) ( $meta['table_id'] ?? '' );
				if ( '' === $table_id || isset( $emitted_tables[ $table_id ] ) ) {
					continue;
				}
				$table = $tables[ $table_id ] ?? null;
				if ( ! is_array( $table ) ) {
					continue;
				}
				$table['ordinal'] = count( $out );
				$out[] = $table;
				$emitted_tables[ $table_id ] = true;
				continue;
			}

			$out[] = array(
				'kind'         => $kind,
				'ordinal'      => count( $out ),
				'source'       => (string) ( $section['source'] ?? '' ),
				'heading_path' => is_array( $section['heading_path'] ?? null ) ? $section['heading_path'] : array(),
				'text'         => (string) ( $section['text'] ?? '' ),
				'meta'         => $meta,
			);
		}
		return $out;
	}

	/**
	 * Readiness técnico objetivo. Não substitui a aceitação humana G-240.
	 *
	 * @param array<string,mixed> $extraction
	 * @param array<int,array<string,mixed>> $sections
	 * @param array<int,array<string,mixed>> $blocks
	 * @return array<string,mixed>
	 */
	public static function ai_readiness( array $extraction, array $sections, array $blocks ): array {
		$source_kind = (string) ( $extraction['source_kind'] ?? 'empty' );
		if ( 'empty' === $source_kind && empty( $sections ) ) {
			return array(
				'status' => 'not_applicable',
				'reasons' => array( 'SOURCE_EMPTY' ),
				'structure_complete' => true,
			);
		}

		$reasons = array();
		$structure_complete = self::structure_complete( $extraction, $blocks, $reasons );
		$warnings = is_array( $extraction['warnings'] ?? null ) ? array_values( array_map( 'strval', $extraction['warnings'] ) ) : array();

		$critical_prefixes = array(
			'SOURCE_OVERSIZE_HARD:',
			'RENDER_FALLBACK_CANDIDATE',
			'HTML_STRUCTURE_DEGRADED_NO_DOM',
		);
		$review_prefixes = array(
			'SHORTCODE_NOT_EXPANDED:',
			'GUTENBERG_DYNAMIC_NOT_RENDERED:',
			'GUTENBERG_BLOCK_UNSUPPORTED:',
			'ELEMENTOR_WIDGET_UNSUPPORTED:',
			'HTML_LOCAL_HEADING_FLATTENED:',
			'HTML_NESTED_LIST_IN_TABLE_FLATTENED:',
		);

		$critical = false;
		$review = false;
		foreach ( $warnings as $warning ) {
			foreach ( $critical_prefixes as $prefix ) {
				if ( str_starts_with( $warning, $prefix ) ) {
					$critical = true;
					$reasons[] = $warning;
					break;
				}
			}
			foreach ( $review_prefixes as $prefix ) {
				if ( str_starts_with( $warning, $prefix ) ) {
					$review = true;
					$reasons[] = $warning;
					break;
				}
			}
		}

		if ( empty( $sections ) ) {
			$critical = true;
			$reasons[] = 'NO_SEMANTIC_CONTENT';
		}

		$status = 'candidate_ready';
		if ( $critical || ! $structure_complete ) {
			$status = 'not_ready';
		} elseif ( $review ) {
			$status = 'review_required';
		}

		return array(
			'status' => $status,
			'reasons' => array_values( array_unique( $reasons ) ),
			'structure_complete' => $structure_complete,
		);
	}

	/** @param array<int,string> $stack @return array<int,array{level:int,text:string}> */
	private static function heading_path( array $stack ): array {
		$out = array();
		foreach ( $stack as $level => $text ) {
			$out[] = array( 'level' => (int) $level, 'text' => (string) $text );
		}
		return $out;
	}

	/** @param array<int,array<string,mixed>> $sections @return array<string,array<string,mixed>> */
	private static function collect_lists( array $sections ): array {
		$lists = array();
		foreach ( $sections as $section ) {
			if ( 'list_item' !== (string) ( $section['kind'] ?? '' ) ) {
				continue;
			}
			$meta = is_array( $section['meta'] ?? null ) ? $section['meta'] : array();
			$list_id = (string) ( $meta['list_id'] ?? '' );
			if ( '' === $list_id ) {
				continue;
			}
			if ( ! isset( $lists[ $list_id ] ) ) {
				$lists[ $list_id ] = array(
					'kind'           => 'list',
					'list_id'        => $list_id,
					'list_type'      => (string) ( $meta['list_type'] ?? 'unknown' ),
					'depth'          => max( 0, (int) ( $meta['depth'] ?? 0 ) ),
					'parent_item_id' => (string) ( $meta['parent_item_id'] ?? '' ),
					'source'         => (string) ( $section['source'] ?? '' ),
					'heading_path'   => is_array( $section['heading_path'] ?? null ) ? $section['heading_path'] : array(),
					'items'          => array(),
				);
			}
			$lists[ $list_id ]['items'][] = array(
				'item_id'        => (string) ( $meta['item_id'] ?? '' ),
				'item_index'     => max( 0, (int) ( $meta['item_index'] ?? 0 ) ),
				'parent_item_id' => (string) ( $meta['parent_item_id'] ?? '' ),
				'text'           => (string) ( $section['text'] ?? '' ),
				'children'       => array(),
			);
		}
		return $lists;
	}

	/** @param array<string,array<string,mixed>> $lists @return array<string,mixed> */
	private static function render_list_tree( string $list_id, array $lists ): array {
		$list = $lists[ $list_id ];
		foreach ( $list['items'] as &$item ) {
			$item_id = (string) ( $item['item_id'] ?? '' );
			foreach ( $lists as $child_id => $candidate ) {
				if ( (string) ( $candidate['parent_item_id'] ?? '' ) === $item_id ) {
					$item['children'][] = self::render_list_tree( (string) $child_id, $lists );
				}
			}
		}
		unset( $item );
		unset( $list['parent_item_id'] );
		return $list;
	}

	/** @param array<string,array<string,mixed>> $lists @param array<string,bool> $emitted */
	private static function mark_list_tree_emitted( string $list_id, array $lists, array &$emitted ): void {
		$emitted[ $list_id ] = true;
		$list = $lists[ $list_id ] ?? array();
		foreach ( (array) ( $list['items'] ?? array() ) as $item ) {
			$item_id = (string) ( $item['item_id'] ?? '' );
			foreach ( $lists as $child_id => $candidate ) {
				if ( (string) ( $candidate['parent_item_id'] ?? '' ) === $item_id ) {
					self::mark_list_tree_emitted( (string) $child_id, $lists, $emitted );
				}
			}
		}
	}

	/** @param array<int,array<string,mixed>> $sections @return array<string,array<string,mixed>> */
	private static function collect_tables( array $sections ): array {
		$tables = array();
		foreach ( $sections as $section ) {
			$kind = (string) ( $section['kind'] ?? '' );
			if ( ! in_array( $kind, array( 'table_row', 'table_caption' ), true ) ) {
				continue;
			}
			$meta = is_array( $section['meta'] ?? null ) ? $section['meta'] : array();
			$table_id = (string) ( $meta['table_id'] ?? '' );
			if ( '' === $table_id ) {
				continue;
			}
			if ( ! isset( $tables[ $table_id ] ) ) {
				$tables[ $table_id ] = array(
					'kind'         => 'table',
					'table_id'     => $table_id,
					'source'       => (string) ( $section['source'] ?? '' ),
					'heading_path' => is_array( $section['heading_path'] ?? null ) ? $section['heading_path'] : array(),
					'caption'      => '',
					'rows'         => array(),
				);
			}
			if ( 'table_caption' === $kind ) {
				$tables[ $table_id ]['caption'] = (string) ( $section['text'] ?? '' );
				continue;
			}
			$tables[ $table_id ]['rows'][] = array(
				'row_index' => max( 0, (int) ( $meta['row_index'] ?? 0 ) ),
				'cells'     => is_array( $meta['cells'] ?? null ) ? $meta['cells'] : array(),
			);
		}
		return $tables;
	}

	/** @param array<string,mixed> $extraction @param array<int,array<string,mixed>> $blocks @param array<int,string> $reasons */
	private static function structure_complete( array $extraction, array $blocks, array &$reasons ): bool {
		$expected = is_array( $extraction['structure'] ?? null ) ? $extraction['structure'] : array();
		$actual = array( 'headings' => 0, 'lists' => 0, 'list_items' => 0, 'tables' => 0, 'table_rows' => 0, 'table_cells' => 0 );
		foreach ( $blocks as $block ) {
			$kind = (string) ( $block['kind'] ?? '' );
			if ( 'heading' === $kind ) {
				++$actual['headings'];
			} elseif ( 'list' === $kind ) {
				self::count_list_tree( $block, $actual );
			} elseif ( 'table' === $kind ) {
				++$actual['tables'];
				$rows = is_array( $block['rows'] ?? null ) ? $block['rows'] : array();
				$actual['table_rows'] += count( $rows );
				foreach ( $rows as $row ) {
					$actual['table_cells'] += is_array( $row['cells'] ?? null ) ? count( $row['cells'] ) : 0;
				}
			}
		}

		$complete = true;
		foreach ( array_keys( $actual ) as $key ) {
			if ( ! array_key_exists( $key, $expected ) ) {
				continue;
			}
			if ( (int) $expected[ $key ] !== (int) $actual[ $key ] ) {
				$complete = false;
				$reasons[] = 'STRUCTURE_COUNT_MISMATCH:' . $key . ':' . (int) $expected[ $key ] . ':' . (int) $actual[ $key ];
			}
		}
		return $complete;
	}

	/** @param array<string,mixed> $list @param array<string,int> $actual */
	private static function count_list_tree( array $list, array &$actual ): void {
		++$actual['lists'];
		$items = is_array( $list['items'] ?? null ) ? $list['items'] : array();
		$actual['list_items'] += count( $items );
		foreach ( $items as $item ) {
			foreach ( (array) ( $item['children'] ?? array() ) as $child ) {
				if ( is_array( $child ) ) {
					self::count_list_tree( $child, $actual );
				}
		}
	}
}
