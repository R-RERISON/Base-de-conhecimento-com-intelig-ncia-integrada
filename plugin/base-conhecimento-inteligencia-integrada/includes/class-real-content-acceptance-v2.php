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

	/** Amostra congelada pelo G-240 v1 para comparação A/B. */
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
		'missing_content', 'wrong_order', 'invented_text', 'structure_loss',
		'shortcode_semantics_missing', 'source_corrupt', 'other_review_required',
	);

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 33 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_submit' ) );
	}

	public static function register_page(): void {
		add_submenu_page( Admin_Page::PAGE_SLUG, 'Aceitação G-240 v2', 'Aceitação G-240 v2', 'manage_options', self::PAGE_SLUG, array( self::class, 'render_page' ) );
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}

		echo '<div class="wrap"><h1>SPEC-004 — G-240 Real Content Acceptance v2</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>Reteste estrutural A/B.</strong> A amostra é exatamente a mesma que falhou no v1. O campo “aceitável para IA” foi removido; AI readiness é calculado pelo sistema.</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		foreach ( self::SAMPLE as $slot => $config ) {
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
				echo '<div class="notice notice-error inline"><p><strong>STALE:</strong> este post mudou desde o aceite v1. O item não poderá passar sem nova baseline.</p></div>';
			}
			if ( ! is_object( $post ) || is_wp_error( $source ) || is_wp_error( $document ) ) {
				echo '<div class="notice notice-error inline"><p>Falha ao materializar este item. O gate ficará bloqueado.</p></div>';
				continue;
			}

			echo '<p><strong>Post ID:</strong> ' . esc_html( (string) $post_id ) . ' — <strong>' . esc_html( (string) ( $post->post_title ?? '' ) ) . '</strong></p>';
			echo '<p><strong>source_kind:</strong> ' . esc_html( (string) ( $document['source_kind'] ?? '' ) ) . ' | <strong>schema:</strong> ' . esc_html( (string) ( $document['schema_version'] ?? '' ) ) . '</p>';
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
			echo '<label><strong>Razão da falha, se houver:</strong> <select name="reason[' . esc_attr( $slot ) . ']"><option value="">—</option>';
			foreach ( self::REASONS as $reason ) {
				echo '<option value="' . esc_attr( $reason ) . '">' . esc_html( $reason ) . '</option>';
			}
			echo '</select></label></fieldset>';
		}

		submit_button( 'Gerar evidência G-240 v2 (JSON)', 'primary', 'submit', true, array( 'style' => 'margin-top:24px' ) );
		echo '</form></div>';
	}

	public static function handle_submit(): void {
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

		$ids = array_values( array_map( static fn ( array $config ): int => (int) $config['post_id'], self::SAMPLE ) );
		$before = self::snapshot( $ids );
		$before_hash = self::aggregate_fingerprint( $before );
		$items = array();
		$passed = 0;
		$stale_count = 0;
		$repeatability_failures = 0;
		$id_mismatches = 0;

		foreach ( self::SAMPLE as $slot => $config ) {
			$post_id = (int) $config['post_id'];
			$submitted_id = isset( $posted_ids[ $slot ] ) ? (int) $posted_ids[ $slot ] : 0;
			if ( $submitted_id !== $post_id ) {
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
			foreach ( self::VERDICT_FIELDS as $field => $label ) {
				unset( $label );
				$value = isset( $posted_verdicts[ $slot ][ $field ] ) && '1' === (string) $posted_verdicts[ $slot ][ $field ];
				$verdict[ $field ] = $value;
				$all_true = $all_true && $value;
			}
			$reason = isset( $posted_reasons[ $slot ] ) && is_scalar( $posted_reasons[ $slot ] ) ? (string) $posted_reasons[ $slot ] : '';
			if ( ! in_array( $reason, self::REASONS, true ) ) {
				$reason = '';
			}
			if ( ! $all_true && '' === $reason ) {
				$reason = 'other_review_required';
			}
			$pass = $all_true && ! $stale && $repeatable && 0 === $id_mismatches;
			if ( $pass ) {
				++$passed;
			}

			$items[] = array(
				'slot' => $slot,
				'post_id' => $post_id,
				'source_kind' => is_array( $doc_a ) ? (string) ( $doc_a['source_kind'] ?? '' ) : 'error',
				'schema_version' => is_array( $doc_a ) ? (string) ( $doc_a['schema_version'] ?? '' ) : 'error',
				'source_hash' => is_array( $doc_a ) ? (string) ( $doc_a['source_hash'] ?? '' ) : '',
				'document_hash' => is_array( $doc_a ) ? (string) ( $doc_a['document_hash'] ?? '' ) : '',
				'ai_readiness' => is_array( $doc_a ) && is_array( $doc_a['ai_readiness'] ?? null ) ? $doc_a['ai_readiness'] : array( 'status' => 'error' ),
				'baseline_editorial_fingerprint' => $baseline_fp,
				'current_editorial_fingerprint' => $current_fp,
				'stale' => $stale,
				'repeatable' => $repeatable,
				'verdict' => $verdict,
				'reason' => $reason,
				'pass' => $pass,
			);
		}

		$after = self::snapshot( $ids );
		$after_hash = self::aggregate_fingerprint( $after );
		$changed = self::changed_snapshot_count( $before, $after );
		$gate_pass = count( self::SAMPLE ) === $passed && 0 === $stale_count && 0 === $repeatability_failures && 0 === $id_mismatches && 0 === $changed && hash_equals( $before_hash, $after_hash );

		return array(
			'schema_version' => '2.0.0',
			'mode' => 'temporary_spec004_g240_real_content_acceptance_v2',
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
				'exports_titles_or_urls' => false,
				'exports_post_ids' => true,
				'persists_results' => false,
				'sample_fingerprint_before' => $before_hash,
				'sample_fingerprint_after' => $after_hash,
				'sample_fingerprint_equal' => hash_equals( $before_hash, $after_hash ),
				'changed_posts_during_report_generation' => $changed,
			),
			'acceptance' => array(
				'expected_slots' => count( self::SAMPLE ),
				'passed_slots' => $passed,
				'stale_slots' => $stale_count,
				'repeatability_failures' => $repeatability_failures,
				'sample_id_mismatches' => $id_mismatches,
				'gate_pass' => $gate_pass,
				'items' => $items,
			),
		);
	}

	/** @param array<string,mixed> $source */
	private static function render_source( array $source ): void {
		echo '<details open><summary><strong>post_content</strong></summary><pre style="white-space:pre-wrap;max-height:520px;overflow:auto;background:#fff;padding:12px;border:1px solid #ccd0d4">' . esc_html( (string) ( $source['post_content'] ?? '' ) ) . '</pre></details>';
		if ( ! empty( $source['flags']['has_elementor_meta'] ) ) {
			echo '<details><summary><strong>_elementor_data</strong> (raw, não executado)</summary><pre style="white-space:pre-wrap;max-height:520px;overflow:auto;background:#fff;padding:12px;border:1px solid #ccd0d4">' . esc_html( self::pretty( $source['elementor_raw'] ?? '' ) ) . '</pre></details>';
		}
	}

	/** @param array<string,mixed> $document */
	private static function render_document( array $document ): void {
		echo '<p><strong>structure:</strong> <code>' . esc_html( self::json( $document['structure'] ?? array() ) ) . '</code></p>';
		echo '<p><strong>warnings:</strong> <code>' . esc_html( self::json( $document['extraction']['warnings'] ?? array() ) ) . '</code></p>';
		foreach ( (array) ( $document['blocks'] ?? array() ) as $block ) {
			if ( is_array( $block ) ) {
				self::render_block( $block );
			}
		}
		echo '<details style="margin-top:16px"><summary>sections[] para diagnóstico</summary><pre style="white-space:pre-wrap;max-height:420px;overflow:auto">' . esc_html( self::json( $document['sections'] ?? array(), true ) ) . '</pre></details>';
	}

	/** @param array<string,mixed> $block */
	private static function render_block( array $block ): void {
		$kind = (string) ( $block['kind'] ?? 'paragraph' );
		$path = is_array( $block['heading_path'] ?? null ) ? $block['heading_path'] : array();
		if ( $path && 'heading' !== $kind ) {
			$labels = array_map( static fn ( array $part ): string => (string) ( $part['text'] ?? '' ), $path );
			echo '<div style="font-size:11px;color:#646970;margin-top:10px">↳ ' . esc_html( implode( ' › ', $labels ) ) . '</div>';
		}
		if ( 'heading' === $kind ) {
			$level = max( 1, min( 6, (int) ( $block['meta']['level'] ?? 2 ) ) );
			echo '<h4 style="margin-bottom:4px">H' . esc_html( (string) $level ) . ' — ' . esc_html( (string) ( $block['text'] ?? '' ) ) . '</h4>';
			return;
		}
		if ( 'list' === $kind ) {
			self::render_list( $block );
			return;
		}
		if ( 'table' === $kind ) {
			if ( '' !== (string) ( $block['caption'] ?? '' ) ) {
				echo '<p><strong>Caption:</strong> ' . esc_html( (string) $block['caption'] ) . '</p>';
			}
			echo '<table class="widefat striped" style="margin:8px 0"><tbody>';
			foreach ( (array) ( $block['rows'] ?? array() ) as $row ) {
				echo '<tr>';
				foreach ( (array) ( $row['cells'] ?? array() ) as $cell ) {
					$tag = 'header' === (string) ( $cell['kind'] ?? '' ) ? 'th' : 'td';
					echo '<' . $tag . ' colspan="' . esc_attr( (string) max( 1, (int) ( $cell['colspan'] ?? 1 ) ) ) . '" rowspan="' . esc_attr( (string) max( 1, (int) ( $cell['rowspan'] ?? 1 ) ) ) . '">' . esc_html( (string) ( $cell['text'] ?? '' ) ) . '</' . $tag . '>';
				}
				echo '</tr>';
			}
			echo '</tbody></table>';
			return;
		}
		if ( 'code' === $kind ) {
			echo '<pre style="background:#f6f7f7;padding:10px">' . esc_html( (string) ( $block['text'] ?? '' ) ) . '</pre>';
			return;
		}
		if ( 'quote' === $kind ) {
			echo '<blockquote style="border-left:4px solid #646970;padding-left:12px">' . esc_html( (string) ( $block['text'] ?? '' ) ) . '</blockquote>';
			return;
		}
		echo '<p><span style="font-size:11px;color:#646970">[' . esc_html( $kind ) . ']</span> ' . esc_html( (string) ( $block['text'] ?? '' ) ) . '</p>';
	}

	/** @param array<string,mixed> $list */
	private static function render_list( array $list ): void {
		$tag = 'ordered' === (string) ( $list['list_type'] ?? '' ) ? 'ol' : 'ul';
		echo '<' . $tag . '>';
		foreach ( (array) ( $list['items'] ?? array() ) as $item ) {
			echo '<li>' . esc_html( (string) ( $item['text'] ?? '' ) );
			foreach ( (array) ( $item['children'] ?? array() ) as $child ) {
				if ( is_array( $child ) ) {
					self::render_list( $child );
				}
			}
			echo '</li>';
		}
		echo '</' . $tag . '>';
	}

	private static function editorial_fingerprint( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return '';
		}
		$elementor = get_post_meta( $post_id, '_elementor_data', true );
		$elementor_string = is_string( $elementor ) ? $elementor : self::json( $elementor );
		return hash( 'sha256', implode( "\n", array(
			(string) $post_id,
			(string) ( $post->post_status ?? '' ),
			(string) ( $post->post_modified_gmt ?? '' ),
			hash( 'sha256', (string) ( $post->post_title ?? '' ) ),
			hash( 'sha256', (string) ( $post->post_content ?? '' ) ),
			hash( 'sha256', $elementor_string ),
		) ) );
	}

	/** @param array<int,int> $ids @return array<int,string> */
	private static function snapshot( array $ids ): array {
		$out = array();
		foreach ( $ids as $id ) {
			$out[ $id ] = self::editorial_fingerprint( $id );
		}
		ksort( $out, SORT_NUMERIC );
		return $out;
	}

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		$parts = array();
		foreach ( $snapshot as $id => $fingerprint ) {
			$parts[] = $id . ':' . $fingerprint;
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

	/** @param array<string,mixed> $document */
	private static function canonical_sha( array $document ): string {
		$json = Knowledge_Document::canonical_json( $document );
		return is_string( $json ) ? hash( 'sha256', $json ) : '';
	}

	private static function pretty( mixed $value ): string {
		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			return is_array( $decoded ) ? self::json( $decoded, true ) : $value;
		}
		return self::json( $value, true );
	}

	private static function json( mixed $value, bool $pretty = false ): string {
		$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ( $pretty ? JSON_PRETTY_PRINT : 0 );
		$json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $value, $flags ) : json_encode( $value, $flags );
		return is_string( $json ) ? $json : '';
	}
}
