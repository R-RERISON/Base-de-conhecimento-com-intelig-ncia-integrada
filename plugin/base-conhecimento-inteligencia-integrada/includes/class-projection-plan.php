<?php
/**
 * Projection Plan read-only da SPEC-004/G-245.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Descreve uma projeção futura sem gerar ou persistir Elementor JSON.
 */
final class Projection_Plan {

	public const SCHEMA_VERSION = '1.0.0';

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function build( int $post_id ): array|\WP_Error {
		$document = Knowledge_Document::build( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}

		$extraction = is_array( $document['extraction'] ?? null ) ? $document['extraction'] : array();
		$status     = (string) ( $extraction['elementor_compatibility']['status'] ?? 'blocked' );
		$reasons    = is_array( $extraction['elementor_compatibility']['reasons'] ?? null )
			? array_values( array_map( 'strval', $extraction['elementor_compatibility']['reasons'] ) )
			: array();
		$warnings   = is_array( $extraction['warnings'] ?? null )
			? array_values( array_map( 'strval', $extraction['warnings'] ) )
			: array();
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return new \WP_Error( 'bdc_kb_projection_plan_post_unavailable', 'O post não está disponível para o snapshot do plano.' );
		}
		$plan = array(
			'projection_schema_version' => self::SCHEMA_VERSION,
			'post_id'                  => (int) $document['post_id'],
			'source_kind'              => (string) $document['source_kind'],
			'source_hash_before'       => (string) $document['source_hash'],
			'post_modified_gmt_before' => (string) $post->post_modified_gmt,
			'elementor_compatibility'  => array(
				'status'  => $status,
				'reasons' => $reasons,
			),
			'projection_strategy'      => self::strategy( (string) $document['source_kind'], $status ),
			'projection_hash'          => '',
			'warnings'                 => $warnings,
			'requires_review'          => 'native' !== $status && ( 'projectable' !== $status || ! empty( $reasons ) ),
		);

		try {
			$hash_payload = $plan;
			unset( $hash_payload['projection_hash'] );
			$plan['projection_hash'] = Canonical_JSON::hash( $hash_payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_projection_plan_hash_failed', 'Falha ao calcular projection_hash.', array( 'exception' => get_class( $error ) ) );
		}

		return $plan;
	}

	private static function strategy( string $source_kind, string $status ): string {
		if ( 'native' === $status ) {
			return 'no_migration_required';
		}
		if ( 'blocked' === $status ) {
			return 'blocked';
		}
		if ( 'gutenberg' === $source_kind ) {
			return 'static_gutenberg_mapping_review';
		}
		if ( 'legacy_html' === $source_kind ) {
			return 'controlled_html_projection';
		}
		if ( 'plain_text' === $source_kind ) {
			return 'plain_text_projection';
		}
		if ( 'mixed' === $source_kind ) {
			return 'mixed_source_review';
		}
		return 'manual_review';
	}
}