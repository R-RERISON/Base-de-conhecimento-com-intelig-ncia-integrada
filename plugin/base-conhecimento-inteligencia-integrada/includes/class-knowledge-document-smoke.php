<?php
/**
 * Smoke ambiental temporário e read-only do G-230.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** TEMPORÁRIO: remover/desabilitar após evidência ambiental do G-230. */
final class Knowledge_Document_Smoke {

	public const ACTION    = 'bdc_kb_spec004_g230_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-g230-smoke';
	private const NONCE_ACTION = 'bdc_kb_spec004_g230_smoke_run';
	private const NONCE_FIELD  = 'bdc_kb_spec004_g230_smoke_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 32 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page( Admin_Page::PAGE_SLUG, 'Smoke G-230', 'Smoke G-230', 'manage_options', self::PAGE_SLUG, array( self::class, 'render_page' ) );
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SPEC-004 — Smoke G-230 Knowledge Document', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Build temporário de homologação.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'O runner constrói o Knowledge Document duas vezes para todo o corpus, compara hashes/JSON e não exporta conteúdo, IDs, títulos ou URLs.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'Evite edição concorrente durante a execução para manter o corpus estável entre as duas passagens.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar smoke G-230 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
			wp_die( esc_html__( 'Falha ao serializar o relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}
		$filename = 'bdc-kb-spec004-g230-smoke-' . gmdate( 'Ymd-His' ) . '.json';
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
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
		$changed_count = self::changed_snapshot_count( $snapshot_before, $snapshot_after );
		$source_hashes = array();
		$document_hashes = array();
		foreach ( $first['documents'] as $document ) {
			$source_hashes[] = $document['source_hash'];
			$document_hashes[] = $document['document_hash'];
		}

		return array(
			'schema_version' => '1.0.0',
			'mode' => 'temporary_spec004_g230_read_only_smoke',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
				'knowledge_document_schema' => Knowledge_Document::SCHEMA_VERSION,
				'multisite' => is_multisite(),
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
				'changed_posts_during_run' => $changed_count,
				'corpus_count_before' => count( $ids_before ),
				'corpus_count_after' => count( $ids_after ),
			),
			'knowledge_documents' => array(
				'total_posts' => count( $ids_before ),
				'first_pass_documents' => count( $first['documents'] ),
				'second_pass_documents' => count( $second['documents'] ),
				'first_pass_errors' => $first['errors'],
				'second_pass_errors' => $second['errors'],
				'first_pass_throwables' => $first['throwables'],
				'second_pass_throwables' => $second['throwables'],
				'hash_mismatches' => $hash_mismatches,
				'canonical_json_mismatches' => $json_mismatches,
				'unique_source_hashes' => count( array_unique( $source_hashes ) ),
				'unique_document_hashes' => count( array_unique( $document_hashes ) ),
				'sections_total' => $first['sections_total'],
				'source_kinds' => $first['source_kinds'],
				'elementor_compatibility' => $first['compatibility'],
				'aggregate_source_hash' => self::aggregate_document_hash( $first['documents'], 'source_hash' ),
				'aggregate_document_hash' => self::aggregate_document_hash( $first['documents'], 'document_hash' ),
			),
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_expectations' => array(
				'editorial_fingerprint_equal' => true,
				'changed_posts_during_run' => 0,
				'corpus_count_unchanged' => true,
				'first_pass_errors' => 0,
				'second_pass_errors' => 0,
				'first_pass_throwables' => 0,
				'second_pass_throwables' => 0,
				'hash_mismatches' => 0,
				'canonical_json_mismatches' => 0
			),
		);
	}

	/** @param array<int,int> $ids @return array<string,mixed> */
	private static function document_pass( array $ids ): array {
		$documents = array();
		$errors = 0;
		$throwables = 0;
		$sections_total = 0;
		$source_kinds = array();
		$compatibility = array();
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
					'source_hash' => (string) $document['source_hash'],
					'document_hash' => (string) $document['document_hash'],
					'json_sha256' => hash( 'sha256', $json ),
				);
				$sections_total += count( (array) ( $document['sections'] ?? array() ) );
				self::increment( $source_kinds, (string) ( $document['source_kind'] ?? 'unknown' ) );
				$status = isset( $document['extraction']['elementor_compatibility']['status'] ) ? (string) $document['extraction']['elementor_compatibility']['status'] : 'unknown';
				self::increment( $compatibility, $status );
			} catch ( \Throwable $error ) {
				unset( $error );
				++$throwables;
			}
		}
		arsort( $source_kinds, SORT_NUMERIC );
		arsort( $compatibility, SORT_NUMERIC );
		return array( 'documents' => $documents, 'errors' => $errors, 'throwables' => $throwables, 'sections_total' => $sections_total, 'source_kinds' => $source_kinds, 'compatibility' => $compatibility );
	}

	/** @param array<int,array<string,string>> $documents */
	private static function aggregate_document_hash( array $documents, string $field ): string {
		ksort( $documents, SORT_NUMERIC );
		$parts = array();
		foreach ( $documents as $post_id => $document ) {
			$parts[] = $post_id . ':' . (string) ( $document[ $field ] ?? '' );
		}
		return hash( 'sha256', implode( "\n", $parts ) );
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts( array( 'post_type' => 'post', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'no_found_rows' => true, 'suppress_filters' => false ) );
		return is_array( $ids ) ? array_values( array_map( 'intval', $ids ) ) : array();
	}

	/** @param array<int,int> $ids @return array<int,string> */
	private static function editorial_snapshot( array $ids ): array {
		$snapshot = array();
		foreach ( $ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) { continue; }
			$elementor = get_post_meta( $post_id, '_elementor_data', true );
			$elementor_string = is_string( $elementor ) ? $elementor : self::stable_json( $elementor );
			$snapshot[ $post_id ] = hash( 'sha256', implode( "\n", array( (string) $post_id, (string) ( $post->post_status ?? '' ), (string) ( $post->post_modified_gmt ?? '' ), hash( 'sha256', (string) ( $post->post_title ?? '' ) ), hash( 'sha256', (string) ( $post->post_content ?? '' ) ), hash( 'sha256', $elementor_string ) ) ) );
		}
		ksort( $snapshot, SORT_NUMERIC );
		return $snapshot;
	}

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		$parts = array();
		foreach ( $snapshot as $post_id => $fingerprint ) { $parts[] = $post_id . ':' . $fingerprint; }
		return hash( 'sha256', implode( "\n", $parts ) );
	}

	/** @param array<int,string> $before @param array<int,string> $after */
	private static function changed_snapshot_count( array $before, array $after ): int {
		$keys = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );
		$changed = 0;
		foreach ( $keys as $key ) { if ( ( $before[ $key ] ?? null ) !== ( $after[ $key ] ?? null ) ) { ++$changed; } }
		return $changed;
	}

	/** @param array<string,int> $counts */
	private static function increment( array &$counts, string $key ): void {
		$key = '' === $key ? 'unknown' : $key;
		$counts[ $key ] = (int) ( $counts[ $key ] ?? 0 ) + 1;
	}

	private static function stable_json( mixed $value ): string {
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}
}
