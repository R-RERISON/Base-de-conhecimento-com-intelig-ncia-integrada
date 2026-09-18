<?php
/**
 * Read-only stale-source guard for SPEC-004 / G-245 / T084.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Stale_Source_Guard {

	public const CONTRACT_VERSION = '1.0.0';

	/** @return array<string,mixed>|\WP_Error */
	public static function inspect_post( int $post_id, string $expected_source_hash ): array|\WP_Error {
		if ( $post_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_stale_invalid_post_id', 'post_id inválido para stale-source guard.' );
		}
		$document = Knowledge_Document::build( $post_id );
		if ( $document instanceof \WP_Error ) {
			return $document;
		}
		$current = strtolower( trim( (string) ( $document['source_hash'] ?? '' ) ) );
		return self::assess( $expected_source_hash, $current );
	}

	/** @return array<string,mixed> */
	public static function assess( string $expected_source_hash, string $current_source_hash ): array {
		$expected = strtolower( trim( $expected_source_hash ) );
		$current = strtolower( trim( $current_source_hash ) );
		$reasons = array();
		$status = 'fresh';

		if ( ! self::is_sha256( $expected ) ) {
			$status = 'blocking';
			$reasons[] = 'EXPECTED_SOURCE_HASH_INVALID';
		}
		if ( ! self::is_sha256( $current ) ) {
			$status = 'blocking';
			$reasons[] = 'CURRENT_SOURCE_HASH_INVALID';
		}
		if ( 'blocking' !== $status && ! hash_equals( $expected, $current ) ) {
			$status = 'stale';
			$reasons[] = 'SOURCE_CHANGED_SINCE_PROJECTION';
		}

		return array(
			'contract_version' => self::CONTRACT_VERSION,
			'expected_source_hash' => self::is_sha256( $expected ) ? $expected : null,
			'current_source_hash' => self::is_sha256( $current ) ? $current : null,
			'status' => $status,
			'is_fresh' => 'fresh' === $status,
			'is_stale' => 'stale' === $status,
			'reasons' => $reasons,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'safety' => array(
				'persists_state' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
			),
		);
	}

	/** @return bool|\WP_Error */
	public static function assert_fresh( array $assessment ): bool|\WP_Error {
		if ( true === ( $assessment['is_fresh'] ?? false ) && 'fresh' === (string) ( $assessment['status'] ?? '' ) ) {
			return true;
		}
		return new \WP_Error(
			'bdc_kb_stale_source_blocked',
			'Source alterado ou inválido desde o Projection Plan; execução bloqueada.',
			array( 'status' => 409, 'guard' => $assessment )
		);
	}

	private static function is_sha256( string $value ): bool {
		return 1 === preg_match( '/^[a-f0-9]{64}$/', $value );
	}
}
