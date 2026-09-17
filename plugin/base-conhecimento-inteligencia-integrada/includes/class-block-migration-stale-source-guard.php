<?php
/**
 * Generic fail-closed stale-source guard for Core Block migration.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Block_Migration_Stale_Source_Guard {

	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed> */
	public static function assess( array $planned, array $current ): array {
		$reasons = array();
		$planned_kind = (string) ( $planned['source_kind'] ?? '' );
		$current_kind = (string) ( $current['source_kind'] ?? '' );
		$planned_hash = (string) ( $planned['fidelity_hash'] ?? '' );
		$current_hash = (string) ( $current['fidelity_hash'] ?? '' );
		$planned_material = is_array( $planned['source_material'] ?? null ) ? $planned['source_material'] : array();
		$current_material = is_array( $current['source_material'] ?? null ) ? $current['source_material'] : array();

		if ( '' === $planned_hash || ! hash_equals( $planned_hash, $current_hash ) ) {
			$reasons[] = 'FIDELITY_HASH_CHANGED';
		}
		if ( $planned_kind !== $current_kind ) {
			$reasons[] = 'SOURCE_KIND_CHANGED';
		}
		foreach ( array( 'post_content_sha256', 'elementor_data_sha256' ) as $field ) {
			$a = (string) ( $planned_material[ $field ] ?? '' );
			$b = (string) ( $current_material[ $field ] ?? '' );
			if ( '' === $a || '' === $b || ! hash_equals( $a, $b ) ) {
				$reasons[] = 'SOURCE_MATERIAL_CHANGED:' . $field;
			}
		}

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'status' => empty( $reasons ) ? 'fresh' : 'stale',
			'reasons' => array_values( array_unique( $reasons ) ),
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'safety' => array(
				'read_only' => true,
				'persists_state' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
			),
		);
	}

	/** @return bool|\WP_Error */
	public static function assert_fresh( int $post_id, array $planned ): bool|\WP_Error {
		$current = Migration_Fidelity_Source::build( $post_id );
		if ( $current instanceof \WP_Error ) {
			return $current;
		}
		$assessment = self::assess( $planned, $current );
		if ( 'fresh' === ( $assessment['status'] ?? '' ) ) {
			return true;
		}
		return new \WP_Error(
			'bdc_kb_block_migration_source_stale',
			'Fonte editorial alterada após o planejamento; migração bloqueada.',
			array( 'status' => 409, 'assessment' => $assessment )
		);
	}
}
