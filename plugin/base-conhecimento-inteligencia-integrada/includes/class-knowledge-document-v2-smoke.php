<?php
/**
 * Smoke ambiental read-only do Knowledge Document v2 após remediação estrutural G-240.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Knowledge_Document_V2_Smoke {
	public const ACTION    = 'bdc_kb_spec004_kd_v2_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-kd-v2-smoke';
	private const NONCE_ACTION = 'bdc_kb_spec004_kd_v2_smoke_run';
	private const NONCE_FIELD  = 'bdc_kb_spec004_kd_v2_smoke_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 32 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page( Admin_Page::PAGE_SLUG, 'Validação KD v2', 'Validação KD v2', 'manage_options', self::PAGE_SLUG, array( self::class, 'render_page' ) );
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.' );
		}
		echo '<div class="wrap"><h1>SPEC-004 — Validação ambiental Knowledge Document v2</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>Read-only.</strong> Executa duas passagens sobre todo o corpus para validar schema 2.0.0, hashes, JSON canônico, estrutura e zero mutação.</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( 'Executar validação KD v2 e baixar JSON', 'primary' );
		echo '</form></div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método HTTP não permitido.', '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] ) ? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( 'Nonce inválido ou expirado.', '', array( 'response' => 403 ) );
		}
		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha ao serializar relatório.', '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-kd-v2-smoke-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$snapshot_before = self::editorial_snapshot( $ids_before );
		$fingerprint_before = self::aggregate_fingerprint( $snapshot_before );
		$first = self::document_pass( $ids_before );
		$second = self::document_pass( $ids_before );

		$hash_mismatches = 0;
		$json_mismatches = 0;
		foreach ( $ids_before as $post_id ) {
			if ( ! isset( $first['documents'][ $post_id ], $second['documents'][ $post_id ] ) ) {
				continue;
			}
			$a = $first['documents'][ $post_id ];
			$b = $second['documents'][ $post_id ];
			if ( $a['source_hash'] !== $b['source_hash'] || $a['document_hash'] !== $b['document_hash'] ) {
				++$hash_mismatches;
			}
			if ( $a['json_sha256'] !== $b['json_sha256'] ) {
				++$json_mismatches;
			}
		}

		$ids_after = self::post_ids();
		$snapshot_after = self::editorial_snapshot( $ids_after );
		$fingerprint_after = self::aggregate_fingerprint( $snapshot_after );
		$changed = self::changed_snapshot_count( $snapshot_before, $snapshot_after );
		$gate_pass = count( $ids_before ) === count( $ids_after )
			&& count( $ids_before ) === count( $first['documents'] )
			&& count( $ids_before ) === count( $second['documents'] )
			&& 0 === $first['errors'] && 0 === $second['errors']
			&& 0 === $first['throwables'] && 0 === $second['throwables']
			&& 0 === $hash_mismatches && 0 === $json_mismatches
			&& 0 === $first['structure_incomplete'] && 0 === $second['structure_incomplete']
			&& 0 === $changed && hash_equals( $fingerprint_before, $fingerprint_after );

		return array(
			'schema_version' => '2.0.0',
			'mode' => 'temporary_spec004_knowledge_document_v2_smoke',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
				'knowledge_document_schema' => Knowledge_Document::SCHEMA_VERSION,
			),
			'safety' => array(
				'read_only_design' => true,
				'exports_editorial_content' => false,
				'exports_post_ids' => false,
				'exports_titles_or_urls' => false,
				'persists_documents_or_hashes' => false,
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => hash_equals( $fingerprint_before, $fingerprint_after ),
				'changed_posts_during_run' => $changed,
				'corpus_count_before' => count( $ids_before ),
				'corpus_count_after' => count( $ids_after ),
			),
			'knowledge_documents' => array(
				'first_pass_documents' => count( $first['documents'] ),
				'second_pass_documents' => count( $second['documents'] ),
				'first_pass_errors' => $first['errors'],
				'second_pass_errors' => $second['errors'],
				'first_pass_throwables' => $first['throwables'],
				'second_pass_throwables' => $second['throwables'],
				'hash_mismatches' => $hash_mismatches,
				'canonical_json_mismatches' => $json_mismatches,
				'first_pass_structure_incomplete' => $first['structure_incomplete'],
				'second_pass_structure_incomplete' => $second['structure_incomplete'],
				'sections_total' => $first['sections_total'],
				'blocks_total' => $first['blocks_total'],
				'source_kinds' => $first['source_kinds'],
				'ai_readiness' => $first['ai_readiness'],
			),
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_pass' => $gate_pass,
		);
	}

	/** @param array<int,int> $ids @return array<string,mixed> */
	private static function document_pass( array $ids ): array {
		$documents = array();
		$errors = 0;
		$throwables = 0;
		$structure_incomplete = 0;
		$sections_total = 0;
		$blocks_total = 0;
		$source_kinds = array();
		$ai_readiness = array();

		foreach ( $ids as $post_id ) {
			try {
				$document = Knowledge_Document::build( $post_id );
				if ( is_wp_error( $document ) ) {
					++$errors;
					continue;
				}
				$json = Knowledge_Document::canonical_json( $document );
				if ( is_wp_error( $json ) ) {
					++$errors;
					continue;
				}
				$documents[ $post_id ] = array(
					'source_hash' => (string) ( $document['source_hash'] ?? '' ),
					'document_hash' => (string) ( $document['document_hash'] ?? '' ),
					'json_sha256' => hash( 'sha256', $json ),
				);
				$sections_total += count( (array) ( $document['sections'] ?? array() ) );
				$blocks_total += count( (array) ( $document['blocks'] ?? array() ) );
				self::increment( $source_kinds, (string) ( $document['source_kind'] ?? 'unknown' ) );
				$status = (string) ( $document['ai_readiness']['status'] ?? 'unknown' );
				self::increment( $ai_readiness, $status );
				if ( false === (bool) ( $document['ai_readiness']['structure_complete'] ?? false ) ) {
					++$structure_incomplete;
				}
			} catch ( \Throwable $error ) {
				unset( $error );
				++$throwables;
			}
		}
		arsort( $source_kinds, SORT_NUMERIC );
		arsort( $ai_readiness, SORT_NUMERIC );
		return array(
			'documents' => $documents, 'errors' => $errors, 'throwables' => $throwables,
			'structure_incomplete' => $structure_incomplete, 'sections_total' => $sections_total,
			'blocks_total' => $blocks_total, 'source_kinds' => $source_kinds, 'ai_readiness' => $ai_readiness,
		);
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts( array(
			'post_type' => 'post', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC',
			'no_found_rows' => true, 'suppress_filters' => false,
		) );
		return is_array( $ids ) ? array_values( array_map( 'intval', $ids ) ) : array();
	}

	/** @param array<int,int> $ids @return array<int,string> */
	private static function editorial_snapshot( array $ids ): array {
		$snapshot = array();
		foreach ( $ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$elementor = get_post_meta( $post_id, '_elementor_data', true );
			$elementor_string = is_string( $elementor ) ? $elementor : self::stable_json( $elementor );
			$snapshot[ $post_id ] = hash( 'sha256', implode( "\n", array(
				(string) $post_id, (string) ( $post->post_status ?? '' ), (string) ( $post->post_modified_gmt ?? '' ),
				hash( 'sha256', (string) ( $post->post_title ?? '' ) ), hash( 'sha256', (string) ( $post->post_content ?? '' ) ), hash( 'sha256', $elementor_string ),
			) ) );
		}
		ksort( $snapshot, SORT_NUMERIC );
		return $snapshot;
	}

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		$parts = array();
		foreach ( $snapshot as $post_id => $fingerprint ) {
			$parts[] = $post_id . ':' . $fingerprint;
		}
		return hash( 'sha256', implode( "\n", $parts ) );
	}

	/** @param array<int,string> $before @param array<int,string> $after */
	private static function changed_snapshot_count( array $before, array $after ): int {
		$count = 0;
		foreach ( array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) ) as $id ) {
			if ( ( $before[ $id ] ?? null ) !== ( $after[ $id ] ?? null ) ) {
				++$count;
			}
		}
		return $count;
	}

	/** @param array<string,int> $counts */
	private static function increment( array &$counts, string $key ): void {
		$counts[ $key ] = (int) ( $counts[ $key ] ?? 0 ) + 1;
	}

	private static function stable_json( mixed $value ): string {
		$json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}
}
