<?php
/**
 * T096 full-corpus in-memory Core Block serialization + parse/serialize round-trip smoke.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Core_Block_Lossless_Roundtrip_Smoke {

	public const ACTION = 'bdc_kb_spec004_g245_lossless_roundtrip_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-lossless-roundtrip-smoke';
	private const NONCE_ACTION = 'bdc_kb_spec004_g245_lossless_roundtrip_smoke_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_lossless_roundtrip_smoke_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 38 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Lossless Blocks G-245',
			'Lossless Blocks G-245',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SPEC-004 — T096 Lossless Core Blocks round-trip', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Read-only.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Serializa somente em memória, usa serialize_blocks/parse_blocks do WordPress Core e valida round-trip. Não grava post_content nem _elementor_data.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar T096 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
		echo '</form></div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] ) ? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Nonce inválido ou expirado.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t096-lossless-roundtrip-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$fingerprint_before = self::editorial_fingerprint( $ids_before );
		$first = self::pass( $ids_before );
		$second = self::pass( $ids_before );

		$fidelity_hash_mismatches = 0;
		$serialization_hash_mismatches = 0;
		foreach ( $ids_before as $post_id ) {
			if ( ! isset( $first['hashes'][ $post_id ], $second['hashes'][ $post_id ] ) ) {
				continue;
			}
			$a = $first['hashes'][ $post_id ];
			$b = $second['hashes'][ $post_id ];
			if ( ! hash_equals( (string) $a['fidelity_hash'], (string) $b['fidelity_hash'] ) ) {
				++$fidelity_hash_mismatches;
			}
			if ( ! hash_equals( (string) $a['serialization_hash'], (string) $b['serialization_hash'] ) ) {
				++$serialization_hash_mismatches;
			}
		}

		$ids_after = self::post_ids();
		$fingerprint_after = self::editorial_fingerprint( $ids_after );
		$corpus_unchanged = $ids_before === $ids_after;
		$fingerprint_equal = hash_equals( $fingerprint_before, $fingerprint_after );

		$gate = $corpus_unchanged
			&& $fingerprint_equal
			&& count( $ids_before ) === count( $first['hashes'] )
			&& count( $ids_before ) === count( $second['hashes'] )
			&& 0 === $first['errors']
			&& 0 === $second['errors']
			&& 0 === $first['throwables']
			&& 0 === $second['throwables']
			&& 0 === $first['safety_violations']
			&& 0 === $second['safety_violations']
			&& 0 === $first['raw_roundtrip_mismatches']
			&& 0 === $second['raw_roundtrip_mismatches']
			&& 0 === $first['parse_serialize_mismatches']
			&& 0 === $second['parse_serialize_mismatches']
			&& 0 === $fidelity_hash_mismatches
			&& 0 === $serialization_hash_mismatches;

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'T096',
			'mode' => 'lossless_core_blocks_in_memory_roundtrip',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'migration_fidelity_source_schema' => Migration_Fidelity_Source::SCHEMA_VERSION,
				'lossless_serializer_schema' => Core_Block_Lossless_Serializer::SCHEMA_VERSION,
				'gutenberg_plugin_dependency' => false,
			),
			'corpus' => array(
				'total_posts' => count( $ids_before ),
				'first_pass' => count( $first['hashes'] ),
				'second_pass' => count( $second['hashes'] ),
				'first_errors' => $first['errors'],
				'second_errors' => $second['errors'],
				'first_throwables' => $first['throwables'],
				'second_throwables' => $second['throwables'],
				'first_safety_violations' => $first['safety_violations'],
				'second_safety_violations' => $second['safety_violations'],
				'first_raw_roundtrip_mismatches' => $first['raw_roundtrip_mismatches'],
				'second_raw_roundtrip_mismatches' => $second['raw_roundtrip_mismatches'],
				'first_parse_serialize_mismatches' => $first['parse_serialize_mismatches'],
				'second_parse_serialize_mismatches' => $second['parse_serialize_mismatches'],
				'fidelity_hash_mismatches' => $fidelity_hash_mismatches,
				'serialization_hash_mismatches' => $serialization_hash_mismatches,
			),
			'distribution' => array(
				'source_kind' => $first['source_kind'],
				'source_status' => $first['source_status'],
				'source_strategy' => $first['source_strategy'],
				'serializer_status' => $first['serializer_status'],
				'source_warnings' => $first['source_warnings'],
				'serializer_warnings' => $first['serializer_warnings'],
				'block_names' => $first['block_names'],
				'source_serializer_matrix' => $first['matrix'],
			),
			'safety' => array(
				'read_only_design' => true,
				'serializes_in_memory' => true,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
				'renders_blocks' => false,
				'executes_shortcodes' => false,
				'calls_external_network' => false,
				'depends_on_gutenberg_plugin' => false,
				'exports_editorial_content' => false,
				'exports_urls' => false,
				'exports_post_ids' => false,
				'corpus_unchanged' => $corpus_unchanged,
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => $fingerprint_equal,
			),
			'gate_result' => array( 't096_lossless_roundtrip_pass' => $gate ),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	/** @param array<int,int> $post_ids @return array<string,mixed> */
	private static function pass( array $post_ids ): array {
		$out = array(
			'hashes' => array(),
			'errors' => 0,
			'throwables' => 0,
			'safety_violations' => 0,
			'raw_roundtrip_mismatches' => 0,
			'parse_serialize_mismatches' => 0,
			'source_kind' => array(),
			'source_status' => array(),
			'source_strategy' => array(),
			'serializer_status' => array(),
			'source_warnings' => array(),
			'serializer_warnings' => array(),
			'block_names' => array(),
			'matrix' => array(),
		);

		foreach ( $post_ids as $post_id ) {
			try {
				$source = Migration_Fidelity_Source::build( $post_id );
				if ( is_wp_error( $source ) ) {
					++$out['errors'];
					continue;
				}
				$serialized = Core_Block_Lossless_Serializer::serialize_source( $source );
				if ( is_wp_error( $serialized ) ) {
					++$out['errors'];
					continue;
				}

				$source_kind = (string) ( $source['source_kind'] ?? 'unknown' );
				$source_status = (string) ( $source['status'] ?? 'unknown' );
				$source_strategy = (string) ( $source['strategy'] ?? 'unknown' );
				$serializer_status = (string) ( $serialized['status'] ?? 'unknown' );
				self::inc( $out['source_kind'], $source_kind );
				self::inc( $out['source_status'], $source_status );
				self::inc( $out['source_strategy'], $source_strategy );
				self::inc( $out['serializer_status'], $serializer_status );
				if ( ! isset( $out['matrix'][ $source_kind ] ) ) {
					$out['matrix'][ $source_kind ] = array();
				}
				self::inc( $out['matrix'][ $source_kind ], $serializer_status );

				foreach ( (array) ( $source['warnings'] ?? array() ) as $warning ) {
					self::inc( $out['source_warnings'], (string) $warning );
				}
				foreach ( (array) ( $serialized['warnings'] ?? array() ) as $warning ) {
					self::inc( $out['serializer_warnings'], (string) $warning );
				}
				foreach ( (array) ( $serialized['blocks'] ?? array() ) as $block ) {
					if ( is_array( $block ) ) {
						self::inc( $out['block_names'], (string) ( $block['blockName'] ?? 'unknown' ) );
					}
				}

				if ( true === ( $source['safety']['persists_state'] ?? true )
					|| true === ( $source['safety']['writes_post_content'] ?? true )
					|| true === ( $source['safety']['writes_elementor_data'] ?? true )
					|| true === ( $source['safety']['depends_on_gutenberg_plugin'] ?? true )
					|| true === ( $serialized['writer_allowed'] ?? true )
					|| true === ( $serialized['migration_execution_allowed'] ?? true )
					|| true === ( $serialized['safety']['persists_state'] ?? true )
					|| true === ( $serialized['safety']['writes_post_content'] ?? true )
					|| true === ( $serialized['safety']['writes_elementor_data'] ?? true )
					|| true === ( $serialized['safety']['depends_on_gutenberg_plugin'] ?? true ) ) {
					++$out['safety_violations'];
				}

				if ( 'serialized_in_memory' === $serializer_status ) {
					$roundtrip = self::validate_roundtrip( $source, $serialized );
					$out['raw_roundtrip_mismatches'] += $roundtrip['raw_mismatches'];
					$out['parse_serialize_mismatches'] += $roundtrip['parse_serialize_mismatches'];
				} elseif ( 'native_noop' === $serializer_status ) {
					$units = (array) ( $source['units'] ?? array() );
					$raw = isset( $units[0]['raw'] ) && is_string( $units[0]['raw'] ) ? $units[0]['raw'] : '';
					if ( ! hash_equals( hash( 'sha256', $raw ), (string) ( $serialized['serialized_sha256'] ?? '' ) ) ) {
						++$out['raw_roundtrip_mismatches'];
					}
				}

				$out['hashes'][ $post_id ] = array(
					'fidelity_hash' => (string) ( $source['fidelity_hash'] ?? '' ),
					'serialization_hash' => (string) ( $serialized['serialization_hash'] ?? '' ),
				);
			} catch ( \Throwable $error ) {
				++$out['throwables'];
			}
		}

		ksort( $out['source_kind'] );
		ksort( $out['source_status'] );
		ksort( $out['source_strategy'] );
		ksort( $out['serializer_status'] );
		ksort( $out['source_warnings'] );
		ksort( $out['serializer_warnings'] );
		ksort( $out['block_names'] );
		ksort( $out['matrix'] );
		foreach ( $out['matrix'] as &$row ) { ksort( $row ); }
		unset( $row );
		return $out;
	}

	/** @return array{raw_mismatches:int,parse_serialize_mismatches:int} */
	private static function validate_roundtrip( array $source, array $serialized ): array {
		$out = array( 'raw_mismatches' => 0, 'parse_serialize_mismatches' => 0 );
		$content = (string) ( $serialized['serialized_post_content'] ?? '' );
		if ( ! function_exists( 'parse_blocks' ) || ! function_exists( 'serialize_blocks' ) ) {
			$out['parse_serialize_mismatches'] = 1;
			return $out;
		}
		$parsed = parse_blocks( $content );
		if ( ! is_array( $parsed ) ) {
			$out['parse_serialize_mismatches'] = 1;
			return $out;
		}
		$meaningful = array_values( array_filter( $parsed, static fn ( $block ): bool => is_array( $block ) && '' !== (string) ( $block['blockName'] ?? '' ) ) );
		$units = array_values( array_filter( (array) ( $source['units'] ?? array() ), 'is_array' ) );
		if ( count( $meaningful ) !== count( $units ) ) {
			++$out['raw_mismatches'];
		} else {
			foreach ( $units as $index => $unit ) {
				$raw = isset( $unit['raw'] ) && is_string( $unit['raw'] ) ? $unit['raw'] : '';
				$parsed_raw = isset( $meaningful[ $index ]['innerHTML'] ) && is_string( $meaningful[ $index ]['innerHTML'] ) ? $meaningful[ $index ]['innerHTML'] : '';
				if ( ! hash_equals( hash( 'sha256', $raw ), hash( 'sha256', $parsed_raw ) ) ) {
					++$out['raw_mismatches'];
				}
			}
		}
		$reserialized = serialize_blocks( $parsed );
		if ( ! is_string( $reserialized ) || ! hash_equals( hash( 'sha256', $content ), hash( 'sha256', $reserialized ) ) ) {
			++$out['parse_serialize_mismatches'];
		}
		return $out;
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => false ) );
		$ids = is_array( $ids ) ? array_map( 'intval', $ids ) : array();
		sort( $ids, SORT_NUMERIC );
		return array_values( array_filter( $ids, static fn ( int $id ): bool => $id > 0 ) );
	}

	/** @param array<int,int> $post_ids */
	private static function editorial_fingerprint( array $post_ids ): string {
		$rows = array();
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) { continue; }
			$rows[] = array(
				'id' => $post_id,
				'content_sha256' => hash( 'sha256', (string) ( $post->post_content ?? '' ) ),
				'elementor_sha256' => hash( 'sha256', (string) get_post_meta( $post_id, '_elementor_data', true ) ),
				'status' => (string) ( $post->post_status ?? '' ),
				'modified_gmt' => (string) ( $post->post_modified_gmt ?? '' ),
			);
		}
		return Canonical_JSON::hash( $rows );
	}

	/** @param array<string,int> $bucket */
	private static function inc( array &$bucket, string $key ): void {
		$key = '' !== $key ? $key : 'unknown';
		$bucket[ $key ] = (int) ( $bucket[ $key ] ?? 0 ) + 1;
	}
}
