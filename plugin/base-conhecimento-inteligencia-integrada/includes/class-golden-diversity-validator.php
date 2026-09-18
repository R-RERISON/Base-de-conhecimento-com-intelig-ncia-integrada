<?php
/**
 * Validador de diversidade e robustez da Golden Suite.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Golden_Diversity_Validator {

	public const CONTRACT_VERSION = '1.0.0';

	/**
	 * @param array<int,array<string,mixed>> $cases
	 * @return array<string,mixed>
	 */
	public static function assess( array $cases ): array {
		$coverage = array(
			'simple_term' => false,
			'product_token' => false,
			'compound_or_version' => false,
			'phrase' => false,
			'acronym' => false,
			'natural_language' => false,
			'real_typo_or_variation' => false,
			'real_alias_or_synonym' => false,
			'summary_dependent' => false,
			'elementor_semantic_gap' => false,
		);
		$evidence = array_fill_keys( array_keys( $coverage ), array() );

		foreach ( $cases as $case ) {
			$query = trim( (string) ( $case['query'] ?? '' ) );
			$id = (string) ( $case['id'] ?? '' );
			$classes = self::classify_query( $query );

			foreach ( $classes as $class ) {
				if ( array_key_exists( $class, $coverage ) ) {
					$coverage[ $class ] = true;
					$evidence[ $class ][] = $id;
				}
			}

			$origin = (string) ( $case['origin'] ?? '' );
			$declared = is_array( $case['declared_classes'] ?? null ) ? array_values( array_map( 'strval', $case['declared_classes'] ) ) : array();

			if ( 'real' === $origin && in_array( 'typo_or_variation', $declared, true ) ) {
				$coverage['real_typo_or_variation'] = true;
				$evidence['real_typo_or_variation'][] = $id;
			}
			if ( 'real' === $origin && in_array( 'alias_or_synonym', $declared, true ) ) {
				$coverage['real_alias_or_synonym'] = true;
				$evidence['real_alias_or_synonym'][] = $id;
			}
			if ( true === ( $case['summary_dependent'] ?? false ) ) {
				$coverage['summary_dependent'] = true;
				$evidence['summary_dependent'][] = $id;
			}
			if ( true === ( $case['elementor_semantic_gap'] ?? false ) ) {
				$coverage['elementor_semantic_gap'] = true;
				$evidence['elementor_semantic_gap'][] = $id;
			}
		}

		$required = array(
			'simple_term',
			'compound_or_version',
			'phrase',
			'acronym',
			'natural_language',
			'summary_dependent',
			'elementor_semantic_gap',
		);
		$conditional = array( 'real_typo_or_variation', 'real_alias_or_synonym' );
		$missing_required = array_values( array_filter( $required, static fn ( string $key ): bool => ! $coverage[ $key ] ) );
		$missing_conditional = array_values( array_filter( $conditional, static fn ( string $key ): bool => ! $coverage[ $key ] ) );

		return array(
			'contract_version' => self::CONTRACT_VERSION,
			'coverage' => $coverage,
			'evidence' => $evidence,
			'missing_required' => $missing_required,
			'missing_conditional_real_world' => $missing_conditional,
			'status' => empty( $missing_required ) ? 'PASS' : 'INCOMPLETE',
			'note' => 'Casos sintéticos podem testar robustez, mas não contam como uso real para typo/alias.',
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
		if (
			1 === count( $tokens )
			&& preg_match( '/[A-Z]/', $query )
			&& preg_match( '/[a-z]/', $query )
			&& preg_match( '/^[A-Za-z0-9._-]+$/', $query )
		) {
			$classes[] = 'product_token';
		}
		if ( preg_match( '/\d/', $query ) || count( $tokens ) === 2 ) {
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
	 * Gera variantes exclusivamente sintéticas para regressão do normalizer.
	 * Não entram como Golden real nem fecham classes de uso real.
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function synthetic_variants( string $query ): array {
		$variants = array();
		$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $query, 'UTF-8' ) : strtolower( $query );
		$upper = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $query, 'UTF-8' ) : strtoupper( $query );
		$spaced = '  ' . preg_replace( '/\s+/', '   ', trim( $query ) ) . '  ';

		foreach ( array(
			'lowercase' => $lower,
			'uppercase' => $upper,
			'extra_whitespace' => $spaced,
		) as $kind => $value ) {
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
}
