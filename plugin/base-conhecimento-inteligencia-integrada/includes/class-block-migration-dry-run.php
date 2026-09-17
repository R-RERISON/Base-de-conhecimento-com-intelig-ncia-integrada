<?php
namespace BDC\KnowledgeBase;
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Block_Migration_Dry_Run {
	public const SCHEMA_VERSION = '1.0.0';

	public static function build( int $post_id ): array|\WP_Error {
		$source = Migration_Fidelity_Source::build( $post_id );
		if ( $source instanceof \WP_Error ) { return $source; }
		$serialized = Core_Block_Lossless_Serializer::serialize_source( $source );
		if ( $serialized instanceof \WP_Error ) { return $serialized; }
		$parity = Core_Block_Editorial_Parity::validate( $source, $serialized );
		if ( $parity instanceof \WP_Error ) { return $parity; }
		$stale = Block_Migration_Stale_Source_Guard::inspect_post( $post_id, $source );
		if ( $stale instanceof \WP_Error ) { return $stale; }
		return self::simulate( $source, $serialized, $parity, $stale );
	}

	public static function simulate( array $source, array $serialized, array $parity, array $stale ): array|\WP_Error {
		$post_id = (int) ( $source['post_id'] ?? 0 );
		$fidelity_hash = strtolower( (string) ( $source['fidelity_hash'] ?? '' ) );
		$serialization_hash = strtolower( (string) ( $serialized['serialization_hash'] ?? '' ) );
		if ( $post_id <= 0 || ! self::sha( $fidelity_hash ) || ! self::sha( $serialization_hash ) ) {
			return new \WP_Error( 'bdc_kb_block_dry_run_identity', 'Source/serializer inválidos.' );
		}
		$source_status = (string) ( $source['status'] ?? '' );
		$serializer_status = (string) ( $serialized['status'] ?? '' );
		$parity_status = (string) ( $parity['status'] ?? '' );
		$stale_status = (string) ( $stale['status'] ?? '' );
		$status = 'ready'; $action = 'would_prepare_journal_lock_recheck_and_write_post_content'; $reasons = array();

		if ( 'not_applicable' === $source_status || 'not_applicable' === $serializer_status ) {
			$status = 'noop'; $action = 'no_migration_needed';
		} elseif ( 'native_noop' === $serializer_status ) {
			$status = 'noop'; $action = 'native_core_blocks_noop';
		} elseif ( 'review_required' === $source_status || 'review_required' === $serializer_status || 'review_required' === $parity_status ) {
			$status = 'review_required'; $action = 'human_review_required'; $reasons[] = 'SOURCE_OR_PARITY_REQUIRES_REVIEW';
		} elseif ( 'serialized_in_memory' !== $serializer_status || 'pass' !== $parity_status ) {
			$status = 'blocked'; $action = 'blocked'; $reasons[] = 'SERIALIZER_OR_PARITY_NOT_READY';
		}
		if ( true !== ( $stale['is_fresh'] ?? false ) || 'fresh' !== $stale_status ) {
			$status = 'blocked'; $action = 'blocked'; $reasons[] = 'STALE_SOURCE_GUARD_NOT_FRESH:' . $stale_status;
		}
		foreach ( array( $source['safety'] ?? array(), $serialized['safety'] ?? array(), $parity['safety'] ?? array() ) as $safety ) {
			if ( ! is_array( $safety ) ) { continue; }
			if ( true === ( $safety['writes_post_content'] ?? false ) || true === ( $safety['writes_elementor_data'] ?? false ) || true === ( $safety['persists_state'] ?? false ) ) {
				$status = 'blocked'; $action = 'blocked'; $reasons[] = 'UPSTREAM_SAFETY_VIOLATION'; break;
			}
		}
		$result = array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => $post_id,
			'source_kind' => (string) ( $source['source_kind'] ?? '' ),
			'fidelity_hash_before' => $fidelity_hash,
			'serialization_hash' => $serialization_hash,
			'serialized_post_content_sha256' => (string) ( $serialized['serialized_sha256'] ?? '' ),
			'source_material' => is_array( $source['source_material'] ?? null ) ? $source['source_material'] : array(),
			'dry_run_status' => $status,
			'simulated_action' => $action,
			'reasons' => array_values( array_unique( $reasons ) ),
			'preconditions' => array(
				'journal_must_be_persisted_before_write' => true,
				'stale_source_must_be_rechecked_immediately_before_write' => true,
				'exclusive_lock_required' => true,
				'explicit_canary_or_batch_authorization_required' => true,
				'manage_options_required' => true,
			),
			'simulation' => array(
				'would_prepare_journal' => 'ready' === $status,
				'would_acquire_lock' => 'ready' === $status,
				'would_recheck_stale_source' => 'ready' === $status,
				'would_write_post_content' => 'ready' === $status,
				'would_preserve_elementor_data' => 'ready' === $status,
			),
			'execution_allowed' => false,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'safety' => array(
				'persists_state' => false, 'persists_journal' => false, 'acquires_lock' => false,
				'writes_post_content' => false, 'writes_elementor_data' => false,
				'calls_external_network' => false, 'executes_shortcodes' => false, 'renders_blocks' => false,
			),
			'dry_run_hash' => '',
		);
		$copy = $result; unset( $copy['dry_run_hash'] );
		try { $result['dry_run_hash'] = Canonical_JSON::hash( $copy ); }
		catch ( \JsonException $e ) { return new \WP_Error( 'bdc_kb_block_dry_run_hash', 'Falha dry_run_hash.' ); }
		return $result;
	}
	private static function sha( string $v ): bool { return 1 === preg_match( '/^[a-f0-9]{64}$/', $v ); }
}
