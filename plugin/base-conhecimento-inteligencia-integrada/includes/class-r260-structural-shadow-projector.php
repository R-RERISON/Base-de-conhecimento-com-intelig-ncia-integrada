<?php
/**
 * R-260B — read-only structural shadow projection.
 *
 * Evaluates whether deterministic numbered paragraphs could become shared
 * structural sections without mutating Content Extractor, KD, Search runtime,
 * anchors or editorial content.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class R260_Structural_Shadow_Projector {

	public const VERSION = 'r260-structural-shadow-v1.0.0';
	private const MAX_SAMPLES_PER_POST = 8;

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @param array<int,array<string,mixed>> $existing_sections
	 * @param array<string,mixed>            $profile
	 * @return array<string,mixed>
	 */
	public static function project(
		int $post_id,
		string $source_kind,
		array $fragments,
		array $existing_sections,
		array $profile
	): array {
		$heading_labels = array();
		foreach ( $fragments as $fragment ) {
			if ( ! is_array( $fragment ) || 'heading' !== (string) ( $fragment['kind'] ?? '' ) ) {
				continue;
			}
			$label = Search_Query_Normalizer::normalize_document_text( (string) ( $fragment['text'] ?? '' ) );
			if ( '' !== $label ) {
				$heading_labels[ $label ] = (int) ( $heading_labels[ $label ] ?? 0 ) + 1;
			}
		}

		$candidates = array_values(
			array_filter(
				(array) ( $profile['candidates'] ?? array() ),
				static fn ( mixed $candidate ): bool => is_array( $candidate )
			)
		);

		$candidate_labels = array();
		foreach ( $candidates as $candidate ) {
			$ordinal = (int) ( $candidate['ordinal'] ?? -1 );
			if ( $ordinal < 0 || ! isset( $fragments[ $ordinal ] ) || ! is_array( $fragments[ $ordinal ] ) ) {
				continue;
			}
			$label = Search_Query_Normalizer::normalize_document_text(
				(string) ( $fragments[ $ordinal ]['text'] ?? '' )
			);
			if ( '' !== $label ) {
				$candidate_labels[ $label ] = (int) ( $candidate_labels[ $label ] ?? 0 ) + 1;
			}
		}

		$states = array(
			'toc_suppressed' => 0,
			'existing_heading_redundant' => 0,
			'duplicate_candidate_ambiguous' => 0,
			'promotable_shadow' => 0,
			'uncertain' => 0,
		);
		$samples = array();

		foreach ( $candidates as $candidate ) {
			$ordinal = (int) ( $candidate['ordinal'] ?? -1 );
			if ( $ordinal < 0 || ! isset( $fragments[ $ordinal ] ) || ! is_array( $fragments[ $ordinal ] ) ) {
				continue;
			}

			$label = Search_Query_Normalizer::normalize_document_text(
				(string) ( $fragments[ $ordinal ]['text'] ?? '' )
			);
			$heading_collision_count = (int) ( $heading_labels[ $label ] ?? 0 );
			$candidate_label_count = (int) ( $candidate_labels[ $label ] ?? 0 );
			$toc_signal = true === (bool) ( $candidate['toc_signal'] ?? false );
			$body_signal = true === (bool) ( $candidate['body_signal'] ?? false );

			if ( $toc_signal ) {
				$state = 'toc_suppressed';
			} elseif ( ! $body_signal ) {
				$state = 'uncertain';
			} elseif ( $heading_collision_count > 0 ) {
				$state = 'existing_heading_redundant';
			} elseif ( $candidate_label_count > 1 ) {
				$state = 'duplicate_candidate_ambiguous';
			} else {
				$state = 'promotable_shadow';
			}
			++$states[ $state ];

			if ( count( $samples ) < self::MAX_SAMPLES_PER_POST ) {
				$samples[] = array(
					'ordinal' => $ordinal,
					'token' => (string) ( $candidate['token'] ?? '' ),
					'text_hash' => hash( 'sha256', $label ),
					'state' => $state,
					'heading_collision_count' => $heading_collision_count,
					'candidate_label_count' => $candidate_label_count,
					'body_span' => (int) ( $candidate['body_span'] ?? 0 ),
					'position_bucket' => (string) ( $candidate['position_bucket'] ?? '' ),
					'anchor_contract_state' => 'paragraph_target_not_supported_by_heading_only_anchor_contract',
				);
			}
		}

		$existing_count = count( $existing_sections );
		$promotable_count = (int) $states['promotable_shadow'];
		$shadow_count = $existing_count + $promotable_count;
		$overflow = max( 0, $shadow_count - Search_Section_Projector::MAX_SECTIONS );

		return array(
			'version' => self::VERSION,
			'post_id' => $post_id,
			'source_kind' => $source_kind,
			'existing_section_count' => $existing_count,
			'existing_at_limit' => $existing_count >= Search_Section_Projector::MAX_SECTIONS,
			'candidate_count' => array_sum( $states ),
			'states' => $states,
			'promotable_shadow_count' => $promotable_count,
			'shadow_section_count' => $shadow_count,
			'max_sections' => Search_Section_Projector::MAX_SECTIONS,
			'max_sections_exceeded' => $overflow > 0,
			'overflow_by' => $overflow,
			'paragraph_anchor_contract_blocked_count' => $promotable_count,
			'samples' => $samples,
			'interpretation' => 'shadow_only_no_runtime_promotion',
		);
	}
}
