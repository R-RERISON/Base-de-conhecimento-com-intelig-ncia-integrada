<?php
/**
 * Validador de diversidade da Golden/Challenge Suite.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Golden_Diversity_Validator {

	public const CONTRACT_VERSION = '1.1.0';

	/**
	 * @param array<int,array<string,mixed>> $cases
	 * @return array<string,mixed>
	 */
	public static function assess( array $cases ): array {
		$keys = array(
			'simple_term',
			'product_token',
			'compound_or_version',
			'phrase',
			'acronym',
			'natural_language',
			'real_typo_or_variation',
			'real_alias_or_synonym',
			'summary_dependent',
			'elementor_semantic_gap',
		);

		$coverage = array_fill_keys( $keys, false );
		$evidence = array_fill_keys( $keys, array() );
		$golden_coverage = array_fill_keys( $keys, false );
		$technical_coverage = array_fill_keys( $keys, false );

		foreach ( $cases as $case ) {
			$query = trim( (string) ( $case['query'] ?? '' ) );
			$id = (string) ( $case['id'] ?? '' );
			$origin = (string) ( $case['origin'] ?? '' );
			$is_golden_origin = in_array( $origin, array( 'real', 'human', 'legacy_validated', 'incident', 'search_log_curated' ), true );
			$is_technical_origin = in_array( $origin, array( 'corpus_derived_challenge', 'synthetic' ), true );

			foreach ( self::classify_query( $query ) as $class ) {
				if ( ! array_key_exists( $class, $coverage ) ) {
					continue;
				}
				$coverage[ $class ] = true;
				$evidence[ $class ][] = $id;
				if ( $is_golden_origin ) {
					$golden_coverage[ $class ] = true;
				}
				if ( $is_technical_origin || $is_golden_origin ) {
					$technical_coverage[ $class ] = true;
				}
			}

			$declared = is_array( $case['declared_classes'] ?? null )
				? array_values( array_map( 'strval', $case['declared_classes'] ) )
				: array();

			if ( $is_golden_origin && in_array( 'typo_or_variation', $declared, true ) ) {
				$coverage['real_typo_or_variation'] = true;
				$golden_coverage['real_typo_or_variation'] = true;
				$technical_coverage['real_typo_or_variation'] = true;
				$evidence['real_typo_or_variation'][] = $id;
			}
			if ( $is_golden_origin && in_array( 'alias_or_synonym', $declared, true ) ) {
				$coverage['real_alias_or_synonym'] = true;
				$golden_coverage['real_alias_or_synonym'] = true;
				$technical_coverage['real_alias_or_synonym'] = true;
				$evidence['real_alias_or_synonym'][] = $id;
			}

			if ( true === ( $case['summary_dependent'] ?? false ) ) {
				$coverage['summary_dependent'] = true;
				$technical_coverage['summary_dependent'] = true;
				if ( $is_golden_origin ) {
					$golden_coverage['summary_dependent'] = true;
				}
				$evidence['summary_dependent'][] = $id;
			}

			if ( true === ( $case['elementor_semantic_gap'] ?? false ) ) {
				$coverage['elementor_semantic_gap'] = true;
				$technical_coverage['elementor_semantic_gap'] = true;
				if ( $is_golden_origin ) {
					$golden_coverage['elementor_semantic_gap'] = true;
				}
				$evidence['elementor_semantic_gap'][] = $id;
			}
		}

		$golden_required = array(
			'simple_term',
			'product_token',
			'compound_or_version',
			'phrase',
			'acronym',
		);
		$technical_required = array(
			'natural_language',
			'summary_dependent',
			'elementor_semantic_gap',
		);
		$real_world_enrichment = array(
			'real_typo_or_variation',
			'real_alias_or_synonym',
		);

		$missing_golden = self::missing( $golden_required, $golden_coverage );
		$missing_technical = self::missing( $technical_required, $technical_coverage );
		$missing_real_world = self::missing( $real_world_enrichment, $golden_coverage );

		return array(
			'contract_version' => self::CONTRACT_VERSION,
			'coverage' => $coverage,
			'golden_or_historical_coverage' => $golden_coverage,
			'technical_coverage' => $technical_coverage,
			'evidence' => $evidence,
			'missing_golden_required' => $missing_golden,
			'missing_technical_required' => $missing_technical,
			'missing_real_world_enrichment' => $missing_real_world,
			'status' => empty( $missing_golden ) && empty( $missing_technical ) ? 'PASS' : 'INCOMPLETE',
			'real_world_enrichment_status' => empty( $missing_real_world ) ? 'PASS' : 'PENDING_TELEMETRY',
			'note' => 'Technical Challenge cases podem provar capacidade técnica; não são representados como consultas reais de usuário. Typo/alias reais serão enriquecidos pela futura Telemetria.',
		);
	}

	/** @return array<int,string> */
	public static function classify_query( string $query ): array {
		$query = trim( $query );
		$tokens = Golden_Candidate_Validator::tokens( $query );
		$classes = array();

		if ( 1 === count( $tokens ) ) {
			$classes[] = 'simple_term';
		}

		if ( preg_match( '/^[A-Z0-9]{2,10}$/', $query ) ) {
			$classes[] = 'acronym';
		}

		$upper_count = preg_match_all( '/[A-Z]/', $query );
		$has_lower = 1 === preg_match( '/[a-z]/', $query );
		$has_digit = 1 === preg_match( '/\d/', $query );
		$has_alpha = 1 === preg_match( '/[A-Za-z]/', $query );
		$bounded_token = 1 === preg_match( '/^[A-Za-z0-9._-]+$/', $query );

		if (
			1 === count( $tokens )
			&& $bounded_token
			&& (
				( $upper_count >= 2 && $has_lower )
				|| ( $has_digit && $has_alpha )
			)
		) {
			$classes[] = 'product_token';
		}

		if ( $has_digit || 2 === count( $tokens ) ) {
			$classes[] = 'compound_or_version';
		}

		if ( count( $tokens ) >= 3 ) {
			$classes[] = 'phrase';
		}

		$normalized = Golden_Candidate_Validator::normalize( $query );
		if (
			count( $tokens ) >= 4
			|| preg_match( '/^(como|qual|quais|onde|quando|porque|por que|o que|como faco|como fazer)\b/', $normalized )
			|| str_contains( $query, '?' )
		) {
			$classes[] = 'natural_language';
		}

		return array_values( array_unique( $classes ) );
	}

	/**
	 * Variantes sintéticas exclusivamente para robustez do normalizer.
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function synthetic_variants( string $query ): array {
		$variants = array();
		$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $query, 'UTF-8' ) : strtolower( $query );
		$upper = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $query, 'UTF-8' ) : strtoupper( $query );
		$spaced = '  ' . preg_replace( '/\s+/', '   ', trim( $query ) ) . '  ';

		foreach (
			array(
				'lowercase' => $lower,
				'uppercase' => $upper,
				'extra_whitespace' => $spaced,
			) as $kind => $value
		) {
			if ( $value !== $query ) {
				$variants[] = array(
					'kind' => $kind,
					'query' => $value,
					'origin' => 'synthetic',
				);
			}
		}

		return $variants;
	}

	/** @param array<int,string> $required @param array<string,bool> $coverage @return array<int,string> */
	private static function missing( array $required, array $coverage ): array {
		return array_values(
			array_filter(
				$required,
				static fn ( string $key ): bool => empty( $coverage[ $key ] )
			)
		);
	}
}
