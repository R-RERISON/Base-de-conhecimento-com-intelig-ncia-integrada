<?php
/**
 * G-240 v2: aceitação humana read-only sobre a mesma amostra que revelou perda estrutural no v1.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Real_Content_Acceptance_V2 {

	public const ACTION    = 'bdc_kb_spec004_g240_acceptance_v2';
	public const PAGE_SLUG = 'bdc-kb-spec004-g240-acceptance-v2';

	private const NONCE_ACTION = 'bdc_kb_spec004_g240_acceptance_v2_submit';
	private const NONCE_FIELD  = 'bdc_kb_spec004_g240_acceptance_v2_nonce';

	/** Mesma amostra do G-240 v1 para comparação A/B. */
	private const SAMPLE = array(
		'elementor_native_typical' => array( 'label' => 'Elementor nativo — típico', 'post_id' => 44981, 'fingerprint' => '8b632a173c96ff9df10cd5b99e2187355efb4bc8d93cf7396cad3af3990e069f' ),
		'elementor_or_mixed_complex' => array( 'label' => 'Elementor/Mixed — complexo', 'post_id' => 1290, 'fingerprint' => 'a813c8191fd9e2d4715aa6971a7a6a6ee2f926e37c65d8a97ea0041e058d7b81' ),
		'legacy_typical' => array( 'label' => 'Legacy HTML — típico', 'post_id' => 370, 'fingerprint' => '5ef68c8633389f12ac86337af5b269b40c8a139cac5c8129b9b063c18f26c95d' ),
		'legacy_complex' => array( 'label' => 'Legacy HTML — complexo', 'post_id' => 1307, 'fingerprint' => '8f481cb627d14911fa6e6b5ade0e533cdc4e1e11845afac6c74356a90f06f97e' ),
		'gutenberg' => array( 'label' => 'Gutenberg', 'post_id' => 45782, 'fingerprint' => '59682c7a6c0b4a902188e792b4bfb5d275c9989273824f769ee6f9d32a55153f' ),
		'shortcode_or_table' => array( 'label' => 'Shortcode/Tabela', 'post_id' => 36431, 'fingerprint' => 'f6df5a12e761b17e9d3fc6dd41f92f6635d19a72ecf70efec473cfdc28d4795f' ),
		'review_required' => array( 'label' => 'Revisão requerida', 'post_id' => 1289, 'fingerprint' => '266bfee946967dfe7868d3ddd39e5cc5e0dc95dd745f0c583bca87429522e634' ),
		'empty_or_corrupt' => array( 'label' => 'Vazio/Corrompido', 'post_id' => 28748, 'fingerprint' => '4c8e6cccbe98b85a1fd16a633796c9e20b7921af9833da0cbf18e1c887f76053' ),
	);

	private const VERDICT_FIELDS = array(
		'coverage_complete'   => 'Cobertura completa',
		'order_preserved'     => 'Ordem semântica preservada',
		'no_invented_text'    => 'Nenhum texto inventado',
		'structure_preserved' => 'Estrutura semântica preservada',
	);

	private const REASONS = array(
		'missing_content',
		'wrong_order',
		'invented_text',
		'structure_loss',
		'shortcode_semantics_missing',
		'source_corrupt',
		'other_review_required',
	);

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 33 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_submit' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Aceitação G-240 v2',
			'Aceitação G-240 v2',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}

		echo '<div class="wrap"><h1>SPEC-004 — G-240 Real Content Acceptance v2</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>Reteste estrutural A/B.</strong> ';
		echo 'A amostra é exatamente a mesma que falhou no v1. Revise apenas os quatro critérios observáveis; AI readiness é calculado pelo sistema.</p></div>';
		echo '<p><strong>Pré-requisito:</strong> execute antes <em>Validação KD v2</em> e só continue se o JSON retornar <code>gate.pass=true</code>.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		foreach ( self::SAMPLE as $slot => $config ) {
			self::render_item( (string) $slot, $config );
		}

		submit_button( 'Gerar evidência G-240 v2 (JSON)', 'primary', 'submit', true, array( 'style' => 'margin-top:24px' ) );
		echo '</form></div>';
	}

	/** @param array<string,mixed> $config */
	private static function render_item( string $slot, array $config ): void {
		$post_id = (int) $config['post_id'];
		$post = get_post( $post_id );
		$source = Content_Source::inspect( $post_id );
		$document = Knowledge_Document::build( $post_id );
		$current_fp = self::editorial_fingerprint( $post_id );
		$stale = '' === $current_fp || ! hash_equals( (string) $config['fingerprint'], $current_fp );

		echo '<hr style="margin:32px 0"><h2>' . esc_html( (string) $config['label'] ) . '</h2>';
		echo '<input type="hidden" name="sample_id[' . esc_attr( $slot ) . ']" value="' . esc_attr( (string) $post_id ) . '">';
		echo '<input type="hidden" name="sample_fingerprint[' . esc_attr( $slot ) . ']" value="' . esc_attr( $current_fp ) . '">';

		if ( $stale ) {
			echo '<div class="notice notice-error inline"><p><strong>STALE:</strong> este post mudou desde o aceite v1. Este item não pode fechar o gate até nova baseline.</p></div>';
		}
		if ( ! is_object( $post ) || is_wp_error( $source ) || is_wp_error( $document ) ) {
			echo '<div class="notice notice-error inline"><p>Falha ao materializar este item. O gate ficará bloqueado.</p></div>';
			return;
		}

		echo '<p><strong>Post ID:</strong> ' . esc_html( (string) $post_id ) . ' — <strong>' . esc_html( (string) ( $post->post_title ?? '' ) ) . '</strong></p>';
		echo '<p><strong>source_kind:</strong> ' . esc_html( (string) ( $document['source_kind'] ?? '' ) );
		echo ' | <strong>schema:</strong> ' . esc_html( (string) ( $document['schema_version'] ?? '' ) ) . '</p>';
		echo '<p><strong>AI readiness calculado:</strong> <code>' . esc_html( self::json( $document['ai_readiness'] ?? array() ) ) . '</code></p>';

		echo '<div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:20px;align-items:start">';
		echo '<div><h3>Fonte editorial</h3>';
		self::render_source( $source );
		echo '</div><div><h3>Knowledge Document v2</h3>';
		self::render_document( $document );
		echo '</div></div>';

		echo '<fieldset style="margin-top:20px;padding:14px;border:1px solid #ccd0d4"><legend><strong>Veredito humano — somente critérios observáveis</strong></legend>';
		foreach ( self::VERDICT_FIELDS as $field => $label ) {
			echo '<label style="display:block;margin:8px 0"><input type="checkbox" name="verdict[' . esc_attr( $slot ) . '][' . esc_attr( $field ) . ']" value="1"> ' . esc_html( $label ) . '</label>';
		}
		echo '<label style="display:block;margin-top:12px"><strong>Razão da falha, se houver:</strong> <select name="reason[' . esc_attr( $slot ) . ']"><option value="">—</option>';
		foreach ( self::REASONS as $reason ) {
			echo '<option value="' . esc_attr( $reason ) . '">' . esc_html( $reason ) . '</option>';
		}
		echo '</select></label></fieldset>';
	}

	/** @param array<string,mixed> $source */
	private static function render_source( array $source ): void {
		$flags = is_array( $source['flags'] ?? null ) ? $source['flags'] : array();
		echo '<p><strong>Flags:</strong> <code>' . esc_html( self::json( $flags ) ) . '</code></p>';
		$content = (string) ( $source['post_content'] ?? '' );
		if ( '' !== trim( $content ) ) {
			echo '<details open><summary><strong>post_content</strong></summary><pre style="max-height:500px;overflow:auto;white-space:pre-wrap">' . esc_html( $content ) . '</pre></details>';
		}
		$elementor = $source['elementor_raw'] ?? '';
		$elementor_text = is_string( $elementor ) ? $elementor : self::json( $elementor );
		if ( '' !== trim( $elementor_text ) ) {
			echo '<details><summary><strong>_elementor_data</strong></summary><pre style="max-height:500px;overflow:auto;white-space:pre-wrap">' . esc_html( self::pretty_json_if_possible( $elementor_text ) ) . '</pre></details>';
		}
	}

	/** @param array<string,mixed> $document */
	private static function render_document( array $document ): void {
		echo '<p><strong>Estrutura:</strong> <code>' . esc_html( self::json( $document['structure'] ?? array() ) ) . '</code></p>';
		echo '<details open><summary><strong>blocks[] semânticos</strong></summary><pre style="max-height:700px;overflow:auto;white-space:pre-wrap">' . esc_html( self::pretty_json( $document['blocks'] ?? array() ) ) . '</pre></details>';
		echo '<details><summary><strong>sections[]</strong></summary><pre style="max-height:700px;overflow:auto;white-space:pre-wrap">' . esc_html( self::pretty_json( $document['sections'] ?? array() ) ) . '</pre></details>';
		echo '<details><summary><strong>warnings/proveniência</strong></summary><pre style="max-height:350px;overflow:auto;white-space:pre-wrap">' . esc_html( self::pretty_json( $document['extraction'] ?? array() ) ) . '</pre></details>';
	}

	public static function handle_submit(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método HTTP não permitido.', '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] )
			: '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_die( 'Nonce inválido ou expirado.', '', array( 'response' => 403 ) );
		}

		$report = self::build_report();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha ao serializar evidência.', '', array( 'response' => 500 ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-g240-v2-acceptance-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function build_report(): array {
		$posted_ids = isset( $_POST['sample_id'] ) && is_array( $_POST['sample_id'] ) ? wp_unslash( $_POST['sample_id'] ) : array();
		$posted_fps = isset( $_POST['sample_fingerprint'] ) && is_array( $_POST['sample_fingerprint'] ) ? wp_unslash( $_POST['sample_fingerprint'] ) : array();
		$posted_verdicts = isset( $_POST['verdict'] ) && is_array( $_POST['verdict'] ) ? wp_unslash( $_POST['verdict'] ) : array();
		$posted_reasons = isset( $_POST['reason'] ) && is_array( $_POST['reason'] ) ? wp_unslash( $_POST['reason'] ) : array();

		$all_ids = self::post_ids();
		$before = self::snapshot( $all_ids );
		$before_hash = self::aggregate_fingerprint( $before );
		$items = array();
		$reviewed = 0;
		$passed = 0;
		$stale_count = 0;
		$repeatability_failures = 0;
		$id_mismatches = 0;
		$system_not_ready = 0;

		foreach ( self::SAMPLE as $slot => $config ) {
			$post_id = (int) $config['post_id'];
			$submitted_id = isset( $posted_ids[ $slot ] ) ? (int) $posted_ids[ $slot ] : 0;
			$id_mismatch = $submitted_id !== $post_id;
			if ( $id_mismatch ) {
				++$id_mismatches;
			}

			$current_fp = self::editorial_fingerprint( $post_id );
			$page_fp = isset( $posted_fps[ $slot ] ) && is_scalar( $posted_fps[ $slot ] ) ? (string) $posted_fps[ $slot ] : '';
			$baseline_fp = (string) $config['fingerprint'];
			$stale = '' === $current_fp || '' === $page_fp || ! hash_equals( $page_fp, $current_fp ) || ! hash_equals( $baseline_fp, $current_fp );
			if ( $stale ) {
				++$stale_count;
			}

			$doc_a = Knowledge_Document::build( $post_id );
			$doc_b = Knowledge_Document::build( $post_id );
			$repeatable = is_array( $doc_a ) && is_array( $doc_b )
				&& (string) ( $doc_a['source_hash'] ?? '' ) === (string) ( $doc_b['source_hash'] ?? '' )
				&& (string) ( $doc_a['document_hash'] ?? '' ) === (string) ( $doc_b['document_hash'] ?? '' )
				&& self::canonical_sha( $doc_a ) === self::canonical_sha( $doc_b );
			if ( ! $repeatable ) {
				++$repeatability_failures;
			}

			$verdict = array();
			$all_true = true;
			$any_marked = false;
			foreach ( self::VERDICT_FIELDS as $field => $label ) {
				unset( $label );
				$value = isset( $posted_verdicts[ $slot ][ $field ] ) && '1' === (string) $posted_verdicts[ $slot ][ $field ];
				$verdict[ $field ] = $value;
				$all_true = $all_true && $value;
				$any_marked = $any_marked || $value;
			}
			if ( $any_marked || isset( $posted_reasons[ $slot ] ) ) {
				++$reviewed;
			}

			$reason = isset( $posted_reasons[ $slot ] ) && is_scalar( $posted_reasons[ $slot ] ) ? (string) $posted_reasons[ $slot ] : '';
			if ( ! in_array( $reason, self::REASONS, true ) ) {
				$reason = '';
			}
			if ( ! $all_true && '' === $reason ) {
				$reason = 'other_review_required';
			}

			$readiness = is_array( $doc_a ) && is_array( $doc_a['ai_readiness'] ?? null ) ? $doc_a['ai_readiness'] : array();
			$readiness_status = (string) ( $readiness['status'] ?? 'not_ready' );
			$system_ready = in_array( $readiness_status, array( 'candidate_ready', 'not_applicable' ), true );
			if ( ! $system_ready ) {
				++$system_not_ready;
			}

			$pass = $all_true && ! $stale && $repeatable && ! $id_mismatch && $system_ready;
			if ( $pass ) {
				++$passed;
			}

			$items[] = array(
				'slot' => $slot,
				'post_id' => $post_id,
				'source_kind' => is_array( $doc_a ) ? (string) ( $doc_a['source_kind'] ?? '' ) : '',
				'schema_version' => is_array( $doc_a ) ? (string) ( $doc_a['schema_version'] ?? '' ) : '',
				'source_hash' => is_array( $doc_a ) ? (string) ( $doc_a['source_hash'] ?? '' ) : '',
				'document_hash' => is_array( $doc_a ) ? (string) ( $doc_a['document_hash'] ?? '' ) : '',
				'baseline_editorial_fingerprint' => $baseline_fp,
				'current_editorial_fingerprint' => $current_fp,
				'stale' => $stale,
				'repeatable' => $repeatable,
				'ai_readiness' => $readiness,
				'system_ready_for_knowledge_candidate' => $system_ready,
				'verdict' => $verdict,
				'reason' => $reason,
				'pass' => $pass,
			);
		}

		$after_ids = self::post_ids();
		$after = self::snapshot( $after_ids );
		$after_hash = self::aggregate_fingerprint( $after );
		$changed = self::changed_snapshot_count( $before, $after );
		$fingerprint_equal = hash_equals( $before_hash, $after_hash );
		$corpus_unchanged = $all_ids === $after_ids;
		$gate_pass = count( self::SAMPLE ) === $passed
			&& 0 === $stale_count
			&& 0 === $repeatability_failures
			&& 0 === $id_mismatches
			&& 0 === $system_not_ready
			&& $fingerprint_equal
			&& $corpus_unchanged
			&& 0 === $changed;

		return array(
			'schema_version' => '2.0.0',
			'mode' => 'temporary_spec004_g240_v2_real_content_acceptance',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
				'knowledge_document_schema' => Knowledge_Document::SCHEMA_VERSION,
				'domdocument' => class_exists( '\\DOMDocument' ),
			),
			'safety' => array(
				'read_only_design' => true,
				'exports_editorial_content' => false,
				'exports_titles_or_urls' => false,
				'exports_post_ids' => true,
				'persists_results' => false,
				'editorial_fingerprint_before' => $before_hash,
				'editorial_fingerprint_after' => $after_hash,
				'editorial_fingerprint_equal' => $fingerprint_equal,
				'changed_posts_during_report_generation' => $changed,
				'corpus_unchanged' => $corpus_unchanged,
			),
			'acceptance' => array(
				'expected_slots' => count( self::SAMPLE ),
				'reviewed_slots' => $reviewed,
				'passed_slots' => $passed,
				'stale_slots' => $stale_count,
				'repeatability_failures' => $repeatability_failures,
				'sample_id_mismatches' => $id_mismatches,
				'system_not_ready_slots' => $system_not_ready,
				'gate_pass' => $gate_pass,
				'items' => $items,
			),
		);
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts(
			array(
				'post_type' => 'post',
				'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);
		return is_array( $ids ) ? array_values( array_map( 'intval', $ids ) ) : array();
	}

	/** @param array<int,int> $ids @return array<int,string> */
	private static function snapshot( array $ids ): array {
		$out = array();
		foreach ( $ids as $post_id ) {
			$fingerprint = self::editorial_fingerprint( $post_id );
			if ( '' !== $fingerprint ) {
				$out[ $post_id ] = $fingerprint;
			}
		}
		ksort( $out, SORT_NUMERIC );
		return $out;
	}

	private static function editorial_fingerprint( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return '';
		}
		$elementor = get_post_meta( $post_id, '_elementor_data', true );
		$elementor_string = is_string( $elementor ) ? $elementor : self::json( $elementor );
		return hash(
			'sha256',
			implode(
				"\n",
				array(
					(string) $post_id,
					(string) ( $post->post_status ?? '' ),
					(string) ( $post->post_modified_gmt ?? '' ),
					hash( 'sha256', (string) ( $post->post_title ?? '' ) ),
					hash( 'sha256', (string) ( $post->post_content ?? '' ) ),
					hash( 'sha256', $elementor_string ),
				)
			)
		);
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
		$keys = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );
		$changed = 0;
		foreach ( $keys as $key ) {
			if ( ( $before[ $key ] ?? null ) !== ( $after[ $key ] ?? null ) ) {
				++$changed;
			}
		}
		return $changed;
	}

	/** @param array<string,mixed> $document */
	private static function canonical_sha( array $document ): string {
		$json = Knowledge_Document::canonical_json( $document );
		return is_string( $json ) ? hash( 'sha256', $json ) : '';
	}

	private static function pretty_json_if_possible( string $value ): string {
		$decoded = json_decode( $value, true );
		return is_array( $decoded ) ? self::pretty_json( $decoded ) : $value;
	}

	private static function pretty_json( mixed $value ): string {
		$json = wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}

	private static function json( mixed $value ): string {
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}
}
