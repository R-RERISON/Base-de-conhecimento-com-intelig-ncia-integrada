<?php
/**
 * R-260D1 — read-only contextual anchor feasibility profiler.
 *
 * Attempts to disambiguate paragraph-derived structural targets using the
 * exact rendered title plus two exact adjacent body blocks derived from the
 * source fragment sequence. Diagnostic only: no anchor injection, no Search
 * projection mutation and no editorial write.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class R260_Contextual_Anchor_Feasibility_Profiler {

	public const VERSION = 'r260-contextual-anchor-feasibility-v1.0.0';
	private const CONTEXT_PARTS_REQUIRED = 2;
	private const MAX_SAMPLES_PER_POST = 8;
	private const CONTEXT_SOURCE_KINDS = array(
		'paragraph',
		'list_item',
		'table_caption',
		'table_row',
		'quote',
		'code',
	);
	private const RENDERED_LEAF_TAGS = array( 'p', 'li', 'tr', 'pre', 'figcaption', 'caption' );

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,mixed>            $hierarchy
	 * @param array<int,array<string,mixed>> $classified_candidates
	 * @return array<string,mixed>
	 */
	public static function profile(
		int $post_id,
		string $source_kind,
		string $rendered_html,
		array $fragments,
		array $hierarchy,
		array $classified_candidates
	): array {
		$states = array(
			'title_unique' => 0,
			'context_unique' => 0,
			'context_ambiguous' => 0,
			'context_insufficient' => 0,
			'context_not_matched' => 0,
			'title_not_rendered' => 0,
		);
		$samples = array();
		$promotable_count = 0;
		$rendered_blocks = self::rendered_blocks( $rendered_html );
		$boundaries = self::boundary_ordinals( $fragments, $hierarchy );

		foreach ( $classified_candidates as $candidate ) {
			if ( ! is_array( $candidate ) || 'promotable_shadow' !== (string) ( $candidate['state'] ?? '' ) ) {
				continue;
			}

			$ordinal = (int) ( $candidate['ordinal'] ?? -1 );
			$title_norm = (string) ( $candidate['title_norm'] ?? '' );
			if ( $ordinal < 0 || '' === $title_norm ) {
				continue;
			}
			++$promotable_count;

			$title_indexes = self::rendered_title_indexes( $rendered_blocks, $title_norm );
			$title_match_count = count( $title_indexes );
			$context_parts = self::source_context_parts( $fragments, $boundaries, $ordinal );
			$contextual_match_count = 0;

			if ( 1 === $title_match_count ) {
				$state = 'title_unique';
			} elseif ( 0 === $title_match_count ) {
				$state = 'title_not_rendered';
			} elseif ( count( $context_parts ) < self::CONTEXT_PARTS_REQUIRED ) {
				$state = 'context_insufficient';
			} else {
				foreach ( $title_indexes as $title_index ) {
					if ( self::context_matches_after( $rendered_blocks, $title_index, $context_parts ) ) {
						++$contextual_match_count;
					}
				}

				if ( 1 === $contextual_match_count ) {
					$state = 'context_unique';
				} elseif ( $contextual_match_count > 1 ) {
					$state = 'context_ambiguous';
				} else {
					$state = 'context_not_matched';
				}
			}
			++$states[ $state ];

			if ( count( $samples ) < self::MAX_SAMPLES_PER_POST ) {
				$samples[] = array(
					'ordinal' => $ordinal,
					'token' => (string) ( $candidate['token'] ?? '' ),
					'text_hash' => hash( 'sha256', $title_norm ),
					'state' => $state,
					'title_match_count' => $title_match_count,
					'context_part_count' => count( $context_parts ),
					'context_hash' => empty( $context_parts )
						? ''
						: hash( 'sha256', implode( "\n", $context_parts ) ),
					'contextual_match_count' => $contextual_match_count,
				);
			}
		}

		$effective_unique = (int) $states['title_unique'] + (int) $states['context_unique'];
		$unresolved = max( 0, $promotable_count - $effective_unique );

		return array(
			'version' => self::VERSION,
			'post_id' => $post_id,
			'source_kind' => $source_kind,
			'promotable_count' => $promotable_count,
			'states' => $states,
			'context_parts_required' => self::CONTEXT_PARTS_REQUIRED,
			'effective_unique_count' => $effective_unique,
			'effective_unique_rate' => $promotable_count > 0 ? (float) $effective_unique / $promotable_count : 0.0,
			'context_resolved_count' => (int) $states['context_unique'],
			'unresolved_remaining_count' => $unresolved,
			'samples' => $samples,
			'interpretation' => 'diagnostic_only_contextual_disambiguation_no_anchor_injection',
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,mixed>            $hierarchy
	 * @return array<int,int>
	 */
	private static function boundary_ordinals( array $fragments, array $hierarchy ): array {
		$boundaries = array();
		foreach ( $fragments as $ordinal => $fragment ) {
			if ( is_array( $fragment ) && 'heading' === (string) ( $fragment['kind'] ?? '' ) ) {
				$boundaries[ (int) $ordinal ] = true;
			}
		}
		foreach ( (array) ( $hierarchy['nodes'] ?? array() ) as $node ) {
			if (
				is_array( $node )
				&& 'numbering_inferred' === (string) ( $node['hierarchy_source'] ?? '' )
				&& (int) ( $node['depth'] ?? 0 ) > 1
			) {
				$ordinal = (int) ( $node['ordinal'] ?? -1 );
				if ( $ordinal >= 0 ) {
					$boundaries[ $ordinal ] = true;
				}
			}
		}
		ksort( $boundaries, SORT_NUMERIC );
		return array_map( 'intval', array_keys( $boundaries ) );
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<int,int>                 $boundaries
	 * @return array<int,string>
	 */
	private static function source_context_parts( array $fragments, array $boundaries, int $ordinal ): array {
		$next_boundary = count( $fragments );
		foreach ( $boundaries as $boundary ) {
			if ( $boundary > $ordinal ) {
				$next_boundary = $boundary;
				break;
			}
		}

		$parts = array();
		for ( $cursor = $ordinal + 1; $cursor < $next_boundary; ++$cursor ) {
			$fragment = is_array( $fragments[ $cursor ] ?? null ) ? $fragments[ $cursor ] : array();
			$kind = (string) ( $fragment['kind'] ?? '' );
			if ( ! in_array( $kind, self::CONTEXT_SOURCE_KINDS, true ) ) {
				continue;
			}
			$normalized = Search_Query_Normalizer::normalize_document_text( (string) ( $fragment['text'] ?? '' ) );
			if ( '' === $normalized ) {
				continue;
			}
			$parts[] = $normalized;
			if ( count( $parts ) >= self::CONTEXT_PARTS_REQUIRED ) {
				break;
			}
		}
		return $parts;
	}

	/**
	 * @return array<int,array{tag:string,offset:int,text_norm:string}>
	 */
	private static function rendered_blocks( string $html ): array {
		if ( '' === $html ) {
			return array();
		}

		$blocks = array();
		foreach ( self::RENDERED_LEAF_TAGS as $tag ) {
			$pattern = '/<' . preg_quote( $tag, '/' ) . '\\b[^>]*>(.*?)<\\/' . preg_quote( $tag, '/' ) . '>/is';
			$found = preg_match_all( $pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
			if ( false === $found || 0 === $found ) {
				continue;
			}
			foreach ( $matches as $match ) {
				$normalized = Search_Query_Normalizer::normalize_document_text( (string) ( $match[1][0] ?? '' ) );
				if ( '' === $normalized ) {
					continue;
				}
				$blocks[] = array(
					'tag' => $tag,
					'offset' => (int) ( $match[0][1] ?? -1 ),
					'text_norm' => $normalized,
				);
			}
		}

		usort(
			$blocks,
			static function ( array $left, array $right ): int {
				$offset = (int) $left['offset'] <=> (int) $right['offset'];
				return 0 !== $offset ? $offset : strcmp( (string) $left['tag'], (string) $right['tag'] );
			}
		);
		return array_values( $blocks );
	}

	/**
	 * @param array<int,array{tag:string,offset:int,text_norm:string}> $blocks
	 * @return array<int,int>
	 */
	private static function rendered_title_indexes( array $blocks, string $title_norm ): array {
		$indexes = array();
		foreach ( $blocks as $index => $block ) {
			if ( 'p' === (string) $block['tag'] && $title_norm === (string) $block['text_norm'] ) {
				$indexes[] = (int) $index;
			}
		}
		return $indexes;
	}

	/**
	 * @param array<int,array{tag:string,offset:int,text_norm:string}> $blocks
	 * @param array<int,string>                                      $context_parts
	 */
	private static function context_matches_after( array $blocks, int $title_index, array $context_parts ): bool {
		for ( $part = 0; $part < self::CONTEXT_PARTS_REQUIRED; ++$part ) {
			$block_index = $title_index + 1 + $part;
			if ( ! isset( $blocks[ $block_index ] ) ) {
				return false;
			}
			if ( (string) $blocks[ $block_index ]['text_norm'] !== (string) ( $context_parts[ $part ] ?? '' ) ) {
				return false;
			}
		}
		return true;
	}
}
