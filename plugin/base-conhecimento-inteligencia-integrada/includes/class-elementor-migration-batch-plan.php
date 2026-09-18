<?php
/**
 * Deterministic resumable batch planner for SPEC-004 / G-245 / T086.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Migration_Batch_Plan {

	public const SCHEMA_VERSION = '1.0.0';
	public const CURSOR_VERSION = '1.0.0';
	public const DEFAULT_BATCH_SIZE = 25;
	public const MAX_BATCH_SIZE = 100;

	/** @return array<string,mixed>|\WP_Error */
	public static function plan( array $dry_runs, int $batch_size = self::DEFAULT_BATCH_SIZE, ?array $cursor = null ): array|\WP_Error {
		if ( $batch_size < 1 || $batch_size > self::MAX_BATCH_SIZE ) {
			return new \WP_Error( 'bdc_kb_batch_invalid_size', 'batch_size deve estar entre 1 e 100.' );
		}

		$normalized = self::normalize_candidates( $dry_runs );
		if ( $normalized instanceof \WP_Error ) {
			return $normalized;
		}
		$candidates = $normalized['eligible'];

		try {
			$cohort_hash = Canonical_JSON::hash(
				array(
					'schema_version' => self::SCHEMA_VERSION,
					'candidates' => $candidates,
				)
			);
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_batch_cohort_hash_failed', 'Falha ao calcular cohort_hash.' );
		}

		$offset = 0;
		if ( null !== $cursor ) {
			$validated = self::validate_cursor( $cursor, $cohort_hash, count( $candidates ) );
			if ( $validated instanceof \WP_Error ) {
				return $validated;
			}
			$offset = $validated;
		}

		$items = array_slice( $candidates, $offset, $batch_size );
		$next_offset = $offset + count( $items );
		$done = $next_offset >= count( $candidates );
		$next_cursor = $done ? null : self::make_cursor( $cohort_hash, $next_offset );
		if ( $next_cursor instanceof \WP_Error ) {
			return $next_cursor;
		}

		try {
			$batch_hash = Canonical_JSON::hash(
				array(
					'cohort_hash' => $cohort_hash,
					'offset' => $offset,
					'batch_size' => $batch_size,
					'items' => $items,
				)
			);
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_batch_hash_failed', 'Falha ao calcular batch_hash.' );
		}

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'cohort_hash' => $cohort_hash,
			'batch_hash' => $batch_hash,
			'batch_size' => $batch_size,
			'offset' => $offset,
			'next_offset' => $next_offset,
			'eligible_total' => count( $candidates ),
			'excluded_total' => $normalized['excluded_total'],
			'items' => $items,
			'item_count' => count( $items ),
			'done' => $done,
			'next_cursor' => $next_cursor,
			'execution_allowed' => false,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'persists_checkpoint' => false,
			'safety' => array(
				'persists_state' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
			),
		);
	}

	/** @return array{eligible:array<int,array<string,mixed>>,excluded_total:int}|\WP_Error */
	private static function normalize_candidates( array $dry_runs ): array|\WP_Error {
		$by_post = array();
		$excluded = 0;
		foreach ( $dry_runs as $dry_run ) {
			if ( ! is_array( $dry_run ) ) {
				return new \WP_Error( 'bdc_kb_batch_invalid_candidate', 'Candidato de dry-run inválido.' );
			}
			if ( 'ready' !== (string) ( $dry_run['dry_run_status'] ?? '' ) ) {
				++$excluded;
				continue;
			}

			$post_id = (int) ( $dry_run['post_id'] ?? 0 );
			$source_hash = strtolower( trim( (string) ( $dry_run['source_hash_before'] ?? '' ) ) );
			$projection_hash = strtolower( trim( (string) ( $dry_run['projection_hash'] ?? '' ) ) );
			$dry_run_hash = strtolower( trim( (string) ( $dry_run['dry_run_hash'] ?? '' ) ) );
			if ( $post_id <= 0 || ! self::is_sha256( $source_hash ) || ! self::is_sha256( $projection_hash ) || ! self::is_sha256( $dry_run_hash ) ) {
				return new \WP_Error( 'bdc_kb_batch_invalid_ready_candidate', 'Candidato ready possui identidade/hash inválido.' );
			}
			if ( true === ( $dry_run['execution_allowed'] ?? false ) || true === ( $dry_run['writer_allowed'] ?? false ) || true === ( $dry_run['migration_execution_allowed'] ?? false ) ) {
				return new \WP_Error( 'bdc_kb_batch_candidate_safety_violation', 'Candidato ready viola invariantes zero-write.' );
			}

			$candidate = array(
				'post_id' => $post_id,
				'source_hash_before' => $source_hash,
				'projection_hash' => $projection_hash,
				'dry_run_hash' => $dry_run_hash,
			);

			if ( isset( $by_post[ $post_id ] ) ) {
				if ( $by_post[ $post_id ] !== $candidate ) {
					return new \WP_Error( 'bdc_kb_batch_conflicting_duplicate', 'Mesmo post_id possui dry-runs ready conflitantes.' );
				}
				continue;
			}
			$by_post[ $post_id ] = $candidate;
		}

		ksort( $by_post, SORT_NUMERIC );
		return array(
			'eligible' => array_values( $by_post ),
			'excluded_total' => $excluded,
		);
	}

	/** @return array<string,mixed>|\WP_Error */
	private static function make_cursor( string $cohort_hash, int $next_offset ): array|\WP_Error {
		$payload = array(
			'cursor_version' => self::CURSOR_VERSION,
			'cohort_hash' => $cohort_hash,
			'next_offset' => $next_offset,
		);
		try {
			$payload['cursor_hash'] = Canonical_JSON::hash( $payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_batch_cursor_hash_failed', 'Falha ao calcular cursor_hash.' );
		}
		return $payload;
	}

	/** @return int|\WP_Error */
	private static function validate_cursor( array $cursor, string $cohort_hash, int $total ): int|\WP_Error {
		$version = (string) ( $cursor['cursor_version'] ?? '' );
		$cursor_cohort = strtolower( trim( (string) ( $cursor['cohort_hash'] ?? '' ) ) );
		$offset = isset( $cursor['next_offset'] ) ? (int) $cursor['next_offset'] : -1;
		$cursor_hash = strtolower( trim( (string) ( $cursor['cursor_hash'] ?? '' ) ) );

		if ( self::CURSOR_VERSION !== $version || ! self::is_sha256( $cursor_cohort ) || ! self::is_sha256( $cursor_hash ) || $offset < 0 || $offset > $total ) {
			return new \WP_Error( 'bdc_kb_batch_invalid_cursor', 'Cursor inválido.' );
		}

		try {
			$expected = Canonical_JSON::hash(
				array(
					'cursor_version' => $version,
					'cohort_hash' => $cursor_cohort,
					'next_offset' => $offset,
				)
			);
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_batch_cursor_validation_failed', 'Falha ao validar cursor.' );
		}

		if ( ! hash_equals( $expected, $cursor_hash ) ) {
			return new \WP_Error( 'bdc_kb_batch_cursor_tampered', 'Cursor falhou na verificação de integridade.' );
		}
		if ( ! hash_equals( $cohort_hash, $cursor_cohort ) ) {
			return new \WP_Error( 'bdc_kb_batch_cursor_stale', 'Cursor pertence a outro cohort; replanejamento obrigatório.' );
		}
		return $offset;
	}

	private static function is_sha256( string $value ): bool {
		return 1 === preg_match( '/^[a-f0-9]{64}$/', $value );
	}
}
