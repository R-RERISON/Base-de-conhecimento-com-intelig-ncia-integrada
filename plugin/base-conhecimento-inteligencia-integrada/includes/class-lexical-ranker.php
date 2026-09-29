<?php
/**
 * Ranking lexical determinístico da Search SPEC-005.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Lexical_Ranker {

	public const VERSION = 'lexical-ranker-v1.0.0';

	private const WEIGHT_TITLE = 60.0;
	private const WEIGHT_SUMMARY = 30.0;
	private const WEIGHT_HEADINGS = 22.0;
	private const WEIGHT_TAXONOMY = 16.0;
	private const WEIGHT_BODY = 10.0;
	private const WEIGHT_GLOBAL = 40.0;

	/**
	 * @param array{normalized:string,tokens:array<int,string>} $query
	 * @param array<int,array<string,mixed>> $documents
	 * @return array<int,array<string,mixed>>
	 */
	public static function rank( array $query, array $documents, int $limit = 20 ): array {
		$limit = max( 1, min( 50, $limit ) );
		$tokens = array_values( array_unique( array_map( 'strval', $query['tokens'] ?? array() ) ) );
		$normalized_query = (string) ( $query['normalized'] ?? '' );

		if ( empty( $tokens ) || '' === $normalized_query ) {
			return array();
		}

		$ranked = array();

		foreach ( $documents as $document ) {
			$post_id = (int) ( $document['post_id'] ?? 0 );
			if ( $post_id <= 0 ) {
				continue;
			}

			$fields = array(
				'title' => (string) ( $document['title_norm'] ?? '' ),
				'summary' => (string) ( $document['summary_norm'] ?? '' ),
				'headings' => (string) ( $document['headings_norm'] ?? '' ),
				'taxonomy' => (string) ( $document['taxonomy_norm'] ?? '' ),
				'body' => (string) ( $document['body_norm'] ?? '' ),
			);

			$coverage = array();
			foreach ( $fields as $name => $text ) {
				$coverage[ $name ] = self::coverage( $tokens, $text );
			}

			$global_text = implode( ' ', $fields );
			$global_coverage = self::coverage( $tokens, $global_text );

			// LIKE é somente candidate retrieval e pode casar substrings.
			// Sem token lexical real, o candidato não pode virar resultado.
			if ( $global_coverage <= 0.0 ) {
				continue;
			}

			$exact = array(
				'title' => self::exact_phrase( $normalized_query, $fields['title'] ),
				'summary' => self::exact_phrase( $normalized_query, $fields['summary'] ),
				'headings' => self::exact_phrase( $normalized_query, $fields['headings'] ),
				'taxonomy' => self::exact_phrase( $normalized_query, $fields['taxonomy'] ),
				'body' => self::exact_phrase( $normalized_query, $fields['body'] ),
			);

			$score = 0.0;
			$signals = array();

			if ( $exact['title'] ) {
				$score += 100.0;
				$signals[] = 'exact_title_phrase';
			}

			$weighted = array(
				'title' => self::WEIGHT_TITLE,
				'summary' => self::WEIGHT_SUMMARY,
				'headings' => self::WEIGHT_HEADINGS,
				'taxonomy' => self::WEIGHT_TAXONOMY,
				'body' => self::WEIGHT_BODY,
			);

			foreach ( $weighted as $name => $weight ) {
				if ( $coverage[ $name ] <= 0.0 ) {
					continue;
				}
				$score += $weight * $coverage[ $name ];
				$signals[] = sprintf( '%s:%.4f', $name, $coverage[ $name ] );
			}

			if ( $global_coverage > 0.0 ) {
				$score += self::WEIGHT_GLOBAL * $global_coverage;
				$signals[] = sprintf( 'global:%.4f', $global_coverage );
			}

			$phrase_bonus = array(
				'summary' => 12.0,
				'headings' => 8.0,
				'taxonomy' => 5.0,
				'body' => 3.0,
			);
			foreach ( $phrase_bonus as $name => $bonus ) {
				if ( $exact[ $name ] ) {
					$score += $bonus;
					$signals[] = 'exact_' . $name . '_phrase';
				}
			}

			$ranked[] = array_merge(
				$document,
				array(
					'score' => round( $score, 4 ),
					'matched_signals' => $signals,
					'_global_coverage' => $global_coverage,
					'_title_coverage' => $coverage['title'],
					'_exact_title' => $exact['title'],
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
				$global = (float) $b['_global_coverage'] <=> (float) $a['_global_coverage'];
				if ( 0 !== $global ) {
					return $global;
				}
				$exact = (int) (bool) $b['_exact_title'] <=> (int) (bool) $a['_exact_title'];
				if ( 0 !== $exact ) {
					return $exact;
				}
				$title = (float) $b['_title_coverage'] <=> (float) $a['_title_coverage'];
				if ( 0 !== $title ) {
					return $title;
				}
				return (int) $a['post_id'] <=> (int) $b['post_id'];
			}
		);

		$ranked = array_slice( $ranked, 0, $limit );

		foreach ( $ranked as $index => &$row ) {
			$row['rank'] = $index + 1;
			unset( $row['_global_coverage'], $row['_title_coverage'], $row['_exact_title'] );
		}
		unset( $row );

		return $ranked;
	}

	/**
	 * @param array<int,string> $tokens
	 */
	public static function coverage( array $tokens, string $normalized_text ): float {
		if ( empty( $tokens ) || '' === $normalized_text ) {
			return 0.0;
		}

		$haystack = array_flip( Search_Query_Normalizer::tokens_from_normalized( $normalized_text ) );
		$matched = 0;

		foreach ( array_values( array_unique( $tokens ) ) as $token ) {
			if ( isset( $haystack[ $token ] ) ) {
				++$matched;
			}
		}

		return $matched / count( array_values( array_unique( $tokens ) ) );
	}

	public static function exact_phrase( string $normalized_query, string $normalized_text ): bool {
		if ( '' === $normalized_query || '' === $normalized_text ) {
			return false;
		}
		return str_contains( ' ' . $normalized_text . ' ', ' ' . $normalized_query . ' ' );
	}
}
