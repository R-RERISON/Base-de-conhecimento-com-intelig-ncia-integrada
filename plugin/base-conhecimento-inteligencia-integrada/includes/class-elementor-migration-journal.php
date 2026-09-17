<?php
/**
 * Journal/rollback domain contract for future Elementor migration writes.
 * SPEC-004 / G-245 / T083.
 *
 * This class is intentionally storage-neutral and performs no persistence.
 * A future mutable phase must persist a prepared record before any editorial write.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Migration_Journal {

	public const SCHEMA_VERSION = '1.0.0';
	public const STATE_PREPARED = 'prepared';
	public const STATE_APPLIED = 'applied';
	public const STATE_PARTIAL_FAILURE = 'partial_failure';
	public const STATE_ROLLED_BACK = 'rolled_back';

	/** @return array<string,mixed>|\WP_Error */
	public static function prepare( array $input ): array|\WP_Error {
		$run_id = trim( (string) ( $input['run_id'] ?? '' ) );
		$post_id = (int) ( $input['post_id'] ?? 0 );
		$source_hash_before = strtolower( trim( (string) ( $input['source_hash_before'] ?? '' ) ) );
		$projection_hash = strtolower( trim( (string) ( $input['projection_hash'] ?? '' ) ) );
		$recorded_at = trim( (string) ( $input['recorded_at'] ?? '' ) );
		$before = is_array( $input['before'] ?? null ) ? $input['before'] : array();

		if ( 1 !== preg_match( '/^[A-Za-z0-9._:-]{8,128}$/', $run_id ) ) {
			return new \WP_Error( 'bdc_kb_journal_invalid_run_id', 'run_id inválido para journal.' );
		}
		if ( $post_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_journal_invalid_post_id', 'post_id inválido para journal.' );
		}
		if ( ! self::is_sha256( $source_hash_before ) || ! self::is_sha256( $projection_hash ) ) {
			return new \WP_Error( 'bdc_kb_journal_invalid_hash', 'Hashes obrigatórios inválidos para journal.' );
		}
		if ( '' === $recorded_at ) {
			return new \WP_Error( 'bdc_kb_journal_missing_recorded_at', 'recorded_at é obrigatório.' );
		}

		$rollback_payload = self::normalize_before_snapshot( $before );
		if ( $rollback_payload instanceof \WP_Error ) {
			return $rollback_payload;
		}

		$rollback_payload_hash = self::hash_payload( $rollback_payload );
		if ( $rollback_payload_hash instanceof \WP_Error ) {
			return $rollback_payload_hash;
		}

		$journal_id = hash( 'sha256', $run_id . '|' . $post_id . '|' . $source_hash_before . '|' . $projection_hash );
		$record = array(
			'schema_version' => self::SCHEMA_VERSION,
			'journal_id' => $journal_id,
			'run_id' => $run_id,
			'post_id' => $post_id,
			'state' => self::STATE_PREPARED,
			'recorded_at' => $recorded_at,
			'source_hash_before' => $source_hash_before,
			'projection_hash' => $projection_hash,
			'source_hash_after' => null,
			'rollback_payload_hash' => $rollback_payload_hash,
			'rollback_payload' => $rollback_payload,
			'rollback_required' => true,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'journal_persisted' => false,
			'journal_must_be_persisted_before_write' => true,
			'journal_hash' => '',
		);

		$journal_hash = self::record_hash( $record );
		if ( $journal_hash instanceof \WP_Error ) {
			return $journal_hash;
		}
		$record['journal_hash'] = $journal_hash;
		return $record;
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function mark_applied( array $record, string $source_hash_after ): array|\WP_Error {
		return self::transition_after_write( $record, self::STATE_APPLIED, $source_hash_after );
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function mark_partial_failure( array $record, string $source_hash_after ): array|\WP_Error {
		return self::transition_after_write( $record, self::STATE_PARTIAL_FAILURE, $source_hash_after );
	}

	/** @return array<string,mixed> */
	public static function rollback_decision( array $record, string $current_source_hash ): array {
		$current_source_hash = strtolower( trim( $current_source_hash ) );
		$state = (string) ( $record['state'] ?? '' );
		$after = strtolower( trim( (string) ( $record['source_hash_after'] ?? '' ) ) );
		$payload_hash = strtolower( trim( (string) ( $record['rollback_payload_hash'] ?? '' ) ) );
		$payload = is_array( $record['rollback_payload'] ?? null ) ? $record['rollback_payload'] : null;

		if ( self::STATE_ROLLED_BACK === $state ) {
			return self::rollback_result( false, 'noop_already_rolled_back', true, array() );
		}
		if ( ! in_array( $state, array( self::STATE_APPLIED, self::STATE_PARTIAL_FAILURE ), true ) ) {
			return self::rollback_result( false, 'blocked_invalid_state', false, array( 'ROLLBACK_STATE_NOT_ELIGIBLE:' . $state ) );
		}
		if ( ! self::is_sha256( $after ) || ! self::is_sha256( $current_source_hash ) || ! hash_equals( $after, $current_source_hash ) ) {
			return self::rollback_result( false, 'blocked_stale_rollback_target', false, array( 'ROLLBACK_TARGET_CHANGED' ) );
		}
		if ( ! is_array( $payload ) || ! self::is_sha256( $payload_hash ) ) {
			return self::rollback_result( false, 'blocked_missing_rollback_capsule', false, array( 'ROLLBACK_CAPSULE_MISSING' ) );
		}
		$actual_payload_hash = self::hash_payload( $payload );
		if ( $actual_payload_hash instanceof \WP_Error || ! hash_equals( $payload_hash, $actual_payload_hash ) ) {
			return self::rollback_result( false, 'blocked_rollback_capsule_integrity', false, array( 'ROLLBACK_CAPSULE_HASH_MISMATCH' ) );
		}

		return self::rollback_result( true, 'restore_before_snapshot', false, array() );
	}

	/** @return array<string,mixed>|\WP_Error */
	public static function mark_rolled_back( array $record, string $restored_source_hash ): array|\WP_Error {
		if ( self::STATE_ROLLED_BACK === (string) ( $record['state'] ?? '' ) ) {
			return $record;
		}
		$expected = strtolower( trim( (string) ( $record['source_hash_before'] ?? '' ) ) );
		$restored = strtolower( trim( $restored_source_hash ) );
		if ( ! self::is_sha256( $expected ) || ! self::is_sha256( $restored ) || ! hash_equals( $expected, $restored ) ) {
			return new \WP_Error( 'bdc_kb_journal_rollback_hash_mismatch', 'Rollback não restaurou o source_hash_before.' );
		}
		$record['state'] = self::STATE_ROLLED_BACK;
		$record['rollback_required'] = false;
		$record['writer_allowed'] = false;
		$record['migration_execution_allowed'] = false;
		$record['journal_hash'] = '';
		$hash = self::record_hash( $record );
		if ( $hash instanceof \WP_Error ) {
			return $hash;
		}
		$record['journal_hash'] = $hash;
		return $record;
	}

	/** @return array<string,mixed> */
	public static function public_record( array $record ): array {
		$public = $record;
		unset( $public['rollback_payload'] );
		$public['contains_editorial_payload'] = false;
		return $public;
	}

	/** @return array<string,mixed>|\WP_Error */
	private static function transition_after_write( array $record, string $state, string $source_hash_after ): array|\WP_Error {
		if ( self::STATE_PREPARED !== (string) ( $record['state'] ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_journal_invalid_transition', 'Somente journal prepared pode registrar resultado de write.' );
		}
		$after = strtolower( trim( $source_hash_after ) );
		if ( ! self::is_sha256( $after ) ) {
			return new \WP_Error( 'bdc_kb_journal_invalid_after_hash', 'source_hash_after inválido.' );
		}
		$record['state'] = $state;
		$record['source_hash_after'] = $after;
		$record['rollback_required'] = true;
		$record['writer_allowed'] = false;
		$record['migration_execution_allowed'] = false;
		$record['journal_hash'] = '';
		$hash = self::record_hash( $record );
		if ( $hash instanceof \WP_Error ) {
			return $hash;
		}
		$record['journal_hash'] = $hash;
		return $record;
	}

	/** @return array<string,string>|\WP_Error */
	private static function normalize_before_snapshot( array $before ): array|\WP_Error {
		if ( ! array_key_exists( 'post_content', $before ) || ! array_key_exists( 'elementor_data', $before ) ) {
			return new \WP_Error( 'bdc_kb_journal_incomplete_before_snapshot', 'Snapshot before deve conter post_content e elementor_data.' );
		}
		if ( ! is_string( $before['post_content'] ) || ! is_string( $before['elementor_data'] ) ) {
			return new \WP_Error( 'bdc_kb_journal_invalid_before_snapshot', 'Snapshot before deve conter strings.' );
		}
		return array(
			'post_content' => $before['post_content'],
			'elementor_data' => $before['elementor_data'],
		);
	}

	/** @return string|\WP_Error */
	private static function hash_payload( array $payload ): string|\WP_Error {
		try {
			return Canonical_JSON::hash( $payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_journal_hash_failed', 'Falha ao calcular hash do journal.' );
		}
	}

	/** @return string|\WP_Error */
	private static function record_hash( array $record ): string|\WP_Error {
		$payload = $record;
		unset( $payload['journal_hash'], $payload['rollback_payload'] );
		return self::hash_payload( $payload );
	}

	/** @return array<string,mixed> */
	private static function rollback_result( bool $allowed, string $action, bool $idempotent, array $reasons ): array {
		return array(
			'rollback_allowed' => $allowed,
			'action' => $action,
			'idempotent_noop' => $idempotent,
			'reasons' => $reasons,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
		);
	}

	private static function is_sha256( string $value ): bool {
		return 1 === preg_match( '/^[a-f0-9]{64}$/', $value );
	}
}
