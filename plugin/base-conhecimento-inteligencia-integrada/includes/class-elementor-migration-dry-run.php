<?php
/**
 * Deterministic zero-write dry-run for SPEC-004 / G-245 / T085.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Migration_Dry_Run {

	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed>|\WP_Error */
	public static function build( int $post_id ): array|\WP_Error {
		$plan = Elementor_Projection_Plan::build( $post_id );
		if ( $plan instanceof \WP_Error ) {
			return $plan;
		}

		$expected_hash = (string) ( $plan['source_hash_before'] ?? '' );
		$stale = Elementor_Stale_Source_Guard::inspect_post( $post_id, $expected_hash );
		if ( $stale instanceof \WP_Error ) {
			return $stale;
		}

		$gateway = Elementor_Gateway::inspect_runtime();
		return self::simulate( $plan, $gateway, $stale );
	}

	/**
	 * Pure deterministic simulator. It never persists state and never calls a writer.
	 *
	 * @param array<string,mixed> $plan
	 * @param array<string,mixed> $gateway
	 * @param array<string,mixed> $stale
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function simulate( array $plan, array $gateway, array $stale ): array|\WP_Error {
		$post_id = (int) ( $plan['post_id'] ?? 0 );
		$source_hash = strtolower( trim( (string) ( $plan['source_hash_before'] ?? '' ) ) );
		$projection_hash = strtolower( trim( (string) ( $plan['projection_hash'] ?? '' ) ) );
		$plan_status = (string) ( $plan['plan_status'] ?? '' );
		$requires_review = true === ( $plan['requires_review'] ?? false );

		if ( $post_id <= 0 || ! self::is_sha256( $source_hash ) || ! self::is_sha256( $projection_hash ) ) {
			return new \WP_Error( 'bdc_kb_dry_run_invalid_plan', 'Projection Plan inválido para dry-run.' );
		}

		$reasons = array();
		$status = 'ready';
		$simulated_action = 'would_prepare_journal_then_apply_projection';

		$plan_safety = is_array( $plan['safety'] ?? null ) ? $plan['safety'] : array();
		if ( true === ( $plan['writer_allowed'] ?? false )
			|| true === ( $plan_safety['writes_post_content'] ?? false )
			|| true === ( $plan_safety['writes_elementor_data'] ?? false )
			|| true === ( $plan_safety['persists_plan'] ?? false ) ) {
			$status = 'blocked';
			$reasons[] = 'PROJECTION_PLAN_SAFETY_VIOLATION';
		}

		if ( 'blocked' === $plan_status ) {
			$status = 'blocked';
			$reasons[] = 'PROJECTION_PLAN_BLOCKED';
		} elseif ( 'review_required' === $plan_status || $requires_review ) {
			if ( 'blocked' !== $status ) {
				$status = 'review_required';
			}
			$reasons[] = 'PROJECTION_REQUIRES_REVIEW';
		} elseif ( in_array( $plan_status, array( 'native_noop', 'not_applicable' ), true ) ) {
			if ( 'blocked' !== $status ) {
				$status = 'noop';
			}
			$simulated_action = 'no_migration_needed';
		} elseif ( 'projectable' !== $plan_status ) {
			$status = 'blocked';
			$reasons[] = 'PROJECTION_PLAN_STATUS_UNKNOWN:' . $plan_status;
		}

		$stale_status = (string) ( $stale['status'] ?? '' );
		if ( true !== ( $stale['is_fresh'] ?? false ) || 'fresh' !== $stale_status ) {
			$status = 'blocked';
			$reasons[] = 'STALE_SOURCE_GUARD_NOT_FRESH:' . $stale_status;
		}

		$compatibility = (string) ( $gateway['compatibility_status'] ?? '' );
		if ( 'blocking' === $compatibility ) {
			$status = 'blocked';
			$reasons[] = 'GATEWAY_COMPATIBILITY_BLOCKING';
		} elseif ( 'review_required' === $compatibility && 'blocked' !== $status ) {
			$status = 'review_required';
			$reasons[] = 'GATEWAY_VERSION_REVIEW_REQUIRED';
		} elseif ( 'compatible_read_only' !== $compatibility ) {
			$status = 'blocked';
			$reasons[] = 'GATEWAY_COMPATIBILITY_UNKNOWN:' . $compatibility;
		}

		$reasons = array_values( array_unique( $reasons ) );
		$would_prepare_journal = 'ready' === $status && 'no_migration_needed' !== $simulated_action;
		$would_recheck_stale = $would_prepare_journal;
		$would_apply_projection = 'ready' === $status && 'no_migration_needed' !== $simulated_action;

		$result = array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => $post_id,
			'source_hash_before' => $source_hash,
			'projection_hash' => $projection_hash,
			'projection_plan_status' => $plan_status,
			'gateway_compatibility_status' => $compatibility,
			'stale_source_status' => $stale_status,
			'dry_run_status' => $status,
			'simulated_action' => $simulated_action,
			'reasons' => $reasons,
			'preconditions' => array(
				'journal_must_be_persisted_before_future_write' => true,
				'stale_source_must_be_rechecked_immediately_before_future_write' => true,
				'gateway_writer_authorization_required' => true,
			),
			'simulation' => array(
				'would_prepare_journal' => $would_prepare_journal,
				'would_recheck_stale_source' => $would_recheck_stale,
				'would_apply_projection' => $would_apply_projection,
			),
			'gateway_writer_denials' => is_array( $gateway['writer_denials'] ?? null ) ? array_values( array_map( 'strval', $gateway['writer_denials'] ) ) : array(),
			'execution_allowed' => false,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'safety' => array(
				'persists_state' => false,
				'persists_journal' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
			),
			'dry_run_hash' => '',
		);

		$hash_payload = $result;
		unset( $hash_payload['dry_run_hash'] );
		try {
			$result['dry_run_hash'] = Canonical_JSON::hash( $hash_payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_dry_run_hash_failed', 'Falha ao calcular dry_run_hash.' );
		}

		return $result;
	}

	/** @return string|\WP_Error */
	public static function canonical_json( array $dry_run ): string|\WP_Error {
		try {
			return Canonical_JSON::encode( $dry_run );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_dry_run_json_failed', 'Falha ao serializar dry-run.' );
		}
	}

	private static function is_sha256( string $value ): bool {
		return 1 === preg_match( '/^[a-f0-9]{64}$/', $value );
	}
}
