<?php
/**
 * SPEC-005 G-560 — automated Search UX contract validator.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_UX_Runner_G560 {

	public const ACTION = 'bdc_kb_spec005_g560_search_ux';
	public const PAGE_SLUG = 'bdc-kb-spec005-g560-search-ux';

	private const NONCE_ACTION = 'bdc_kb_spec005_g560_search_ux';
	private const NONCE_FIELD = 'bdc_kb_spec005_g560_search_ux_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 46 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Search UX G-560',
			'Search UX G-560',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SPEC-005 — G-560 Search UX', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p>';
		echo esc_html__( 'Valida automaticamente estados de busca, acessibilidade estrutural, responsividade declarada e segurança editorial. A revisão visual humana permanece um subgate separado.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar validação G-560 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
		echo '</form>';
		echo '</div>';
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-g560-search-ux-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	public static function run(): array {
		$started = microtime( true );
		$checks = array();
		$errors = array();

		$admin_path = BDC_KB_DIR . 'includes/class-admin-page.php';
		$css_path = BDC_KB_DIR . 'assets/css/visual-foundation.css';
		$admin_source = is_readable( $admin_path ) ? (string) file_get_contents( $admin_path ) : '';
		$css_source = is_readable( $css_path ) ? (string) file_get_contents( $css_path ) : '';

		$source_checks = array(
			'search_service_integration' => str_contains( $admin_source, 'Search_Service::search' ),
			'role_search' => str_contains( $admin_source, 'role="search"' ),
			'visible_search_label' => str_contains( $admin_source, 'bdc-kb-search-label' ),
			'aria_describedby' => str_contains( $admin_source, 'aria-describedby="bdc-kb-search-help"' ),
			'aria_live_feedback' => str_contains( $admin_source, 'aria-live="polite"' ),
			'zero_results_copy' => str_contains( $admin_source, "'zero_results' === \$state" ),
			'invalid_query_copy' => str_contains( $admin_source, "'invalid_query' === \$state" ),
			'technical_error_copy' => str_contains( $admin_source, "'technical_error' === \$state" ),
			'degraded_copy' => str_contains( $admin_source, "'degraded' === \$state" ),
			'relevance_metadata' => str_contains( $admin_source, "Relevância #%d" ),
			'css_search_feedback' => str_contains( $css_source, '.bdc-kb-search-feedback' ),
			'css_focus_visible_foundation' => str_contains( $css_source, ':focus-visible' ),
			'breakpoint_782' => str_contains( $css_source, '@media (max-width: 782px)' ),
			'breakpoint_520' => str_contains( $css_source, '@media (max-width: 520px)' ),
			'mobile_search_actions' => str_contains( $css_source, '.bdc-kb-search-actions { flex-direction: column; }' ),
		);

		foreach ( $source_checks as $id => $pass ) {
			$checks[] = array(
				'id' => $id,
				'category' => 'source_contract',
				'pass' => (bool) $pass,
			);
		}

		$projection_ready = Search_Projection_Repository::is_ready();
		$checks[] = array(
			'id' => 'projection_ready',
			'category' => 'runtime',
			'pass' => $projection_ready,
		);

		$before = self::editorial_fingerprint();

		$live_cases = array(
			array(
				'id' => 'success_windows_11',
				'query' => 'Windows 11',
				'expected_state' => 'success',
				'expected_mode' => 'projection_like',
				'expected_post_id' => 583,
				'max_rank' => 3,
			),
			array(
				'id' => 'summary_semantic_case',
				'query' => 'essencialmente',
				'expected_state' => 'success',
				'expected_mode' => 'projection_like',
				'expected_post_id' => 45070,
				'max_rank' => 3,
			),
			array(
				'id' => 'zero_results_distinct',
				'query' => 'bdczzzznomatch20260919',
				'expected_state' => 'zero_results',
				'expected_mode' => 'projection_like',
				'expected_post_id' => 0,
				'max_rank' => 0,
			),
			array(
				'id' => 'invalid_query_distinct',
				'query' => '!!!',
				'expected_state' => 'invalid_query',
				'expected_mode' => 'none',
				'expected_post_id' => 0,
				'max_rank' => 0,
			),
		);

		$live_results = array();
		foreach ( $live_cases as $case ) {
			try {
				$response = Search_Service::search( $case['query'], 20 );
				$rank = self::rank_of( $response, (int) $case['expected_post_id'] );
				$pass = (string) ( $response['state'] ?? '' ) === $case['expected_state']
					&& (string) ( $response['retrieval_mode'] ?? '' ) === $case['expected_mode'];

				if ( (int) $case['expected_post_id'] > 0 ) {
					$pass = $pass && $rank > 0 && $rank <= (int) $case['max_rank'];
				} else {
					$pass = $pass && 0 === $rank;
				}

				$live_results[] = array(
					'id' => $case['id'],
					'query' => $case['query'],
					'expected_state' => $case['expected_state'],
					'actual_state' => (string) ( $response['state'] ?? '' ),
					'expected_mode' => $case['expected_mode'],
					'actual_mode' => (string) ( $response['retrieval_mode'] ?? '' ),
					'expected_post_id' => (int) $case['expected_post_id'],
					'actual_rank' => $rank,
					'pass' => $pass,
				);
			} catch ( \Throwable $error ) {
				$live_results[] = array(
					'id' => $case['id'],
					'query' => $case['query'],
					'pass' => false,
					'throwable' => get_class( $error ),
					'message' => $error->getMessage(),
				);
				$errors[] = array(
					'code' => 'live_case_throwable',
					'case' => $case['id'],
					'message' => $error->getMessage(),
				);
			}
		}

		foreach ( $live_results as $row ) {
			$checks[] = array(
				'id' => (string) $row['id'],
				'category' => 'live_search_state',
				'pass' => ! empty( $row['pass'] ),
			);
		}

		$after = self::editorial_fingerprint();
		$editorial_equal = '' !== $before && hash_equals( $before, $after );
		$checks[] = array(
			'id' => 'editorial_fingerprint_equal',
			'category' => 'safety',
			'pass' => $editorial_equal,
		);

		$source_pass = self::category_pass( $checks, 'source_contract' );
		$runtime_pass = self::category_pass( $checks, 'runtime' );
		$states_pass = self::category_pass( $checks, 'live_search_state' );
		$safety_pass = self::category_pass( $checks, 'safety' );
		$technical_ready = $source_pass && $runtime_pass && $states_pass && $safety_pass && empty( $errors );

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'G-560',
			'mode' => 'spec005_search_ux_automated_validation',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
			),
			'visual_contract' => array(
				'reference' => 'ux/002-mockup-visual-foundation/visual-contract-v2.md',
				'direct_search_mockup_exists' => false,
				'inherits_surface' => 'Knowledge List / UX-002.3',
				'breakpoints' => array( 782, 520 ),
			),
			'checks' => $checks,
			'live_results' => $live_results,
			'safety' => array(
				'editorial_fingerprint_before' => $before,
				'editorial_fingerprint_after' => $after,
				'editorial_fingerprint_equal' => $editorial_equal,
				'persists_query_log' => false,
				'calls_external_network' => false,
				'depends_on_asi' => false,
			),
			'errors' => $errors,
			'performance' => array(
				'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_result' => array(
				't560_visual_contract_source_pass' => $source_pass,
				't561_responsive_contract_source_pass' => $source_checks['breakpoint_782'] && $source_checks['breakpoint_520'] && $source_checks['mobile_search_actions'],
				't562_accessibility_contract_source_pass' => $source_checks['role_search'] && $source_checks['visible_search_label'] && $source_checks['aria_describedby'] && $source_checks['aria_live_feedback'],
				't563_state_semantics_live_pass' => $states_pass,
				't564_human_visual_acceptance' => 'NOT_RUN',
				'g560_technical_ready' => $technical_ready,
				't565_g560_pass' => false,
				'next_gate' => $technical_ready ? 'G-560-HUMAN' : 'G-560',
			),
			'interpretation_rules' => array(
				'Automated source checks prove presence of contract markers, not rendered visual quality.',
				'Live cases prove Search state semantics in the real WordPress environment.',
				'Visual Contract v2 requires human review for material UI changes; T564 remains NOT_RUN until visual evidence is accepted.',
				'G-560 cannot be marked PASS solely from this automated report.',
			),
		);
	}

	/** @param array<string,mixed> $response */
	private static function rank_of( array $response, int $expected_post_id ): int {
		if ( $expected_post_id <= 0 ) {
			return 0;
		}
		foreach ( (array) ( $response['results'] ?? array() ) as $result ) {
			if ( is_array( $result ) && $expected_post_id === (int) ( $result['post_id'] ?? 0 ) ) {
				return (int) ( $result['rank'] ?? 0 );
			}
		}
		return 0;
	}

	/** @param array<int,array<string,mixed>> $checks */
	private static function category_pass( array $checks, string $category ): bool {
		$found = false;
		foreach ( $checks as $check ) {
			if ( $category !== (string) ( $check['category'] ?? '' ) ) {
				continue;
			}
			$found = true;
			if ( empty( $check['pass'] ) ) {
				return false;
			}
		}
		return $found;
	}

	private static function editorial_fingerprint(): string {
		$ids = get_posts(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'numberposts' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => true,
			)
		);

		$rows = array();
		foreach ( array_map( 'intval', is_array( $ids ) ? $ids : array() ) as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$meta = array();
			foreach ( Meta_Contract::fields() as $definition ) {
				$key = (string) $definition['key'];
				$meta[ $key ] = get_post_meta( $post_id, $key, true );
			}
			$rows[ $post_id ] = array(
				'post_title' => (string) $post->post_title,
				'post_excerpt' => (string) $post->post_excerpt,
				'post_content' => (string) $post->post_content,
				'post_status' => (string) $post->post_status,
				'post_modified_gmt' => (string) $post->post_modified_gmt,
				'summary' => $meta,
			);
		}

		return Canonical_JSON::hash( $rows );
	}
}
