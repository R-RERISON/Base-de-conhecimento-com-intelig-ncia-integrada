<?php
/**
 * Ferramenta temporária e read-only de aceitação de conteúdo real do G-240.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** TEMPORÁRIO: remover/desabilitar após o G-240. */
final class Real_Content_Acceptance {

	public const ACTION    = 'bdc_kb_spec004_g240_acceptance';
	public const PAGE_SLUG = 'bdc-kb-spec004-g240-acceptance';

	private const NONCE_ACTION = 'bdc_kb_spec004_g240_acceptance_submit';
	private const NONCE_FIELD  = 'bdc_kb_spec004_g240_acceptance_nonce';

	private const SLOTS = array(
		'elementor_native_typical'   => 'Elementor nativo — típico',
		'elementor_or_mixed_complex' => 'Elementor/Mixed — complexo',
		'legacy_typical'             => 'Legacy HTML — típico',
		'legacy_complex'             => 'Legacy HTML — complexo',
		'gutenberg'                  => 'Gutenberg',
		'shortcode_or_table'         => 'Shortcode/Tabela',
		'review_required'            => 'Revisão requerida',
		'empty_or_corrupt'           => 'Vazio/Corrompido',
	);

	private const VERDICT_FIELDS = array(
		'coverage_complete'            => 'Cobertura completa',
		'order_preserved'              => 'Ordem semântica preservada',
		'no_invented_text'             => 'Nenhum texto inventado',
		'structure_adequate'           => 'Estrutura adequada',
		'acceptable_for_knowledge_use' => 'Aceitável para busca/IA',
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
			'Aceitação G-240',
			'Aceitação G-240',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}

