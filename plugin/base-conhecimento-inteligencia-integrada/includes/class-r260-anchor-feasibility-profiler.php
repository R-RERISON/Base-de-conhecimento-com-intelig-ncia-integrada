<?php
/**
 * R-260C — read-only anchor feasibility profiler.
 *
 * Measures whether shadow-promotable paragraph-derived structural nodes can be
 * targeted uniquely in rendered HTML. No anchors are injected and no runtime
 * Search behavior is changed.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class R260_Anchor_Feasibility_Profiler {

	public const VERSION = 'r260-anchor-feasibility-v1.0.0';
	private const MAX_SAMPLES_PER_POST = 8;

	/**
	 * @param array<int,array<string,mixed>> $classified_candidates
	 * @return array<string,mixed>
	 */
	public static function profile(
		int $post_id,
		string $source_kind,
		string $rendered_html,
		array $classified_candidates
	): array {
		$states = array(
			'paragraph_unique' => 0,
			'paragraph_ambiguous' => 0,
			'block_unique_nonparagraph' => 0,
			'block_ambiguous' => 0,
			'not_rendered_exact' => 0,
		);
		$samples = array();
		$promotable_count = 0;

		foreach ( $classified_candidates as $candidate ) {
			if ( ! is_array( $candidate ) || 'promotable_shadow' !== (string) ( $candidate['state'] ?? '' ) ) {
				continue;
			}

			$title_norm = (string) ( $candidate['title_norm'] ?? '' );
			if ( '' === $title_norm ) {
				continue;
			}
			++$promotable_count;

			$paragraph_matches = self::exact_matches( $rendered_html, $title_norm, array( 'p' ) );
			$block_matches = self::exact_matches( $rendered_html, $title_norm, array( 'p', 'li', 'div' ) );
			$paragraph_count = count( $paragraph_matches );
			$block_count = count( $block_matches );

			if ( 1 === $paragraph_count ) {
				$state = 'paragraph_unique';
			} elseif ( $paragraph_count > 1 ) {
				$state = 'paragraph_ambiguous';
			} elseif ( 1 === $block_count ) {
				$state = 'block_unique_nonparagraph';
			} elseif ( $block_count > 1 ) {
				$state = 'block_ambiguous';
			} else {
				$state = 'not_rendered_exact';
			}
			++$states[ $state ];

			if ( count( $samples ) < self::MAX_SAMPLES_PER_POST ) {
				$samples[] = array(
					'ordinal' => (int) ( $candidate['ordinal'] ?? -1 ),
					'token' => (string) ( $candidate['token'] ?? '' ),
					'text_hash' => hash( 'sha256', $title_norm ),
					'state' => $state,
					'paragraph_match_count' => $paragraph_count,
					'block_match_count' => $block_count,
					'paragraph_tags' => array_values(
						array_unique(
							array_map(
								static fn ( array $row ): string => (string) ( $row['tag'] ?? '' ),
								$paragraph_matches
							)
						)
					),
					'block_tags' => array_values(
						array_unique(
							array_map(
								static fn ( array $row ): string => (string) ( $row['tag'] ?? '' ),
								$block_matches
							)
						)
					),
				);
			}
		}

		return array(
			'version' => self::VERSION,
			'post_id' => $post_id,
			'source_kind' => $source_kind,
			'promotable_count' => $promotable_count,
			'states' => $states,
			'paragraph_unique_rate' => $promotable_count > 0
				? (float) $states['paragraph_unique'] / $promotable_count
				: 0.0,
			'any_unique_block_rate' => $promotable_count > 0
				? (float) ( $states['paragraph_unique'] + $states['block_unique_nonparagraph'] ) / $promotable_count
				: 0.0,
			'samples' => $samples,
			'interpretation' => 'diagnostic_only_no_anchor_injection',
		);
	}

	/**
	 * @param array<int,string> $tags
	 * @return array<int,array{tag:string,offset:int}>
	 */
	private static function exact_matches( string $html, string $title_norm, array $tags ): array {
		if ( '' === $html || '' === $title_norm || empty( $tags ) ) {
			return array();
		}

		$pattern = '/<(' . implode( '|', array_map( 'preg_quote', $tags ) ) . ')\\b([^>]*)>(.*?)<\/\\1>/is';
		$found = preg_match_all( $pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
		if ( false === $found || 0 === $found ) {
			return array();
		}

		$out = array();
		foreach ( $matches as $match ) {
			$inner = (string) ( $match[3][0] ?? '' );
			$rendered_norm = Search_Query_Normalizer::normalize_document_text( $inner );
			if ( $rendered_norm !== $title_norm ) {
				continue;
			}
			$out[] = array(
				'tag' => strtolower( (string) ( $match[1][0] ?? '' ) ),
				'offset' => (int) ( $match[0][1] ?? -1 ),
			);
		}
		return $out;
	}
}
