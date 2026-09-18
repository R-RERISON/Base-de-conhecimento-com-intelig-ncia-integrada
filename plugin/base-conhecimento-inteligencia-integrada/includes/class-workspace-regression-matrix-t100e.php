<?php
/**
 * T100E-E6 — Workspace Regression Matrix.
 *
 * Hidden, read-only engineering runner. No menu item is exposed in the product.
 *
 * @package BDC_Knowledge_Base
 */
namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class Workspace_Regression_Matrix_T100E {
	public const SCHEMA_VERSION = '1.0.0';
	public const PAGE_SLUG = 'bdc-kb-t100e-e6';
	public const ACTION = 'bdc_kb_t100e_e6_workspace_regression';
	public const NONCE_FIELD = 'bdc_kb_t100e_e6_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_hidden_page' ), 99 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_hidden_page(): void {
		add_submenu_page(
			null,
			'T100E-E6',
			'T100E-E6',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}

		echo '<div class="wrap">';
		echo '<h1>Matriz de regressão da área de gerenciamento</h1>';
		echo '<p>Validação técnica temporária e somente leitura. Nenhum artigo será alterado.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::ACTION, self::NONCE_FIELD );
		submit_button( 'Executar matriz e baixar relatório JSON', 'primary', 'submit', false );
		echo '</form>';
		echo '</div>';
	}

	public static function handle_run(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método não permitido.', '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) && is_scalar( $_POST[ self::NONCE_FIELD ] )
			? wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) : '';
		if ( ! wp_verify_nonce( $nonce, self::ACTION ) ) {
			wp_die( 'Nonce inválido.', '', array( 'response' => 403 ) );
		}

		@set_time_limit( 0 );
		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha ao gerar relatório JSON.', '', array( 'response' => 500 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t100e-e6-workspace-regression-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json;
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$post_ids = get_posts(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => true,
			)
		);
		$post_ids = array_values( array_map( 'intval', is_array( $post_ids ) ? $post_ids : array() ) );

		$fingerprint_before = self::corpus_fingerprint( $post_ids );
		$first = self::scan( $post_ids );
		$second = self::scan( $post_ids );
		$fingerprint_after = self::corpus_fingerprint( $post_ids );
		$pure = self::pure_contract_cases();

		$first_hash = self::matrix_hash( $first );
		$second_hash = self::matrix_hash( $second );
		$coverage = self::coverage( $first, $pure );

		$pass = 0 === (int) ( $first['errors_total'] ?? 0 )
			&& 0 === (int) ( $second['errors_total'] ?? 0 )
			&& 0 === (int) ( $first['throwables_total'] ?? 0 )
			&& 0 === (int) ( $second['throwables_total'] ?? 0 )
			&& 0 === (int) ( $first['safety_violations'] ?? 0 )
			&& 0 === (int) ( $second['safety_violations'] ?? 0 )
			&& '' !== $first_hash
			&& hash_equals( $first_hash, $second_hash )
			&& '' !== $fingerprint_before
			&& hash_equals( $fingerprint_before, $fingerprint_after )
			&& true === ( $coverage['pass'] ?? false )
			&& true === ( $pure['pass'] ?? false );

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'gate' => 'T100E-E6',
			'mode' => 'workspace_regression_matrix_read_only',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
			),
			'corpus' => array(
				'total_posts' => count( $post_ids ),
				'first_pass_processed' => (int) ( $first['processed'] ?? 0 ),
				'second_pass_processed' => (int) ( $second['processed'] ?? 0 ),
			),
			'first_pass' => $first,
			'second_pass' => $second,
			'determinism' => array(
				'first_matrix_hash' => $first_hash,
				'second_matrix_hash' => $second_hash,
				'matrix_hash_equal' => '' !== $first_hash && hash_equals( $first_hash, $second_hash ),
			),
			'editorial_integrity' => array(
				'fingerprint_before' => $fingerprint_before,
				'fingerprint_after' => $fingerprint_after,
				'equal' => '' !== $fingerprint_before && hash_equals( $fingerprint_before, $fingerprint_after ),
			),
			'pure_contract_cases' => $pure,
			'coverage' => $coverage,
			'safety' => array(
				'read_only_design' => true,
				'persists_state' => false,
				'acquires_lock' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'writes_journal' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
				'renders_blocks' => false,
				'exports_editorial_body' => false,
				'exports_post_ids' => false,
				'exports_urls' => false,
			),
			'gate_result' => array(
				't100e_e6_workspace_regression_pass' => $pass,
			),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	/** @return array<string,mixed> */
	private static function scan( array $post_ids ): array {
		$out = array(
			'processed' => 0,
			'errors_total' => 0,
			'error_codes' => array(),
			'throwables_total' => 0,
			'throwable_signatures' => array(),
			'safety_violations' => 0,
			'source_kind_counts' => array(),
			'dry_run_status_counts' => array(),
			'operational_status_counts' => array(),
			'lock_status_counts' => array(),
			'journal_state_counts' => array(),
			'journal_presence_counts' => array( 'none' => 0, 'present' => 0 ),
			'authorization_ready_counts' => array( 'true' => 0, 'false' => 0 ),
			'source_x_operational' => array(),
		);

		foreach ( $post_ids as $post_id ) {
			try {
				$assessment = Post_Core_Blocks_Activity::assess( (int) $post_id );
				if ( $assessment instanceof \WP_Error ) {
					++$out['errors_total'];
					self::inc( $out['error_codes'], $assessment->get_error_code() );
					continue;
				}

				++$out['processed'];
				$source = (string) ( $assessment['source_kind'] ?? 'unknown' );
				$dry = (string) ( $assessment['dry_run_status'] ?? 'unknown' );
				$operational = (string) ( $assessment['operational_status'] ?? 'unknown' );
				$lock = (string) ( $assessment['lock_status'] ?? 'unknown' );
				$journal_count = (int) ( $assessment['journal_event_count'] ?? 0 );
				$journal_state = $journal_count > 0
					? (string) ( $assessment['latest_journal_state'] ?? 'unknown' )
					: 'none';
				$authorization_ready = true === ( $assessment['authorization_ready'] ?? false );

				self::inc( $out['source_kind_counts'], $source );
				self::inc( $out['dry_run_status_counts'], $dry );
				self::inc( $out['operational_status_counts'], $operational );
				self::inc( $out['lock_status_counts'], $lock );
				self::inc( $out['journal_state_counts'], $journal_state );
				self::inc( $out['journal_presence_counts'], $journal_count > 0 ? 'present' : 'none' );
				self::inc( $out['authorization_ready_counts'], $authorization_ready ? 'true' : 'false' );
				self::inc( $out['source_x_operational'], $source . '|' . $operational );

				$safety = is_array( $assessment['safety'] ?? null ) ? $assessment['safety'] : array();
				foreach ( array(
					'persists_state',
					'acquires_lock',
					'writes_post_content',
					'writes_elementor_data',
					'calls_external_network',
					'executes_shortcodes',
					'renders_blocks',
					'exports_editorial_body',
					'exports_urls',
				) as $key ) {
					if ( true === ( $safety[ $key ] ?? false ) ) {
						++$out['safety_violations'];
					}
				}
			} catch ( \Throwable $error ) {
				++$out['throwables_total'];
				$signature = get_class( $error ) . ':' . substr( hash( 'sha256', $error->getMessage() ), 0, 16 );
				self::inc( $out['throwable_signatures'], $signature );
			}
		}

		foreach ( array(
			'error_codes',
			'throwable_signatures',
			'source_kind_counts',
			'dry_run_status_counts',
			'operational_status_counts',
			'lock_status_counts',
			'journal_state_counts',
			'journal_presence_counts',
			'authorization_ready_counts',
			'source_x_operational',
		) as $field ) {
			ksort( $out[ $field ], SORT_STRING );
		}
		return $out;
	}

	/** @return array<string,mixed> */
	private static function pure_contract_cases(): array {
		$cases = array();

		$ready = Post_Core_Blocks_Activity::operational_state( 'ready', 'legacy_html', 'free', 0, '' );
		$cases['ready_without_journal'] = array(
			'pass' => 'ready_for_authorization' === (string) ( $ready['status'] ?? '' )
				&& true === ( $ready['authorization_ready'] ?? false ),
			'observed' => (string) ( $ready['status'] ?? '' ),
		);

		$held = Post_Core_Blocks_Activity::operational_state( 'ready', 'legacy_html', 'held', 0, '' );
		$cases['lock_held_blocks'] = array(
			'pass' => 'blocked' === (string) ( $held['status'] ?? '' )
				&& false === ( $held['authorization_ready'] ?? true ),
			'observed' => (string) ( $held['status'] ?? '' ),
		);

		$terminal = Post_Core_Blocks_Activity::operational_state(
			'ready',
			'legacy_html',
			'free',
			1,
			Block_Migration_Journal::STATE_ROLLED_BACK
		);
		$cases['rolled_back_terminal_allows_reassessment'] = array(
			'pass' => 'ready_for_authorization' === (string) ( $terminal['status'] ?? '' )
				&& in_array( 'TERMINAL_ROLLBACK_HISTORY_PRESENT', (array) ( $terminal['reasons'] ?? array() ), true ),
			'observed' => (string) ( $terminal['status'] ?? '' ),
		);

		$applied = Post_Core_Blocks_Activity::operational_state(
			'ready',
			'legacy_html',
			'free',
			1,
			Block_Migration_Journal::STATE_APPLIED
		);
		$cases['applied_journal_blocks_legacy_rewrite'] = array(
			'pass' => 'blocked' === (string) ( $applied['status'] ?? '' ),
			'observed' => (string) ( $applied['status'] ?? '' ),
		);

		$mixed = Post_Core_Blocks_Activity::operational_state( 'ready', 'mixed', 'free', 0, '' );
		$cases['mixed_requires_human'] = array(
			'pass' => 'human_review_required' === (string) ( $mixed['status'] ?? '' ),
			'observed' => (string) ( $mixed['status'] ?? '' ),
		);

		$review = Post_Core_Blocks_Activity::operational_state( 'review_required', 'elementor', 'free', 0, '' );
		$cases['review_required_requires_human'] = array(
			'pass' => 'human_review_required' === (string) ( $review['status'] ?? '' ),
			'observed' => (string) ( $review['status'] ?? '' ),
		);

		$noop = Post_Core_Blocks_Activity::operational_state( 'noop', 'gutenberg', 'free', 1, Block_Migration_Journal::STATE_APPLIED );
		$cases['native_blocks_no_action'] = array(
			'pass' => 'no_action_required' === (string) ( $noop['status'] ?? '' ),
			'observed' => (string) ( $noop['status'] ?? '' ),
		);

		$planned = self::stale_fixture( 'legacy_html', 'a', 'b', 'c' );
		$fresh = Block_Migration_Stale_Source_Guard::assess( $planned, $planned );
		$current = self::stale_fixture( 'legacy_html', 'd', 'b', 'e' );
		$stale = Block_Migration_Stale_Source_Guard::assess( $planned, $current );
		$cases['stale_guard_fresh'] = array(
			'pass' => 'fresh' === (string) ( $fresh['status'] ?? '' ),
			'observed' => (string) ( $fresh['status'] ?? '' ),
		);
		$cases['stale_guard_detects_drift'] = array(
			'pass' => 'stale' === (string) ( $stale['status'] ?? '' )
				&& in_array( 'FIDELITY_HASH_CHANGED', (array) ( $stale['reasons'] ?? array() ), true )
				&& in_array( 'SOURCE_MATERIAL_CHANGED:elementor_data_sha256', (array) ( $stale['reasons'] ?? array() ), true ),
			'observed' => (string) ( $stale['status'] ?? '' ),
		);

		$pass = true;
		foreach ( $cases as $case ) {
			if ( true !== ( $case['pass'] ?? false ) ) {
				$pass = false;
				break;
			}
		}
		return array(
			'cases' => $cases,
			'passed' => count( array_filter( $cases, static fn( array $case ): bool => true === ( $case['pass'] ?? false ) ) ),
			'total' => count( $cases ),
			'pass' => $pass,
		);
	}

	/** @return array<string,mixed> */
	private static function coverage( array $scan, array $pure ): array {
		$source_counts = (array) ( $scan['source_kind_counts'] ?? array() );
		$operational = (array) ( $scan['operational_status_counts'] ?? array() );
		$journals = (array) ( $scan['journal_presence_counts'] ?? array() );
		$journal_states = (array) ( $scan['journal_state_counts'] ?? array() );
		$locks = (array) ( $scan['lock_status_counts'] ?? array() );

		$required_sources = array( 'gutenberg', 'legacy_html', 'plain_text', 'elementor', 'mixed' );
		$source_coverage = array();
		foreach ( $required_sources as $kind ) {
			$source_coverage[ $kind ] = (int) ( $source_counts[ $kind ] ?? 0 ) > 0;
		}

		$checks = array(
			'required_source_kinds' => ! in_array( false, $source_coverage, true ),
			'journal_terminal_observed' => (int) ( $journal_states[ Block_Migration_Journal::STATE_APPLIED ] ?? 0 )
				+ (int) ( $journal_states[ Block_Migration_Journal::STATE_ROLLED_BACK ] ?? 0 ) > 0,
			'no_journal_observed' => (int) ( $journals['none'] ?? 0 ) > 0,
			'free_lock_observed' => (int) ( $locks['free'] ?? 0 ) > 0,
			'review_required_observed' => (int) ( $operational['human_review_required'] ?? 0 ) > 0,
			'no_action_observed' => (int) ( $operational['no_action_required'] ?? 0 ) > 0,
			'ready_for_authorization_observed' => (int) ( $operational['ready_for_authorization'] ?? 0 ) > 0,
			'held_lock_contract_case' => true === ( $pure['cases']['lock_held_blocks']['pass'] ?? false ),
			'source_drift_contract_case' => true === ( $pure['cases']['stale_guard_detects_drift']['pass'] ?? false ),
		);
		return array(
			'source_kinds' => $source_coverage,
			'checks' => $checks,
			'pass' => ! in_array( false, $checks, true ),
		);
	}

	/** @return array<string,string> */
	private static function stale_fixture( string $kind, string $fidelity_seed, string $post_seed, string $elementor_seed ): array {
		return array(
			'source_kind' => $kind,
			'fidelity_hash' => hash( 'sha256', $fidelity_seed ),
			'source_material' => array(
				'post_content_sha256' => hash( 'sha256', $post_seed ),
				'elementor_data_sha256' => hash( 'sha256', $elementor_seed ),
			),
		);
	}

	private static function corpus_fingerprint( array $post_ids ): string {
		$rows = array();
		foreach ( $post_ids as $post_id ) {
			$post = get_post( (int) $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$elementor = get_post_meta( (int) $post_id, '_elementor_data', true );
			$journals = get_post_meta( (int) $post_id, Block_Migration_Journal_Store::META_KEY, false );
			$lock = get_post_meta( (int) $post_id, Block_Migration_Lock::META_KEY, true );

			$rows[] = implode(
				'|',
				array(
					(int) $post_id,
					hash( 'sha256', (string) ( $post->post_content ?? '' ) ),
					hash( 'sha256', self::stable_json_or_string( $elementor ) ),
					hash( 'sha256', self::stable_json_or_string( $journals ) ),
					hash( 'sha256', self::stable_json_or_string( $lock ) ),
				)
			);
		}
		return hash( 'sha256', implode( "\n", $rows ) );
	}

	private static function matrix_hash( array $scan ): string {
		$copy = $scan;
		unset( $copy['throwable_signatures'] );
		try {
			return Canonical_JSON::hash( $copy );
		} catch ( \JsonException ) {
			return '';
		}
	}

	private static function stable_json_or_string( mixed $value ): string {
		if ( is_string( $value ) ) {
			return $value;
		}
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}

	private static function inc( array &$bucket, string $key ): void {
		$key = '' !== $key ? $key : 'unknown';
		$bucket[ $key ] = (int) ( $bucket[ $key ] ?? 0 ) + 1;
	}
}