		$sample = self::select_sample();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SPEC-004 — G-240 Real Content Acceptance', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Ferramenta temporária de homologação.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'A tela exibe fonte editorial e Knowledge Document para inspeção humana. Nada é persistido; o JSON final não exporta o conteúdo.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'Revise cada slot, marque os cinco critérios somente quando a representação derivada for fiel e então gere a evidência.', 'bdc-knowledge-base' ) . '</p>';

		if ( ! empty( $sample['errors'] ) ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'A seleção encontrou erros. O gate não pode ser fechado enquanto houver falhas de build.', 'bdc-knowledge-base' ) . '</p></div>';
		}

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		foreach ( self::SLOTS as $slot => $label ) {
			$entry = $sample['slots'][ $slot ] ?? null;
			echo '<hr style="margin:32px 0">';
			echo '<h2>' . esc_html( $label ) . '</h2>';

			if ( ! is_array( $entry ) || empty( $entry['available'] ) ) {
				echo '<p><em>' . esc_html__( 'Categoria não disponível no corpus atual.', 'bdc-knowledge-base' ) . '</em></p>';
				echo '<input type="hidden" name="sample_status[' . esc_attr( $slot ) . ']" value="not_available">';
				continue;
			}

			$post_id = (int) $entry['post_id'];
			$post    = get_post( $post_id );
			$doc     = $entry['document'];
			$source  = $entry['source'];
			$fingerprint = self::editorial_fingerprint( $post_id );
			$title = is_object( $post ) ? (string) ( $post->post_title ?? '' ) : '';

			echo '<input type="hidden" name="sample_status[' . esc_attr( $slot ) . ']" value="selected">';
			echo '<input type="hidden" name="sample_id[' . esc_attr( $slot ) . ']" value="' . esc_attr( (string) $post_id ) . '">';
			echo '<input type="hidden" name="sample_fingerprint[' . esc_attr( $slot ) . ']" value="' . esc_attr( $fingerprint ) . '">';

			echo '<p><strong>Post ID:</strong> ' . esc_html( (string) $post_id );
			if ( '' !== $title ) {
				echo ' — <strong>' . esc_html( $title ) . '</strong>';
			}
			echo '</p>';
			echo '<p><strong>source_kind:</strong> ' . esc_html( (string) ( $doc['source_kind'] ?? '' ) );
			echo ' | <strong>readiness:</strong> ' . esc_html( (string) ( $doc['extraction']['elementor_compatibility']['status'] ?? '' ) );
			echo ' | <strong>sections:</strong> ' . esc_html( (string) count( (array) ( $doc['sections'] ?? array() ) ) );
			echo ' | <strong>score:</strong> ' . esc_html( (string) $entry['score'] ) . '</p>';

			$edit_link = get_edit_post_link( $post_id, 'raw' );
			if ( is_string( $edit_link ) && '' !== $edit_link ) {
				echo '<p><a href="' . esc_url( $edit_link ) . '" target="_blank" rel="noopener">' . esc_html__( 'Abrir post em outra aba', 'bdc-knowledge-base' ) . '</a></p>';
			}

			echo '<div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:20px;align-items:start">';
			echo '<div><h3>' . esc_html__( 'Fonte editorial', 'bdc-knowledge-base' ) . '</h3>';
			self::render_source( $source );
			echo '</div>';
			echo '<div><h3>' . esc_html__( 'Knowledge Document', 'bdc-knowledge-base' ) . '</h3>';
			self::render_document( $doc );
			echo '</div></div>';

			echo '<fieldset style="margin-top:20px;padding:14px;border:1px solid #ccd0d4"><legend><strong>' . esc_html__( 'Veredito obrigatório', 'bdc-knowledge-base' ) . '</strong></legend>';
			foreach ( self::VERDICT_FIELDS as $field => $field_label ) {
				echo '<label style="display:block;margin:8px 0"><input type="checkbox" name="verdict[' . esc_attr( $slot ) . '][' . esc_attr( $field ) . ']" value="1"> ' . esc_html( $field_label ) . '</label>';
			}
			echo '<label style="display:block;margin-top:12px"><strong>' . esc_html__( 'Razão da falha, se houver:', 'bdc-knowledge-base' ) . '</strong> ';
			echo '<select name="reason[' . esc_attr( $slot ) . ']"><option value="">—</option>';
			foreach ( self::REASONS as $reason ) {
				echo '<option value="' . esc_attr( $reason ) . '">' . esc_html( $reason ) . '</option>';
			}
			echo '</select></label></fieldset>';
		}

		submit_button( __( 'Gerar evidência G-240 (JSON)', 'bdc-knowledge-base' ), 'primary', 'submit', true, array( 'style' => 'margin-top:24px' ) );
		echo '</form></div>';
	}

	public static function handle_submit(): void {
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

		$report = self::build_report();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar a evidência.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		$filename = 'bdc-kb-spec004-g240-acceptance-' . gmdate( 'Ymd-His' ) . '.json';
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function select_sample(): array {
		$ids = self::post_ids();
		$candidates = array();
		$errors = 0;

		foreach ( $ids as $post_id ) {
			try {
				$document = Knowledge_Document::build( $post_id );
				$source   = Content_Source::inspect( $post_id );
				if ( is_wp_error( $document ) || is_wp_error( $source ) ) {
					++$errors;
					continue;
				}
				$candidates[] = self::candidate( $post_id, $document, $source );
			} catch ( \Throwable $error ) {
				unset( $error );
				++$errors;
			}
		}

		$used  = array();
		$slots = array();

		$slots['elementor_native_typical'] = self::pick_typical( $candidates, static fn ( array $c ): bool => 'native' === $c['compatibility'] && in_array( $c['source_kind'], array( 'elementor', 'mixed' ), true ), $used );
		$slots['elementor_or_mixed_complex'] = self::pick_complex( $candidates, static fn ( array $c ): bool => 'mixed' === $c['source_kind'] || ( 'native' === $c['compatibility'] && 'elementor' === $c['source_kind'] ), $used );
		$slots['legacy_typical'] = self::pick_typical( $candidates, static fn ( array $c ): bool => 'legacy_html' === $c['source_kind'], $used );
		$slots['legacy_complex'] = self::pick_complex( $candidates, static fn ( array $c ): bool => 'legacy_html' === $c['source_kind'], $used );
		$slots['gutenberg'] = self::pick_complex( $candidates, static fn ( array $c ): bool => 'gutenberg' === $c['source_kind'] || in_array( 'gutenberg', $c['strategies'], true ), $used );
		$slots['shortcode_or_table'] = self::pick_complex( $candidates, static fn ( array $c ): bool => $c['structure']['shortcodes'] > 0 || $c['structure']['tables'] > 0 || $c['has_shortcode_warning'], $used );
		$slots['review_required'] = self::pick_complex( $candidates, static fn ( array $c ): bool => 'review_required' === $c['compatibility'], $used );
		$slots['empty_or_corrupt'] = self::pick_complex( $candidates, static fn ( array $c ): bool => 'empty' === $c['source_kind'] || $c['has_corrupt_warning'], $used );

		return array( 'slots' => $slots, 'errors' => $errors, 'corpus_count' => count( $ids ) );
	}

	/** @param array<string,mixed> $document @param array<string,mixed> $source @return array<string,mixed> */
	private static function candidate( int $post_id, array $document, array $source ): array {
		$structure = is_array( $document['structure'] ?? null ) ? $document['structure'] : array();
		$warnings  = is_array( $document['extraction']['warnings'] ?? null ) ? array_values( array_map( 'strval', $document['extraction']['warnings'] ) ) : array();
		$sections  = is_array( $document['sections'] ?? null ) ? $document['sections'] : array();
		$score = count( $sections )
			+ 3 * (int) ( $structure['headings'] ?? 0 )
			+ 3 * (int) ( $structure['lists'] ?? 0 )
			+ 5 * (int) ( $structure['tables'] ?? 0 )
			+ (int) ( $structure['images'] ?? 0 )
			+ (int) ( $structure['links'] ?? 0 )
			+ 2 * (int) ( $structure['code_blocks'] ?? 0 )
			+ 2 * (int) ( $structure['shortcodes'] ?? 0 )
			+ 4 * count( $warnings );

		return array(
			'post_id' => $post_id,
			'document' => $document,
			'source' => $source,
			'source_kind' => (string) ( $document['source_kind'] ?? 'empty' ),
			'compatibility' => (string) ( $document['extraction']['elementor_compatibility']['status'] ?? 'review_required' ),
			'strategies' => is_array( $document['extraction']['strategies'] ?? null ) ? array_values( array_map( 'strval', $document['extraction']['strategies'] ) ) : array(),
			'structure' => array(
				'headings' => (int) ( $structure['headings'] ?? 0 ),
				'lists' => (int) ( $structure['lists'] ?? 0 ),
				'tables' => (int) ( $structure['tables'] ?? 0 ),
				'images' => (int) ( $structure['images'] ?? 0 ),
				'links' => (int) ( $structure['links'] ?? 0 ),
				'code_blocks' => (int) ( $structure['code_blocks'] ?? 0 ),
				'shortcodes' => (int) ( $structure['shortcodes'] ?? 0 ),
			),
			'sections_count' => count( $sections ),
			'score' => $score,
			'has_shortcode_warning' => self::has_warning_prefix( $warnings, 'SHORTCODE_NOT_EXPANDED:' ),
			'has_corrupt_warning' => in_array( 'ELEMENTOR_JSON_INVALID', $warnings, true ) || in_array( 'SOURCE_EMPTY', $warnings, true ),
		);
	}

	/** @param array<int,array<string,mixed>> $candidates @param callable(array<string,mixed>):bool $filter @param array<int,bool> $used @return array<string,mixed> */
	private static function pick_typical( array $candidates, callable $filter, array &$used ): array {
		$pool = self::filtered_pool( $candidates, $filter, $used );
		if ( empty( $pool ) ) { return array( 'available' => false ); }
		$counts = array_map( static fn ( array $c ): int => (int) $c['sections_count'], $pool );
		sort( $counts, SORT_NUMERIC );
		$median = $counts[ (int) floor( ( count( $counts ) - 1 ) / 2 ) ];
		usort( $pool, static function ( array $a, array $b ) use ( $median ): int {
			$da = abs( (int) $a['sections_count'] - $median );
			$db = abs( (int) $b['sections_count'] - $median );
			return $da === $db ? ( (int) $a['post_id'] <=> (int) $b['post_id'] ) : ( $da <=> $db );
		} );
		$selected = $pool[0];
		$used[ (int) $selected['post_id'] ] = true;
		$selected['available'] = true;
		return $selected;
	}

	/** @param array<int,array<string,mixed>> $candidates @param callable(array<string,mixed>):bool $filter @param array<int,bool> $used @return array<string,mixed> */
	private static function pick_complex( array $candidates, callable $filter, array &$used ): array {
		$pool = self::filtered_pool( $candidates, $filter, $used );
		if ( empty( $pool ) ) { return array( 'available' => false ); }
		usort( $pool, static fn ( array $a, array $b ): int => (int) $a['score'] === (int) $b['score'] ? ( (int) $a['post_id'] <=> (int) $b['post_id'] ) : ( (int) $b['score'] <=> (int) $a['score'] ) );
		$selected = $pool[0];
		$used[ (int) $selected['post_id'] ] = true;
		$selected['available'] = true;
		return $selected;
	}

	/** @param array<int,array<string,mixed>> $candidates @param callable(array<string,mixed>):bool $filter @param array<int,bool> $used @return array<int,array<string,mixed>> */
	private static function filtered_pool( array $candidates, callable $filter, array $used ): array {
		$eligible = array_values( array_filter( $candidates, $filter ) );
		$unused = array_values( array_filter( $eligible, static fn ( array $c ): bool => ! isset( $used[ (int) $c['post_id'] ] ) ) );
		return ! empty( $unused ) ? $unused : $eligible;
	}

	/** @param array<string,mixed> $source */
	private static function render_source( array $source ): void {
		$flags = is_array( $source['flags'] ?? null ) ? $source['flags'] : array();
		echo '<p><strong>flags:</strong> <code>' . esc_html( self::display_json( $flags ) ) . '</code></p>';
		$raw_elementor = $source['elementor_raw'] ?? '';
		if ( ( $flags['has_elementor_meta'] ?? false ) ) {
			echo '<details open><summary><strong>_elementor_data (somente leitura)</strong></summary>';
			echo '<pre style="max-height:440px;overflow:auto;white-space:pre-wrap;border:1px solid #ddd;padding:10px;background:#fff">' . esc_html( self::pretty_source( $raw_elementor ) ) . '</pre></details>';
		}
		$content = isset( $source['post_content'] ) ? (string) $source['post_content'] : '';
		if ( '' !== $content ) {
			echo '<details open><summary><strong>post_content (somente leitura)</strong></summary>';
			echo '<pre style="max-height:440px;overflow:auto;white-space:pre-wrap;border:1px solid #ddd;padding:10px;background:#fff">' . esc_html( $content ) . '</pre></details>';
		}
		if ( '' === $content && empty( $raw_elementor ) ) { echo '<p><em>' . esc_html__( 'Fonte editorial vazia.', 'bdc-knowledge-base' ) . '</em></p>'; }
	}

	/** @param array<string,mixed> $document */
	private static function render_document( array $document ): void {
		echo '<p><strong>source_hash:</strong> <code>' . esc_html( (string) ( $document['source_hash'] ?? '' ) ) . '</code></p>';
		echo '<p><strong>document_hash:</strong> <code>' . esc_html( (string) ( $document['document_hash'] ?? '' ) ) . '</code></p>';
		echo '<p><strong>structure:</strong> <code>' . esc_html( self::display_json( $document['structure'] ?? array() ) ) . '</code></p>';
		echo '<p><strong>warnings:</strong> <code>' . esc_html( self::display_json( $document['extraction']['warnings'] ?? array() ) ) . '</code></p>';
		echo '<div style="max-height:720px;overflow:auto;border:1px solid #ddd;padding:10px;background:#fff">';
		foreach ( (array) ( $document['sections'] ?? array() ) as $section ) {
			if ( ! is_array( $section ) ) { continue; }
			echo '<div style="margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #eee">';
			echo '<small>#' . esc_html( (string) ( $section['ordinal'] ?? '' ) ) . ' · ' . esc_html( (string) ( $section['kind'] ?? '' ) ) . '</small>';
			echo '<div style="white-space:pre-wrap">' . esc_html( (string) ( $section['text'] ?? '' ) ) . '</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	/** @return array<string,mixed> */
	private static function build_report(): array {
		$sample_status = isset( $_POST['sample_status'] ) && is_array( $_POST['sample_status'] ) ? wp_unslash( $_POST['sample_status'] ) : array();
		$sample_ids = isset( $_POST['sample_id'] ) && is_array( $_POST['sample_id'] ) ? wp_unslash( $_POST['sample_id'] ) : array();
		$expected_fingerprints = isset( $_POST['sample_fingerprint'] ) && is_array( $_POST['sample_fingerprint'] ) ? wp_unslash( $_POST['sample_fingerprint'] ) : array();
		$posted_verdicts = isset( $_POST['verdict'] ) && is_array( $_POST['verdict'] ) ? wp_unslash( $_POST['verdict'] ) : array();
		$posted_reasons = isset( $_POST['reason'] ) && is_array( $_POST['reason'] ) ? wp_unslash( $_POST['reason'] ) : array();

		$selected_ids = array();
		foreach ( self::SLOTS as $slot => $label ) {
			unset( $label );
			if ( 'selected' === (string) ( $sample_status[ $slot ] ?? '' ) ) {
				$post_id = (int) ( $sample_ids[ $slot ] ?? 0 );
				if ( $post_id > 0 ) { $selected_ids[] = $post_id; }
			}
		}
		$selected_ids = array_values( array_unique( $selected_ids ) );
		$snapshot_before = self::snapshot( $selected_ids );
		$fingerprint_before = self::aggregate_fingerprint( $snapshot_before );
		$items = array();
		$available_count = 0;
		$passed_count = 0;
		$stale_count = 0;
		$repeatability_failures = 0;

		foreach ( self::SLOTS as $slot => $label ) {
			unset( $label );
			$status = (string) ( $sample_status[ $slot ] ?? 'not_available' );
			if ( 'selected' !== $status ) {
				$items[] = array( 'slot' => $slot, 'status' => 'not_available' );
				continue;
			}
			++$available_count;
			$post_id = (int) ( $sample_ids[ $slot ] ?? 0 );
			$expected = isset( $expected_fingerprints[ $slot ] ) && is_scalar( $expected_fingerprints[ $slot ] ) ? (string) $expected_fingerprints[ $slot ] : '';
			$current = self::editorial_fingerprint( $post_id );
			$stale = '' === $expected || ! hash_equals( $expected, $current );
			if ( $stale ) { ++$stale_count; }
			$doc_a = Knowledge_Document::build( $post_id );
			$doc_b = Knowledge_Document::build( $post_id );
			$repeatable = ! is_wp_error( $doc_a ) && ! is_wp_error( $doc_b ) && (string) ( $doc_a['source_hash'] ?? '' ) === (string) ( $doc_b['source_hash'] ?? '' ) && (string) ( $doc_a['document_hash'] ?? '' ) === (string) ( $doc_b['document_hash'] ?? '' );
			if ( ! $repeatable ) { ++$repeatability_failures; }
			$verdict = array();
			$all_true = true;
			foreach ( self::VERDICT_FIELDS as $field => $field_label ) {
				unset( $field_label );
				$value = isset( $posted_verdicts[ $slot ][ $field ] ) && '1' === (string) $posted_verdicts[ $slot ][ $field ];
				$verdict[ $field ] = $value;
				$all_true = $all_true && $value;
			}
			$reason = isset( $posted_reasons[ $slot ] ) && is_scalar( $posted_reasons[ $slot ] ) ? (string) $posted_reasons[ $slot ] : '';
			if ( ! in_array( $reason, self::REASONS, true ) ) { $reason = ''; }
			if ( ! $all_true && '' === $reason ) { $reason = 'other_review_required'; }
			$pass = ! $stale && $repeatable && $all_true;
			if ( $pass ) { ++$passed_count; }
			$items[] = array(
				'slot' => $slot,
				'status' => 'reviewed',
				'post_id' => $post_id,
				'source_kind' => ! is_wp_error( $doc_a ) ? (string) ( $doc_a['source_kind'] ?? '' ) : 'error',
				'elementor_compatibility' => ! is_wp_error( $doc_a ) ? (string) ( $doc_a['extraction']['elementor_compatibility']['status'] ?? '' ) : 'error',
				'source_hash' => ! is_wp_error( $doc_a ) ? (string) ( $doc_a['source_hash'] ?? '' ) : '',
				'document_hash' => ! is_wp_error( $doc_a ) ? (string) ( $doc_a['document_hash'] ?? '' ) : '',
				'expected_editorial_fingerprint' => $expected,
				'current_editorial_fingerprint' => $current,
				'stale' => $stale,
				'repeatable' => $repeatable,
				'verdict' => $verdict,
				'reason' => $reason,
				'pass' => $pass,
			);
		}

		$snapshot_after = self::snapshot( $selected_ids );
		$fingerprint_after = self::aggregate_fingerprint( $snapshot_after );
		$changed = self::changed_snapshot_count( $snapshot_before, $snapshot_after );
		$gate_pass = $available_count > 0 && $passed_count === $available_count && 0 === $stale_count && 0 === $repeatability_failures && 0 === $changed;

		return array(
			'schema_version' => '1.0.0',
			'mode' => 'temporary_spec004_g240_real_content_acceptance',
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
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => hash_equals( $fingerprint_before, $fingerprint_after ),
				'changed_posts_during_report_generation' => $changed,
			),
			'acceptance' => array(
				'available_slots' => $available_count,
				'passed_slots' => $passed_count,
				'stale_slots' => $stale_count,
				'repeatability_failures' => $repeatability_failures,
				'gate_pass' => $gate_pass,
				'items' => $items,
			),
			'gate_expectations' => array(
				'all_available_slots_pass' => true,
				'stale_slots' => 0,
				'repeatability_failures' => 0,
				'editorial_fingerprint_equal' => true,
				'changed_posts_during_report_generation' => 0,
			),
		);
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts( array( 'post_type' => 'post', 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'ID', 'order' => 'ASC', 'no_found_rows' => true, 'suppress_filters' => false ) );
		return is_array( $ids ) ? array_values( array_map( 'intval', $ids ) ) : array();
	}

	private static function editorial_fingerprint( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) { return ''; }
		$elementor = get_post_meta( $post_id, '_elementor_data', true );
		$elementor_string = is_string( $elementor ) ? $elementor : self::display_json( $elementor );
		return hash( 'sha256', implode( "\n", array( (string) $post_id, (string) ( $post->post_status ?? '' ), (string) ( $post->post_modified_gmt ?? '' ), hash( 'sha256', (string) ( $post->post_title ?? '' ) ), hash( 'sha256', (string) ( $post->post_content ?? '' ) ), hash( 'sha256', $elementor_string ) ) ) );
	}

	/** @param array<int,int> $ids @return array<int,string> */
	private static function snapshot( array $ids ): array {
		$out = array();
		foreach ( $ids as $post_id ) { $out[ $post_id ] = self::editorial_fingerprint( $post_id ); }
		ksort( $out, SORT_NUMERIC );
		return $out;
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

	/** @param array<int,string> $warnings */
	private static function has_warning_prefix( array $warnings, string $prefix ): bool {
		foreach ( $warnings as $warning ) { if ( str_starts_with( $warning, $prefix ) ) { return true; } }
		return false;
	}

	private static function pretty_source( mixed $value ): string {
		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			if ( is_array( $decoded ) ) { return self::display_json( $decoded, true ); }
			return $value;
		}
		return self::display_json( $value, true );
	}

	private static function display_json( mixed $value, bool $pretty = false ): string {
		$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
		if ( $pretty ) { $flags |= JSON_PRETTY_PRINT; }
		$json = function_exists( 'wp_json_encode' ) ? wp_json_encode( $value, $flags ) : json_encode( $value, $flags );
		return is_string( $json ) ? $json : '';
	}
}
