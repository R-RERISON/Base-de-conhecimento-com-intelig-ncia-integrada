<?php
/**
 * Deterministic, read-only Elementor Projection Plan for SPEC-004 / G-245.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Projection_Plan {

	public const SCHEMA_VERSION = '1.0.0';
	public const MATRIX_VERSION = 'g245-compatibility-matrix-v1';

	/** @var array<string,string> */
	private const KNOWN_PROVIDERS = array(
		'bdc_resumo_executivo' => 'plugin:gerenciador-resumo-executivo',
		'caption'               => 'wordpress-core',
		'dbc_table'             => 'plugin:dbc-table-custom',
		'table'                 => 'plugin:tablepress',
		'video'                 => 'wordpress-core',
	);

	/** @return array<string,mixed>|\WP_Error */
	public static function build( int $post_id ): array|\WP_Error {
		$document = Knowledge_Document::build( $post_id );
		if ( $document instanceof \WP_Error ) {
			return $document;
		}

		$source = Content_Source::inspect( $post_id );
		if ( $source instanceof \WP_Error ) {
			return $source;
		}

		$dependencies = self::inspect_shortcode_dependencies( $source );
		return self::from_document( $document, $dependencies );
	}

	/**
	 * Pure deterministic planner. No WordPress write is performed here.
	 *
	 * @param array<string,mixed> $document
	 * @param array<int,array<string,mixed>> $dependencies
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function from_document( array $document, array $dependencies = array() ): array|\WP_Error {
		$post_id = (int) ( $document['post_id'] ?? 0 );
		$source_hash = (string) ( $document['source_hash'] ?? '' );
		if ( $post_id <= 0 || '' === $source_hash ) {
			return new \WP_Error( 'bdc_kb_projection_invalid_document', 'Knowledge Document inválido para Projection Plan.' );
		}

		$source_kind = (string) ( $document['source_kind'] ?? 'empty' );
		$kd_schema = (string) ( $document['schema_version'] ?? '' );
		$extraction = is_array( $document['extraction'] ?? null ) ? $document['extraction'] : array();
		$compat = is_array( $extraction['elementor_compatibility'] ?? null ) ? $extraction['elementor_compatibility'] : array();
		$compat_status = (string) ( $compat['status'] ?? 'review_required' );
		$compat_reasons = is_array( $compat['reasons'] ?? null ) ? array_values( array_map( 'strval', $compat['reasons'] ) ) : array();
		$extraction_warnings = is_array( $extraction['warnings'] ?? null ) ? array_values( array_map( 'strval', $extraction['warnings'] ) ) : array();

		$normalized_dependencies = self::normalize_dependencies( $dependencies );
		$warnings = self::unique_preserve_order( array_merge( $compat_reasons, self::migration_relevant_warnings( $extraction_warnings ) ) );

		$requires_review = false;
		foreach ( $normalized_dependencies as $dependency ) {
			$tag = (string) ( $dependency['tag'] ?? '' );
			$registered = true === ( $dependency['registered'] ?? false );
			if ( 'faq_wd' === $tag ) {
				$warnings[] = 'LEGACY_SHORTCODE_ORPHAN:faq_wd';
				$requires_review = true;
			} elseif ( 'wpt' === $tag ) {
				$warnings[] = 'LEGACY_SHORTCODE_UNKNOWN:wpt';
				$requires_review = true;
			} elseif ( ! $registered ) {
				$warnings[] = 'SHORTCODE_HANDLER_MISSING:' . $tag;
				$requires_review = true;
			}
		}

		$warnings = self::unique_preserve_order( $warnings );
		$decision = self::strategy_for( $source_kind, $compat_status );
		$plan_status = $decision['status'];
		$strategy = $decision['strategy'];
		$operations = $decision['operations'];

		if ( in_array( $compat_status, array( 'review_required', 'blocked' ), true ) ) {
			$requires_review = true;
		}

		if ( 'mixed' === $source_kind && 'native' === $compat_status ) {
			$warnings[] = 'MIXED_SOURCE_NATIVE_REVIEW';
			$requires_review = true;
		}

		if ( $requires_review && ! in_array( $plan_status, array( 'blocked', 'not_applicable' ), true ) ) {
			$plan_status = 'review_required';
			if ( 'preserve_native' !== $strategy ) {
				$strategy = 'manual_adapter_required';
				$operations = array(
					array( 'op' => 'REQUIRE_MANUAL_ADAPTER' ),
					array( 'op' => 'PRESERVE_SOURCE_UNTIL_APPROVED' ),
				);
			}
		}

		$warnings = self::unique_preserve_order( $warnings );
		$plan = array(
			'schema_version'                    => self::SCHEMA_VERSION,
			'matrix_version'                    => self::MATRIX_VERSION,
			'post_id'                           => $post_id,
			'source_kind'                       => $source_kind,
			'source_hash_before'                => $source_hash,
			'knowledge_document_schema_version' => $kd_schema,
			'elementor_compatibility'           => array(
				'status'  => $compat_status,
				'reasons' => $compat_reasons,
			),
			'plan_status'                       => $plan_status,
			'projection_strategy'               => $strategy,
			'operations'                        => $operations,
			'dependencies'                      => array( 'shortcodes' => $normalized_dependencies ),
			'warnings'                          => $warnings,
			'requires_review'                   => $requires_review,
			'writer_allowed'                    => false,
			'safety'                            => array(
				'persists_plan'          => false,
				'executes_shortcodes'    => false,
				'calls_external_network' => false,
				'writes_post_content'    => false,
				'writes_elementor_data'  => false,
			),
			'projection_hash'                   => '',
		);

		$hash_payload = $plan;
		unset( $hash_payload['projection_hash'] );
		try {
			$plan['projection_hash'] = Canonical_JSON::hash( $hash_payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_projection_hash_failed', 'Falha ao calcular projection_hash.', array( 'exception' => get_class( $error ) ) );
		}

		return $plan;
	}

	/** @param array<string,mixed> $plan @return string|\WP_Error */
	public static function canonical_json( array $plan ): string|\WP_Error {
		try {
			return Canonical_JSON::encode( $plan );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_projection_json_failed', 'Falha ao serializar Projection Plan.', array( 'exception' => get_class( $error ) ) );
		}
	}

	/**
	 * @param array<string,mixed> $source
	 * @return array<int,array<string,mixed>>
	 */
	private static function inspect_shortcode_dependencies( array $source ): array {
		$origins = array(
			'post_content'  => (string) ( $source['post_content'] ?? '' ),
			'elementor_data' => (string) ( $source['elementor_string'] ?? '' ),
		);
		$by_tag = array();
		foreach ( $origins as $origin => $content ) {
			if ( '' === $content ) {
				continue;
			}
			$inspection = Shortcode_Inspector::inspect( $content );
			foreach ( (array) ( $inspection['matches'] ?? array() ) as $match ) {
				$tag = strtolower( (string) ( $match['tag'] ?? '' ) );
				if ( '' === $tag ) {
					continue;
				}
				if ( ! isset( $by_tag[ $tag ] ) ) {
					$by_tag[ $tag ] = array(
						'tag'        => $tag,
						'registered' => true === ( $match['registered'] ?? false ),
						'origins'    => array(),
						'provider'   => self::KNOWN_PROVIDERS[ $tag ] ?? null,
					);
				}
				$by_tag[ $tag ]['registered'] = true === $by_tag[ $tag ]['registered'] || true === ( $match['registered'] ?? false );
				$by_tag[ $tag ]['origins'][] = $origin;
			}
		}
		ksort( $by_tag, SORT_STRING );
		return self::normalize_dependencies( array_values( $by_tag ) );
	}

	/**
	 * @param array<int,array<string,mixed>> $dependencies
	 * @return array<int,array<string,mixed>>
	 */
	private static function normalize_dependencies( array $dependencies ): array {
		$out = array();
		foreach ( $dependencies as $dependency ) {
			if ( ! is_array( $dependency ) ) {
				continue;
			}
			$tag = strtolower( trim( (string) ( $dependency['tag'] ?? '' ) ) );
			if ( '' === $tag ) {
				continue;
			}
			$origins = is_array( $dependency['origins'] ?? null ) ? array_values( array_unique( array_map( 'strval', $dependency['origins'] ) ) ) : array();
			sort( $origins, SORT_STRING );
			$provider = $dependency['provider'] ?? ( self::KNOWN_PROVIDERS[ $tag ] ?? null );
			$out[ $tag ] = array(
				'tag'        => $tag,
				'registered' => true === ( $dependency['registered'] ?? false ),
				'origins'    => $origins,
				'provider'   => is_string( $provider ) && '' !== $provider ? $provider : null,
			);
		}
		ksort( $out, SORT_STRING );
		return array_values( $out );
	}

	/** @return array{status:string,strategy:string,operations:array<int,array<string,string>>} */
	private static function strategy_for( string $source_kind, string $compat_status ): array {
		if ( 'blocked' === $compat_status ) {
			return array( 'status' => 'blocked', 'strategy' => 'blocked_no_projection', 'operations' => array() );
		}
		if ( 'empty' === $source_kind ) {
			return array( 'status' => 'not_applicable', 'strategy' => 'empty_noop', 'operations' => array() );
		}
		if ( 'native' === $compat_status ) {
			return array(
				'status' => 'native_noop',
				'strategy' => 'preserve_native',
				'operations' => array( array( 'op' => 'NOOP_PRESERVE_NATIVE' ) ),
			);
		}
		if ( 'review_required' === $compat_status ) {
			return array(
				'status' => 'review_required',
				'strategy' => 'manual_adapter_required',
				'operations' => array(
					array( 'op' => 'REQUIRE_MANUAL_ADAPTER' ),
					array( 'op' => 'PRESERVE_SOURCE_UNTIL_APPROVED' ),
				),
			);
		}

		switch ( $source_kind ) {
			case 'legacy_html':
				return array(
					'status' => 'projectable',
					'strategy' => 'legacy_html_to_container_html',
					'operations' => array(
						array( 'op' => 'CREATE_ROOT_CONTAINER' ),
						array( 'op' => 'PROJECT_LEGACY_HTML_SEMANTICALLY' ),
					),
				);
			case 'plain_text':
				return array(
					'status' => 'projectable',
					'strategy' => 'plain_text_to_text_editor',
					'operations' => array(
						array( 'op' => 'CREATE_ROOT_CONTAINER' ),
						array( 'op' => 'ADD_TEXT_EDITOR_PRESERVE_TEXT' ),
					),
				);
			case 'gutenberg':
				return array(
					'status' => 'projectable',
					'strategy' => 'gutenberg_static_mapping',
					'operations' => array(
						array( 'op' => 'CREATE_ROOT_CONTAINER' ),
						array( 'op' => 'MAP_STATIC_BLOCKS' ),
					),
				);
			case 'mixed':
			case 'elementor':
				return array(
					'status' => 'review_required',
					'strategy' => 'manual_adapter_required',
					'operations' => array( array( 'op' => 'REQUIRE_MANUAL_ADAPTER' ) ),
				);
			default:
				return array(
					'status' => 'review_required',
					'strategy' => 'manual_adapter_required',
					'operations' => array( array( 'op' => 'REQUIRE_MANUAL_ADAPTER' ) ),
				);
		}
	}

	/** @param array<int,string> $warnings @return array<int,string> */
	private static function migration_relevant_warnings( array $warnings ): array {
		$prefixes = array(
			'ELEMENTOR_JSON_INVALID',
			'SHORTCODE_NOT_EXPANDED:',
			'GUTENBERG_DYNAMIC_NOT_RENDERED:',
			'GUTENBERG_BLOCK_UNSUPPORTED:',
			'ELEMENTOR_WIDGET_UNSUPPORTED:',
			'SOURCE_OVERSIZE_HARD:',
		);
		$out = array();
		foreach ( $warnings as $warning ) {
			foreach ( $prefixes as $prefix ) {
				if ( str_starts_with( $warning, $prefix ) ) {
					$out[] = $warning;
					break;
				}
		}
		return self::unique_preserve_order( $out );
	}

	/** @param array<int,string> $values @return array<int,string> */
	private static function unique_preserve_order( array $values ): array {
		$out = array();
		$seen = array();
		foreach ( $values as $value ) {
			if ( isset( $seen[ $value ] ) ) {
				continue;
			}
			$seen[ $value ] = true;
			$out[] = $value;
		}
		return $out;
	}
}
