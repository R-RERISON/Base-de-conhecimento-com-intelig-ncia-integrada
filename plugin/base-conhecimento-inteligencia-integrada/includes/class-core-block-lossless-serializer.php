<?php
/**
 * In-memory, lossless Core Block serializer for SPEC-004.
 * No persistence. Uses only stable WordPress Core block primitives.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Core_Block_Lossless_Serializer {

	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed>|\WP_Error */
	public static function serialize_source( array $source ): array|\WP_Error {
		$status = (string) ( $source['status'] ?? 'review_required' );
		$strategy = (string) ( $source['strategy'] ?? '' );
		$fidelity_hash = (string) ( $source['fidelity_hash'] ?? '' );

		if ( ! preg_match( '/^[a-f0-9]{64}$/', $fidelity_hash ) ) {
			return new \WP_Error( 'bdc_kb_lossless_serializer_invalid_source', 'Migration Fidelity Source inválida.' );
		}
		if ( 'not_applicable' === $status ) {
			return self::result( $source, 'not_applicable', array(), '', array() );
		}
		if ( 'review_required' === $status ) {
			return self::result( $source, 'review_required', array(), '', array( 'LOSSLESS_SERIALIZER_SOURCE_REVIEW_REQUIRED' ) );
		}
		if ( 'ready' !== $status ) {
			return self::result( $source, 'blocked', array(), '', array( 'LOSSLESS_SERIALIZER_SOURCE_NOT_READY' ) );
		}

		$units = is_array( $source['units'] ?? null ) ? $source['units'] : array();
		if ( 'native_core_blocks' === $strategy ) {
			$raw = isset( $units[0]['raw'] ) && is_string( $units[0]['raw'] ) ? $units[0]['raw'] : '';
			return self::result( $source, 'native_noop', array(), $raw, array() );
		}

		$blocks = array();
		foreach ( $units as $unit ) {
			if ( ! is_array( $unit ) ) {
				continue;
			}
			$kind = (string) ( $unit['kind'] ?? '' );
			$raw = isset( $unit['raw'] ) && is_string( $unit['raw'] ) ? $unit['raw'] : '';
			if ( in_array( $kind, array( 'post_content_rich_html', 'post_content_plain_text', 'elementor_text_editor_html' ), true ) ) {
				$blocks[] = self::raw_block( 'core/freeform', $raw );
				continue;
			}
			if ( 'elementor_shortcode' === $kind ) {
				$blocks[] = self::raw_block( 'core/shortcode', $raw );
				continue;
			}
			return self::result( $source, 'review_required', $blocks, '', array( 'LOSSLESS_SERIALIZER_UNSUPPORTED_UNIT:' . ( '' !== $kind ? $kind : 'unknown' ) ) );
		}

		if ( ! function_exists( 'serialize_blocks' ) ) {
			return new \WP_Error( 'bdc_kb_lossless_serializer_core_unavailable', 'serialize_blocks() indisponível no WordPress Core.' );
		}

		$serialized = serialize_blocks( $blocks );
		if ( ! is_string( $serialized ) ) {
			return new \WP_Error( 'bdc_kb_lossless_serializer_failed', 'Falha ao serializar Core Blocks.' );
		}
		return self::result( $source, 'serialized_in_memory', $blocks, $serialized, array() );
	}

	/** @return array<string,mixed> */
	private static function raw_block( string $name, string $raw ): array {
		return array(
			'blockName' => $name,
			'attrs' => array(),
			'innerBlocks' => array(),
			'innerHTML' => $raw,
			'innerContent' => array( $raw ),
		);
	}

	/** @param array<int,array<string,mixed>> $blocks @param array<int,string> $warnings @return array<string,mixed> */
	private static function result( array $source, string $status, array $blocks, string $serialized, array $warnings ): array {
		$unit_manifest = array();
		foreach ( (array) ( $source['units'] ?? array() ) as $unit ) {
			if ( is_array( $unit ) ) {
				$unit_manifest[] = array(
					'kind' => (string) ( $unit['kind'] ?? '' ),
					'raw_sha256' => (string) ( $unit['raw_sha256'] ?? '' ),
					'bytes' => (int) ( $unit['bytes'] ?? 0 ),
				);
			}
		}
		$manifest = array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => (int) ( $source['post_id'] ?? 0 ),
			'source_fidelity_hash' => (string) ( $source['fidelity_hash'] ?? '' ),
			'status' => $status,
			'unit_manifest' => $unit_manifest,
			'block_names' => array_values( array_map( static fn ( array $block ): string => (string) ( $block['blockName'] ?? '' ), $blocks ) ),
			'serialized_sha256' => hash( 'sha256', $serialized ),
			'warnings' => $warnings,
		);
		try {
			$serialization_hash = Canonical_JSON::hash( $manifest );
		} catch ( \JsonException ) {
			$serialization_hash = '';
		}
		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => (int) ( $source['post_id'] ?? 0 ),
			'source_kind' => (string) ( $source['source_kind'] ?? '' ),
			'source_fidelity_hash' => (string) ( $source['fidelity_hash'] ?? '' ),
			'status' => $status,
			'blocks' => $blocks,
			'serialized_post_content' => $serialized,
			'serialized_sha256' => hash( 'sha256', $serialized ),
			'serialization_hash' => $serialization_hash,
			'warnings' => $warnings,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'safety' => array(
				'read_only' => true,
				'persists_state' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'renders_blocks' => false,
				'executes_shortcodes' => false,
				'calls_external_network' => false,
				'depends_on_gutenberg_plugin' => false,
			),
		);
	}
}
