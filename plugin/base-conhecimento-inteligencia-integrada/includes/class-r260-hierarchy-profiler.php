<?php
/**
 * R-260 — read-only numbered hierarchy profiler.
 *
 * Diagnostic only. Consumes extracted fragments + hierarchy projection and
 * produces bounded metadata to distinguish duplicated TOC-like sequences from
 * content-bearing pseudo-headings. Never mutates editorial content.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class R260_Hierarchy_Profiler {

	private const MAX_SAMPLES_PER_POST = 5;

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<string,mixed>            $hierarchy
	 * @return array<string,mixed>
	 */
	public static function profile( int $post_id, string $source_kind, array $fragments, array $hierarchy ): array {
		$fragment_count = count( $fragments );
		$heading_count = 0;
		foreach ( $fragments as $fragment ) {
			if ( is_array( $fragment ) && 'heading' === (string) ( $fragment['kind'] ?? '' ) ) {
				++$heading_count;
			}
		}

		$nodes = array();
		foreach ( (array) ( $hierarchy['nodes'] ?? array() ) as $node ) {
			if ( ! is_array( $node ) ) {
				continue;
			}
			$ordinal = (int) ( $node['ordinal'] ?? -1 );
			if ( $ordinal < 0 || ! isset( $fragments[ $ordinal ] ) || ! is_array( $fragments[ $ordinal ] ) ) {
				continue;
			}
			$fragment = $fragments[ $ordinal ];
			$text_norm = Search_Query_Normalizer::normalize_document_text( (string) ( $fragment['text'] ?? '' ) );
			$nodes[] = array(
				'ordinal' => $ordinal,
				'token' => (string) ( $node['token'] ?? '' ),
				'depth' => (int) ( $node['depth'] ?? 0 ),
				'hierarchy_source' => (string) ( $node['hierarchy_source'] ?? '' ),
				'hierarchy_confidence' => (string) ( $node['hierarchy_confidence'] ?? '' ),
				'kind' => (string) ( $fragment['kind'] ?? '' ),
				'text_norm' => $text_norm,
				'has_heading_context' => self::has_heading_context( $fragments, $ordinal ),
			);
		}

		$label_occurrences = array();
		$token_occurrences = array();
		foreach ( $nodes as $node ) {
			$label = (string) ( $node['text_norm'] ?? '' );
			$token = (string) ( $node['token'] ?? '' );
			if ( '' !== $label ) {
				$label_occurrences[ $label ][] = (int) $node['ordinal'];
			}
			if ( '' !== $token ) {
				$token_occurrences[ $token ][] = (int) $node['ordinal'];
			}
		}

		$boundaries = array();
		foreach ( $fragments as $ordinal => $fragment ) {
			if ( ! is_array( $fragment ) ) {
				continue;
			}
			if ( 'heading' === (string) ( $fragment['kind'] ?? '' ) ) {
				$boundaries[ (int) $ordinal ] = true;
			}
		}
		foreach ( $nodes as $node ) {
			if (
				'numbering_inferred' === (string) $node['hierarchy_source']
				&& (int) $node['depth'] > 1
			) {
				$boundaries[ (int) $node['ordinal'] ] = true;
			}
		}
		ksort( $boundaries, SORT_NUMERIC );
		$boundary_ordinals = array_keys( $boundaries );

		$candidates = array();
		foreach ( $nodes as $node ) {
			if (
				'numbering_inferred' !== (string) $node['hierarchy_source']
				|| 'deterministic' !== (string) $node['hierarchy_confidence']
				|| (int) $node['depth'] <= 1
				|| 'paragraph' !== (string) $node['kind']
				|| true === (bool) $node['has_heading_context']
			) {
				continue;
			}

			$ordinal = (int) $node['ordinal'];
			$label = (string) $node['text_norm'];
			$token = (string) $node['token'];
			$later_same_label = self::has_later_occurrence( (array) ( $label_occurrences[ $label ] ?? array() ), $ordinal );
			$later_same_token = self::has_later_occurrence( (array) ( $token_occurrences[ $token ] ?? array() ), $ordinal );
			$body_span = self::content_span_until_next_boundary( $fragments, $boundary_ordinals, $ordinal );
			$position_ratio = $fragment_count > 1 ? $ordinal / max( 1, $fragment_count - 1 ) : 0.0;
			$position_bucket = $position_ratio <= 0.25 ? 'early' : ( $position_ratio <= 0.75 ? 'middle' : 'late' );

			$toc_signal = 'early' === $position_bucket && $later_same_label && $body_span <= 1;
			$body_signal = $body_span >= 2;

			$candidates[] = array(
				'ordinal' => $ordinal,
				'token' => $token,
				'text_hash' => hash( 'sha256', $label ),
				'position_bucket' => $position_bucket,
				'body_span' => $body_span,
				'later_same_label' => $later_same_label,
				'later_same_token' => $later_same_token,
				'toc_signal' => $toc_signal,
				'body_signal' => $body_signal,
			);
		}

		$summary = array(
			'post_id' => $post_id,
			'source_kind' => $source_kind,
			'fragment_count' => $fragment_count,
			'heading_count' => $heading_count,
			'candidate_count' => count( $candidates ),
			'early_candidate_count' => self::count_true( $candidates, 'position_bucket', 'early' ),
			'later_same_label_count' => self::count_true( $candidates, 'later_same_label', true ),
			'later_same_token_count' => self::count_true( $candidates, 'later_same_token', true ),
			'body_span_ge2_count' => count(
				array_filter(
					$candidates,
					static fn ( array $candidate ): bool => (int) ( $candidate['body_span'] ?? 0 ) >= 2
				)
			),
			'toc_signal_count' => self::count_true( $candidates, 'toc_signal', true ),
			'body_signal_count' => self::count_true( $candidates, 'body_signal', true ),
		);
		$summary['uncertain_count'] = max(
			0,
			$summary['candidate_count'] - count(
				array_filter(
					$candidates,
					static fn ( array $candidate ): bool =>
						true === (bool) ( $candidate['toc_signal'] ?? false )
						|| true === (bool) ( $candidate['body_signal'] ?? false )
				)
			)
		);

		return array(
			'summary' => $summary,
			'samples' => array_slice( $candidates, 0, self::MAX_SAMPLES_PER_POST ),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 */
	private static function has_heading_context( array $fragments, int $ordinal ): bool {
		$fragment = is_array( $fragments[ $ordinal ] ?? null ) ? $fragments[ $ordinal ] : array();
		$heading_path = is_array( $fragment['heading_path'] ?? null ) ? $fragment['heading_path'] : array();
		if ( ! empty( $heading_path ) ) {
			return true;
		}
		for ( $cursor = $ordinal - 1; $cursor >= 0; --$cursor ) {
			$previous = is_array( $fragments[ $cursor ] ?? null ) ? $fragments[ $cursor ] : array();
			if ( 'heading' === (string) ( $previous['kind'] ?? '' ) ) {
				return true;
			}
		}
		return false;
	}

	/** @param array<int,int> $occurrences */
	private static function has_later_occurrence( array $occurrences, int $ordinal ): bool {
		foreach ( $occurrences as $candidate_ordinal ) {
			if ( (int) $candidate_ordinal > $ordinal ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<int,int>                 $boundary_ordinals
	 */
	private static function content_span_until_next_boundary( array $fragments, array $boundary_ordinals, int $ordinal ): int {
		$next_boundary = count( $fragments );
		foreach ( $boundary_ordinals as $boundary ) {
			if ( (int) $boundary > $ordinal ) {
				$next_boundary = (int) $boundary;
				break;
			}
		}

		$count = 0;
		for ( $cursor = $ordinal + 1; $cursor < $next_boundary; ++$cursor ) {
			$fragment = is_array( $fragments[ $cursor ] ?? null ) ? $fragments[ $cursor ] : array();
			$kind = (string) ( $fragment['kind'] ?? '' );
			$text = trim( (string) ( $fragment['text'] ?? '' ) );
			if (
				'' !== $text
				&& in_array( $kind, array( 'paragraph', 'quote', 'list_item', 'table_caption', 'table_row', 'code', 'image' ), true )
			) {
				++$count;
			}
		}
		return $count;
	}

	/**
	 * @param array<int,array<string,mixed>> $rows
	 */
	private static function count_true( array $rows, string $field, mixed $expected ): int {
		$count = 0;
		foreach ( $rows as $row ) {
			if ( ( $row[ $field ] ?? null ) === $expected ) {
				++$count;
			}
		}
		return $count;
	}
}
