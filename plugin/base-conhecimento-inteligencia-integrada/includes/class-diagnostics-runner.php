<?php
/**
 * Runner temporário de diagnóstico onclick da SPEC-001.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Executa diagnóstico efêmero, gera JSON e remove todas as fixtures próprias.
 *
 * IMPORTANTE: classe temporária. Deve ser removida antes do package/release da SPEC-001.
 */
final class Diagnostics_Runner {

	public const ACTION = 'bdc_kb_run_diagnostics';

	private const NONCE_ACTION   = 'bdc_kb_run_diagnostics_v1';
	private const NONCE_FIELD    = 'bdc_kb_diag_nonce';
	private const FIXTURE_META   = '_bdc_kb_diagnostic_fixture';
	private const FIXTURE_VALUE  = 'spec001-onclick-v1';
	private const SCHEMA_VERSION = '1.0.0';

	/**
	 * Registra hooks apenas quando a flag explícita está ativa.
	 */
	public static function register(): void {
		if ( ! self::is_enabled() ) {
			return;
		}

		add_action( 'admin_notices', array( self::class, 'render_notice' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle' ) );
	}

	public static function is_enabled(): bool {
		return defined( 'BDC_KB_ENABLE_DIAGNOSTICS' ) && true === BDC_KB_ENABLE_DIAGNOSTICS;
	}

	/**
	 * Renderiza o acionador somente na tela da SPEC-001 e para administradores.
	 */
	public static function render_notice(): void {
		if ( ! self::is_enabled() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) && is_scalar( $_GET['page'] )
			? sanitize_key( wp_unslash( (string) $_GET['page'] ) )
			: '';

		if ( Admin_Page::PAGE_SLUG !== $page ) {
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<p><strong>' . esc_html__( 'Diagnóstico temporário SPEC-001', 'bdc-knowledge-base' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Cria somente fixtures efêmeras marcadas, gera um JSON de análise e executa limpeza automática. Remova a flag BDC_KB_ENABLE_DIAGNOSTICS após o teste.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<p><button type="submit" class="button button-secondary">' . esc_html__( 'Executar diagnóstico e gerar JSON', 'bdc-knowledge-base' ) . '</button></p>';
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Endpoint autenticado que devolve o relatório como JSON baixável.
	 */
	public static function handle(): never {
		if ( ! self::is_enabled() ) {
			wp_die( esc_html__( 'Diagnóstico desabilitado.', 'bdc-knowledge-base' ), '', array( 'response' => 404 ) );
		}

		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para executar o diagnóstico.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$report   = self::run();
		$filename = 'bdc-kb-diagnostics-' . gmdate( 'Ymd-His' ) . '.json';

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Executa o diagnóstico in-process usando WordPress real.
	 *
	 * @return array<string,mixed>
	 */
	private static function run(): array {
		$started_at    = microtime( true );
		$fixture_ids   = array();
		$stale_removed = self::cleanup_fixtures();

		$report = array(
			'schema_version' => self::SCHEMA_VERSION,
			'mode'           => 'temporary_onclick',
			'generated_at'   => gmdate( 'c' ),
			'environment'    => array(
				'wordpress' => (string) get_bloginfo( 'version' ),
				'php'       => PHP_VERSION,
				'plugin'    => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : 'unknown',
				'multisite' => is_multisite(),
			),
			'runtime_fingerprint' => self::runtime_fingerprint(),
			'safety' => array(
				'flag_required'            => 'BDC_KB_ENABLE_DIAGNOSTICS=true',
				'requires_capability'      => 'manage_options',
				'persistent_report'        => false,
				'fixture_marker'           => self::FIXTURE_VALUE,
				'stale_removed_before_run' => $stale_removed,
			),
			'checks'  => array(),
			'cleanup' => array(),
		);

		try {
			$fixture_id = self::create_fixture( 'post' );
			if ( is_wp_error( $fixture_id ) || (int) $fixture_id <= 0 ) {
				self::add_check( $report, 'DIAG-001', 'HARNESS', false, 'Não foi possível criar a fixture temporária.' );
				return self::finalize_report( $report, $started_at );
			}

			$fixture_ids[] = (int) $fixture_id;
			self::add_check( $report, 'DIAG-001', 'HARNESS', true, 'Fixture temporária criada.' );

			self::run_editorial_checks( $report, (int) $fixture_id );
			self::run_summary_checks( $report, (int) $fixture_id );
			self::run_security_checks( $report, (int) $fixture_id, $fixture_ids );
			self::run_fault_injection_checks( $report, (int) $fixture_id );
		} catch ( \Throwable $exception ) {
			self::add_check(
				$report,
				'DIAG-999',
				'HARNESS',
				false,
				'Exceção não esperada durante o diagnóstico.',
				array( 'exception_type' => get_class( $exception ) )
			);
		} finally {
			foreach ( array_unique( array_map( 'intval', $fixture_ids ) ) as $fixture_id ) {
				if ( $fixture_id > 0 ) {
					wp_delete_post( $fixture_id, true );
				}
			}
		}

		$report['cleanup'] = array(
			'deleted_fixture_ids_count' => count( array_unique( $fixture_ids ) ),
			'residual_fixtures'          => self::count_fixtures(),
		);

		return self::finalize_report( $report, $started_at );
	}

	private static function run_editorial_checks( array &$report, int $post_id ): void {
		$before_post      = get_post( $post_id );
		$before_elementor = get_post_meta( $post_id, '_elementor_data', true );

		$result          = Summary_Store::update( $post_id, array( 'objective' => 'Objetivo alterado pelo diagnóstico' ) );
		$after_post      = get_post( $post_id );
		$after_elementor = get_post_meta( $post_id, '_elementor_data', true );

		$passed = ! is_wp_error( $result )
			&& is_object( $before_post )
			&& is_object( $after_post )
			&& $before_post->post_title === $after_post->post_title
			&& $before_post->post_content === $after_post->post_content
			&& $before_elementor === $after_elementor;

		self::add_check( $report, 'G001-01', 'G-001', $passed, 'Summary não altera post_title, post_content ou _elementor_data.' );
	}

	private static function run_summary_checks( array &$report, int $post_id ): void {
		self::seed_summary( $post_id, 'O0', 'E0', 'I0' );

		$result = Summary_Store::update( $post_id, array( 'objective' => 'O1' ) );
		$partial_ok = ! is_wp_error( $result )
			&& 'O1' === get_post_meta( $post_id, '_bdc_es_objective', true )
			&& 'E0' === get_post_meta( $post_id, '_bdc_es_escalation', true )
			&& 'I0' === get_post_meta( $post_id, '_bdc_es_important', true );
		self::add_check( $report, 'G020-01', 'G-020', $partial_ok, 'Update parcial preserva campos omitidos.' );

		$result = Summary_Store::update( $post_id, array( 'escalation' => "  \n " ) );
		$delete_ok = ! is_wp_error( $result ) && ! metadata_exists( 'post', $post_id, '_bdc_es_escalation' );
		self::add_check( $report, 'G020-02', 'G-020', $delete_ok, 'Vazio sanitizado remove a meta.' );

		$attempts = 0;
		$counter  = static function ( $check, $object_id, $meta_key ) use ( $post_id, &$attempts ) {
			if ( (int) $object_id === $post_id && in_array( $meta_key, self::meta_keys(), true ) ) {
				++$attempts;
			}
			return $check;
		};
		add_filter( 'update_post_metadata', $counter, 10, 3 );
		try {
			$result = Summary_Store::update( $post_id, array( 'objective' => 'X', 'intruso' => 'Y' ) );
		} finally {
			remove_filter( 'update_post_metadata', $counter, 10 );
		}
		$allowlist_ok = is_wp_error( $result ) && 'bdc_kb_unknown_field' === $result->get_error_code() && 0 === $attempts;
		self::add_check( $report, 'G020-03', 'G-020', $allowlist_ok, 'Campo desconhecido rejeita o payload antes de qualquer write.' );

		$attempts = 0;
		add_filter( 'update_post_metadata', $counter, 10, 3 );
		try {
			$current = (string) get_post_meta( $post_id, '_bdc_es_objective', true );
			$result  = Summary_Store::update( $post_id, array( 'objective' => $current ) );
		} finally {
			remove_filter( 'update_post_metadata', $counter, 10 );
		}
		$noop_ok = ! is_wp_error( $result ) && 0 === $attempts;
		self::add_check( $report, 'G020-04', 'G-020', $noop_ok, 'NO_CHANGE não chama update_post_meta.' );

		$result = Summary_Store::update( $post_id, array( 'important' => "<script>alert('x')</script>\nLinha válida" ) );
		$stored = (string) get_post_meta( $post_id, '_bdc_es_important', true );
		$xss_ok = ! is_wp_error( $result ) && false === stripos( $stored, '<script' ) && false === stripos( $stored, '</script' );
		self::add_check( $report, 'G020-05', 'G-020', $xss_ok, 'HTML/script é sanitizado antes da persistência.' );

		$unicode = "Ação çã — linha 1\nCaminho C:\\Temp\\Arquivo";
		$result  = Summary_Store::update( $post_id, array( 'important' => $unicode ) );
		$unicode_ok = ! is_wp_error( $result ) && Meta_Contract::sanitize_text( $unicode ) === get_post_meta( $post_id, '_bdc_es_important', true );
		self::add_check( $report, 'G020-06', 'G-020', $unicode_ok, 'Unicode, multiline e backslash seguem a sanitização canônica do WordPress.' );
	}

	private static function run_security_checks( array &$report, int $post_id, array &$fixture_ids ): void {
		$before = (string) get_post_meta( $post_id, '_bdc_es_objective', true );
		$deny   = static function ( array $caps, string $cap, int $user_id, array $args ) use ( $post_id ): array {
			unset( $user_id );
			if ( 'edit_post' === $cap && isset( $args[0] ) && (int) $args[0] === $post_id ) {
				return array( 'do_not_allow' );
			}
			return $caps;
		};

		add_filter( 'map_meta_cap', $deny, 99, 4 );
		try {
			$result = Summary_Store::update( $post_id, array( 'objective' => 'Tentativa IDOR' ) );
		} finally {
			remove_filter( 'map_meta_cap', $deny, 99 );
		}

		$forbidden_ok = is_wp_error( $result )
			&& 'bdc_kb_forbidden' === $result->get_error_code()
			&& $before === get_post_meta( $post_id, '_bdc_es_objective', true );
		self::add_check( $report, 'G070-01', 'G-070', $forbidden_ok, 'Capability por objeto bloqueia tentativa sem edit_post.' );

		$page_id = self::create_fixture( 'page' );
		if ( is_wp_error( $page_id ) || (int) $page_id <= 0 ) {
			self::add_check( $report, 'G070-02', 'G-070', false, 'Não foi possível criar fixture page para teste de escopo.' );
		} else {
			$fixture_ids[] = (int) $page_id;
			$page_result   = Summary_Store::read( (int) $page_id );
			$page_ok       = is_wp_error( $page_result ) && 'bdc_kb_unsupported_post_type' === $page_result->get_error_code();
			self::add_check( $report, 'G070-02', 'G-070', $page_ok, 'Post type page é rejeitado pelo domínio.' );
		}

		$summary_nonce_action = 'bdc_kb_save_summary_' . $post_id;
		$nonce                = wp_create_nonce( $summary_nonce_action );
		$nonce_ok             = false !== wp_verify_nonce( $nonce, $summary_nonce_action )
			&& false === wp_verify_nonce( 'nonce-invalido', $summary_nonce_action );
		self::add_check(
			$report,
			'G070-03',
			'G-070',
			$nonce_ok,
			'Primitive de nonce valida ação vinculada ao post e rejeita nonce inválido.',
			array( 'scope' => 'primitive; o handler completo continua sujeito ao browser/integration acceptance' )
		);
	}

	private static function run_fault_injection_checks( array &$report, int $post_id ): void {
		self::seed_summary( $post_id, 'O0', 'E0', 'I0' );
		$result = self::run_update_with_faults(
			$post_id,
			array( 'objective' => 'O1', 'escalation' => 'E1', 'important' => 'I1' ),
			array( 2 )
		);
		$fail_safe_ok = is_wp_error( $result )
			&& is_array( $result->get_error_data() )
			&& Summary_Store::STATUS_FAIL_SAFE === ( $result->get_error_data()['status'] ?? '' )
			&& 'O0' === get_post_meta( $post_id, '_bdc_es_objective', true )
			&& 'E0' === get_post_meta( $post_id, '_bdc_es_escalation', true )
			&& 'I0' === get_post_meta( $post_id, '_bdc_es_important', true );
		self::add_check( $report, 'B006-01', 'B-006', $fail_safe_ok, 'Falha no segundo write restaura integralmente o snapshot e retorna FAIL_SAFE.' );

		self::seed_summary( $post_id, 'O0', 'E0', 'I0' );
		$result = self::run_update_with_faults(
			$post_id,
			array( 'objective' => 'O1', 'escalation' => 'E1', 'important' => 'I1' ),
			array( 2, 4 )
		);
		$critical_ok = is_wp_error( $result )
			&& is_array( $result->get_error_data() )
			&& Summary_Store::STATUS_PARTIAL_FAILURE_CRITICAL === ( $result->get_error_data()['status'] ?? '' );
		self::add_check( $report, 'B006-02', 'B-006', $critical_ok, 'Falha durante a compensação é detectada como PARTIAL_FAILURE_CRITICAL.' );
	}

	/**
	 * @param array<string,string> $changes
	 * @param array<int,int>       $fail_attempts
	 * @return array<string,mixed>|\WP_Error
	 */
	private static function run_update_with_faults( int $post_id, array $changes, array $fail_attempts ): array|\WP_Error {
		$attempt = 0;
		$filter  = static function ( $check, $object_id, $meta_key ) use ( $post_id, $fail_attempts, &$attempt ) {
			if ( (int) $object_id !== $post_id || ! in_array( $meta_key, self::meta_keys(), true ) ) {
				return $check;
			}

			++$attempt;
			if ( in_array( $attempt, $fail_attempts, true ) ) {
				return false;
			}

			return $check;
		};

		add_filter( 'update_post_metadata', $filter, 10, 3 );
		try {
			return Summary_Store::update( $post_id, $changes );
		} finally {
			remove_filter( 'update_post_metadata', $filter, 10 );
		}
	}

	/**
	 * @return array<int,string>
	 */
	private static function meta_keys(): array {
		return array_values(
			array_map(
				static fn ( array $definition ): string => $definition['key'],
				Meta_Contract::fields()
			)
		);
	}

	private static function seed_summary( int $post_id, string $objective, string $escalation, string $important ): void {
		update_post_meta( $post_id, '_bdc_es_objective', wp_slash( $objective ) );
		update_post_meta( $post_id, '_bdc_es_escalation', wp_slash( $escalation ) );
		update_post_meta( $post_id, '_bdc_es_important', wp_slash( $important ) );
	}

	/**
	 * @return int|\WP_Error
	 */
	private static function create_fixture( string $post_type ): int|\WP_Error {
		return wp_insert_post(
			array(
				'post_type'    => $post_type,
				'post_status'  => 'draft',
				'post_title'   => '[BDC-DIAG] Fixture temporária ' . gmdate( 'Ymd-His' ),
				'post_content' => 'BDC_DIAG_CONTENT_SENTINEL',
				'post_author'  => get_current_user_id(),
				'meta_input'   => array(
					self::FIXTURE_META  => self::FIXTURE_VALUE,
					'_elementor_data'   => '{"bdc_diag":"sentinel"}',
					'_bdc_es_objective' => 'O0',
					'_bdc_es_escalation' => 'E0',
					'_bdc_es_important' => 'I0',
				),
			),
			true
		);
	}

	private static function cleanup_fixtures(): int {
		$ids = get_posts(
			array(
				'post_type'        => 'any',
				'post_status'      => 'any',
				'numberposts'      => 50,
				'fields'           => 'ids',
				'meta_key'         => self::FIXTURE_META,
				'meta_value'       => self::FIXTURE_VALUE,
				'suppress_filters' => true,
			)
		);

		$removed = 0;
		foreach ( $ids as $id ) {
			if ( wp_delete_post( (int) $id, true ) ) {
				++$removed;
			}
		}

		return $removed;
	}

	private static function count_fixtures(): int {
		$ids = get_posts(
			array(
				'post_type'        => 'any',
				'post_status'      => 'any',
				'numberposts'      => 50,
				'fields'           => 'ids',
				'meta_key'         => self::FIXTURE_META,
				'meta_value'       => self::FIXTURE_VALUE,
				'suppress_filters' => true,
			)
		);

		return count( $ids );
	}

	/**
	 * @return array<string,string>
	 */
	private static function runtime_fingerprint(): array {
		$files = array(
			'bootstrap'     => BDC_KB_FILE,
			'meta_contract' => BDC_KB_DIR . 'includes/class-meta-contract.php',
			'summary_store' => BDC_KB_DIR . 'includes/class-summary-store.php',
			'admin_page'    => BDC_KB_DIR . 'includes/class-admin-page.php',
		);
		$result = array();

		foreach ( $files as $name => $path ) {
			$result[ $name ] = is_readable( $path ) ? hash_file( 'sha256', $path ) : 'unreadable';
		}

		return $result;
	}

	/**
	 * @param array<string,mixed> $report
	 * @param array<string,mixed> $details
	 */
	private static function add_check( array &$report, string $id, string $gate, bool $passed, string $message, array $details = array() ): void {
		$report['checks'][] = array(
			'id'      => $id,
			'gate'    => $gate,
			'status'  => $passed ? 'PASS' : 'FAIL',
			'message' => $message,
			'details' => $details,
		);
	}

	/**
	 * @param array<string,mixed> $report
	 * @return array<string,mixed>
	 */
	private static function finalize_report( array $report, float $started_at ): array {
		$passes = 0;
		$fails  = 0;

		foreach ( $report['checks'] as $check ) {
			if ( 'PASS' === ( $check['status'] ?? '' ) ) {
				++$passes;
			} else {
				++$fails;
			}
		}

		$residual = isset( $report['cleanup']['residual_fixtures'] ) ? (int) $report['cleanup']['residual_fixtures'] : self::count_fixtures();
		if ( ! isset( $report['cleanup']['residual_fixtures'] ) ) {
			$report['cleanup']['residual_fixtures'] = $residual;
		}

		$report['summary'] = array(
			'pass'        => $passes,
			'fail'        => $fails,
			'overall'     => 0 === $fails && 0 === $residual ? 'PASS' : 'FAIL',
			'duration_ms' => (int) round( ( microtime( true ) - $started_at ) * 1000 ),
		);
		$report['limitations'] = array(
			'Este diagnóstico não substitui o browser acceptance G-110.',
			'O teste de nonce no JSON cobre a primitive vinculada ao post; o fluxo HTTP completo do handler deve ser confirmado no browser acceptance.',
			'Antes de package/release, remova BDC_KB_ENABLE_DIAGNOSTICS e o runner temporário do runtime.',
		);

		return $report;
	}
}
