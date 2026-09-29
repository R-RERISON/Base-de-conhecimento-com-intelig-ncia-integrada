<?php
/**
 * SPEC-005 G-550 Golden Gate runtime runner.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Golden_Gate_Runner_G550 {

	public const ACTION = 'bdc_kb_spec005_g550_golden';
	public const PAGE_SLUG = 'bdc-kb-spec005-g550-golden';

	private const NONCE_ACTION = 'bdc_kb_spec005_g550_golden';
	private const NONCE_FIELD = 'bdc_kb_spec005_g550_golden_nonce';
	private const TECHNICAL_MAX_RANK = 3;

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 45 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Golden Gate G-550',
			'Golden Gate G-550',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — G-550 Golden Gate', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p>';
		echo esc_html__( 'Executa Golden Relevance e Technical Challenge exclusivamente contra a Search Projection pronta. Não persiste queries nem altera conteúdo editorial.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar G-550 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-g550-golden-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	public static function run(): array {
		$started = microtime( true );

		$base = self::base_report();

		$version_mismatches = self::runtime_version_mismatches();
		if ( ! empty( $version_mismatches ) ) {
			$base['status'] = 'STALE';
			$base['technical_errors'][] = array(
				'code' => 'golden_runtime_version_stale',
				'message' => 'Versões runtime divergem do contrato Golden congelado.',
				'mismatches' => $version_mismatches,
			);
			$base['gate_result'] = self::gate_result( false, 0, 0, 0, 1, 'G-550' );
			$base['performance']['total_runtime_ms'] = round( ( microtime( true ) - $started ) * 1000, 4 );
			return $base;
		}

		if ( ! Search_Projection_Repository::is_ready() ) {
			$base['status'] = 'TECHNICAL_ERROR';
			$base['technical_errors'][] = array(
				'code' => 'projection_not_ready',
				'message' => 'Search Projection precisa estar ready para G-550.',
			);
			$base['gate_result'] = self::gate_result( false, 0, 0, 0, 1, 'G-550' );
			$base['performance']['total_runtime_ms'] = round( ( microtime( true ) - $started ) * 1000, 4 );
			return $base;
		}

		$suites = Golden_Suite_Loader::load_all();
		if ( $suites instanceof \WP_Error ) {
			$code = $suites->get_error_code();
			$base['status'] = str_contains( $code, 'stale' ) ? 'STALE' : ( 'golden_suite_not_configured' === $code ? 'NOT_CONFIGURED' : 'TECHNICAL_ERROR' );
			$base['technical_errors'][] = array(
				'code' => $code,
				'message' => $suites->get_error_message(),
				'data' => $suites->get_error_data(),
			);
			$base['gate_result'] = self::gate_result( false, 0, 0, 0, 1, 'G-550' );
			$base['performance']['total_runtime_ms'] = round( ( microtime( true ) - $started ) * 1000, 4 );
			return $base;
		}

		$base['suites'] = array(
			'golden' => self::suite_descriptor( $suites['golden'] ),
			'challenge' => self::suite_descriptor( $suites['challenge'] ),
		);

		$blocking_failed = 0;
		$warning_failed = 0;
		$technical_failed = 0;
		$technical_errors = array();
		$results = array();

		foreach ( (array) $suites['golden']['items'] as $item ) {
			$started_item = microtime( true );
			try {
				$row = self::execute_item(
					(array) $item,
					(int) ( $item['max_rank'] ?? 0 ),
					'golden'
				);
				$row['runtime_ms'] = round( ( microtime( true ) - $started_item ) * 1000, 4 );

				if ( ! empty( $row['technical_error'] ) ) {
					$technical_errors[] = array(
						'id' => (string) ( $item['id'] ?? '' ),
						'code' => (string) $row['technical_error'],
					);
				} elseif ( true === ( $item['active'] ?? false ) && 'blocking' === (string) ( $item['severity'] ?? '' ) ) {
					if ( ! $row['pass'] ) {
						++$blocking_failed;
					}
				} elseif ( ! $row['pass'] ) {
					++$warning_failed;
				}

				$results[] = $row;
			} catch ( \Throwable $error ) {
				$technical_errors[] = array(
					'id' => (string) ( $item['id'] ?? '' ),
					'code' => 'throwable',
					'class' => get_class( $error ),
					'message' => $error->getMessage(),
				);
			}
		}

		foreach ( (array) $suites['challenge']['items'] as $item ) {
			$started_item = microtime( true );
			try {
				$row = self::execute_item(
					(array) $item,
					self::TECHNICAL_MAX_RANK,
					'technical_challenge'
				);
				$row['runtime_ms'] = round( ( microtime( true ) - $started_item ) * 1000, 4 );

				if ( ! empty( $row['technical_error'] ) ) {
					$technical_errors[] = array(
						'id' => (string) ( $item['id'] ?? '' ),
						'code' => (string) $row['technical_error'],
					);
				} elseif ( ! $row['pass'] ) {
					++$technical_failed;
				}

				$results[] = $row;
			} catch ( \Throwable $error ) {
				$technical_errors[] = array(
					'id' => (string) ( $item['id'] ?? '' ),
					'code' => 'throwable',
					'class' => get_class( $error ),
					'message' => $error->getMessage(),
				);
			}
		}

		$pass = 0 === $blocking_failed
			&& 0 === $technical_failed
			&& empty( $technical_errors );

		$base['status'] = $pass ? 'PASS' : 'FAIL';
		$base['blocking_failed'] = $blocking_failed;
		$base['warning_failed'] = $warning_failed;
		$base['technical_failed'] = $technical_failed;
		$base['technical_errors'] = $technical_errors;
		$base['results'] = $results;
		$base['gate_result'] = self::gate_result(
			$pass,
			$blocking_failed,
			$warning_failed,
			$technical_failed,
			count( $technical_errors ),
			$pass ? 'G-560' : 'G-550'
		);
		$base['performance'] = array(
			'count' => count( $results ),
			'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
		);

		return $base;
	}

	/**
	 * Pure item evaluation helper for unit tests.
	 *
	 * @param array<string,mixed> $item
	 * @param array<string,mixed> $response
	 * @return array<string,mixed>
	 */
	public static function evaluate_response(
		array $item,
		array $response,
		int $max_rank,
		string $suite_kind
	): array {
		$expected = (int) ( $item['expected_post_id'] ?? 0 );
		$actual_rank = 0;
		$matched_signals = array();

		foreach ( (array) ( $response['results'] ?? array() ) as $result ) {
			if ( ! is_array( $result ) ) {
				continue;
			}
			if ( $expected === (int) ( $result['post_id'] ?? 0 ) ) {
				$actual_rank = (int) ( $result['rank'] ?? 0 );
				$matched_signals = array_values( array_map( 'strval', (array) ( $result['matched_signals'] ?? array() ) ) );
				break;
			}
		}

		$mode = (string) ( $response['retrieval_mode'] ?? '' );
		$state = (string) ( $response['state'] ?? '' );
		$technical_error = '';

		if ( 'projection_like' !== $mode ) {
			$technical_error = 'golden_requires_projection_like';
		}
		if ( in_array( $state, array( 'technical_error', 'invalid_query' ), true ) ) {
			$technical_error = 'search_state_' . sanitize_key( $state );
		}

		$pass = '' === $technical_error
			&& $actual_rank > 0
			&& $actual_rank <= $max_rank;

		return array(
			'id' => (string) ( $item['id'] ?? '' ),
			'suite_kind' => $suite_kind,
			'query' => (string) ( $item['query'] ?? '' ),
			'query_norm' => (string) ( $item['query_norm'] ?? '' ),
			'expected_post_id' => $expected,
			'max_rank' => $max_rank,
			'actual_rank' => $actual_rank,
			'pass' => $pass,
			'severity' => 'golden' === $suite_kind ? (string) ( $item['severity'] ?? '' ) : 'technical',
			'active' => 'golden' === $suite_kind ? (bool) ( $item['active'] ?? false ) : false,
			'disposition' => 'golden' === $suite_kind ? (string) ( $item['disposition'] ?? '' ) : '',
			'retrieval_state' => $state,
			'retrieval_mode' => $mode,
			'top_post_ids' => array_slice(
				array_values(
					array_map(
						static fn ( array $result ): int => (int) ( $result['post_id'] ?? 0 ),
						array_filter( (array) ( $response['results'] ?? array() ), 'is_array' )
					)
				),
				0,
				10
			),
			'matched_signals' => $matched_signals,
			'technical_error' => $technical_error,
		);
	}

	/**
	 * @param array<string,mixed> $item
	 * @return array<string,mixed>
	 */
	private static function execute_item( array $item, int $max_rank, string $suite_kind ): array {
		$response = Search_Service::search( (string) ( $item['query'] ?? '' ), 20 );
		return self::evaluate_response( $item, $response, $max_rank, $suite_kind );
	}

	/** @return array<string,mixed> */
	private static function base_report(): array {
		return array(
			'schema_version' => '1.0.0',
			'gate' => 'G-550',
			'mode' => 'spec005_golden_gate_runtime',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
				'db_server' => self::db_version(),
			),
			'runtime_versions' => array(
				'normalizer' => Search_Query_Normalizer::VERSION,
				'document' => Search_Document_Builder::VERSION,
				'algorithm' => Lexical_Ranker::VERSION,
				'result' => Search_Service::RESULT_VERSION,
				'runner' => Golden_Suite_Loader::RUNNER_VERSION,
			),
			'projection' => array(
				'ready' => Search_Projection_Repository::is_ready(),
				'state' => Search_Projection_Repository::state(),
			),
			'suites' => array(),
			'status' => 'NOT_RUN',
			'blocking_failed' => 0,
			'warning_failed' => 0,
			'technical_failed' => 0,
			'technical_errors' => array(),
			'results' => array(),
			'performance' => array(
				'count' => 0,
				'total_runtime_ms' => 0.0,
			),
			'gate_result' => self::gate_result( false, 0, 0, 0, 0, 'G-550' ),
			'privacy' => array(
				'persists_query_log' => false,
				'exports_identity' => false,
				'exports_ip' => false,
				'exports_session' => false,
				'calls_external_network' => false,
				'depends_on_asi' => false,
			),
		);
	}

	/**
	 * @param array<string,mixed> $suite
	 * @return array<string,mixed>
	 */
	private static function suite_descriptor( array $suite ): array {
		return array(
			'suite_kind' => (string) ( $suite['suite_kind'] ?? '' ),
			'suite_version' => (string) ( $suite['suite_version'] ?? '' ),
			'set_hash' => (string) ( $suite['set_hash'] ?? '' ),
			'computed_set_hash' => (string) ( $suite['_runtime']['computed_set_hash'] ?? '' ),
			'resource' => (string) ( $suite['_runtime']['resource'] ?? '' ),
			'count' => count( (array) ( $suite['items'] ?? array() ) ),
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function gate_result(
		bool $pass,
		int $blocking_failed,
		int $warning_failed,
		int $technical_failed,
		int $technical_error_count,
		string $next_gate
	): array {
		return array(
			't550_explicit_runner_executed' => true,
			't551_json_report_generated' => true,
			't552_blocking_failures_zero' => 0 === $blocking_failed,
			't553_warnings_documented' => true,
			't554_evidence_current_pass' => 0 === $technical_error_count,
			't555_g550_pass' => $pass,
			'blocking_failed' => $blocking_failed,
			'warning_failed' => $warning_failed,
			'technical_failed' => $technical_failed,
			'technical_error_count' => $technical_error_count,
			'next_gate' => $next_gate,
		);
	}

	/** @return array<int,array<string,string>> */
	private static function runtime_version_mismatches(): array {
		$checks = array(
			'normalizer' => array( Golden_Suite_Loader::EXPECTED_NORMALIZER_VERSION, Search_Query_Normalizer::VERSION ),
			'document' => array( Golden_Suite_Loader::EXPECTED_DOCUMENT_VERSION, Search_Document_Builder::VERSION ),
			'algorithm' => array( Golden_Suite_Loader::EXPECTED_ALGORITHM_VERSION, Lexical_Ranker::VERSION ),
			'result' => array( Golden_Suite_Loader::EXPECTED_RESULT_VERSION, Search_Service::RESULT_VERSION ),
		);
		$mismatches = array();
		foreach ( $checks as $component => $versions ) {
			if ( $versions[0] !== $versions[1] ) {
				$mismatches[] = array(
					'component' => $component,
					'expected' => $versions[0],
					'actual' => $versions[1],
				);
			}
		}
		return $mismatches;
	}

	private static function db_version(): string {
		global $wpdb;
		return method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '';
	}
}
