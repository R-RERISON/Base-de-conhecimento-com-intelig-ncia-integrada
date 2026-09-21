<?php
/**
 * Ranking lexical determinístico de seções da Search SPEC-005 / G-590.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Section_Ranker {

	public const VERSION = 'lexical-section-ranker-v1.0.0';

	private const WEIGHT_EXACT_TITLE = 100.0;
	private const WEIGHT_TITLE = 60.0;
	private const WEIGHT_TEXT = 25.0;
	private const BONUS_TITLE_PHRASE = 35.0;
	private const BONUS_TEXT_PHRASE = 15.0;
	private const MAX_PARENT_BOOST = 20.0;

	/**
	 * @param array{normalized:string,tokens:array<int,string>} $query
	 * @param array<int,array<string,mixed>> $sections
	 * @return array<int,array<string,mixed>>
	 */
	public static function rank( array $query, array $sections, int $parent_rank, int $limit = 3 ): array {
		$limit = max( 1, min( 5, $limit ) );
		$parent_rank = max( 1, $parent_rank );
		$tokens = array_values( array_unique( array_map( 'strval', (array) ( $query['tokens'] ?? array() ) ) ) );
		$normalized_query = (string) ( $query['normalized'] ?? '' );

		if ( '' === $normalized_query || empty( $tokens ) ) {
			return array();
		}

		$ranked = array();

		foreach ( $sections as $section ) {
			if ( ! is_array( $section ) || 'generated' !== (string) ( $section['anchor_state'] ?? '' ) ) {
				continue;
			}

			$title = (string) ( $section['title_norm'] ?? '' );
			$text = (string) ( $section['text_norm'] ?? '' );
			$title_coverage = Lexical_Ranker::coverage( $tokens, $title );
			$text_coverage = Lexical_Ranker::coverage( $tokens, $text );
			$global_coverage = self::global_coverage( $tokens, $title, $text );

			if ( $global_coverage <= 0.0 ) {
				continue;
			}
			if ( count( $tokens ) >= 2 && $global_coverage < 0.5 ) {
				continue;
			}

			$score = 0.0;
			$signals = array();

			if ( $title === $normalized_query ) {
				$score += self::WEIGHT_EXACT_TITLE;
				$signals[] = 'exact_section_title';
			} elseif ( Lexical_Ranker::exact_phrase( $normalized_query, $title ) ) {
				$score += self::BONUS_TITLE_PHRASE;
				$signals[] = 'exact_section_title_phrase';
			}

			if ( $title_coverage > 0.0 ) {
				$score += self::WEIGHT_TITLE * $title_coverage;
				$signals[] = sprintf( 'section_title:%.4f', $title_coverage );
			}
			if ( $text_coverage > 0.0 ) {
				$score += self::WEIGHT_TEXT * $text_coverage;
				$signals[] = sprintf( 'section_text:%.4f', $text_coverage );
			}
			if ( Lexical_Ranker::exact_phrase( $normalized_query, $text ) ) {
				$score += self::BONUS_TEXT_PHRASE;
				$signals[] = 'exact_section_text_phrase';
			}

			$parent_boost = max( 0.0, self::MAX_PARENT_BOOST - ( ( $parent_rank - 1 ) * 2.0 ) );
			if ( $parent_boost > 0.0 ) {
				$score += $parent_boost;
				$signals[] = sprintf( 'parent_rank_boost:%.1f', $parent_boost );
			}

			$ranked[] = array_merge(
				$section,
				array(
					'score' => round( $score, 4 ),
					'matched_signals' => $signals,
					'parent_rank' => $parent_rank,
					'_title_coverage' => $title_coverage,
				)
			);
		}

		usort(
			$ranked,
			static function ( array $a, array $b ): int {
				$score = (float) $b['score'] <=> (float) $a['score'];
				if ( 0 !== $score ) {
					return $score;
				}
				$title = (float) $b['_title_coverage'] <=> (float) $a['_title_coverage'];
				if ( 0 !== $title ) {
					return $title;
				}
				$parent = (int) $a['parent_rank'] <=> (int) $b['parent_rank'];
				if ( 0 !== $parent ) {
					return $parent;
				}
				$ordinal = (int) ( $a['ordinal'] ?? 0 ) <=> (int) ( $b['ordinal'] ?? 0 );
				if ( 0 !== $ordinal ) {
					return $ordinal;
				}
				return strcmp( (string) ( $a['section_key'] ?? '' ), (string) ( $b['section_key'] ?? '' ) );
			}
		);

		$ranked = array_slice( $ranked, 0, $limit );
		foreach ( $ranked as &$section ) {
			unset( $section['_title_coverage'] );
		}
		unset( $section );

		return $ranked;
	}

	/**
	 * @param array<int,string> $tokens
	 */
	private static function global_coverage( array $tokens, string $title, string $text ): float {
		return Lexical_Ranker::coverage( $tokens, trim( $title . ' ' . $text ) );
	}
}
