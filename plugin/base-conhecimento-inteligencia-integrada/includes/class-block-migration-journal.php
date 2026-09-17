<?php
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Journal {
	public const SCHEMA_VERSION = '1.0.0';
	public const FAMILY = 'core_blocks';
	public const STATE_PREPARED = 'prepared';
	public const STATE_APPLIED = 'applied';
	public const STATE_PARTIAL_FAILURE = 'partial_failure';
	public const STATE_ROLLED_BACK = 'rolled_back';

	public static function prepare( array $input ): array|\WP_Error {
		$run_id = trim( (string) ( $input['run_id'] ?? '' ) );
		$post_id = (int) ( $input['post_id'] ?? 0 );
		$fidelity_hash = strtolower( trim( (string) ( $input['fidelity_hash_before'] ?? '' ) ) );
		$serialization_hash = strtolower( trim( (string) ( $input['serialization_hash'] ?? '' ) ) );
		$recorded_at = trim( (string) ( $input['recorded_at'] ?? '' ) );
		$source_kind = trim( (string) ( $input['source_kind'] ?? '' ) );
		$before = is_array( $input['before'] ?? null ) ? $input['before'] : array();

		if ( 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,128}$/', $run_id ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_invalid_run_id', 'run_id inválido.' );
		}
		if ( $post_id <= 0 || ! self::is_sha256( $fidelity_hash ) || ! self::is_sha256( $serialization_hash ) || '' === $recorded_at || '' === $source_kind ) {
			return new \WP_Error( 'bdc_kb_block_journal_invalid_identity', 'Identidade/hash obrigatórios inválidos.' );
		}
		$rollback = self::normalize_before( $before );
		if ( $rollback instanceof \WP_Error ) { return $rollback; }
		$rollback_hash = self::payload_hash( $rollback );
		if ( $rollback_hash instanceof \WP_Error ) { return $rollback_hash; }

		$record = array(
			'schema_version' => self::SCHEMA_VERSION,
			'family' => self::FAMILY,
			'journal_id' => hash( 'sha256', $run_id . '|' . $post_id . '|' . $fidelity_hash . '|' . $serialization_hash ),
			'run_id' => $run_id,
			'post_id' => $post_id,
			'source_kind' => $source_kind,
			'state' => self::STATE_PREPARED,
			'recorded_at' => $recorded_at,
			'fidelity_hash_before' => $fidelity_hash,
			'serialization_hash' => $serialization_hash,
			'post_content_sha256_after' => null,
			'elementor_data_sha256_after' => null,
			'rollback_payload_hash' => $rollback_hash,
			'rollback_payload' => $rollback,
			'rollback_required' => true,
			'journal_persisted' => false,
			'journal_must_be_persisted_before_write' => true,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'journal_hash' => '',
		);
		$hash = self::record_hash( $record );
		if ( $hash instanceof \WP_Error ) { return $hash; }
		$record['journal_hash'] = $hash;
		return $record;
	}

	public static function mark_persisted( array $record ): array|\WP_Error {
		$valid = self::validate_record( $record, false );
		if ( $valid instanceof \WP_Error ) { return $valid; }
		if ( self::STATE_PREPARED !== (string) $record['state'] ) {
			return new \WP_Error( 'bdc_kb_block_journal_persist_state', 'Somente prepared pode ser persistido.' );
		}
		if ( true === ( $record['journal_persisted'] ?? false ) ) { return $record; }
		$record['journal_persisted'] = true;
		return self::rehash( $record );
	}

	public static function mark_applied( array $record, string $post_content_sha256_after, string $elementor_data_sha256_after ): array|\WP_Error {
		return self::transition_after_write( $record, self::STATE_APPLIED, $post_content_sha256_after, $elementor_data_sha256_after );
	}

	public static function mark_partial_failure( array $record, string $post_content_sha256_after, string $elementor_data_sha256_after ): array|\WP_Error {
		return self::transition_after_write( $record, self::STATE_PARTIAL_FAILURE, $post_content_sha256_after, $elementor_data_sha256_after );
	}

	public static function rollback_decision( array $record, string $current_post_content_sha256, string $current_elementor_data_sha256 ): array {
		$state = (string) ( $record['state'] ?? '' );
		if ( self::STATE_ROLLED_BACK === $state ) {
			return array( 'allowed' => false, 'status' => 'noop_already_rolled_back', 'idempotent_noop' => true, 'reasons' => array() );
		}
		if ( ! in_array( $state, array( self::STATE_APPLIED, self::STATE_PARTIAL_FAILURE ), true ) ) {
			return array( 'allowed' => false, 'status' => 'blocked_invalid_state', 'idempotent_noop' => false, 'reasons' => array( 'ROLLBACK_STATE_NOT_ELIGIBLE:' . $state ) );
		}
		$expected_content = strtolower( (string) ( $record['post_content_sha256_after'] ?? '' ) );
		$expected_elementor = strtolower( (string) ( $record['elementor_data_sha256_after'] ?? '' ) );
		if ( ! self::is_sha256( $current_post_content_sha256 ) || ! self::is_sha256( $current_elementor_data_sha256 )
			|| ! hash_equals( $expected_content, strtolower( $current_post_content_sha256 ) )
			|| ! hash_equals( $expected_elementor, strtolower( $current_elementor_data_sha256 ) ) ) {
			return array( 'allowed' => false, 'status' => 'blocked_stale_rollback_target', 'idempotent_noop' => false, 'reasons' => array( 'ROLLBACK_TARGET_CHANGED' ) );
		}
		$valid = self::validate_record( $record, true );
		if ( $valid instanceof \WP_Error ) {
			return array( 'allowed' => false, 'status' => 'blocked_journal_integrity', 'idempotent_noop' => false, 'reasons' => array( 'ROLLBACK_JOURNAL_INVALID' ) );
		}
		return array( 'allowed' => true, 'status' => 'restore_before_snapshot', 'idempotent_noop' => false, 'reasons' => array() );
	}

	public static function mark_rolled_back( array $record, string $restored_post_content_sha256, string $restored_elementor_data_sha256 ): array|\WP_Error {
		if ( self::STATE_ROLLED_BACK === (string) ( $record['state'] ?? '' ) ) { return $record; }
		$payload = is_array( $record['rollback_payload'] ?? null ) ? $record['rollback_payload'] : array();
		$expected_content = hash( 'sha256', (string) ( $payload['post_content'] ?? '' ) );
		$expected_elementor = hash( 'sha256', (string) ( $payload['elementor_data'] ?? '' ) );
		if ( ! hash_equals( $expected_content, strtolower( $restored_post_content_sha256 ) ) || ! hash_equals( $expected_elementor, strtolower( $restored_elementor_data_sha256 ) ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_rollback_hash_mismatch', 'Rollback não restaurou o snapshot original.' );
		}
		$record['state'] = self::STATE_ROLLED_BACK;
		$record['rollback_required'] = false;
		$record['writer_allowed'] = false;
		$record['migration_execution_allowed'] = false;
		return self::rehash( $record );
	}

	public static function validate_record( array $record, bool $require_persisted = false ): bool|\WP_Error {
		if ( self::SCHEMA_VERSION !== (string) ( $record['schema_version'] ?? '' ) || self::FAMILY !== (string) ( $record['family'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_schema', 'Schema/family inválido.' );
		}
		if ( ! self::is_sha256( (string) ( $record['journal_id'] ?? '' ) )
			|| ! self::is_sha256( (string) ( $record['fidelity_hash_before'] ?? '' ) )
			|| ! self::is_sha256( (string) ( $record['serialization_hash'] ?? '' ) )
			|| ! self::is_sha256( (string) ( $record['rollback_payload_hash'] ?? '' ) )
			|| ! self::is_sha256( (string) ( $record['journal_hash'] ?? '' ) ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_hash', 'Hash inválido.' );
		}
		if ( $require_persisted && true !== ( $record['journal_persisted'] ?? false ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_not_persisted', 'Journal durável obrigatório.' );
		}
		if ( true !== ( $record['journal_must_be_persisted_before_write'] ?? false ) || true === ( $record['writer_allowed'] ?? false ) || true === ( $record['migration_execution_allowed'] ?? false ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_safety', 'Invariante de segurança violada.' );
		}
		$payload = is_array( $record['rollback_payload'] ?? null ) ? $record['rollback_payload'] : null;
		if ( ! is_array( $payload ) ) { return new \WP_Error( 'bdc_kb_block_journal_capsule', 'Rollback capsule ausente.' ); }
		$actual_payload = self::payload_hash( $payload );
		if ( $actual_payload instanceof \WP_Error || ! hash_equals( (string) $record['rollback_payload_hash'], $actual_payload ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_capsule_hash', 'Rollback capsule adulterada.' );
		}
		$actual_record = self::record_hash( $record );
		if ( $actual_record instanceof \WP_Error || ! hash_equals( (string) $record['journal_hash'], $actual_record ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_record_hash', 'Journal adulterado.' );
		}
		return true;
	}

	public static function public_record( array $record ): array {
		unset( $record['rollback_payload'] );
		$record['contains_editorial_payload'] = false;
		return $record;
	}

	private static function transition_after_write( array $record, string $state, string $content_hash, string $elementor_hash ): array|\WP_Error {
		if ( self::STATE_PREPARED !== (string) ( $record['state'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_transition', 'Transição inválida.' );
		}
		$valid = self::validate_record( $record, true );
		if ( $valid instanceof \WP_Error ) { return $valid; }
		$content_hash = strtolower( trim( $content_hash ) );
		$elementor_hash = strtolower( trim( $elementor_hash ) );
		if ( ! self::is_sha256( $content_hash ) || ! self::is_sha256( $elementor_hash ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_after_hash', 'Hash after inválido.' );
		}
		$record['state'] = $state;
		$record['post_content_sha256_after'] = $content_hash;
		$record['elementor_data_sha256_after'] = $elementor_hash;
		$record['rollback_required'] = true;
		$record['writer_allowed'] = false;
		$record['migration_execution_allowed'] = false;
		return self::rehash( $record );
	}

	private static function normalize_before( array $before ): array|\WP_Error {
		if ( ! is_string( $before['post_content'] ?? null ) || ! is_string( $before['elementor_data'] ?? null ) ) {
			return new \WP_Error( 'bdc_kb_block_journal_snapshot', 'Snapshot deve conter post_content e elementor_data strings.' );
		}
		return array( 'post_content' => $before['post_content'], 'elementor_data' => $before['elementor_data'] );
	}

	private static function rehash( array $record ): array|\WP_Error {
		$record['journal_hash'] = '';
		$hash = self::record_hash( $record );
		if ( $hash instanceof \WP_Error ) { return $hash; }
		$record['journal_hash'] = $hash;
		return $record;
	}
	private static function payload_hash( array $payload ): string|\WP_Error {
		try { return Canonical_JSON::hash( $payload ); } catch ( \JsonException $e ) { return new \WP_Error( 'bdc_kb_block_journal_payload_hash', 'Falha hash payload.' ); }
	}
	private static function record_hash( array $record ): string|\WP_Error {
		$copy = $record; $copy['journal_hash'] = '';
		try { return Canonical_JSON::hash( $copy ); } catch ( \JsonException $e ) { return new \WP_Error( 'bdc_kb_block_journal_record_hash_failed', 'Falha hash record.' ); }
	}
	private static function is_sha256( string $v ): bool { return 1 === preg_match( '/^[a-f0-9]{64}$/', strtolower( trim( $v ) ) ); }
}
