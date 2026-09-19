<?php
/**
 * Runtime loader/validator for SPEC-005 Golden and Technical Challenge suites.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Golden_Suite_Loader {

	public const RUNNER_VERSION = 'golden-runner-v1.0.0';

	public const GOLDEN_VERSION = 'golden-relevance-v1.0.0';
	public const GOLDEN_HASH = 'e449364d3ace062ea9e7b20580c2b69afe80f9d1efc3661b25136b9bb7a3f8d4';

	public const CHALLENGE_VERSION = 'technical-challenge-v1.0.0';
	public const CHALLENGE_HASH = '2928dcd85e242bb50e013d58c71388b3302db76f462570cb09f19db61fc6e807';

	private const GOLDEN_RELATIVE_PATH = 'resources/search/golden-relevance-v1.0.0.json';
	private const CHALLENGE_RELATIVE_PATH = 'resources/search/technical-challenge-v1.0.0.json';

	/** @var array<int,string> */
	private const GOLDEN_FIELDS = array(
		'id',
		'query',
		'query_norm',
		'expected_post_id',
		'max_rank',
		'severity',
		'source',
		'active',
		'disposition',
		'quarantined',
		'rationale',
		'contract_version',
	);

	/** @var array<int,string> */
	private const CHALLENGE_FIELDS = array(
		'id',
		'query',
		'query_norm',
		'expected_post_id',
		'origin',
		'declared_classes',
		'source_field',
		'source_kind',
		'summary_dependent',
		'elementor_semantic_gap',
		'document_frequency',
		'active_for_golden_blocking',
		'contract_version',
	);

	/**
	 * @return array{golden:array<string,mixed>,challenge:array<string,mixed>}|\WP_Error
	 */
	public static function load_all(): array|\WP_Error {
		$golden = self::load_suite(
			self::GOLDEN_RELATIVE_PATH,
			'golden_relevance',
			self::GOLDEN_VERSION,
			self::GOLDEN_HASH,
			self::GOLDEN_FIELDS
		);
		if ( $golden instanceof \WP_Error ) {
			return $golden;
		}

		$challenge = self::load_suite(
			self::CHALLENGE_RELATIVE_PATH,
			'technical_challenge',
			self::CHALLENGE_VERSION,
			self::CHALLENGE_HASH,
			self::CHALLENGE_FIELDS
		);
		if ( $challenge instanceof \WP_Error ) {
			return $challenge;
		}

		return array(
			'golden' => $golden,
			'challenge' => $challenge,
		);
	}

	/**
	 * Public deterministic hash helper used by local tests.
	 *
	 * @param array<int,array<string,mixed>> $items
	 * @param array<int,string> $field_order
	 */
	public static function canonical_items_hash( array $items, array $field_order ): string|\WP_Error {
		usort(
			$items,
			static fn ( array $a, array $b ): int => strcmp( (string) ( $a['id'] ?? '' ), (string) ( $b['id'] ?? '' ) )
		);

		$canonical_items = array();

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				return new \WP_Error( 'golden_suite_invalid_item', 'Item inválido na suite.' );
			}

			$canonical = array();
			foreach ( $field_order as $field ) {
				if ( ! array_key_exists( $field, $item ) ) {
					return new \WP_Error(
						'golden_suite_missing_field',
						'Campo obrigatório ausente na suite.',
						array( 'field' => $field, 'id' => (string) ( $item['id'] ?? '' ) )
					);
				}
				$canonical[ $field ] = $item[ $field ];
			}
			$canonical_items[] = $canonical;
		}

		$json = wp_json_encode(
			$canonical_items,
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);

		if ( ! is_string( $json ) ) {
			return new \WP_Error( 'golden_suite_hash_json_failed', 'Falha ao serializar suite para hash.' );
		}

		return hash( 'sha256', $json );
	}

	/**
	 * @param array<int,string> $field_order
	 * @return array<string,mixed>|\WP_Error
	 */
	private static function load_suite(
		string $relative_path,
		string $expected_kind,
		string $expected_version,
		string $expected_hash,
		array $field_order
	): array|\WP_Error {
		$path = BDC_KB_DIR . $relative_path;

		if ( ! is_readable( $path ) ) {
			return new \WP_Error(
				'golden_suite_resource_missing',
				'Recurso da suite não encontrado.',
				array( 'resource' => $relative_path )
			);
		}

		$raw = file_get_contents( $path );
		if ( false === $raw ) {
			return new \WP_Error(
				'golden_suite_resource_read_failed',
				'Falha ao ler recurso da suite.',
				array( 'resource' => $relative_path )
			);
		}

		$decoded = json_decode( $raw, true );
		if ( ! is_array( $decoded ) ) {
			return new \WP_Error(
				'golden_suite_invalid_json',
				'JSON da suite inválido.',
				array( 'resource' => $relative_path )
			);
		}

		if ( $expected_kind !== (string) ( $decoded['suite_kind'] ?? '' ) ) {
			return new \WP_Error( 'golden_suite_kind_stale', 'suite_kind divergente.' );
		}
		if ( $expected_version !== (string) ( $decoded['suite_version'] ?? '' ) ) {
			return new \WP_Error( 'golden_suite_version_stale', 'suite_version divergente.' );
		}
		if ( $expected_hash !== (string) ( $decoded['set_hash'] ?? '' ) ) {
			return new \WP_Error( 'golden_suite_declared_hash_stale', 'set_hash declarado divergente.' );
		}

		$items = is_array( $decoded['items'] ?? null ) ? $decoded['items'] : array();
		if ( empty( $items ) ) {
			return new \WP_Error( 'golden_suite_not_configured', 'Suite sem itens ativos/configurados.' );
		}

		$actual_hash = self::canonical_items_hash( $items, $field_order );
		if ( $actual_hash instanceof \WP_Error ) {
			return $actual_hash;
		}
		if ( ! hash_equals( $expected_hash, $actual_hash ) ) {
			return new \WP_Error(
				'golden_suite_hash_stale',
				'Conteúdo da suite não corresponde ao set_hash congelado.',
				array(
					'expected' => $expected_hash,
					'actual' => $actual_hash,
					'resource' => $relative_path,
				)
			);
		}

		$decoded['_runtime'] = array(
			'resource' => $relative_path,
			'computed_set_hash' => $actual_hash,
			'runner_contract_version' => self::RUNNER_VERSION,
		);

		return $decoded;
	}
}
