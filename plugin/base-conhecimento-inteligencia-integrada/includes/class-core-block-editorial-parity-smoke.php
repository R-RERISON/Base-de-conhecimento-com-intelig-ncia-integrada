<?php
/**
 * T097 full-corpus static editorial parity + stale-source smoke.
 * Read-only: never renders blocks/shortcodes and never persists content.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Core_Block_Editorial_Parity_Smoke {

	public const ACTION = 'bdc_kb_spec004_g245_editorial_parity_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-editorial-parity-smoke';
	private const NONCE_ACTION = 'bdc_kb_spec004_g245_editorial_parity_smoke_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_editorial_parity_smoke_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 40 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Editorial Parity G-245',
			'Editorial Parity G-245',
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
		echo '<h1>' . esc_html__( 'SPEC-004 — T097 Static Editorial Parity', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Read-only.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Valida block registry, payload editorial, determinismo e stale-source sem renderizar blocks/shortcodes e sem persistir conteúdo.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar T097 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
		echo '</form></div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] )
			: '';
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t097-editorial-parity-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$fingerprint_before = self::editorial_fingerprint( $ids_before );
		$registry = Core_Block_Editorial_Parity::registry_status();
		$first = self::pass( $ids_before );
		$second = self::pass( $ids_before );
		$manifest_mismatches = 0;
		foreach ( $ids_before as $post_id ) {
			$a = (string) ( $first['manifest_hashes'][ $post_id ] ?? '' );
			$b = (string) ( $second['manifest_hashes'][ $post_id ] ?? '' );
			if ( '' === $a || '' === $b || ! hash_equals( $a, $b ) ) {
				++$manifest_mismatches;
			}
		}
		$ids_after = self::post_ids();
		$fingerprint_after = self::editorial_fingerprint( $ids_after );
		$corpus_unchanged = $ids_before === $ids_after;
		$fingerprint_equal = hash_equals( $fingerprint_before, $fingerprint_after );
		$gate = true === ( $registry['pass'] ?? false )
			&& $corpus_unchanged
			&& $fingerprint_equal
			&& count( $ids_before ) === count( $first['manifest_hashes'] )
			&& count( $ids_before ) === count( $second['manifest_hashes'] )
			&& 0 === $first['errors'] && 0 === $second['errors']
			&& 0 === $first['throwables'] && 0 === $second['throwables']
			&& 0 === $first['safety_violations'] && 0 === $second['safety_violations']
			&& 0 === $first['parity_mismatches'] && 0 === $second['parity_mismatches']
			&& 0 === $first['stale_sources'] && 0 === $second['stale_sources']
			&& 0 === $manifest_mismatches;

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'T097',
			'mode' => 'static_editorial_parity_and_stale_source_read_only',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'migration_fidelity_source_schema' => Migration_Fidelity_Source::SCHEMA_VERSION,
				'lossless_serializer_schema' => Core_Block_Lossless_Serializer::SCHEMA_VERSION,
				'editorial_parity_schema' => Core_Block_Editorial_Parity::SCHEMA_VERSION,
				'stale_guard_schema' => Block_Migration_Stale_Source_Guard::SCHEMA_VERSION,
				'gutenberg_plugin_dependency' => false,
			),
			'block_registry' => $registry,
			'corpus' => array(
				'total_posts' => count( $ids_before ),
				'first_pass' => count( $first['manifest_hashes'] ),
				'second_pass' => count( $second['manifest_hashes'] ),
				'first_errors' => $first['errors'],
				'second_errors' => $second['errors'],
				'first_throwables' => $first['throwables'],
				'second_throwables' => $second['throwables'],
				'first_safety_violations' => $first['safety_violations'],
				'second_safety_violations' => $second['safety_violations'],
				'first_parity_mismatches' => $first['parity_mismatches'],
				'second_parity_mismatches' => $second['parity_mismatches'],
				'first_stale_sources' => $first['stale_sources'],
				'second_stale_sources' => $second['stale_sources'],
				'manifest_hash_mismatches' => $manifest_mismatches,
			),
			'distribution' => array(
				'source_kind' => $first['source_kind'],
				'parity_status' => $first['parity_status'],
				'parity_warnings' => $first['parity_warnings'],
				'stale_status' => $first['stale_status'],
			),
			'safety' => array(
				'read_only_design' => true,
				'renders_blocks' => false,
				'executes_shortcodes' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
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
			'gate_result' => array( 't097_static_editorial_parity_pass' => $gate ),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	/** @param array<int,int> $post_ids @return array<string,mixed> */
	private static function pass( array $post_ids ): array {
		$out = array(
			'manifest_hashes' => array(), 'errors' => 0, 'throwables' => 0,
			'safety_violations' => 0, 'parity_mismatches' => 0, 'stale_sources' => 0,
			'source_kind' => array(), 'parity_status' => array(), 'parity_warnings' => array(), 'stale_status' => array(),
		);
		foreach ( $post_ids as $post_id ) {
			try {
				$planned = Migration_Fidelity_Source::build( $post_id );
				if ( $planned instanceof \WP_Error ) { ++$out['errors']; continue; }
				$serialization = Core_Block_Lossless_Serializer::serialize_source( $planned );
				if ( $serialization instanceof \WP_Error ) { ++$out['errors']; continue; }
				$parsed = array();
				if ( 'serialized_in_memory' === (string) ( $serialization['status'] ?? '' ) ) {
					if ( ! function_exists( 'parse_blocks' ) ) { ++$out['errors']; continue; }
					$parsed_value = parse_blocks( (string) ( $serialization['serialized_post_content'] ?? '' ) );
					$parsed = is_array( $parsed_value ) ? $parsed_value : array();
				}
				$parity = Core_Block_Editorial_Parity::assess( $planned, $serialization, $parsed );
				$current = Migration_Fidelity_Source::build( $post_id );
				if ( $current instanceof \WP_Error ) { ++$out['errors']; continue; }
				$stale = Block_Migration_Stale_Source_Guard::assess( $planned, $current );
				self::inc( $out['source_kind'], (string) ( $planned['source_kind'] ?? 'unknown' ) );
				self::inc( $out['parity_status'], (string) ( $parity['status'] ?? 'unknown' ) );
				self::inc( $out['stale_status'], (string) ( $stale['status'] ?? 'unknown' ) );
				foreach ( (array) ( $parity['warnings'] ?? array() ) as $warning ) { self::inc( $out['parity_warnings'], (string) $warning ); }
				if ( 'mismatch' === ( $parity['status'] ?? '' ) || (int) ( $parity['mismatches'] ?? 0 ) > 0 ) { ++$out['parity_mismatches']; }
				if ( 'stale' === ( $stale['status'] ?? '' ) ) { ++$out['stale_sources']; }
				if ( true === ( $serialization['writer_allowed'] ?? false ) || true === ( $serialization['migration_execution_allowed'] ?? false )
					|| true === ( $parity['writer_allowed'] ?? false ) || true === ( $parity['migration_execution_allowed'] ?? false )
					|| true === ( $stale['writer_allowed'] ?? false ) || true === ( $stale['migration_execution_allowed'] ?? false ) ) {
					++$out['safety_violations'];
				}
				$manifest = array(
					'source_fidelity_hash' => (string) ( $planned['fidelity_hash'] ?? '' ),
					'serialization_hash' => (string) ( $serialization['serialization_hash'] ?? '' ),
					'parity_status' => (string) ( $parity['status'] ?? '' ),
					'parity_mismatches' => (int) ( $parity['mismatches'] ?? 0 ),
					'stale_status' => (string) ( $stale['status'] ?? '' ),
				);
				$out['manifest_hashes'][ $post_id ] = Canonical_JSON::hash( $manifest );
			} catch ( \Throwable $error ) {
				++$out['throwables'];
			}
		}
		ksort( $out['source_kind'] ); ksort( $out['parity_status'] ); ksort( $out['parity_warnings'] ); ksort( $out['stale_status'] );
		return $out;
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => false ) );
		$ids = is_array( $ids ) ? array_map( 'intval', $ids ) : array();
		sort( $ids, SORT_NUMERIC );
		return array_values( array_filter( $ids, static fn( int $id ): bool => $id > 0 ) );
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
