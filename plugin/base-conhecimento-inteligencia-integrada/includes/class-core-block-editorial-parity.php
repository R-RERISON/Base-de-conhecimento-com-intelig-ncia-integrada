<?php
/**
 * Static editorial parity checks for lossless Core Block migration.
 * Never renders blocks or executes shortcodes.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Core_Block_Editorial_Parity {

	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed> */
	public static function registry_status(): array {
		$required = array( 'core/freeform', 'core/shortcode' );
		$missing = array();
		if ( ! class_exists( '\WP_Block_Type_Registry' ) ) {
			$missing = $required;
		} else {
			$registry = \WP_Block_Type_Registry::get_instance();
			foreach ( $required as $name ) {
				if ( ! $registry->is_registered( $name ) ) {
					$missing[] = $name;
				}
			}
		}
		return array(
			'required' => $required,
			'missing' => $missing,
			'pass' => empty( $missing ),
		);
	}

	/** @param array<int,array<string,mixed>> $parsed_blocks @return array<string,mixed> */
	public static function assess( array $source, array $serialization, array $parsed_blocks ): array {
		$serializer_status = (string) ( $serialization['status'] ?? 'blocked' );
		$warnings = array();
		$mismatches = 0;
		$expected = array();

		if ( 'not_applicable' === $serializer_status ) {
			return self::result( 'not_applicable', 0, array(), array() );
		}
		if ( 'review_required' === $serializer_status ) {
			return self::result( 'review_required', 0, array( 'EDITORIAL_PARITY_SOURCE_REVIEW_REQUIRED' ), array() );
		}
		if ( 'native_noop' === $serializer_status ) {
			$units = (array) ( $source['units'] ?? array() );
			$raw = isset( $units[0]['raw'] ) && is_string( $units[0]['raw'] ) ? $units[0]['raw'] : '';
			$serialized = (string) ( $serialization['serialized_post_content'] ?? '' );
			if ( ! hash_equals( hash( 'sha256', $raw ), hash( 'sha256', $serialized ) ) ) {
				$mismatches++;
				$warnings[] = 'EDITORIAL_PARITY_NATIVE_NOOP_CHANGED';
			}
			return self::result( 0 === $mismatches ? 'native_noop' : 'mismatch', $mismatches, $warnings, array() );
		}
		if ( 'serialized_in_memory' !== $serializer_status ) {
			return self::result( 'blocked', 1, array( 'EDITORIAL_PARITY_SERIALIZER_NOT_READY' ), array() );
		}

		foreach ( (array) ( $source['units'] ?? array() ) as $unit ) {
			if ( ! is_array( $unit ) ) {
				continue;
			}
			$kind = (string) ( $unit['kind'] ?? '' );
			$name = self::expected_block_name( $kind );
			if ( '' === $name ) {
				$mismatches++;
				$warnings[] = 'EDITORIAL_PARITY_UNSUPPORTED_UNIT:' . ( '' !== $kind ? $kind : 'unknown' );
				continue;
			}
			$expected[] = array(
				'block_name' => $name,
				'raw_sha256' => (string) ( $unit['raw_sha256'] ?? '' ),
			);
		}

		if ( count( $expected ) !== count( $parsed_blocks ) ) {
			$mismatches++;
			$warnings[] = 'EDITORIAL_PARITY_BLOCK_COUNT_MISMATCH';
		}

		$limit = min( count( $expected ), count( $parsed_blocks ) );
		for ( $i = 0; $i < $limit; $i++ ) {
			$parsed = is_array( $parsed_blocks[ $i ] ?? null ) ? $parsed_blocks[ $i ] : array();
			$actual_name = (string) ( $parsed['blockName'] ?? '' );
			$actual_raw = isset( $parsed['innerHTML'] ) && is_string( $parsed['innerHTML'] ) ? $parsed['innerHTML'] : '';
			if ( $expected[ $i ]['block_name'] !== $actual_name ) {
				$mismatches++;
				$warnings[] = 'EDITORIAL_PARITY_BLOCK_NAME_MISMATCH';
			}
			if ( ! hash_equals( $expected[ $i ]['raw_sha256'], hash( 'sha256', $actual_raw ) ) ) {
				$mismatches++;
				$warnings[] = 'EDITORIAL_PARITY_RAW_PAYLOAD_MISMATCH';
			}
		}

		return self::result( 0 === $mismatches ? 'pass' : 'mismatch', $mismatches, $warnings, $expected );
	}

	private static function expected_block_name( string $kind ): string {
		if ( in_array( $kind, array( 'post_content_rich_html', 'post_content_plain_text', 'elementor_text_editor_html' ), true ) ) {
			return 'core/freeform';
		}
		if ( 'elementor_shortcode' === $kind ) {
			return 'core/shortcode';
		}
		return '';
	}

	/** @param array<int,string> $warnings @param array<int,array<string,string>> $expected @return array<string,mixed> */
	private static function result( string $status, int $mismatches, array $warnings, array $expected ): array {
		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'status' => $status,
			'mismatches' => $mismatches,
			'warnings' => array_values( array_unique( $warnings ) ),
			'expected_manifest' => $expected,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'safety' => array(
				'read_only' => true,
				'renders_blocks' => false,
				'executes_shortcodes' => false,
				'persists_state' => false,
			),
		);
	}
}
