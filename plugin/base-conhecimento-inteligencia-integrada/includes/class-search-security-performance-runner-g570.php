<?php
/**
 * SPEC-005 G-570 — Security & Performance environmental runner.
 *
 * Read-only with respect to editorial data and Search Projection.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Security_Performance_Runner_G570 {

	public const ACTION = 'bdc_kb_spec005_g570_security_performance';
	public const PAGE_SLUG = 'bdc-kb-spec005-g570-security-performance';

	public const BENCHMARK_REPEATS = 5;
	public const BENCHMARK_WARMUPS = 1;
	public const PERF_P95_BUDGET_MS = 750.0;
	public const PERF_MAX_BUDGET_MS = 1500.0;

	private const NONCE_ACTION = 'bdc_kb_spec005_g570_security_performance';
	private const NONCE_FIELD = 'bdc_kb_spec005_g570_security_performance_nonce';

	/** @var array<int,string> */
	private const BENCHMARK_QUERIES = array(
		'Windows 11',
		'SCCM',
		'Termo de assinatura',
		'essencialmente',
		'phising',
		'bdczzzznomatch20260919',
	);

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 47 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Security & Performance G-570',
			'Security & Performance G-570',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — G-570 Segurança e Performance', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p>';
		echo esc_html__( 'Validação read-only de capabilities, bounds SQL, entradas abusivas e benchmark p50/p95. Não altera posts, Projection ou configurações.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar G-570 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-g570-security-performance-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	public static function run(): array {
		global $wpdb;

		$started = microtime( true );
		$errors = array();
		$throwables = array();
		$checks = array();

		$editorial_before = self::editorial_fingerprint();

		$source = self::source_contract_checks();
		foreach ( $source['checks'] as $check ) {
			$checks[] = $check;
		}

		$projection_ready = Search_Projection_Repository::is_ready();
		$checks[] = self::check( 'projection_ready', 'runtime', $projection_ready );

		$scope = self::scope_capability_checks();
		foreach ( $scope['checks'] as $check ) {
			$checks[] = $check;
		}
		$errors = array_merge( $errors, $scope['errors'] );
		$throwables = array_merge( $throwables, $scope['throwables'] );

		$bounds = self::sql_bounds_checks();
		foreach ( $bounds['checks'] as $check ) {
			$checks[] = $check;
		}
		$errors = array_merge( $errors, $bounds['errors'] );
		$throwables = array_merge( $throwables, $bounds['throwables'] );

		$abuse = self::abuse_checks();
		foreach ( $abuse['checks'] as $check ) {
			$checks[] = $check;
		}
		$errors = array_merge( $errors, $abuse['errors'] );
		$throwables = array_merge( $throwables, $abuse['throwables'] );

		$benchmark = self::benchmark();
		foreach ( $benchmark['checks'] as $check ) {
			$checks[] = $check;
		}
		$errors = array_merge( $errors, $benchmark['errors'] );
		$throwables = array_merge( $throwables, $benchmark['throwables'] );

		$editorial_after = self::editorial_fingerprint();
		$editorial_equal = '' !== $editorial_before
			&& '' !== $editorial_after
			&& hash_equals( $editorial_before, $editorial_after );
		$checks[] = self::check( 'editorial_fingerprint_equal', 'safety', $editorial_equal );

		$t570 = self::category_pass( $checks, 'scope_capability' )
			&& self::category_pass( $checks, 'source_scope' );
		$t571 = self::category_pass( $checks, 'sql_bounds' )
			&& self::category_pass( $checks, 'source_sql' );
		$t572 = self::category_pass( $checks, 'abuse' );
		$t573 = self::category_pass( $checks, 'performance' );
		$safety = self::category_pass( $checks, 'safety' )
			&& self::category_pass( $checks, 'source_safety' );

		$pass = $projection_ready
			&& $t570
			&& $t571
			&& $t572
			&& $t573
			&& $safety
			&& empty( $errors )
			&& empty( $throwables );

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'G-570',
			'mode' => 'spec005_security_performance_environmental',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
				'db_server' => method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '',
			),
			'contract' => array(
				'reference' => 'specs/005-search-lexical-golden-queries/g570-security-performance-contract-v1.md',
				'benchmark_warmups_per_query' => self::BENCHMARK_WARMUPS,
				'benchmark_repeats_per_query' => self::BENCHMARK_REPEATS,
				'benchmark_sample_count_expected' => count( self::BENCHMARK_QUERIES ) * self::BENCHMARK_REPEATS,
				'p95_budget_ms' => self::PERF_P95_BUDGET_MS,
				'max_budget_ms' => self::PERF_MAX_BUDGET_MS,
				'budget_is_production_sla' => false,
			),
			'projection' => array(
				'ready' => $projection_ready,
				'state' => Search_Projection_Repository::state(),
			),
			'checks' => $checks,
			'scope_capability' => $scope['details'],
			'sql_bounds' => $bounds['details'],
			'abuse' => $abuse['details'],
			'performance' => $benchmark['details'],
			'safety' => array(
				'editorial_fingerprint_before' => $editorial_before,
				'editorial_fingerprint_after' => $editorial_after,
				'editorial_fingerprint_equal' => $editorial_equal,
				'persists_query_log' => false,
				'calls_external_network' => false,
				'depends_on_asi' => false,
				'uses_fulltext' => false,
				'runner_writes_projection' => false,
				'runner_writes_editorial' => false,
			),
			'errors' => $errors,
			'throwables' => $throwables,
			'runner' => array(
				'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_result' => array(
				't570_scope_capability_pass' => $t570,
				't571_sql_bounds_pass' => $t571,
				't572_abuse_long_query_pass' => $t572,
				't573_performance_pass' => $t573,
				't574_g570_pass' => $pass,
				'next_gate' => $pass ? 'G-580' : 'G-570',
			),
			'interpretation_rules' => array(
				'Performance budget is a homologation guardrail for the current corpus, not a production SLA.',
				'Benchmark queries are curated technical fixtures and are not user telemetry.',
				'Capability-denial tests use temporary WordPress filters and remove them before continuing.',
				'G-570 does not modify ranking, Search Projection, posts, metadata or taxonomies.',
			),
		);
	}

	/**
	 * @return array{checks:array<int,array<string,mixed>>,details:array<string,mixed>}
	 */
	private static function source_contract_checks(): array {
		$service = self::read_source( 'includes/class-search-service.php' );
		$repository = self::read_source( 'includes/class-search-projection-repository.php' );
		$normalizer = self::read_source( 'includes/class-search-query-normalizer.php' );
		$ranker = self::read_source( 'includes/class-lexical-ranker.php' );
		$combined = $service . "\n" . $repository . "\n" . $normalizer . "\n" . $ranker;

		$authorization_before_ranking = false;
		$authorized_pos = strpos( $service, '$authorized = self::authorized_documents' );
		$rank_pos = strpos( $service, 'Lexical_Ranker::rank' );
		if ( false !== $authorized_pos && false !== $rank_pos ) {
			$authorization_before_ranking = $authorized_pos < $rank_pos;
		}

		$scope_checks = array(
			'service_requires_edit_posts' => str_contains( $service, "current_user_can( 'edit_posts' )" ),
			'service_revalidates_edit_post' => str_contains( $service, "current_user_can( 'edit_post', \$post_id )" ),
			'authorization_before_final_ranking' => $authorization_before_ranking,
			'fallback_perm_editable' => str_contains( $service, "'perm' => 'editable'" ),
			'post_type_from_meta_contract' => str_contains( $service, "Meta_Contract::POST_TYPE" ),
			'status_allowlist_present' => str_contains( $service, "private const ALLOWED_STATUSES" ),
			'response_does_not_export_body_norm' => ! str_contains( $service, "'body_norm' =>" ),
			'response_does_not_export_summary_norm' => ! str_contains( $service, "'summary_norm' =>" ),
		);

		$sql_checks = array(
			'repository_uses_prepare' => str_contains( $repository, '$wpdb->prepare' ),
			'repository_uses_esc_like' => str_contains( $repository, '$wpdb->esc_like' ),
			'candidate_cap_200' => Search_Projection_Repository::CANDIDATE_CAP === 200,
			'result_hard_cap_50' => Search_Service::MAX_LIMIT === 50,
			'result_default_20' => Search_Service::DEFAULT_LIMIT === 20,
			'query_chars_256' => Search_Query_Normalizer::MAX_QUERY_CHARS === 256,
			'query_bytes_1024' => Search_Query_Normalizer::MAX_QUERY_BYTES === 1024,
			'query_tokens_16' => Search_Query_Normalizer::MAX_TOKENS === 16,
			'token_chars_128' => Search_Query_Normalizer::MAX_TOKEN_CHARS === 128,
			'repository_has_no_request_globals' => ! str_contains( $repository, '$_GET' ) && ! str_contains( $repository, '$_POST' ),
			'no_fulltext_sql' => 1 !== preg_match( '/\\bFULLTEXT\\b|(?<![A-Za-z0-9_])MATCH\\s*\\(|(?<![A-Za-z0-9_])AGAINST\\s*\\(/i', $repository ),
		);

		$safety_checks = array(
			'no_asi_runtime_identifier' => 1 !== preg_match( '/\basi(?:4)?_/i', $combined ),
			'no_network_call' => 1 !== preg_match( '/wp_remote_[A-Za-z0-9_]*\\s*\\(|curl_[A-Za-z0-9_]+\\s*\\(|(?<![A-Za-z0-9_])fsockopen\\s*\\(|(?<![A-Za-z0-9_])stream_socket_client\\s*\\(/i', $combined ),
			'no_query_logging_option' => ! str_contains( $combined, 'query_log' ) && ! str_contains( $combined, 'search_log' ),
			'no_editorial_write_calls_in_search_runtime' => 1 !== preg_match( '/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i', $combined ),
		);

		$checks = array();
		foreach ( $scope_checks as $id => $pass ) {
			$checks[] = self::check( $id, 'source_scope', (bool) $pass );
		}
		foreach ( $sql_checks as $id => $pass ) {
			$checks[] = self::check( $id, 'source_sql', (bool) $pass );
		}
		foreach ( $safety_checks as $id => $pass ) {
			$checks[] = self::check( $id, 'source_safety', (bool) $pass );
		}

		return array(
			'checks' => $checks,
			'details' => array(
				'scope_checks' => $scope_checks,
				'sql_checks' => $sql_checks,
				'safety_checks' => $safety_checks,
			),
		);
	}

	/**
	 * @return array{checks:array<int,array<string,mixed>>,details:array<string,mixed>,errors:array<int,array<string,mixed>>,throwables:array<int,array<string,mixed>>}
	 */
	private static function scope_capability_checks(): array {
		$checks = array();
		$errors = array();
		$throwables = array();
		$details = array();

		try {
			$baseline = Search_Service::search( 'Windows 11', 20 );
			$baseline_has_583 = self::response_has_post( $baseline, 583 );
			$checks[] = self::check( 'baseline_expected_post_visible', 'scope_capability', $baseline_has_583 );
			$details['baseline_expected_post_583_visible'] = $baseline_has_583;

			$deny_edit_posts = static function ( array $allcaps ): array {
				$allcaps['edit_posts'] = false;
				return $allcaps;
			};

			add_filter( 'user_has_cap', $deny_edit_posts, PHP_INT_MAX, 4 );
			try {
				$denied = Search_Service::search( 'Windows 11', 20 );
			} finally {
				remove_filter( 'user_has_cap', $deny_edit_posts, PHP_INT_MAX );
			}

			$edit_posts_denied = 'technical_error' === (string) ( $denied['state'] ?? '' )
				&& 'none' === (string) ( $denied['retrieval_mode'] ?? '' )
				&& 'search_forbidden' === (string) ( $denied['error_code'] ?? '' )
				&& 0 === (int) ( $denied['count'] ?? -1 );
			$checks[] = self::check( 'edit_posts_denial_fail_closed', 'scope_capability', $edit_posts_denied );
			$details['edit_posts_denial'] = array(
				'state' => (string) ( $denied['state'] ?? '' ),
				'mode' => (string) ( $denied['retrieval_mode'] ?? '' ),
				'error_code' => (string) ( $denied['error_code'] ?? '' ),
				'count' => (int) ( $denied['count'] ?? 0 ),
			);

			$deny_post_583 = static function ( array $caps, string $cap, int $user_id, array $args ): array {
				unset( $user_id );
				if ( 'edit_post' === $cap && 583 === (int) ( $args[0] ?? 0 ) ) {
					return array( 'do_not_allow' );
				}
				return $caps;
			};

			add_filter( 'map_meta_cap', $deny_post_583, PHP_INT_MAX, 4 );
			try {
				$object_denied = Search_Service::search( 'Windows 11', 20 );
			} finally {
				remove_filter( 'map_meta_cap', $deny_post_583, PHP_INT_MAX );
			}

			$post_583_hidden = ! self::response_has_post( $object_denied, 583 );
			$checks[] = self::check( 'edit_post_object_denial_hides_document', 'scope_capability', $post_583_hidden );
			$details['object_denial'] = array(
				'denied_post_id' => 583,
				'denied_post_exported' => ! $post_583_hidden,
				'state' => (string) ( $object_denied['state'] ?? '' ),
				'mode' => (string) ( $object_denied['retrieval_mode'] ?? '' ),
				'count' => (int) ( $object_denied['count'] ?? 0 ),
			);

			$all_authorized = true;
			foreach ( (array) ( $baseline['results'] ?? array() ) as $result ) {
				if ( ! is_array( $result ) ) {
					continue;
				}
				$post_id = (int) ( $result['post_id'] ?? 0 );
				if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) || true !== ( $result['visibility_revalidated'] ?? false ) ) {
					$all_authorized = false;
					break;
				}
			}
			$checks[] = self::check( 'live_results_all_object_authorized', 'scope_capability', $all_authorized );
			$details['live_results_authorized'] = $all_authorized;
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'scope_capability', $error );
		}

		return compact( 'checks', 'details', 'errors', 'throwables' );
	}

	/**
	 * @return array{checks:array<int,array<string,mixed>>,details:array<string,mixed>,errors:array<int,array<string,mixed>>,throwables:array<int,array<string,mixed>>}
	 */
	private static function sql_bounds_checks(): array {
		$checks = array();
		$errors = array();
		$throwables = array();
		$details = array();

		try {
			$candidates = Search_Projection_Repository::retrieve_candidates( array( 'de' ), 999 );
			$candidate_ok = is_array( $candidates ) && count( $candidates ) <= Search_Projection_Repository::CANDIDATE_CAP;
			$checks[] = self::check( 'candidate_hard_cap_runtime', 'sql_bounds', $candidate_ok );
			$details['candidate_cap'] = array(
				'requested' => 999,
				'hard_cap' => Search_Projection_Repository::CANDIDATE_CAP,
				'returned' => is_array( $candidates ) ? count( $candidates ) : null,
				'error_code' => $candidates instanceof \WP_Error ? $candidates->get_error_code() : '',
			);

			$high_limit = Search_Service::search( 'SCCM', 5000 );
			$high_ok = (int) ( $high_limit['count'] ?? 0 ) <= Search_Service::MAX_LIMIT;
			$checks[] = self::check( 'result_hard_cap_runtime', 'sql_bounds', $high_ok );
			$details['high_result_limit'] = array(
				'requested' => 5000,
				'hard_cap' => Search_Service::MAX_LIMIT,
				'returned' => (int) ( $high_limit['count'] ?? 0 ),
				'state' => (string) ( $high_limit['state'] ?? '' ),
				'mode' => (string) ( $high_limit['retrieval_mode'] ?? '' ),
			);

			$low_limit = Search_Service::search( 'SCCM', 0 );
			$low_ok = (int) ( $low_limit['count'] ?? 0 ) <= 1;
			$checks[] = self::check( 'result_minimum_limit_clamp_runtime', 'sql_bounds', $low_ok );
			$details['low_result_limit'] = array(
				'requested' => 0,
				'effective_max_expected' => 1,
				'returned' => (int) ( $low_limit['count'] ?? 0 ),
			);

			$sql_like = Search_Service::search( "%_ ' OR 1=1 -- SCCM", 20 );
			$sql_like_ok = 'projection_like' === (string) ( $sql_like['retrieval_mode'] ?? '' )
				&& ! in_array( (string) ( $sql_like['state'] ?? '' ), array( 'technical_error', 'invalid_query' ), true )
				&& (int) ( $sql_like['count'] ?? 0 ) <= Search_Service::DEFAULT_LIMIT;
			$checks[] = self::check( 'sql_like_payload_bounded_runtime', 'sql_bounds', $sql_like_ok );
			$details['sql_like_payload'] = array(
				'state' => (string) ( $sql_like['state'] ?? '' ),
				'mode' => (string) ( $sql_like['retrieval_mode'] ?? '' ),
				'count' => (int) ( $sql_like['count'] ?? 0 ),
				'normalized' => (string) ( $sql_like['query']['normalized'] ?? '' ),
			);
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'sql_bounds', $error );
		}

		return compact( 'checks', 'details', 'errors', 'throwables' );
	}

	/**
	 * @return array{checks:array<int,array<string,mixed>>,details:array<string,mixed>,errors:array<int,array<string,mixed>>,throwables:array<int,array<string,mixed>>}
	 */
	private static function abuse_checks(): array {
		$checks = array();
		$errors = array();
		$throwables = array();
		$details = array();

		$cases = array(
			'non_string' => array(
				'value' => array( 'Windows 11' ),
				'error_code' => 'search_invalid_query_type',
			),
			'punctuation_only' => array(
				'value' => '!!!',
				'error_code' => 'search_empty_query',
			),
			'257_chars' => array(
				'value' => str_repeat( 'a', 257 ),
				'error_code' => 'search_query_too_long',
			),
			'17_tokens' => array(
				'value' => implode( ' ', array_map( static fn ( int $i ): string => 't' . $i, range( 1, 17 ) ) ),
				'error_code' => 'search_too_many_tokens',
			),
			'129_char_token' => array(
				'value' => str_repeat( 'a', 129 ),
				'error_code' => 'search_token_too_long',
			),
		);

		foreach ( $cases as $id => $case ) {
			try {
				$response = Search_Service::search( $case['value'], 20 );
				$pass = 'invalid_query' === (string) ( $response['state'] ?? '' )
					&& 'none' === (string) ( $response['retrieval_mode'] ?? '' )
					&& $case['error_code'] === (string) ( $response['error_code'] ?? '' )
					&& 0 === (int) ( $response['count'] ?? -1 );
				$checks[] = self::check( $id, 'abuse', $pass );
				$details[ $id ] = array(
					'state' => (string) ( $response['state'] ?? '' ),
					'mode' => (string) ( $response['retrieval_mode'] ?? '' ),
					'error_code' => (string) ( $response['error_code'] ?? '' ),
					'count' => (int) ( $response['count'] ?? 0 ),
				);
			} catch ( \Throwable $error ) {
				$checks[] = self::check( $id, 'abuse', false );
				$throwables[] = self::throwable_row( 'abuse_' . $id, $error );
			}
		}

		try {
			$html = Search_Query_Normalizer::normalize( '<script>alert(1)</script><b>Windows 11</b>' );
			$html_pass = is_array( $html )
				&& ! str_contains( (string) $html['original'], '<' )
				&& ! str_contains( (string) $html['original'], '>' )
				&& ! str_contains( (string) $html['normalized'], '<' )
				&& ! str_contains( (string) $html['normalized'], '>' );
			$checks[] = self::check( 'html_script_sanitized', 'abuse', $html_pass );
			$details['html_script'] = array(
				'normalizer_error' => $html instanceof \WP_Error ? $html->get_error_code() : '',
				'contains_angle_bracket_after_normalization' => is_array( $html )
					? str_contains( (string) $html['original'], '<' ) || str_contains( (string) $html['original'], '>' )
					: null,
			);

			$control = Search_Query_Normalizer::normalize( "SCCM\0 Windows\x07 11" );
			$control_pass = is_array( $control )
				&& ! str_contains( (string) $control['original'], "\0" )
				&& ! str_contains( (string) $control['original'], "\x07" );
			$checks[] = self::check( 'control_characters_removed', 'abuse', $control_pass );
			$details['control_characters'] = array(
				'normalizer_error' => $control instanceof \WP_Error ? $control->get_error_code() : '',
				'normalized' => is_array( $control ) ? (string) $control['normalized'] : '',
			);
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'abuse_sanitization', $error );
		}

		return compact( 'checks', 'details', 'errors', 'throwables' );
	}

	/**
	 * @return array{checks:array<int,array<string,mixed>>,details:array<string,mixed>,errors:array<int,array<string,mixed>>,throwables:array<int,array<string,mixed>>}
	 */
	private static function benchmark(): array {
		global $wpdb;

		$checks = array();
		$errors = array();
		$throwables = array();
		$samples = array();
		$per_query = array();
		$fallback_count = 0;
		$technical_error_count = 0;

		foreach ( self::BENCHMARK_QUERIES as $query ) {
			try {
				for ( $i = 0; $i < self::BENCHMARK_WARMUPS; ++$i ) {
					Search_Service::search( $query, 20 );
				}

				$query_samples = array();
				$query_db_deltas = array();

				for ( $i = 0; $i < self::BENCHMARK_REPEATS; ++$i ) {
					$db_before = (int) $wpdb->num_queries;
					$started = microtime( true );
					$response = Search_Service::search( $query, 20 );
					$elapsed = ( microtime( true ) - $started ) * 1000;
					$db_delta = max( 0, (int) $wpdb->num_queries - $db_before );

					$mode = (string) ( $response['retrieval_mode'] ?? '' );
					$state = (string) ( $response['state'] ?? '' );
					if ( 'wordpress_fallback' === $mode ) {
						++$fallback_count;
					}
					if ( 'technical_error' === $state || 'invalid_query' === $state ) {
						++$technical_error_count;
					}

					$query_samples[] = $elapsed;
					$query_db_deltas[] = $db_delta;
					$samples[] = $elapsed;
				}

				$stats = self::stats( $query_samples );
				$per_query[] = array(
					'query' => $query,
					'repeats' => count( $query_samples ),
					'p50_ms' => $stats['p50'],
					'p95_ms' => $stats['p95'],
					'max_ms' => $stats['max'],
					'mean_ms' => $stats['mean'],
					'db_query_delta_min' => empty( $query_db_deltas ) ? 0 : min( $query_db_deltas ),
					'db_query_delta_max' => empty( $query_db_deltas ) ? 0 : max( $query_db_deltas ),
				);
			} catch ( \Throwable $error ) {
				$throwables[] = self::throwable_row( 'benchmark', $error );
			}
		}

		$global = self::stats( $samples );
		$sample_count_ok = count( $samples ) === count( self::BENCHMARK_QUERIES ) * self::BENCHMARK_REPEATS;
		$p95_ok = $sample_count_ok && $global['p95'] <= self::PERF_P95_BUDGET_MS;
		$max_ok = $sample_count_ok && $global['max'] <= self::PERF_MAX_BUDGET_MS;
		$mode_ok = 0 === $fallback_count;
		$error_ok = 0 === $technical_error_count && empty( $throwables );

		$checks[] = self::check( 'benchmark_sample_count', 'performance', $sample_count_ok );
		$checks[] = self::check( 'benchmark_p95_budget', 'performance', $p95_ok );
		$checks[] = self::check( 'benchmark_max_budget', 'performance', $max_ok );
		$checks[] = self::check( 'benchmark_zero_fallback', 'performance', $mode_ok );
		$checks[] = self::check( 'benchmark_zero_technical_error', 'performance', $error_ok );

		return array(
			'checks' => $checks,
			'details' => array(
				'queries' => self::BENCHMARK_QUERIES,
				'queries_are_curated_fixtures' => true,
				'warmups_per_query' => self::BENCHMARK_WARMUPS,
				'repeats_per_query' => self::BENCHMARK_REPEATS,
				'sample_count' => count( $samples ),
				'global' => $global,
				'per_query' => $per_query,
				'p95_budget_ms' => self::PERF_P95_BUDGET_MS,
				'max_budget_ms' => self::PERF_MAX_BUDGET_MS,
				'fallback_count' => $fallback_count,
				'technical_error_count' => $technical_error_count,
				'budget_is_production_sla' => false,
			),
			'errors' => $errors,
			'throwables' => $throwables,
		);
	}

	/**
	 * @return array{id:string,category:string,pass:bool}
	 */
	private static function check( string $id, string $category, bool $pass ): array {
		return array(
			'id' => $id,
			'category' => $category,
			'pass' => $pass,
		);
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

	/** @param array<string,mixed> $response */
	private static function response_has_post( array $response, int $post_id ): bool {
		foreach ( (array) ( $response['results'] ?? array() ) as $result ) {
			if ( is_array( $result ) && $post_id === (int) ( $result['post_id'] ?? 0 ) ) {
				return true;
			}
		}
		return false;
	}

	/** @return array<string,float|int> */
	private static function stats( array $values ): array {
		if ( empty( $values ) ) {
			return array(
				'count' => 0,
				'mean' => 0.0,
				'p50' => 0.0,
				'p95' => 0.0,
				'max' => 0.0,
			);
		}

		sort( $values, SORT_NUMERIC );
		$count = count( $values );

		return array(
			'count' => $count,
			'mean' => round( array_sum( $values ) / $count, 4 ),
			'p50' => round( self::percentile( $values, 0.50 ), 4 ),
			'p95' => round( self::percentile( $values, 0.95 ), 4 ),
			'max' => round( (float) $values[ $count - 1 ], 4 ),
		);
	}

	/** @param array<int,float> $sorted */
	private static function percentile( array $sorted, float $p ): float {
		$count = count( $sorted );
		if ( 0 === $count ) {
			return 0.0;
		}
		$index = (int) ceil( $p * $count ) - 1;
		$index = max( 0, min( $count - 1, $index ) );
		return (float) $sorted[ $index ];
	}

	private static function read_source( string $relative ): string {
		$path = BDC_KB_DIR . $relative;
		if ( ! is_readable( $path ) ) {
			return '';
		}
		$content = file_get_contents( $path );
		return is_string( $content ) ? $content : '';
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

			$summary = array();
			foreach ( Meta_Contract::fields() as $definition ) {
				$key = (string) $definition['key'];
				$summary[ $key ] = get_post_meta( $post_id, $key, true );
			}

			$taxonomies = array();
			foreach ( Classification_Contract::fields() as $definition ) {
				$taxonomy = (string) $definition['taxonomy'];
				$term_ids = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
				if ( is_wp_error( $term_ids ) ) {
					$taxonomies[ $taxonomy ] = array( 'error' => $term_ids->get_error_code() );
					continue;
				}
				$term_ids = array_values( array_map( 'intval', is_array( $term_ids ) ? $term_ids : array() ) );
				sort( $term_ids, SORT_NUMERIC );
				$taxonomies[ $taxonomy ] = $term_ids;
			}

			$rows[ $post_id ] = array(
				'post_title' => (string) $post->post_title,
				'post_excerpt' => (string) $post->post_excerpt,
				'post_content' => (string) $post->post_content,
				'post_status' => (string) $post->post_status,
				'post_modified_gmt' => (string) $post->post_modified_gmt,
				'elementor_data' => get_post_meta( $post_id, '_elementor_data', true ),
				'summary' => $summary,
				'taxonomies' => $taxonomies,
			);
		}

		return Canonical_JSON::hash( $rows );
	}

	/** @return array<string,string> */
	private static function throwable_row( string $phase, \Throwable $error ): array {
		return array(
			'phase' => $phase,
			'class' => get_class( $error ),
			'code' => (string) $error->getCode(),
			'message' => $error->getMessage(),
		);
	}
}
