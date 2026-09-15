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

	public const SCHEMA_VERSION = '1.0.0';

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

		$url = function_exists( 'get_permalink' ) ? get_permalink( $post_id ) : '';
		return self::from_extraction( $post, $extraction, is_string( $url ) ? $url : '' );
	}

	/**
	 * Monta o documento a partir de uma extração já realizada.
	 * Útil para composição determinística e testes; não efetua writes.
	 *
	 * @param object              $post       WP_Post ou projeção equivalente.
	 * @param array<string,mixed> $extraction Saída do Content_Extractor.
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function from_extraction( object $post, array $extraction, string $canonical_url = '' ): array|\WP_Error {
		$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
		if ( $post_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_invalid_post', 'Post inválido para Knowledge Document.' );
		}

		$title        = Content_Normalizer::text( isset( $post->post_title ) ? (string) $post->post_title : '' );
		$modified_gmt = isset( $post->post_modified_gmt ) ? (string) $post->post_modified_gmt : '';
		$source_kind  = isset( $extraction['source_kind'] ) ? (string) $extraction['source_kind'] : 'empty';
		$sections     = self::sections( is_array( $extraction['fragments'] ?? null ) ? $extraction['fragments'] : array() );
		$structure    = self::structure( is_array( $extraction['structure'] ?? null ) ? $extraction['structure'] : array() );
		$extraction_projection = self::extraction_projection( $extraction );

		$source_payload = array(
			'schema_version' => self::SCHEMA_VERSION,
			'source_kind'    => $source_kind,
			'title'          => $title,
			'sections'       => $sections,
			'structure'      => $structure,
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
			'structure'      => $structure,
			'extraction'     => $extraction_projection,
		);

		// Hash semântico do documento: exclui campos operacionais mutáveis e o próprio hash.
		$hash_payload = $document;
		unset( $hash_payload['document_hash'], $hash_payload['canonical_url'], $hash_payload['modified_gmt'] );

		try {
			$document['document_hash'] = Canonical_JSON::hash( $hash_payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_document_hash_failed', 'Falha ao calcular document_hash.', array( 'exception' => get_class( $error ) ) );
		}

		return $document;
	}

	/**
	 * Serializa um Knowledge Document de forma determinística.
	 *
	 * @param array<string,mixed> $document
	 * @return string|\WP_Error
	 */
	public static function canonical_json( array $document ): string|\WP_Error {
		try {
			return Canonical_JSON::encode( $document );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_document_json_failed', 'Falha ao serializar Knowledge Document.', array( 'exception' => get_class( $error ) ) );
		}
	}

	/** @param array<int,array<string,mixed>> $fragments @return array<int,array<string,mixed>> */
	private static function sections( array $fragments ): array {
		$out = array();
		foreach ( $fragments as $fragment ) {
			if ( ! is_array( $fragment ) ) {
				continue;
			}
			$text = Content_Normalizer::text(
				isset( $fragment['text'] ) ? (string) $fragment['text'] : '',
				'code' === (string) ( $fragment['kind'] ?? '' )
			);
			if ( '' === $text ) {
				continue;
			}

			$kind = isset( $fragment['kind'] ) ? (string) $fragment['kind'] : 'paragraph';
			$section = array(
				'kind'    => $kind,
				'heading' => 'heading' === $kind ? $text : '',
				'text'    => $text,
				'ordinal' => count( $out ),
				'source'  => isset( $fragment['source'] ) ? (string) $fragment['source'] : '',
			);

			if ( isset( $fragment['meta'] ) && is_array( $fragment['meta'] ) && ! empty( $fragment['meta'] ) ) {
				$section['meta'] = $fragment['meta'];
			}
			$out[] = $section;
		}
		return $out;
	}

	/** @param array<string,mixed> $structure @return array<string,int> */
	private static function structure( array $structure ): array {
		$keys = array( 'headings', 'lists', 'tables', 'images', 'links', 'code_blocks', 'shortcodes' );
		$out  = array();
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
}
