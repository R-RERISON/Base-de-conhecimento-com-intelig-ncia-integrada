<?php
/**
 * Read-only canary readiness assessment for SPEC-004 / G-245 / T087A.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Canary_Readiness {

	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed>|\WP_Error */
	public static function assess( array $context ): array|\WP_Error {
		$candidate = is_array( $context['candidate'] ?? null ) ? $context['candidate'] : array();
		$dry_run = is_array( $context['dry_run'] ?? null ) ? $context['dry_run'] : array();
		$gateway = is_array( $context['gateway'] ?? null ) ? $context['gateway'] : array();
		$stale = is_array( $context['stale'] ?? null ) ? $context['stale'] : array();
		$post_id = (int) ( $candidate['post_id'] ?? 0 );
		if ( $post_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_canary_invalid_candidate', 'Candidato canário inválido.' );
		}

		$checks = array(
			'single_item_scope' => 1 === (int) ( $context['canary_scope_size'] ?? 0 ),
			'journal_storage_durable' => true === ( $context['journal_storage_durable'] ?? false ),
			'rollback_capsule_integrity' => true === ( $context['rollback_capsule_integrity'] ?? false ),
			'dry_run_ready' => 'ready' === (string) ( $dry_run['dry_run_status'] ?? '' ),
			'stale_source_fresh' => true === ( $stale['is_fresh'] ?? false ) && 'fresh' === (string) ( $stale['status'] ?? '' ),
			'gateway_version_compatible' => 'compatible_read_only' === (string) ( $gateway['compatibility_status'] ?? '' ),
			'candidate_identity_matches' => self::identity_matches( $candidate, $dry_run ),
			'dry_run_zero_write' => false === ( $dry_run['execution_allowed'] ?? true ) && false === ( $dry_run['writer_allowed'] ?? true ) && false === ( $dry_run['migration_execution_allowed'] ?? true ),
		);

		$technical = true;
		$reasons = array();
		foreach ( $checks as $name => $ok ) {
			if ( ! $ok ) {
				$technical = false;
				$reasons[] = 'PRECONDITION_FAILED:' . strtoupper( $name );
			}
		}

		$authorization = true === ( $context['explicit_human_authorization'] ?? false );
		if ( ! $technical ) {
			$status = 'blocked';
		} elseif ( ! $authorization ) {
			$status = 'awaiting_explicit_authorization';
			$reasons[] = 'EXPLICIT_HUMAN_AUTHORIZATION_REQUIRED';
		} else {
			$status = 'ready_for_controlled_canary';
		}

		$result = array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => $post_id,
			'status' => $status,
			'technical_preconditions_satisfied' => $technical,
			'explicit_human_authorization' => $authorization,
			'checks' => $checks,
			'reasons' => $reasons,
			'canary_scope_size' => (int) ( $context['canary_scope_size'] ?? 0 ),
			'execution_allowed' => false,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'requires_separate_mutable_executor' => true,
			'requires_immediate_pre_write_stale_recheck' => true,
			'requires_write_ahead_journal' => true,
			'requires_post_write_verification' => true,
			'requires_rollback_proof' => true,
			'safety' => array(
				'persists_state' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
			),
			'readiness_hash' => '',
		);

		$payload = $result;
		unset( $payload['readiness_hash'] );
		try {
			$result['readiness_hash'] = Canonical_JSON::hash( $payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_canary_hash_failed', 'Falha ao calcular readiness_hash.' );
		}
		return $result;
	}

	private static function identity_matches( array $candidate, array $dry_run ): bool {
		if ( (int) ( $candidate['post_id'] ?? 0 ) !== (int) ( $dry_run['post_id'] ?? 0 ) ) {
			return false;
		}
		foreach ( array( 'source_hash_before', 'projection_hash', 'dry_run_hash' ) as $key ) {
			$a = strtolower( trim( (string) ( $candidate[ $key ] ?? '' ) ) );
			$b = strtolower( trim( (string) ( $dry_run[ $key ] ?? '' ) ) );
			if ( ! self::is_sha256( $a ) || ! self::is_sha256( $b ) || ! hash_equals( $a, $b ) ) {
				return false;
			}
		}
		return true;
	}

	private static function is_sha256( string $value ): bool {
		return 1 === preg_match( '/^[a-f0-9]{64}$/', $value );
	}
}
