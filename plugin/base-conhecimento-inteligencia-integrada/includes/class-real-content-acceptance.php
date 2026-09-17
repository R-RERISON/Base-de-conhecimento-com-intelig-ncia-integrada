<?php
/**
 * Aceitação temporária de conteúdo real do G-240.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Executa duas passagens read-only sobre uma amostra representativa.
 */
final class Real_Content_Acceptance {

	public const ACTION    = 'bdc_kb_spec004_g240_acceptance';
	public const PAGE_SLUG = 'bdc-kb-spec004-g240-acceptance';

	private const NONCE_ACTION = 'bdc_kb_spec004_g240_run';
	private const NONCE_FIELD  = 'bdc_kb_spec004_g240_nonce';
	private const PER_CATEGORY = 3;

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 33 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'G-240 Real Content Acceptance',
			'G-240 Acceptance',
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
		echo '<h1>' . esc_html__( 'SPEC-004 — G-240 Real Content Acceptance', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Runner temporário de homologação.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Executa duas passagens read-only em amostra representativa e exporta somente agregados, hashes e assinaturas estruturais.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'Não edite posts durante a execução. Nenhum texto, ID, título ou URL é exportado.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar G-240 e exibir relatório', 'bdc-knowledge-base' ), 'primary' );
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

		self::render_report( $report, $json );
	}

	/** @param array<string,mixed> $report */
	private static function render_report( array $report, string $json ): void {
		$acceptance = is_array( $report['acceptance'] ?? null ) ? $report['acceptance'] : array();
		$safety     = is_array( $report['safety'] ?? null ) ? $report['safety'] : array();
		$passed     = 0 === (int) ( $acceptance['first_pass_errors'] ?? 1 )
			&& 0 === (int) ( $acceptance['second_pass_errors'] ?? 1 )
			&& 0 === (int) ( $acceptance['first_pass_throwables'] ?? 1 )
			&& 0 === (int) ( $acceptance['second_pass_throwables'] ?? 1 )
			&& 0 === (int) ( $acceptance['hash_mismatches'] ?? 1 )
			&& 0 === (int) ( $acceptance['canonical_json_mismatches'] ?? 1 )
			&& ! empty( $safety['editorial_fingerprint_equal'] )
			&& 0 === (int) ( $safety['changed_posts_during_run'] ?? 1 );

		wp_die(
			'<div class="wrap">'
			. '<h1>' . esc_html__( 'SPEC-004 — Resultado G-240', 'bdc-knowledge-base' ) . '</h1>'
			. '<div class="notice ' . ( $passed ? 'notice-success' : 'notice-error' ) . ' inline"><p><strong>'
			. esc_html( $passed ? 'PASS' : 'FAIL' ) . '</strong> — '
			. esc_html__( 'relatório read-only executado no ambiente local.', 'bdc-knowledge-base' )
			. '</p></div>'
			. '<p>' . esc_html__( 'O JSON abaixo contém somente agregados, hashes e assinaturas estruturais. Nenhum conteúdo editorial foi exportado.', 'bdc-knowledge-base' ) . '</p>'
			. '<pre style="max-height:70vh;overflow:auto;background:#fff;border:1px solid #c3c4c7;padding:16px;white-space:pre-wrap;">' . esc_html( $json ) . '</pre>'
			. '</div>',
			esc_html__( 'Resultado G-240', 'bdc-knowledge-base' ),
			array( 'response' => 200 )
		);
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids = self::post_ids();
		$before = self::editorial_snapshot( $ids );
		$selected = self::select_sample( $ids );
		$first = self::document_pass( $selected );
		$second = self::document_pass( $selected );
		$after_ids = self::post_ids();
		$after = self::editorial_snapshot( $after_ids );

		$hash_mismatches = 0;
		$json_mismatches = 0;
		foreach ( $selected as $category => $category_ids ) {
			foreach ( $category_ids as $post_id ) {
				$a = $first['documents'][ $post_id ] ?? null;
				$b = $second['documents'][ $post_id ] ?? null;
				if ( ! is_array( $a ) || ! is_array( $b ) ) {
					continue;
				}
				if ( $a['source_hash'] !== $b['source_hash'] || $a['document_hash'] !== $b['document_hash'] ) {
					++$hash_mismatches;
				}
				if ( $a['json_sha256'] !== $b['json_sha256'] ) {
					++$json_mismatches;
				}
			}
		}

		$fingerprint_before = self::aggregate_fingerprint( $before );
		$fingerprint_after  = self::aggregate_fingerprint( $after );
		return array(
			'schema_version' => '1.0.0',
			'mode' => 'temporary_spec004_g240_real_content_acceptance',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
			),
			'safety' => array(
				'read_only_design' => true,
				'exports_editorial_content' => false,
				'exports_post_ids' => false,
				'exports_titles_or_urls' => false,
				'persists_documents_or_hashes' => false,
				'editorial_fingerprint_equal' => hash_equals( $fingerprint_before, $fingerprint_after ),
				'changed_posts_during_run' => self::changed_snapshot_count( $before, $after ),
				'corpus_count_before' => count( $ids ),
				'corpus_count_after' => count( $after_ids ),
			),
			'sample' => array(
				'available_posts' => count( $ids ),
				'per_category_limit' => self::PER_CATEGORY,
				'categories' => array_map( 'count', $selected ),
				'coverage' => array_keys( $selected ),
				'cases' => $first['cases'],
			),
			'acceptance' => array(
				'first_pass_errors' => $first['errors'],
				'second_pass_errors' => $second['errors'],
				'first_pass_throwables' => $first['throwables'],
				'second_pass_throwables' => $second['throwables'],
				'hash_mismatches' => $hash_mismatches,
				'canonical_json_mismatches' => $json_mismatches,
				'first_pass_sections' => $first['sections'],
				'sample_cases_with_documents' => count( $first['documents'] ),
			),
			'performance' => array( 'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ), 'peak_memory_bytes' => memory_get_peak_usage( true ) ),
		);
	}

	/** @param array<int,array<int,int>> $selected @return array<string,mixed> */
	private static function document_pass( array $selected ): array {
		$documents = array();
		$cases = array();
		$errors = 0;
		$throwables = 0;
		$sections = 0;
		foreach ( $selected as $category => $ids ) {
			foreach ( $ids as $post_id ) {
				try {
					$document = Knowledge_Document::build( $post_id );
					if ( is_wp_error( $document ) ) { ++$errors; continue; }
					$json = Knowledge_Document::canonical_json( $document );
					if ( is_wp_error( $json ) ) { ++$errors; continue; }
					$documents[ $post_id ] = array( 'source_hash' => $document['source_hash'], 'document_hash' => $document['document_hash'], 'json_sha256' => hash( 'sha256', $json ) );
					$sections += count( (array) $document['sections'] );
					$cases[] = self::case_signature( $category, $document );
				} catch ( \Throwable $error ) { unset( $error ); ++$throwables; }
			}
		}
		return array( 'documents' => $documents, 'cases' => $cases, 'errors' => $errors, 'throwables' => $throwables, 'sections' => $sections );
	}

	/** @param array<string,mixed> $document @return array<string,mixed> */
	private static function case_signature( string $category, array $document ): array {
		$sections = array();
		foreach ( (array) $document['sections'] as $section ) {
			$sections[] = array( 'kind' => (string) ( $section['kind'] ?? '' ), 'source' => (string) ( $section['source'] ?? '' ), 'ordinal' => (int) ( $section['ordinal'] ?? 0 ), 'text_length' => strlen( (string) ( $section['text'] ?? '' ) ), 'text_sha256' => hash( 'sha256', (string) ( $section['text'] ?? '' ) ) );
		}
		return array( 'category' => $category, 'source_kind' => (string) $document['source_kind'], 'source_hash' => (string) $document['source_hash'], 'document_hash' => (string) $document['document_hash'], 'section_count' => count( $sections ), 'sections' => $sections, 'structure' => $document['structure'], 'warnings' => $document['extraction']['warnings'], 'readiness' => $document['extraction']['elementor_compatibility']['status'] );
	}

	/** @param array<int,int> $ids @return array<string,array<int,int>> */
	private static function select_sample( array $ids ): array {
		$selected = array();
		foreach ( $ids as $post_id ) {
			$extraction = Content_Extractor::extract( $post_id );
			if ( is_wp_error( $extraction ) ) { continue; }
			$category = self::category( $extraction );
			if ( count( $selected[ $category ] ?? array() ) < self::PER_CATEGORY ) { $selected[ $category ][] = $post_id; }
		}
		return $selected;
	}

	/** @param array<string,mixed> $extraction */
	private static function category( array $extraction ): string {
		$source = (string) ( $extraction['source_kind'] ?? '' );
		$warnings = (array) ( $extraction['warnings'] ?? array() );
		$readiness = (string) ( $extraction['elementor_compatibility']['status'] ?? '' );
		if ( 'elementor' === $source ) { return 'elementor_native'; }
		if ( 'mixed' === $source ) { return 'elementor_mixed'; }
		if ( 'gutenberg' === $source ) { return 'gutenberg'; }
		foreach ( $warnings as $warning ) { if ( str_starts_with( (string) $warning, 'SHORTCODE_' ) || str_contains( (string) $warning, 'TABLE' ) ) { return 'shortcode_or_table'; } }
		if ( 'review_required' === $readiness ) { return 'review_required'; }
		if ( 'empty' === $source ) { return 'empty_or_corrupt'; }
		if ( 'legacy_html' === $source ) { return 'legacy_html'; }
		return 'plain_text';
	}

	/** @return array<int,int> */
	private static function post_ids(): array { $ids = get_posts( array( 'post_type' => 'post', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'no_found_rows' => true, 'suppress_filters' => false ) ); return is_array( $ids ) ? array_values( array_map( 'intval', $ids ) ) : array(); }

	/** @param array<int,int> $ids @return array<int,string> */
	private static function editorial_snapshot( array $ids ): array { $snapshot = array(); foreach ( $ids as $post_id ) { $post = get_post( $post_id ); if ( ! is_object( $post ) ) { continue; } $elementor = get_post_meta( $post_id, '_elementor_data', true ); $snapshot[ $post_id ] = hash( 'sha256', implode( "\n", array( $post_id, $post->post_status, $post->post_modified_gmt, hash( 'sha256', $post->post_content ), hash( 'sha256', is_string( $elementor ) ? $elementor : '' ) ) ) ); } ksort( $snapshot, SORT_NUMERIC ); return $snapshot; }

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string { $parts = array(); foreach ( $snapshot as $post_id => $fingerprint ) { $parts[] = $post_id . ':' . $fingerprint; } return hash( 'sha256', implode( "\n", $parts ) ); }

	/** @param array<int,string> $before @param array<int,string> $after */
	private static function changed_snapshot_count( array $before, array $after ): int { $changed = 0; foreach ( array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) ) as $key ) { if ( ( $before[ $key ] ?? null ) !== ( $after[ $key ] ?? null ) ) { ++$changed; } } return $changed; }
}
