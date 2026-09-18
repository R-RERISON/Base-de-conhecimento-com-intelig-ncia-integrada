<?php
/**
 * Validador determinístico dos Golden Candidates da SPEC-005.
 *
 * Não executa Search produtivo, não consulta legado e não persiste estado.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Golden_Candidate_Validator {

	public const CONTRACT_VERSION = '1.0.0';
	public const STATUS_AUTO_PASS = 'AUTO_PASS';
	public const STATUS_REVIEW_REQUIRED = 'REVIEW_REQUIRED';
	public const STATUS_AUTO_FAIL = 'AUTO_FAIL';

	/**
	 * Avalia evidências objetivas de uma expectativa Golden já existente.
	 *
	 * O validador pode confirmar continuidade de uma expectativa humana/histórica,
	 * mas nunca cria/troca expected_post_id por conta própria.
	 *
	 * @param array<string,mixed> $evidence
	 * @return array<string,mixed>
	 */
	public static function assess( array $evidence ): array {
		$reasons = array();
		$status = self::STATUS_AUTO_PASS;

		$exists = true === ( $evidence['expected_exists'] ?? false );
		$published = true === ( $evidence['expected_published'] ?? false );
		$expected_rank = max( 0, (int) ( $evidence['expected_rank'] ?? 0 ) );
		$max_rank = max( 1, (int) ( $evidence['max_rank'] ?? 3 ) );
		$semantic_coverage = self::bounded_percent( $evidence['semantic_coverage_percent'] ?? 0.0 );
		$title_coverage = self::bounded_percent( $evidence['title_coverage_percent'] ?? 0.0 );
		$summary_coverage = self::bounded_percent( $evidence['summary_coverage_percent'] ?? 0.0 );
		$native_coverage = self::bounded_percent( $evidence['native_coverage_percent'] ?? 0.0 );
		$exact_title_phrase = true === ( $evidence['exact_title_phrase'] ?? false );
		$query_token_count = max( 0, (int) ( $evidence['query_token_count'] ?? 0 ) );
		$competitor = is_array( $evidence['strongest_competitor'] ?? null ) ? $evidence['strongest_competitor'] : array();
		$competitor_ahead = true === ( $competitor['ahead_of_expected'] ?? false );
		$competitor_score = (float) ( $competitor['validation_score'] ?? 0.0 );
		$expected_score = (float) ( $evidence['expected_validation_score'] ?? 0.0 );
		$competitor_semantic = self::bounded_percent( $competitor['semantic_coverage_percent'] ?? 0.0 );
		$competitor_title = self::bounded_percent( $competitor['title_coverage_percent'] ?? 0.0 );
		$extractor_error = true === ( $evidence['extractor_error'] ?? false );

		if ( ! $exists ) {
			return self::result(
				self::STATUS_AUTO_FAIL,
				array( 'EXPECTED_POST_MISSING' ),
				$evidence,
				false
			);
		}

		if ( ! $published ) {
			return self::result(
				self::STATUS_AUTO_FAIL,
				array( 'EXPECTED_POST_NOT_PUBLISHED' ),
				$evidence,
				false
			);
		}

		if ( $extractor_error ) {
			return self::result(
				self::STATUS_AUTO_FAIL,
				array( 'EXPECTED_CONTENT_EXTRACTION_FAILED' ),
				$evidence,
				false
			);
		}

		if ( 0 === $expected_rank ) {
			return self::result(
				self::STATUS_AUTO_FAIL,
				array( 'EXPECTED_POST_NOT_RETRIEVED' ),
				$evidence,
				false
			);
		}

		if ( $expected_rank > $max_rank ) {
			return self::result(
				self::STATUS_AUTO_FAIL,
				array( 'EXPECTED_POST_OUTSIDE_MAX_RANK' ),
				$evidence,
				false
			);
		}

		if ( $semantic_coverage < 100.0 ) {
			$status = self::STATUS_REVIEW_REQUIRED;
			$reasons[] = 'EXPECTED_SEMANTIC_QUERY_COVERAGE_INCOMPLETE';
		}

		if ( $expected_rank > 1 ) {
			$status = self::STATUS_REVIEW_REQUIRED;
			$reasons[] = 'EXPECTED_NOT_TOP1';
		}

		$competitor_material = $competitor_ahead
			&& $competitor_semantic >= $semantic_coverage
			&& $competitor_score >= ( $expected_score - 0.001 );

		if ( $competitor_material ) {
			$status = self::STATUS_REVIEW_REQUIRED;
			$reasons[] = 'STRONG_COMPETITOR_AHEAD';
		}

		if (
			1 === $query_token_count
			&& $competitor_ahead
			&& $competitor_semantic >= 100.0
			&& $competitor_title >= $title_coverage
		) {
			$status = self::STATUS_REVIEW_REQUIRED;
			$reasons[] = 'SINGLE_TOKEN_AMBIGUITY';
		}

		if (
			self::STATUS_AUTO_PASS === $status
			&& ! $exact_title_phrase
			&& $title_coverage <= 0.0
			&& $summary_coverage <= 0.0
			&& $native_coverage <= 0.0
		) {
			$status = self::STATUS_REVIEW_REQUIRED;
			$reasons[] = 'NO_DIRECT_NATIVE_SIGNAL';
		}

		if ( empty( $reasons ) ) {
			$reasons[] = 'OBJECTIVE_CONTINUITY_VALIDATED';
		}

		return self::result(
			$status,
			array_values( array_unique( $reasons ) ),
			$evidence,
			self::STATUS_AUTO_PASS === $status
		);
	}

	/**
	 * Score auxiliar apenas para detectar ambiguidade entre expected e concorrentes.
	 * NÃO é o ranker Search da SPEC-005.
	 *
	 * @param array<string,mixed> $signals
	 */
	public static function validation_score( array $signals ): float {
		$title = self::bounded_percent( $signals['title_coverage_percent'] ?? 0.0 );
		$summary = self::bounded_percent( $signals['summary_coverage_percent'] ?? 0.0 );
		$semantic = self::bounded_percent( $signals['semantic_coverage_percent'] ?? 0.0 );
		$native = self::bounded_percent( $signals['native_coverage_percent'] ?? 0.0 );
		$exact = true === ( $signals['exact_title_phrase'] ?? false );

		$score = ( 0.45 * $semantic )
			+ ( 0.30 * $title )
			+ ( 0.15 * $summary )
			+ ( 0.10 * $native )
			+ ( $exact ? 15.0 : 0.0 );

		return round( $score, 4 );
	}

	public static function normalize( string $value ): string {
		$value = wp_strip_all_tags( $value );
		$value = function_exists( 'remove_accents' ) ? remove_accents( $value ) : $value;
		$value = function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
		$value = preg_replace( '/[^a-z0-9]+/u', ' ', $value ) ?? '';
		return trim( preg_replace( '/\s+/', ' ', $value ) ?? '' );
	}

	/** @return array<int,string> */
	public static function tokens( string $value ): array {
		$normalized = self::normalize( $value );
		if ( '' === $normalized ) {
			return array();
		}
		$tokens = preg_split( '/\s+/', $normalized ) ?: array();
		$out = array();
		foreach ( $tokens as $token ) {
			if ( '' !== $token && ! in_array( $token, $out, true ) ) {
				$out[] = $token;
			}
		}
		return $out;
	}

	public static function coverage_percent( string $query, string $text ): float {
		$query_tokens = self::tokens( $query );
		if ( empty( $query_tokens ) ) {
			return 0.0;
		}
		$text_tokens = array_flip( self::tokens( $text ) );
		$matched = 0;
		foreach ( $query_tokens as $token ) {
			if ( isset( $text_tokens[ $token ] ) ) {
				++$matched;
			}
		}
		return round( 100.0 * $matched / count( $query_tokens ), 4 );
	}

	public static function exact_phrase( string $query, string $text ): bool {
		$q = self::normalize( $query );
		$t = self::normalize( $text );
		return '' !== $q && '' !== $t && str_contains( $t, $q );
	}

	/** @param array<string,mixed> $evidence @return array<string,mixed> */
	private static function result( string $status, array $reasons, array $evidence, bool $auto_accept ): array {
		return array(
			'validator_contract_version' => self::CONTRACT_VERSION,
			'status' => $status,
			'auto_accept_existing_expectation' => $auto_accept,
			'reasons' => $reasons,
			'recommended_severity' => $auto_accept ? 'blocking' : 'pending_review',
			'expected_post_id' => max( 0, (int) ( $evidence['expected_post_id'] ?? 0 ) ),
			'max_rank' => max( 1, (int) ( $evidence['max_rank'] ?? 3 ) ),
			'expected_rank' => max( 0, (int) ( $evidence['expected_rank'] ?? 0 ) ),
			'evidence_score' => round( (float) ( $evidence['expected_validation_score'] ?? 0.0 ), 4 ),
		);
	}

	private static function bounded_percent( mixed $value ): float {
		return max( 0.0, min( 100.0, (float) $value ) );
	}
}
