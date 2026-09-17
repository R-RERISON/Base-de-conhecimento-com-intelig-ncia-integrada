<?php
/**
 * Knowledge Document canônico e reconstruível da SPEC-004.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Knowledge_Document {

	public const SCHEMA_VERSION = '2.1.0';

	/**
	 * Constrói o documento canônico a partir da fonte editorial atual.
	 * Não persiste documento, hash, cache ou qualquer estado editorial.
	 *
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function build( int $post_id ): array|\WP_Error {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return new \WP_Error( 'bdc_kb_post_not_found', 'Post não encontrado.' );
		}
		$post_type = isset( $post->post_type ) ? (string) $post->post_type : '';
		if ( 'post' !== $post_type ) {
			return new \WP_Error( 'bdc_kb_unsupported_post_type', 'A SPEC-004 suporta somente post.' );
		}
		$extraction = Content_Extractor::extract( $post_id );
		if ( $extraction instanceof \WP_Error ) {
			return $extraction;
		}
		$source = array();
		if ( class_exists( Content_Source::class ) ) {
			$source_result = Content_Source::inspect( $post_id );
			if ( is_array( $source_result ) ) {
				$source = $source_result;
			}
		}
		$url = function_exists( 'get_permalink' ) ? get_permalink( $post_id ) : '';
		return self::from_extraction( $post, $extraction, is_string( $url ) ? $url : '', $source );
	}

	/**
	 * @param object              $post WP_Post ou projeção equivalente.
	 * @param array<string,mixed> $extraction Saída do Content_Extractor.
	 * @param array<string,mixed> $source_context Fonte bruta read-only opcional para relationship fidelity.
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function from_extraction( object $post, array $extraction, string $canonical_url = '', array $source_context = array() ): array|\WP_Error {
		$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
		if ( $post_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_invalid_post', 'Post inválido para Knowledge Document.' );
		}

		$title        = Content_Normalizer::text( isset( $post->post_title ) ? (string) $post->post_title : '' );
		$modified_gmt = isset( $post->post_modified_gmt ) ? (string) $post->post_modified_gmt : '';
		$source_kind  = isset( $extraction['source_kind'] ) ? (string) $extraction['source_kind'] : 'empty';
		$fragments    = is_array( $extraction['fragments'] ?? null ) ? $extraction['fragments'] : array();
		$sections     = Semantic_Structure::sections( $fragments );

		$numbered = Numbered_Hierarchy_Resolver::resolve( $sections );
		$sections = $numbered['sections'];
		$blocks   = Semantic_Structure::blocks( $sections );

		$strategies = is_array( $extraction['strategies'] ?? null ) ? array_values( array_map( 'strval', $extraction['strategies'] ) ) : array();
		$relationships = Hierarchy_Relationships::evaluate( $source_context, $fragments, $strategies );

		$effective_extraction = $extraction;
		$effective_warnings = is_array( $effective_extraction['warnings'] ?? null ) ? array_values( array_map( 'strval', $effective_extraction['warnings'] ) ) : array();
		$effective_warnings = array_merge(
			$effective_warnings,
			is_array( $relationships['reasons'] ?? null ) ? array_values( array_map( 'strval', $relationships['reasons'] ) ) : array(),
			is_array( $numbered['warnings'] ?? null ) ? array_values( array_map( 'strval', $numbered['warnings'] ) ) : array()
		);
		$effective_extraction['warnings'] = self::unique_preserve_order( $effective_warnings );
		$effective_extraction['relationship_fidelity'] = $relationships;

		$structure     = self::structure( is_array( $extraction['structure'] ?? null ) ? $extraction['structure'] : array() );
		$ai_readiness  = Semantic_Structure::ai_readiness( $effective_extraction, $sections, $blocks );
		$ai_readiness  = self::apply_hierarchy_readiness( $ai_readiness, $relationships, $numbered );
		$hierarchy     = self::hierarchy_projection( $relationships, $numbered );
		$extraction_projection = self::extraction_projection( $effective_extraction );

		$source_payload = array(
			'schema_version' => self::SCHEMA_VERSION,
			'source_kind'    => $source_kind,
			'title'          => $title,
			'sections'       => $sections,
			'blocks'         => $blocks,
			'structure'      => $structure,
			'hierarchy'      => $hierarchy,
		);
		try {
			$source_hash = Canonical_JSON::hash( $source_payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_source_hash_failed', 'Falha ao calcular source_hash.', array( 'exception' => get_class( $error ) ) );
		}

		$document = array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id'        => $post_id,
			'source_kind'    => $source_kind,
			'source_hash'    => $source_hash,
			'document_hash'  => '',
			'title'          => $title,
			'canonical_url'  => $canonical_url,
			'modified_gmt'   => $modified_gmt,
			'sections'       => $sections,
			'blocks'         => $blocks,
			'structure'      => $structure,
			'hierarchy'      => $hierarchy,
			'ai_readiness'   => $ai_readiness,
			'extraction'     => $extraction_projection,
		);

		$hash_payload = $document;
		unset( $hash_payload['document_hash'], $hash_payload['canonical_url'], $hash_payload['modified_gmt'] );
		try {
			$document['document_hash'] = Canonical_JSON::hash( $hash_payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_document_hash_failed', 'Falha ao calcular document_hash.', array( 'exception' => get_class( $error ) ) );
		}
		return $document;
	}

	/** @param array<string,mixed> $document @return string|\WP_Error */
	public static function canonical_json( array $document ): string|\WP_Error {
		try {
			return Canonical_JSON::encode( $document );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_document_json_failed', 'Falha ao serializar Knowledge Document.', array( 'exception' => get_class( $error ) ) );
		}
	}

	/** @param array<string,mixed> $readiness @param array<string,mixed> $relationships @param array<string,mixed> $numbered @return array<string,mixed> */
	private static function apply_hierarchy_readiness( array $readiness, array $relationships, array $numbered ): array {
		$cardinality_complete = true === ( $readiness['structure_complete'] ?? false );
		$relationship_complete = true === ( $relationships['complete'] ?? true );
		$reasons = is_array( $readiness['reasons'] ?? null ) ? array_values( array_map( 'strval', $readiness['reasons'] ) ) : array();
		$relationship_reasons = is_array( $relationships['reasons'] ?? null ) ? array_values( array_map( 'strval', $relationships['reasons'] ) ) : array();
		$numbered_warnings = is_array( $numbered['warnings'] ?? null ) ? array_values( array_map( 'strval', $numbered['warnings'] ) ) : array();
		$reasons = self::unique_preserve_order( array_merge( $reasons, $relationship_reasons, $numbered_warnings ) );

		$status = (string) ( $readiness['status'] ?? 'not_ready' );
		if ( ! $relationship_complete ) {
			$status = 'not_ready';
		} elseif ( 'not_ready' !== $status && ! empty( $numbered_warnings ) ) {
			$status = 'review_required';
		}

		$readiness['status'] = $status;
		$readiness['reasons'] = $reasons;
		$readiness['cardinality_complete'] = $cardinality_complete;
		$readiness['relationship_complete'] = $relationship_complete;
		$readiness['structure_complete'] = $cardinality_complete && $relationship_complete;
		$readiness['numbered_hierarchy_status'] = (string) ( $numbered['status'] ?? 'none' );
		return $readiness;
	}

	/** @param array<string,mixed> $relationships @param array<string,mixed> $numbered @return array<string,mixed> */
	private static function hierarchy_projection( array $relationships, array $numbered ): array {
		return array(
			'relationship_fidelity' => array(
				'applicable'       => (bool) ( $relationships['applicable'] ?? false ),
				'source_count'     => max( 0, (int) ( $relationships['source_count'] ?? 0 ) ),
				'heading_enforced' => (bool) ( $relationships['heading_enforced'] ?? false ),
				'expected'         => is_array( $relationships['expected'] ?? null ) ? $relationships['expected'] : array(),
				'actual'           => is_array( $relationships['actual'] ?? null ) ? $relationships['actual'] : array(),
				'complete'         => (bool) ( $relationships['complete'] ?? true ),
				'reasons'          => is_array( $relationships['reasons'] ?? null ) ? array_values( array_map( 'strval', $relationships['reasons'] ) ) : array(),
			),
			'numbered_hierarchy' => array(
				'status'              => (string) ( $numbered['status'] ?? 'none' ),
				'strong_signal_count' => max( 0, (int) ( $numbered['strong_signal_count'] ?? 0 ) ),
				'resolved_edges'      => max( 0, (int) ( $numbered['resolved_edges'] ?? 0 ) ),
				'nodes'               => is_array( $numbered['nodes'] ?? null ) ? $numbered['nodes'] : array(),
				'edges'               => is_array( $numbered['edges'] ?? null ) ? $numbered['edges'] : array(),
				'warnings'            => is_array( $numbered['warnings'] ?? null ) ? array_values( array_map( 'strval', $numbered['warnings'] ) ) : array(),
			),
		);
	}

	/** @param array<string,mixed> $structure @return array<string,int> */
	private static function structure( array $structure ): array {
		$keys = array(
			'headings', 'paragraphs', 'lists', 'list_items', 'tables', 'table_rows',
			'table_cells', 'images', 'links', 'code_blocks', 'shortcodes',
		);
		$out = array();
		foreach ( $keys as $key ) {
			$out[ $key ] = max( 0, (int) ( $structure[ $key ] ?? 0 ) );
		}
		return $out;
	}

	/** @param array<string,mixed> $extraction @return array<string,mixed> */
	private static function extraction_projection( array $extraction ): array {
		$strategies = is_array( $extraction['strategies'] ?? null ) ? array_values( array_map( 'strval', $extraction['strategies'] ) ) : array();
		$warnings   = is_array( $extraction['warnings'] ?? null ) ? array_values( array_map( 'strval', $extraction['warnings'] ) ) : array();
		$compat     = is_array( $extraction['elementor_compatibility'] ?? null ) ? $extraction['elementor_compatibility'] : array();
		return array(
			'strategies'              => $strategies,
			'fallback_used'           => (bool) ( $extraction['fallback_used'] ?? false ),
			'warnings'                => $warnings,
			'elementor_compatibility' => array(
				'status'  => isset( $compat['status'] ) ? (string) $compat['status'] : 'review_required',
				'reasons' => is_array( $compat['reasons'] ?? null ) ? array_values( array_map( 'strval', $compat['reasons'] ) ) : array(),
			),
		);
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
