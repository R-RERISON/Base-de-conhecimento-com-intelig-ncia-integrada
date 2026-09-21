<?php
/**
 * G-590 — Section Retrieval / Deep-Link environmental runner.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Section_Runner_G590 {

	public const ACTION = 'bdc_kb_spec005_g590_section';
	public const PAGE_SLUG = 'bdc-kb-spec005-g590-section';

	private const NONCE_ACTION = 'bdc_kb_spec005_g590_section';
	private const NONCE_FIELD = 'bdc_kb_spec005_g590_section_nonce';
	private const MAX_PROBES = 12;
	private const MIN_PROBES = 5;
	private const BENCHMARK_QUERY_CAP = 6;
	private const BENCHMARK_WARMUPS = 1;
	private const BENCHMARK_REPEATS = 5;
	private const PERF_P95_BUDGET_MS = 900.0;
	private const PERF_MAX_BUDGET_MS = 1500.0;
	private const JOB_VERSION = 'g590-resumable-v1.0.0';
	private const JOB_OPTION = 'bdc_kb_spec005_g590_job';
	private const JOB_LOCK_PREFIX = 'bdc_kb_g590_lock_';
	private const AJAX_START = 'bdc_kb_g590_start';
	private const AJAX_STEP = 'bdc_kb_g590_step';
	private const AJAX_STATUS = 'bdc_kb_g590_status';
	private const DOWNLOAD_ACTION = 'bdc_kb_g590_download';
	private const AJAX_NONCE_ACTION = 'bdc_kb_g590_ajax';
	private const SNAPSHOT_BATCH_SIZE = 50;
	private const COVERAGE_BATCH_SIZE = 25;

	/** @var array<int,string> */
	private const GOVERNED_META_KEYS = array(
		'_elementor_data',
		'_bdc_es_objective',
		'_bdc_es_responsible_team',
		'_bdc_es_catalog_item',
		'_bdc_es_affected_service',
		'_bdc_es_systems_involved',
		'_bdc_es_target_audience',
		'_bdc_es_escalation',
		'_bdc_es_important',
		'_bdc_es_helpful_tips',
		'_kb2ops_review_state',
		'_kb2ops_knowledge_type',
		'_kb2ops_technologies',
		'_kb2ops_review_notes',
		'_kb2ops_reviewed_at',
		'_kb2ops_reviewed_by',
		'_kb2ops_target_audience',
		'_kb2ops_service',
		'_kb2ops_keywords',
		'_kb2ops_versions',
		'_kb2ops_include_ai',
		'_kb2ops_review_history',
	);

	/** @var array<int,string> */
	private const ALLOWED_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 48 );
		add_action( 'wp_ajax_' . self::AJAX_START, array( self::class, 'ajax_start' ) );
		add_action( 'wp_ajax_' . self::AJAX_STEP, array( self::class, 'ajax_step' ) );
		add_action( 'wp_ajax_' . self::AJAX_STATUS, array( self::class, 'ajax_status' ) );
		add_action( 'admin_post_' . self::DOWNLOAD_ACTION, array( self::class, 'handle_download' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Section Retrieval G-590',
			'Section Retrieval G-590',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$job = self::load_job();
		$public_job = self::public_job_state( $job );
		$ajax_nonce = wp_create_nonce( self::AJAX_NONCE_ACTION );

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SPEC-005 — G-590 Section Retrieval & Deep-Link', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p>';
		echo esc_html__( 'Execução resumível: o gate é dividido em fases curtas para não depender do timeout HTTP do proxy/webserver. O rebuild continua explícito e o conteúdo editorial permanece read-only.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<div id="bdc-g590-runner" class="card" style="max-width:820px;padding:20px">';
		echo '<p><strong>' . esc_html__( 'Estado:', 'bdc-knowledge-base' ) . '</strong> <span data-bdc-g590-status>' . esc_html( (string) ( $public_job['status_label'] ?? 'Pronto para iniciar' ) ) . '</span></p>';
		echo '<p><strong>' . esc_html__( 'Fase:', 'bdc-knowledge-base' ) . '</strong> <span data-bdc-g590-phase>' . esc_html( (string) ( $public_job['phase_label'] ?? '—' ) ) . '</span></p>';
		echo '<progress data-bdc-g590-progress max="100" value="' . esc_attr( (string) ( $public_job['progress'] ?? 0 ) ) . '" style="width:100%;height:20px"></progress>';
		echo '<p data-bdc-g590-detail>' . esc_html( (string) ( $public_job['detail'] ?? 'Nenhuma execução ativa.' ) ) . '</p>';
		echo '<p>';
		echo '<button type="button" class="button button-primary" data-bdc-g590-start>' . esc_html__( 'Iniciar / Retomar G-590', 'bdc-knowledge-base' ) . '</button> ';
		echo '<button type="button" class="button" data-bdc-g590-restart>' . esc_html__( 'Reiniciar evidência', 'bdc-knowledge-base' ) . '</button> ';
		echo '<a class="button" data-bdc-g590-download href="' . esc_url( (string) ( $public_job['download_url'] ?? '' ) ) . '"' . ( empty( $public_job['download_url'] ) ? ' hidden' : '' ) . '>' . esc_html__( 'Baixar JSON final', 'bdc-knowledge-base' ) . '</a>';
		echo '</p>';
		echo '<div class="notice notice-error inline" data-bdc-g590-error style="display:none"><p></p></div>';
		echo '</div>';
		echo '</div>';

		wp_enqueue_script( 'jquery' );
		$config = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce' => $ajax_nonce,
			'actions' => array(
				'start' => self::AJAX_START,
				'step' => self::AJAX_STEP,
				'status' => self::AJAX_STATUS,
			),
			'initial' => $public_job,
		);
		$script = 'window.BDCG590=' . wp_json_encode( $config ) . ';' . self::browser_runner_script();
		wp_add_inline_script( 'jquery', $script, 'after' );
	}


	public static function handle_run(): void {
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) );
		exit;
	}



	public static function ajax_start(): void {
		self::authorize_ajax();
		$restart = isset( $_POST['restart'] ) && '1' === (string) wp_unslash( $_POST['restart'] );
		try {
			$job = self::start_job( $restart );
			wp_send_json_success( self::public_job_state( $job ) );
		} catch ( \Throwable $error ) {
			wp_send_json_error(
				array( 'message' => 'Falha ao iniciar G-590: ' . $error->getMessage() ),
				500
			);
		}
	}

	public static function ajax_status(): void {
		self::authorize_ajax();
		wp_send_json_success( self::public_job_state( self::load_job() ) );
	}

	public static function ajax_step(): void {
		self::authorize_ajax();
		$job_id = isset( $_POST['job_id'] ) && is_scalar( $_POST['job_id'] )
			? sanitize_text_field( wp_unslash( (string) $_POST['job_id'] ) )
			: '';
		$job = self::load_job();

		if ( '' === $job_id || $job_id !== (string) ( $job['job_id'] ?? '' ) ) {
			wp_send_json_error( array( 'message' => 'Job G-590 inexistente ou divergente.' ), 409 );
		}
		if ( 'running' !== (string) ( $job['status'] ?? '' ) ) {
			wp_send_json_success( self::public_job_state( $job ) );
		}

		$lock = self::JOB_LOCK_PREFIX . md5( $job_id );
		if ( get_transient( $lock ) ) {
			$public = self::public_job_state( $job );
			$public['busy'] = true;
			$public['detail'] = 'Fase em processamento no servidor. Aguardando conclusão sem iniciar execução concorrente.';
			wp_send_json_success( $public );
		}

		set_transient( $lock, (string) time(), 2 * MINUTE_IN_SECONDS );
		try {
			$job = self::step_job( $job );
			self::save_job( $job );
		} catch ( \Throwable $error ) {
			$job['status'] = 'failed';
			$job['phase'] = 'failed';
			$job['updated_at'] = gmdate( 'c' );
			$job['job_error'] = array(
				'class' => get_class( $error ),
				'code' => (string) $error->getCode(),
				'message' => $error->getMessage(),
			);
			self::save_job( $job );
		} finally {
			delete_transient( $lock );
		}

		wp_send_json_success( self::public_job_state( $job ) );
	}

	public static function handle_download(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$job_id = isset( $_GET['job_id'] ) && is_scalar( $_GET['job_id'] )
			? sanitize_text_field( wp_unslash( (string) $_GET['job_id'] ) )
			: '';
		$nonce = isset( $_GET['_wpnonce'] ) && is_scalar( $_GET['_wpnonce'] )
			? wp_unslash( (string) $_GET['_wpnonce'] )
			: '';

		if ( '' === $job_id || ! wp_verify_nonce( $nonce, self::DOWNLOAD_ACTION . '_' . $job_id ) ) {
			wp_die( esc_html__( 'Nonce inválido ou expirado.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}

		$job = self::load_job();
		if (
			$job_id !== (string) ( $job['job_id'] ?? '' )
			|| 'complete' !== (string) ( $job['status'] ?? '' )
			|| ! is_array( $job['report'] ?? null )
		) {
			wp_die( esc_html__( 'Relatório G-590 ainda não está disponível.', 'bdc-knowledge-base' ), '', array( 'response' => 409 ) );
		}

		$json = wp_json_encode( $job['report'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-g590-section-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	private static function authorize_ajax(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permissão insuficiente.' ), 403 );
		}
		check_ajax_referer( self::AJAX_NONCE_ACTION, 'nonce' );
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_send_json_error( array( 'message' => 'Método HTTP não permitido.' ), 405 );
		}
	}

	/** @return array<string,mixed> */
	private static function start_job( bool $restart ): array {
		$existing = self::load_job();
		if (
			! $restart
			&& self::JOB_VERSION === (string) ( $existing['job_version'] ?? '' )
			&& in_array( (string) ( $existing['status'] ?? '' ), array( 'running', 'complete' ), true )
		) {
			return $existing;
		}

		$post_ids = self::corpus_ids();
		if ( empty( $post_ids ) ) {
			throw new \RuntimeException( 'Corpus G-590 vazio.' );
		}

		$state_before = Search_Projection_Repository::state();
		$job = array(
			'job_version' => self::JOB_VERSION,
			'job_id' => wp_generate_uuid4(),
			'status' => 'running',
			'phase' => 'editorial_before',
			'cursor' => 0,
			'started_at' => gmdate( 'c' ),
			'started_unix' => microtime( true ),
			'updated_at' => gmdate( 'c' ),
			'plugin_version' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
			'build_id' => defined( 'BDC_KB_BUILD_ID' ) ? BDC_KB_BUILD_ID : '',
			'post_ids' => $post_ids,
			'post_ids_after' => array(),
			'editorial_before' => array(),
			'editorial_after' => array(),
			'state_before' => $state_before,
			'schema_before' => Search_Projection_Repository::schema_exists(),
			'rows_before' => self::row_count(),
			'projection_snapshot_before' => self::projection_snapshot_hash(),
			'state_versions_before_current' => self::state_versions_current( $state_before ),
			'lifecycle' => array(),
			'rebuild' => array(),
			'coverage_accumulator' => self::coverage_accumulator_empty(),
			'coverage' => array(),
			'probes' => array(),
			'performance' => array(),
			'golden' => array(),
			'errors' => array(),
			'throwables' => array(),
			'report' => array(),
		);
		self::save_job( $job );
		return $job;
	}

	/** @param array<string,mixed> $job @return array<string,mixed> */
	private static function step_job( array $job ): array {
		$phase = (string) ( $job['phase'] ?? '' );
		$post_ids = array_values( array_map( 'intval', (array) ( $job['post_ids'] ?? array() ) ) );

		switch ( $phase ) {
			case 'editorial_before':
				$cursor = max( 0, (int) ( $job['cursor'] ?? 0 ) );
				$batch = array_slice( $post_ids, $cursor, self::SNAPSHOT_BATCH_SIZE );
				$job['editorial_before'] = array_replace(
					(array) ( $job['editorial_before'] ?? array() ),
					self::editorial_snapshot( $batch )
				);
				$job['cursor'] = $cursor + count( $batch );
				if ( (int) $job['cursor'] >= count( $post_ids ) ) {
					$job['phase'] = 'preflight_rebuild';
					$job['cursor'] = 0;
				}
				break;

			case 'preflight_rebuild':
				$state_before = is_array( $job['state_before'] ?? null ) ? $job['state_before'] : array();
				$lifecycle = Search_Lifecycle::prepare_schema();
				$state_after_prepare = Search_Projection_Repository::state();
				$schema_contract = self::schema_contract();
				$rows_after_prepare = self::row_count();
				$snapshot_after_prepare = self::projection_snapshot_hash();
				$schema_before = ! empty( $job['schema_before'] );
				$rows_before = $job['rows_before'] ?? null;
				$snapshot_before = (string) ( $job['projection_snapshot_before'] ?? '' );

				$prepare_did_not_reindex = $schema_before
					? $rows_before === $rows_after_prepare
						&& $snapshot_before === $snapshot_after_prepare
						&& false === (bool) ( $lifecycle['implicit_rebuild'] ?? true )
					: 0 === (int) $rows_after_prepare
						&& false === (bool) ( $lifecycle['implicit_rebuild'] ?? true );

				$state_was_empty = empty( $state_before );
				$state_versions_before_current = ! empty( $job['state_versions_before_current'] );
				$version_transition_safe = $state_versions_before_current
					|| ( $state_was_empty
						&& 'not_built' === (string) ( $state_after_prepare['status'] ?? '' )
						&& self::state_versions_current( $state_after_prepare ) )
					|| ( ! $state_was_empty
						&& ! $state_versions_before_current
						&& 'degraded' === (string) ( $state_after_prepare['status'] ?? '' )
						&& self::state_versions_current( $state_after_prepare ) );

				$rebuild = array();
				try {
					$rebuild = Search_Rebuild_Service::rebuild();
				} catch ( \Throwable $error ) {
					$job['throwables'][] = self::throwable_row( 'explicit_rebuild', $error );
				}

				$job['lifecycle'] = array(
					'state_after_prepare' => $state_after_prepare,
					'prepare_schema' => $lifecycle,
					'schema_contract' => $schema_contract,
					'rows_after_prepare' => $rows_after_prepare,
					'projection_snapshot_after_prepare' => $snapshot_after_prepare,
					'prepare_did_not_reindex' => $prepare_did_not_reindex,
					'version_transition_safe' => $version_transition_safe,
				);
				$job['rebuild'] = $rebuild;
				$job['phase'] = 'coverage';
				$job['cursor'] = 0;
				break;

			case 'coverage':
				$cursor = max( 0, (int) ( $job['cursor'] ?? 0 ) );
				$batch = array_slice( $post_ids, $cursor, self::COVERAGE_BATCH_SIZE );
				$accumulator = is_array( $job['coverage_accumulator'] ?? null )
					? $job['coverage_accumulator']
					: self::coverage_accumulator_empty();
				$job['coverage_accumulator'] = self::coverage_accumulate( $accumulator, $batch );
				$job['cursor'] = $cursor + count( $batch );
				if ( (int) $job['cursor'] >= count( $post_ids ) ) {
					$job['coverage'] = self::coverage_finalize(
						(array) $job['coverage_accumulator'],
						count( $post_ids )
					);
					unset( $job['coverage_accumulator'] );
					$job['phase'] = 'probes';
					$job['cursor'] = 0;
				}
				break;

			case 'probes':
				$coverage = is_array( $job['coverage'] ?? null ) ? $job['coverage'] : array();
				$job['probes'] = self::section_and_anchor_probes( (array) ( $coverage['probe_candidates'] ?? array() ) );
				$job['phase'] = 'performance';
				break;

			case 'performance':
				$coverage = is_array( $job['coverage'] ?? null ) ? $job['coverage'] : array();
				$job['performance'] = self::performance_benchmark( (array) ( $coverage['probe_candidates'] ?? array() ) );
				$job['phase'] = 'golden';
				break;

			case 'golden':
				try {
					$job['golden'] = Golden_Gate_Runner_G550::run();
				} catch ( \Throwable $error ) {
					$job['throwables'][] = self::throwable_row( 'golden_regression', $error );
					$job['golden'] = array();
				}
				$job['post_ids_after'] = self::corpus_ids();
				$job['phase'] = 'editorial_after';
				$job['cursor'] = 0;
				break;

			case 'editorial_after':
				$post_ids_after = array_values( array_map( 'intval', (array) ( $job['post_ids_after'] ?? array() ) ) );
				$cursor = max( 0, (int) ( $job['cursor'] ?? 0 ) );
				$batch = array_slice( $post_ids_after, $cursor, self::SNAPSHOT_BATCH_SIZE );
				$job['editorial_after'] = array_replace(
					(array) ( $job['editorial_after'] ?? array() ),
					self::editorial_snapshot( $batch )
				);
				$job['cursor'] = $cursor + count( $batch );
				if ( (int) $job['cursor'] >= count( $post_ids_after ) ) {
					$job['phase'] = 'finalize';
					$job['cursor'] = 0;
				}
				break;

			case 'finalize':
				$job['report'] = self::assemble_job_report( $job );
				$job['status'] = 'complete';
				$job['phase'] = 'complete';
				break;

			default:
				throw new \RuntimeException( 'Fase G-590 inválida: ' . $phase );
		}

		$job['updated_at'] = gmdate( 'c' );
		$job['peak_memory_bytes'] = max( (int) ( $job['peak_memory_bytes'] ?? 0 ), memory_get_peak_usage( true ) );
		return $job;
	}

	/** @return array<string,mixed> */
	private static function load_job(): array {
		$job = get_option( self::JOB_OPTION, array() );
		return is_array( $job ) ? $job : array();
	}

	/** @param array<string,mixed> $job */
	private static function save_job( array $job ): void {
		update_option( self::JOB_OPTION, $job, false );
	}

	/** @param array<string,mixed> $job @return array<string,mixed> */
	private static function public_job_state( array $job ): array {
		if ( empty( $job ) || self::JOB_VERSION !== (string) ( $job['job_version'] ?? '' ) ) {
			return array(
				'job_id' => '',
				'status' => 'idle',
				'status_label' => 'Pronto para iniciar',
				'phase' => 'idle',
				'phase_label' => '—',
				'progress' => 0,
				'detail' => 'Nenhuma execução resumível ativa.',
				'download_url' => '',
				'busy' => false,
			);
		}

		$status = (string) ( $job['status'] ?? 'idle' );
		$phase = (string) ( $job['phase'] ?? 'idle' );
		$labels = array(
			'editorial_before' => 'Fingerprint editorial — antes',
			'preflight_rebuild' => 'Lifecycle + rebuild explícito',
			'coverage' => 'Coverage estrutural do corpus',
			'probes' => 'Section/deep-link probes',
			'performance' => 'Benchmark',
			'golden' => 'Golden regression',
			'editorial_after' => 'Fingerprint editorial — depois',
			'finalize' => 'Consolidação da evidência',
			'complete' => 'Concluído',
			'failed' => 'Falha operacional',
		);
		$status_labels = array(
			'running' => 'Em execução',
			'complete' => 'Concluído — JSON disponível',
			'failed' => 'Falha operacional',
		);

		$download_url = '';
		if ( 'complete' === $status && ! empty( $job['report'] ) ) {
			$job_id = (string) ( $job['job_id'] ?? '' );
			$download_url = wp_nonce_url(
				admin_url( 'admin-post.php?action=' . self::DOWNLOAD_ACTION . '&job_id=' . rawurlencode( $job_id ) ),
				self::DOWNLOAD_ACTION . '_' . $job_id
			);
		}

		$detail = self::job_detail( $job );
		if ( 'failed' === $status && is_array( $job['job_error'] ?? null ) ) {
			$detail = 'Falha operacional: ' . (string) ( $job['job_error']['message'] ?? 'erro não identificado' );
		}

		return array(
			'job_id' => (string) ( $job['job_id'] ?? '' ),
			'status' => $status,
			'status_label' => (string) ( $status_labels[ $status ] ?? $status ),
			'phase' => $phase,
			'phase_label' => (string) ( $labels[ $phase ] ?? $phase ),
			'progress' => self::job_progress( $job ),
			'detail' => $detail,
			'download_url' => $download_url,
			'busy' => false,
		);
	}

	/** @param array<string,mixed> $job */
	private static function job_progress( array $job ): int {
		$phase = (string) ( $job['phase'] ?? '' );
		$cursor = max( 0, (int) ( $job['cursor'] ?? 0 ) );
		$total = max( 1, count( (array) ( $job['post_ids'] ?? array() ) ) );
		$after_total = max( 1, count( (array) ( $job['post_ids_after'] ?? array() ) ) );

		return match ( $phase ) {
			'editorial_before' => min( 15, (int) floor( 15 * $cursor / $total ) ),
			'preflight_rebuild' => 15,
			'coverage' => 45 + min( 30, (int) floor( 30 * $cursor / $total ) ),
			'probes' => 75,
			'performance' => 80,
			'golden' => 85,
			'editorial_after' => 90 + min( 9, (int) floor( 9 * $cursor / $after_total ) ),
			'finalize' => 99,
			'complete' => 100,
			'failed' => min( 99, max( 1, (int) ( $job['progress_at_failure'] ?? 1 ) ) ),
			default => 0,
		};
	}

	/** @param array<string,mixed> $job */
	private static function job_detail( array $job ): string {
		$phase = (string) ( $job['phase'] ?? '' );
		$cursor = max( 0, (int) ( $job['cursor'] ?? 0 ) );
		$total = count( (array) ( $job['post_ids'] ?? array() ) );

		if ( in_array( $phase, array( 'editorial_before', 'coverage' ), true ) ) {
			return sprintf( '%d de %d posts processados nesta fase.', min( $cursor, $total ), $total );
		}
		if ( 'editorial_after' === $phase ) {
			$after_total = count( (array) ( $job['post_ids_after'] ?? array() ) );
			return sprintf( '%d de %d posts processados nesta fase.', min( $cursor, $after_total ), $after_total );
		}
		if ( 'preflight_rebuild' === $phase ) {
			return 'Executando lifecycle e rebuild explícito já homologado no G-580.';
		}
		if ( 'complete' === $phase ) {
			return 'Evidência consolidada. O resultado do gate está dentro do JSON; conclusão operacional não implica PASS funcional.';
		}
		return 'Executando fase isolada. A página pode ser recarregada e a execução será retomada.';
	}

	private static function browser_runner_script(): string {
		return <<<'JS'
(function(){
	'use strict';
	var cfg=window.BDCG590||{};
	var root=document.getElementById('bdc-g590-runner');
	if(!root){return;}
	var start=root.querySelector('[data-bdc-g590-start]');
	var restart=root.querySelector('[data-bdc-g590-restart]');
	var statusEl=root.querySelector('[data-bdc-g590-status]');
	var phaseEl=root.querySelector('[data-bdc-g590-phase]');
	var progressEl=root.querySelector('[data-bdc-g590-progress]');
	var detailEl=root.querySelector('[data-bdc-g590-detail]');
	var download=root.querySelector('[data-bdc-g590-download]');
	var errorBox=root.querySelector('[data-bdc-g590-error]');
	var running=false;

	function render(s){
		if(!s){return;}
		statusEl.textContent=s.status_label||s.status||'';
		phaseEl.textContent=s.phase_label||s.phase||'';
		progressEl.value=Number(s.progress||0);
		detailEl.textContent=s.detail||'';
		if(s.download_url){download.href=s.download_url;download.hidden=false;}else{download.hidden=true;}
		start.disabled=running||s.status==='complete';
		restart.disabled=running;
	}

	function showError(message){
		errorBox.style.display='block';
		errorBox.querySelector('p').textContent=message;
	}
	function clearError(){errorBox.style.display='none';errorBox.querySelector('p').textContent='';}
	function delay(ms){return new Promise(function(resolve){window.setTimeout(resolve,ms);});}

	async function request(action,extra){
		var body=new URLSearchParams();
		body.set('action',action);
		body.set('nonce',cfg.nonce||'');
		Object.keys(extra||{}).forEach(function(key){body.set(key,String(extra[key]));});
		var response=await fetch(cfg.ajaxUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:body.toString()});
		var payload;
		try{payload=await response.json();}catch(e){throw new Error('Resposta HTTP não-JSON ('+response.status+').');}
		if(!response.ok||!payload||payload.success!==true){
			var msg=payload&&payload.data&&payload.data.message?payload.data.message:'Falha HTTP '+response.status;
			throw new Error(msg);
		}
		return payload.data||{};
	}

	async function recover(jobId){
		try{
			var s=await request(cfg.actions.status,{job_id:jobId||''});
			render(s);
			if(s.status==='running'){await delay(1200);return cycle(s.job_id);}
			return s;
		}catch(e){showError('Conexão interrompida. A execução é resumível; use “Iniciar / Retomar G-590”. '+e.message);running=false;render(cfg.initial||{});return null;}
	}

	async function cycle(jobId){
		running=true;clearError();
		try{
			while(true){
				var s=await request(cfg.actions.step,{job_id:jobId});
				render(s);
				if(s.status!=='running'){running=false;render(s);return s;}
				if(s.busy){await delay(1000);}else{await delay(120);}
			}
		}catch(e){
			showError('A requisição atual não concluiu no navegador. Consultando estado persistido para retomar sem duplicar trabalho. '+e.message);
			return recover(jobId);
		}
	}

	async function begin(forceRestart){
		if(running){return;}
		running=true;clearError();render(cfg.initial||{});
		try{
			var s=await request(cfg.actions.start,{restart:forceRestart?'1':'0'});
			cfg.initial=s;render(s);
			if(s.status==='running'){return cycle(s.job_id);}
			running=false;render(s);return s;
		}catch(e){running=false;showError(e.message);render(cfg.initial||{});return null;}
	}

	start.addEventListener('click',function(){begin(false);});
	restart.addEventListener('click',function(){begin(true);});
	render(cfg.initial||{});
})();
JS;
	}



	/** @param array<string,mixed> $job @return array<string,mixed> */
	private static function assemble_job_report( array $job ): array {
		$post_ids = array_values( array_map( 'intval', (array) ( $job['post_ids'] ?? array() ) ) );
		$post_ids_after = array_values( array_map( 'intval', (array) ( $job['post_ids_after'] ?? array() ) ) );
		$editorial_before = (array) ( $job['editorial_before'] ?? array() );
		$editorial_after = (array) ( $job['editorial_after'] ?? array() );
		ksort( $editorial_before, SORT_NUMERIC );
		ksort( $editorial_after, SORT_NUMERIC );

		$editorial_fingerprint_before = Canonical_JSON::hash( $editorial_before );
		$editorial_fingerprint_after = Canonical_JSON::hash( $editorial_after );
		$editorial_equal = hash_equals( $editorial_fingerprint_before, $editorial_fingerprint_after )
			&& $post_ids === $post_ids_after;

		$lifecycle_data = is_array( $job['lifecycle'] ?? null ) ? $job['lifecycle'] : array();
		$rebuild = is_array( $job['rebuild'] ?? null ) ? $job['rebuild'] : array();
		$coverage = is_array( $job['coverage'] ?? null ) ? $job['coverage'] : array();
		$probes = is_array( $job['probes'] ?? null ) ? $job['probes'] : array();
		$performance = is_array( $job['performance'] ?? null ) ? $job['performance'] : array();
		$golden = is_array( $job['golden'] ?? null ) ? $job['golden'] : array();
		$errors = (array) ( $job['errors'] ?? array() );
		$throwables = (array) ( $job['throwables'] ?? array() );

		$rebuild_state_after = is_array( $rebuild['state_after'] ?? null ) ? $rebuild['state_after'] : array();
		$rebuild_determinism = is_array( $rebuild['determinism'] ?? null ) ? $rebuild['determinism'] : array();
		$rebuild_pass = 'PASS' === (string) ( $rebuild['status'] ?? '' )
			&& 'ready' === (string) ( $rebuild_state_after['status'] ?? '' )
			&& 0 === (int) ( $rebuild_determinism['mismatch_count'] ?? -1 );

		$golden_pass = 'PASS' === (string) ( $golden['status'] ?? '' )
			&& 0 === (int) ( $golden['blocking_failed'] ?? -1 )
			&& 0 === (int) ( $golden['technical_failed'] ?? -1 )
			&& empty( $golden['technical_errors'] ?? array() );

		$coverage_complete = count( $post_ids ) === (int) ( $coverage['posts_analyzed'] ?? -1 )
			&& 0 === (int) ( $coverage['extractor_error_count'] ?? -1 );
		$no_uncontextual_numbered_gap = 0 === (int) ( $coverage['numbered_without_heading_context'] ?? -1 );
		$probe_pass = (int) ( $probes['eligible_probe_count'] ?? 0 ) >= self::MIN_PROBES
			&& empty( $coverage['unprobed_source_kinds'] ?? array() )
			&& 0 === (int) ( $probes['section_query_failed'] ?? -1 )
			&& 0 === (int) ( $probes['deep_link_failed'] ?? -1 )
			&& 0 === (int) ( $probes['visible_text_changed'] ?? -1 );

		$source_safety = self::runtime_source_safety();
		$schema_contract = is_array( $lifecycle_data['schema_contract'] ?? null ) ? $lifecycle_data['schema_contract'] : array();
		$prepare_did_not_reindex = ! empty( $lifecycle_data['prepare_did_not_reindex'] );
		$version_transition_safe = ! empty( $lifecycle_data['version_transition_safe'] );

		$t59014 = $golden_pass
			&& ! empty( $source_safety['parent_ranker_version_frozen'] )
			&& ! empty( $source_safety['candidate_query_does_not_load_sections'] );
		$t59015 = $coverage_complete && $no_uncontextual_numbered_gap;
		$t59016 = $probe_pass;
		$t59017 = ! empty( $schema_contract['pass'] )
			&& $prepare_did_not_reindex
			&& $version_transition_safe
			&& $rebuild_pass;
		$t59018 = $editorial_equal
			&& ! empty( $performance['pass'] )
			&& ! empty( $source_safety['no_editorial_write'] )
			&& ! empty( $source_safety['no_network'] )
			&& ! empty( $source_safety['no_asi'] );
		$g590_pass = $t59014 && $t59015 && $t59016 && $t59017 && $t59018
			&& empty( $errors )
			&& empty( $throwables );

		$public_coverage = $coverage;
		unset( $public_coverage['probe_candidates'] );

		$started_unix = (float) ( $job['started_unix'] ?? microtime( true ) );
		return array(
			'schema_version' => '1.1.0',
			'gate' => 'G-590',
			'mode' => 'spec005_section_retrieval_deeplink_environmental',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : (string) ( $job['plugin_version'] ?? '' ),
				'build_id' => defined( 'BDC_KB_BUILD_ID' ) ? BDC_KB_BUILD_ID : (string) ( $job['build_id'] ?? '' ),
				'multisite' => is_multisite(),
				'db_server' => self::db_version(),
			),
			'contracts' => array(
				'section' => 'g590-section-retrieval-contract-v1.md',
				'deep_link' => 'g590-deep-link-contract-v1.md',
				'regression' => 'g590-regression-contract-v1.md',
				'g550_addendum' => 'g550-addendum-document-v1.1-compatibility.md',
				'orchestration' => self::JOB_VERSION,
			),
			'lifecycle' => array(
				'state_before' => is_array( $job['state_before'] ?? null ) ? $job['state_before'] : array(),
				'schema_before' => ! empty( $job['schema_before'] ),
				'rows_before' => $job['rows_before'] ?? null,
				'projection_snapshot_before' => (string) ( $job['projection_snapshot_before'] ?? '' ),
				'prepare_schema' => is_array( $lifecycle_data['prepare_schema'] ?? null ) ? $lifecycle_data['prepare_schema'] : array(),
				'state_versions_before_current' => ! empty( $job['state_versions_before_current'] ),
				'state_after_prepare' => is_array( $lifecycle_data['state_after_prepare'] ?? null ) ? $lifecycle_data['state_after_prepare'] : array(),
				'version_transition_safe' => $version_transition_safe,
				'schema_contract' => $schema_contract,
				'rows_after_prepare' => $lifecycle_data['rows_after_prepare'] ?? null,
				'projection_snapshot_after_prepare' => (string) ( $lifecycle_data['projection_snapshot_after_prepare'] ?? '' ),
				'prepare_did_not_reindex' => $prepare_did_not_reindex,
				'explicit_rebuild' => $rebuild,
			),
			'coverage' => $public_coverage,
			'section_deep_link_probes' => $probes,
			'performance' => $performance,
			'post_level_golden_regression' => $golden,
			'safety' => array(
				'editorial_fingerprint_before' => $editorial_fingerprint_before,
				'editorial_fingerprint_after' => $editorial_fingerprint_after,
				'editorial_fingerprint_equal' => $editorial_equal,
				'corpus_ids_equal' => $post_ids === $post_ids_after,
				'runtime_source' => $source_safety,
			),
			'errors' => $errors,
			'throwables' => $throwables,
			'runner' => array(
				'execution_model' => 'resumable_ajax_v1',
				'job_version' => self::JOB_VERSION,
				'job_id' => (string) ( $job['job_id'] ?? '' ),
				'snapshot_batch_size' => self::SNAPSHOT_BATCH_SIZE,
				'coverage_batch_size' => self::COVERAGE_BATCH_SIZE,
				'total_runtime_ms' => round( max( 0.0, microtime( true ) - $started_unix ) * 1000, 4 ),
				'peak_memory_bytes' => (int) ( $job['peak_memory_bytes'] ?? memory_get_peak_usage( true ) ),
			),
			'gate_result' => array(
				't59014_cross_spec_regression_pass' => $t59014,
				't59015_coverage_audit_pass' => $t59015,
				't59016_section_golden_deeplink_pass' => $t59016,
				't59017_lifecycle_schema_pass' => $t59017,
				't59018_security_performance_safety_pass' => $t59018,
				't59019_g590_pass' => $g590_pass,
				'next_gate' => $g590_pass ? 'G-585' : 'G-590',
			),
			'interpretation_rules' => array(
				'Section Projection não pode alterar o ranking post-level fechado.',
				'Número hierárquico forte sem heading contextual é blocker por potencial perda de navegabilidade.',
				'Número hierárquico dentro de heading é reportado para revisão de granularidade, mas não é auto-falha.',
				'Deep-link só passa quando o anchor é materializável e o texto visível permanece idêntico.',
				'O runner ambiental é resumível; timeout HTTP do navegador/proxy não equivale a falha funcional do gate.',
				'G-590 PASS ainda não autoriza aposentadoria do ASI; G-585 e Master Parity Ledger continuam obrigatórios.',
			),
		);
	}

	/** @return array<string,mixed> */
	public static function run(): array {
		$started = microtime( true );
		$errors = array();
		$throwables = array();
		$post_ids = self::corpus_ids();
		$editorial_before = self::editorial_snapshot( $post_ids );
		$editorial_fingerprint_before = Canonical_JSON::hash( $editorial_before );

		$state_before = Search_Projection_Repository::state();
		$schema_before = Search_Projection_Repository::schema_exists();
		$rows_before = self::row_count();
		$snapshot_before = self::projection_snapshot_hash();

		$state_versions_before_current = self::state_versions_current( $state_before );
		$lifecycle = Search_Lifecycle::prepare_schema();
		$state_after_prepare = Search_Projection_Repository::state();
		$schema_contract = self::schema_contract();
		$rows_after_prepare = self::row_count();
		$snapshot_after_prepare = self::projection_snapshot_hash();

		$prepare_did_not_reindex = $schema_before
			? $rows_before === $rows_after_prepare
				&& $snapshot_before === $snapshot_after_prepare
				&& false === (bool) ( $lifecycle['implicit_rebuild'] ?? true )
			: 0 === (int) $rows_after_prepare
				&& false === (bool) ( $lifecycle['implicit_rebuild'] ?? true );

		$state_was_empty = empty( $state_before );
		$version_transition_safe = $state_versions_before_current
			|| ( $state_was_empty
				&& 'not_built' === (string) ( $state_after_prepare['status'] ?? '' )
				&& self::state_versions_current( $state_after_prepare ) )
			|| ( ! $state_was_empty
				&& ! $state_versions_before_current
				&& 'degraded' === (string) ( $state_after_prepare['status'] ?? '' )
				&& self::state_versions_current( $state_after_prepare ) );

		$rebuild = array();
		try {
			$rebuild = Search_Rebuild_Service::rebuild();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'explicit_rebuild', $error );
		}

		$coverage = self::coverage_audit( $post_ids );
		$probe_candidates = (array) ( $coverage['probe_candidates'] ?? array() );
		$probes = self::section_and_anchor_probes( $probe_candidates );
		$performance = self::performance_benchmark( $probe_candidates );

		$golden = array();
		try {
			$golden = Golden_Gate_Runner_G550::run();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'golden_regression', $error );
		}

		$editorial_after = self::editorial_snapshot( self::corpus_ids() );
		$editorial_fingerprint_after = Canonical_JSON::hash( $editorial_after );
		$editorial_equal = hash_equals( $editorial_fingerprint_before, $editorial_fingerprint_after );

		$rebuild_state_after = is_array( $rebuild['state_after'] ?? null ) ? $rebuild['state_after'] : array();
		$rebuild_determinism = is_array( $rebuild['determinism'] ?? null ) ? $rebuild['determinism'] : array();

		$rebuild_pass = 'PASS' === (string) ( $rebuild['status'] ?? '' )
			&& 'ready' === (string) ( $rebuild_state_after['status'] ?? '' )
			&& 0 === (int) ( $rebuild_determinism['mismatch_count'] ?? -1 );

		$golden_pass = 'PASS' === (string) ( $golden['status'] ?? '' )
			&& 0 === (int) ( $golden['blocking_failed'] ?? -1 )
			&& 0 === (int) ( $golden['technical_failed'] ?? -1 )
			&& empty( $golden['technical_errors'] ?? array() );

		$coverage_complete = count( $post_ids ) === (int) ( $coverage['posts_analyzed'] ?? -1 )
			&& 0 === (int) ( $coverage['extractor_error_count'] ?? -1 );

		$no_uncontextual_numbered_gap = 0 === (int) ( $coverage['numbered_without_heading_context'] ?? -1 );

		$probe_pass = (int) ( $probes['eligible_probe_count'] ?? 0 ) >= self::MIN_PROBES
			&& empty( $coverage['unprobed_source_kinds'] ?? array() )
			&& 0 === (int) ( $probes['section_query_failed'] ?? -1 )
			&& 0 === (int) ( $probes['deep_link_failed'] ?? -1 )
			&& 0 === (int) ( $probes['visible_text_changed'] ?? -1 );

		$source_safety = self::runtime_source_safety();

		$t59014 = $golden_pass
			&& ! empty( $source_safety['parent_ranker_version_frozen'] )
			&& ! empty( $source_safety['candidate_query_does_not_load_sections'] );

		$t59015 = $coverage_complete && $no_uncontextual_numbered_gap;
		$t59016 = $probe_pass;
		$t59017 = ! empty( $schema_contract['pass'] )
			&& $prepare_did_not_reindex
			&& $version_transition_safe
			&& $rebuild_pass;
		$t59018 = $editorial_equal
			&& ! empty( $performance['pass'] )
			&& ! empty( $source_safety['no_editorial_write'] )
			&& ! empty( $source_safety['no_network'] )
			&& ! empty( $source_safety['no_asi'] );

		$g590_pass = $t59014 && $t59015 && $t59016 && $t59017 && $t59018
			&& empty( $errors )
			&& empty( $throwables );

		unset( $coverage['probe_candidates'] );

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'G-590',
			'mode' => 'spec005_section_retrieval_deeplink_environmental',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'build_id' => defined( 'BDC_KB_BUILD_ID' ) ? BDC_KB_BUILD_ID : '',
				'multisite' => is_multisite(),
				'db_server' => self::db_version(),
			),
			'contracts' => array(
				'section' => 'g590-section-retrieval-contract-v1.md',
				'deep_link' => 'g590-deep-link-contract-v1.md',
				'regression' => 'g590-regression-contract-v1.md',
				'g550_addendum' => 'g550-addendum-document-v1.1-compatibility.md',
			),
			'lifecycle' => array(
				'state_before' => $state_before,
				'schema_before' => $schema_before,
				'rows_before' => $rows_before,
				'projection_snapshot_before' => $snapshot_before,
				'prepare_schema' => $lifecycle,
				'state_versions_before_current' => $state_versions_before_current,
				'state_after_prepare' => $state_after_prepare,
				'version_transition_safe' => $version_transition_safe,
				'schema_contract' => $schema_contract,
				'rows_after_prepare' => $rows_after_prepare,
				'projection_snapshot_after_prepare' => $snapshot_after_prepare,
				'prepare_did_not_reindex' => $prepare_did_not_reindex,
				'explicit_rebuild' => $rebuild,
			),
			'coverage' => $coverage,
			'section_deep_link_probes' => $probes,
			'performance' => $performance,
			'post_level_golden_regression' => $golden,
			'safety' => array(
				'editorial_fingerprint_before' => $editorial_fingerprint_before,
				'editorial_fingerprint_after' => $editorial_fingerprint_after,
				'editorial_fingerprint_equal' => $editorial_equal,
				'runtime_source' => $source_safety,
			),
			'errors' => $errors,
			'throwables' => $throwables,
			'runner' => array(
				'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_result' => array(
				't59014_cross_spec_regression_pass' => $t59014,
				't59015_coverage_audit_pass' => $t59015,
				't59016_section_golden_deeplink_pass' => $t59016,
				't59017_lifecycle_schema_pass' => $t59017,
				't59018_security_performance_safety_pass' => $t59018,
				't59019_g590_pass' => $g590_pass,
				'next_gate' => $g590_pass ? 'G-585' : 'G-590',
			),
			'interpretation_rules' => array(
				'Section Projection não pode alterar o ranking post-level fechado.',
				'Número hierárquico forte sem heading contextual é blocker por potencial perda de navegabilidade.',
				'Número hierárquico dentro de heading é reportado para revisão de granularidade, mas não é auto-falha.',
				'Deep-link só passa quando o anchor é materializável e o texto visível permanece idêntico.',
				'G-590 PASS ainda não autoriza aposentadoria do ASI; G-585 e Master Parity Ledger continuam obrigatórios.',
			),
		);
	}


	/** @return array<string,mixed> */
	private static function coverage_accumulator_empty(): array {
		return array(
			'posts_analyzed' => 0,
			'extractor_error_count' => 0,
			'extractor_errors' => array(),
			'source_kinds' => array(),
			'generated_by_source_kind' => array(),
			'section_count' => 0,
			'generated_anchor_count' => 0,
			'unresolved_anchor_count' => 0,
			'posts_without_sections' => 0,
			'heading_levels' => array_fill( 1, 6, 0 ),
			'numbered_non_heading_nodes' => 0,
			'numbered_with_heading_context' => 0,
			'numbered_without_heading_context' => 0,
			'title_frequency' => array(),
			'token_frequency' => array(),
			'candidates' => array(),
		);
	}

	/**
	 * @param array<string,mixed> $accumulator
	 * @param array<int,int>      $post_ids
	 * @return array<string,mixed>
	 */
	private static function coverage_accumulate( array $accumulator, array $post_ids ): array {
		foreach ( $post_ids as $post_id ) {
			$post_id = (int) $post_id;
			$extraction = Content_Extractor::extract( $post_id );
			if ( $extraction instanceof \WP_Error ) {
				++$accumulator['extractor_error_count'];
				if ( count( (array) $accumulator['extractor_errors'] ) < 50 ) {
					$accumulator['extractor_errors'][] = array(
						'post_id' => $post_id,
						'code' => $extraction->get_error_code(),
					);
				}
				continue;
			}

			++$accumulator['posts_analyzed'];
			$post_status = (string) get_post_status( $post_id );
			$source_kind = sanitize_key( (string) ( $extraction['source_kind'] ?? 'unknown' ) );
			$accumulator['source_kinds'][ $source_kind ] = (int) ( $accumulator['source_kinds'][ $source_kind ] ?? 0 ) + 1;
			$fragments = (array) ( $extraction['fragments'] ?? array() );
			$sections = Search_Section_Projector::project( $post_id, $fragments );

			if ( empty( $sections ) ) {
				++$accumulator['posts_without_sections'];
			}

			foreach ( $sections as $section ) {
				++$accumulator['section_count'];
				$level = max( 1, min( 6, (int) ( $section['level'] ?? 2 ) ) );
				$accumulator['heading_levels'][ $level ] = (int) ( $accumulator['heading_levels'][ $level ] ?? 0 ) + 1;

				$anchor_state = (string) ( $section['anchor_state'] ?? '' );
				if ( 'generated' === $anchor_state ) {
					++$accumulator['generated_anchor_count'];
					$accumulator['generated_by_source_kind'][ $source_kind ] =
						(int) ( $accumulator['generated_by_source_kind'][ $source_kind ] ?? 0 ) + 1;
				} else {
					++$accumulator['unresolved_anchor_count'];
				}

				$title_norm = (string) ( $section['title_norm'] ?? '' );
				$text_norm = (string) ( $section['text_norm'] ?? '' );
				if ( '' !== $title_norm ) {
					$accumulator['title_frequency'][ $title_norm ] =
						(int) ( $accumulator['title_frequency'][ $title_norm ] ?? 0 ) + 1;

					$section_tokens = Search_Query_Normalizer::tokens_from_normalized(
						trim( $title_norm . ' ' . $text_norm )
					);
					foreach ( array_unique( $section_tokens ) as $token ) {
						$accumulator['token_frequency'][ $token ] =
							(int) ( $accumulator['token_frequency'][ $token ] ?? 0 ) + 1;
					}

					if ( 'publish' === $post_status && 'generated' === $anchor_state ) {
						$accumulator['candidates'][] = array(
							'post_id' => $post_id,
							'post_status' => $post_status,
							'source_kind' => $source_kind,
							'section_key' => (string) ( $section['section_key'] ?? '' ),
							'title' => (string) ( $section['title'] ?? '' ),
							'title_norm' => $title_norm,
							'text_tokens' => Search_Query_Normalizer::tokens_from_normalized( $text_norm ),
							'anchor_id' => (string) ( $section['anchor_id'] ?? '' ),
							'anchor_state' => $anchor_state,
							'source_ordinal' => (int) ( $section['source_ordinal'] ?? 0 ),
						);
					}
				}
			}

			$hierarchy = Numbered_Hierarchy_Resolver::resolve( $fragments );
			foreach ( (array) ( $hierarchy['nodes'] ?? array() ) as $node ) {
				$ordinal = (int) ( $node['ordinal'] ?? -1 );
				if ( $ordinal < 0 || ! isset( $fragments[ $ordinal ] ) ) {
					continue;
				}
				$fragment = (array) $fragments[ $ordinal ];
				if ( 'heading' === (string) ( $fragment['kind'] ?? '' ) ) {
					continue;
				}
				++$accumulator['numbered_non_heading_nodes'];

				$has_heading_context = false;
				for ( $cursor = $ordinal - 1; $cursor >= 0; --$cursor ) {
					$previous = (array) ( $fragments[ $cursor ] ?? array() );
					if ( 'heading' === (string) ( $previous['kind'] ?? '' ) ) {
						$has_heading_context = true;
						break;
					}
				}
				if ( $has_heading_context ) {
					++$accumulator['numbered_with_heading_context'];
				} else {
					++$accumulator['numbered_without_heading_context'];
				}
			}
		}

		return $accumulator;
	}

	/** @param array<string,mixed> $accumulator @return array<string,mixed> */
	private static function coverage_finalize( array $accumulator, int $corpus_count ): array {
		$title_frequency = (array) ( $accumulator['title_frequency'] ?? array() );
		$token_frequency = (array) ( $accumulator['token_frequency'] ?? array() );
		$eligible_by_source_kind = array();

		foreach ( (array) ( $accumulator['candidates'] ?? array() ) as $candidate ) {
			$title_norm = (string) ( $candidate['title_norm'] ?? '' );
			$title_query = Search_Query_Normalizer::normalize( (string) ( $candidate['title'] ?? '' ) );
			if ( $title_query instanceof \WP_Error || empty( $title_query['tokens'] ) ) {
				continue;
			}

			$probe_query = (string) $candidate['title'];
			$probe_strategy = 'unique_title';
			$discriminator_token = '';
			$discriminator_frequency = 0;
			$title_count = (int) ( $title_frequency[ $title_norm ] ?? 0 );

			if ( 1 !== $title_count ) {
				$title_tokens = array_fill_keys( (array) $title_query['tokens'], true );
				$discriminators = array();
				foreach ( (array) ( $candidate['text_tokens'] ?? array() ) as $token ) {
					if ( isset( $title_tokens[ $token ] ) || strlen( (string) $token ) < 3 ) {
						continue;
					}
					$discriminators[] = array(
						'token' => (string) $token,
						'frequency' => (int) ( $token_frequency[ $token ] ?? PHP_INT_MAX ),
					);
				}
				usort(
					$discriminators,
					static function ( array $left, array $right ): int {
						$frequency = (int) $left['frequency'] <=> (int) $right['frequency'];
						return 0 !== $frequency
							? $frequency
							: strcmp( (string) $left['token'], (string) $right['token'] );
					}
				);

				foreach ( $discriminators as $discriminator ) {
					$candidate_query = trim( (string) $candidate['title'] . ' ' . (string) $discriminator['token'] );
					$normalized_candidate = Search_Query_Normalizer::normalize( $candidate_query );
					if ( $normalized_candidate instanceof \WP_Error ) {
						continue;
					}
					$probe_query = $candidate_query;
					$probe_strategy = 'title_plus_rare_section_token';
					$discriminator_token = (string) $discriminator['token'];
					$discriminator_frequency = (int) $discriminator['frequency'];
					break;
				}
				if ( 'title_plus_rare_section_token' !== $probe_strategy ) {
					continue;
				}
			}

			unset( $candidate['text_tokens'] );
			$candidate['probe_query'] = $probe_query;
			$candidate['probe_strategy'] = $probe_strategy;
			$candidate['title_frequency'] = $title_count;
			$candidate['discriminator_token'] = $discriminator_token;
			$candidate['discriminator_frequency'] = $discriminator_frequency;
			$kind = (string) ( $candidate['source_kind'] ?? 'unknown' );
			$eligible_by_source_kind[ $kind ][] = $candidate;
		}

		foreach ( $eligible_by_source_kind as &$candidates_for_kind ) {
			usort(
				$candidates_for_kind,
				static function ( array $left, array $right ): int {
					$left_strategy = 'unique_title' === (string) ( $left['probe_strategy'] ?? '' ) ? 0 : 1;
					$right_strategy = 'unique_title' === (string) ( $right['probe_strategy'] ?? '' ) ? 0 : 1;
					$comparison = $left_strategy <=> $right_strategy;
					if ( 0 !== $comparison ) {
						return $comparison;
					}
					$comparison = (int) ( $left['title_frequency'] ?? PHP_INT_MAX )
						<=> (int) ( $right['title_frequency'] ?? PHP_INT_MAX );
					if ( 0 !== $comparison ) {
						return $comparison;
					}
					$comparison = (int) ( $left['discriminator_frequency'] ?? PHP_INT_MAX )
						<=> (int) ( $right['discriminator_frequency'] ?? PHP_INT_MAX );
					if ( 0 !== $comparison ) {
						return $comparison;
					}
					$comparison = (int) ( $left['post_id'] ?? 0 ) <=> (int) ( $right['post_id'] ?? 0 );
					return 0 !== $comparison
						? $comparison
						: (int) ( $left['source_ordinal'] ?? 0 ) <=> (int) ( $right['source_ordinal'] ?? 0 );
				}
			);
		}
		unset( $candidates_for_kind );

		$source_kinds = (array) ( $accumulator['source_kinds'] ?? array() );
		$generated_by_source_kind = (array) ( $accumulator['generated_by_source_kind'] ?? array() );
		ksort( $source_kinds, SORT_STRING );
		ksort( $generated_by_source_kind, SORT_STRING );
		ksort( $eligible_by_source_kind, SORT_STRING );

		$required_probe_source_kinds = array_keys(
			array_filter(
				$generated_by_source_kind,
				static fn ( int $count ): bool => $count > 0
			)
		);
		$probe_candidates = array();
		$selected_section_keys = array();
		$unprobed_source_kinds = array();

		foreach ( $required_probe_source_kinds as $kind ) {
			$candidates_for_kind = (array) ( $eligible_by_source_kind[ $kind ] ?? array() );
			if ( empty( $candidates_for_kind ) ) {
				$unprobed_source_kinds[] = $kind;
				continue;
			}
			$candidate = $candidates_for_kind[0];
			$key = (string) ( $candidate['section_key'] ?? '' );
			$probe_candidates[] = $candidate;
			$selected_section_keys[ $key ] = true;
		}

		foreach ( $eligible_by_source_kind as $candidates_for_kind ) {
			foreach ( $candidates_for_kind as $candidate ) {
				if ( count( $probe_candidates ) >= self::MAX_PROBES ) {
					break 2;
				}
				$key = (string) ( $candidate['section_key'] ?? '' );
				if ( '' === $key || isset( $selected_section_keys[ $key ] ) ) {
					continue;
				}
				$probe_candidates[] = $candidate;
				$selected_section_keys[ $key ] = true;
			}
		}

		$heading_levels = (array) ( $accumulator['heading_levels'] ?? array_fill( 1, 6, 0 ) );
		ksort( $heading_levels, SORT_NUMERIC );

		return array(
			'corpus_count' => max( 0, $corpus_count ),
			'posts_analyzed' => (int) ( $accumulator['posts_analyzed'] ?? 0 ),
			'extractor_error_count' => (int) ( $accumulator['extractor_error_count'] ?? 0 ),
			'extractor_errors' => array_slice( (array) ( $accumulator['extractor_errors'] ?? array() ), 0, 50 ),
			'source_kinds' => $source_kinds,
			'generated_by_source_kind' => $generated_by_source_kind,
			'required_probe_source_kinds' => $required_probe_source_kinds,
			'unprobed_source_kinds' => $unprobed_source_kinds,
			'section_count' => (int) ( $accumulator['section_count'] ?? 0 ),
			'generated_anchor_count' => (int) ( $accumulator['generated_anchor_count'] ?? 0 ),
			'unresolved_anchor_count' => (int) ( $accumulator['unresolved_anchor_count'] ?? 0 ),
			'posts_without_sections' => (int) ( $accumulator['posts_without_sections'] ?? 0 ),
			'heading_levels' => $heading_levels,
			'numbered_non_heading_nodes' => (int) ( $accumulator['numbered_non_heading_nodes'] ?? 0 ),
			'numbered_with_heading_context' => (int) ( $accumulator['numbered_with_heading_context'] ?? 0 ),
			'numbered_without_heading_context' => (int) ( $accumulator['numbered_without_heading_context'] ?? 0 ),
			'numbered_granularity_review_required' => (int) ( $accumulator['numbered_with_heading_context'] ?? 0 ) > 0,
			'probe_candidate_count' => count( $probe_candidates ),
			'probe_candidates' => $probe_candidates,
		);
	}

	/** @return array<string,mixed> */
	private static function coverage_audit( array $post_ids ): array {
		$posts_analyzed = 0;
		$extractor_errors = array();
		$source_kinds = array();
		$generated_by_source_kind = array();
		$total_sections = 0;
		$generated = 0;
		$unresolved = 0;
		$posts_without_sections = 0;
		$heading_levels = array_fill( 1, 6, 0 );
		$numbered_non_heading = 0;
		$numbered_without_heading = 0;
		$numbered_with_heading = 0;
		$unique_title_candidates = array();
		$title_frequency = array();
		$token_frequency = array();

		foreach ( $post_ids as $post_id ) {
			$extraction = Content_Extractor::extract( $post_id );
			if ( $extraction instanceof \WP_Error ) {
				$extractor_errors[] = array(
					'post_id' => $post_id,
					'code' => $extraction->get_error_code(),
				);
				continue;
			}
			++$posts_analyzed;

			$source_kind = sanitize_key( (string) ( $extraction['source_kind'] ?? 'unknown' ) );
			$source_kinds[ $source_kind ] = (int) ( $source_kinds[ $source_kind ] ?? 0 ) + 1;
			$fragments = (array) ( $extraction['fragments'] ?? array() );
			$sections = Search_Section_Projector::project( $post_id, $fragments );

			if ( empty( $sections ) ) {
				++$posts_without_sections;
			}

			foreach ( $sections as $section ) {
				++$total_sections;
				$level = max( 1, min( 6, (int) ( $section['level'] ?? 2 ) ) );
				++$heading_levels[ $level ];
				if ( 'generated' === (string) ( $section['anchor_state'] ?? '' ) ) {
					++$generated;
					$generated_by_source_kind[ $source_kind ] = (int) ( $generated_by_source_kind[ $source_kind ] ?? 0 ) + 1;
				} else {
					++$unresolved;
				}

				$title_norm = (string) ( $section['title_norm'] ?? '' );
				$text_norm = (string) ( $section['text_norm'] ?? '' );
				if ( '' !== $title_norm ) {
					$title_frequency[ $title_norm ] = (int) ( $title_frequency[ $title_norm ] ?? 0 ) + 1;
					$section_tokens = Search_Query_Normalizer::tokens_from_normalized(
						trim( $title_norm . ' ' . $text_norm )
					);
					foreach ( array_unique( $section_tokens ) as $token ) {
						$token_frequency[ $token ] = (int) ( $token_frequency[ $token ] ?? 0 ) + 1;
					}

					$unique_title_candidates[] = array(
						'post_id' => $post_id,
						'post_status' => (string) get_post_status( $post_id ),
						'source_kind' => $source_kind,
						'section_key' => (string) ( $section['section_key'] ?? '' ),
						'title' => (string) ( $section['title'] ?? '' ),
						'title_norm' => $title_norm,
						'text_norm' => $text_norm,
						'anchor_id' => (string) ( $section['anchor_id'] ?? '' ),
						'anchor_state' => (string) ( $section['anchor_state'] ?? '' ),
						'source_ordinal' => (int) ( $section['source_ordinal'] ?? 0 ),
					);
				}
			}

			$hierarchy = Numbered_Hierarchy_Resolver::resolve( $fragments );
			foreach ( (array) ( $hierarchy['nodes'] ?? array() ) as $node ) {
				$ordinal = (int) ( $node['ordinal'] ?? -1 );
				if ( $ordinal < 0 || ! isset( $fragments[ $ordinal ] ) ) {
					continue;
				}
				$fragment = (array) $fragments[ $ordinal ];
				if ( 'heading' === (string) ( $fragment['kind'] ?? '' ) ) {
					continue;
				}
				++$numbered_non_heading;

				$has_heading_context = false;
				for ( $cursor = $ordinal - 1; $cursor >= 0; --$cursor ) {
					$previous = (array) ( $fragments[ $cursor ] ?? array() );
					if ( 'heading' === (string) ( $previous['kind'] ?? '' ) ) {
						$has_heading_context = true;
						break;
					}
				}
				if ( $has_heading_context ) {
					++$numbered_with_heading;
				} else {
					++$numbered_without_heading;
				}
			}
		}

		$eligible_by_source_kind = array();
		foreach ( $unique_title_candidates as $candidate ) {
			$title_norm = (string) $candidate['title_norm'];
			if (
				'publish' !== (string) $candidate['post_status']
				|| 'generated' !== (string) $candidate['anchor_state']
			) {
				continue;
			}

			$title_query = Search_Query_Normalizer::normalize( (string) $candidate['title'] );
			if ( $title_query instanceof \WP_Error || empty( $title_query['tokens'] ) ) {
				continue;
			}

			$probe_query = (string) $candidate['title'];
			$probe_strategy = 'unique_title';
			$discriminator_token = '';
			$discriminator_frequency = 0;
			$title_count = (int) ( $title_frequency[ $title_norm ] ?? 0 );

			if ( 1 !== $title_count ) {
				$title_tokens = array_fill_keys( (array) $title_query['tokens'], true );
				$text_tokens = Search_Query_Normalizer::tokens_from_normalized(
					(string) ( $candidate['text_norm'] ?? '' )
				);
				$discriminators = array();

				foreach ( $text_tokens as $token ) {
					if ( isset( $title_tokens[ $token ] ) || strlen( $token ) < 3 ) {
						continue;
					}
					$discriminators[] = array(
						'token' => $token,
						'frequency' => (int) ( $token_frequency[ $token ] ?? PHP_INT_MAX ),
					);
				}

				usort(
					$discriminators,
					static function ( array $left, array $right ): int {
						$frequency = (int) $left['frequency'] <=> (int) $right['frequency'];
						return 0 !== $frequency
							? $frequency
							: strcmp( (string) $left['token'], (string) $right['token'] );
					}
				);

				foreach ( $discriminators as $discriminator ) {
					$candidate_query = trim( (string) $candidate['title'] . ' ' . (string) $discriminator['token'] );
					$normalized_candidate = Search_Query_Normalizer::normalize( $candidate_query );
					if ( $normalized_candidate instanceof \WP_Error ) {
						continue;
					}
					$probe_query = $candidate_query;
					$probe_strategy = 'title_plus_rare_section_token';
					$discriminator_token = (string) $discriminator['token'];
					$discriminator_frequency = (int) $discriminator['frequency'];
					break;
				}

				if ( 'title_plus_rare_section_token' !== $probe_strategy ) {
					continue;
				}
			}

			$candidate['probe_query'] = $probe_query;
			$candidate['probe_strategy'] = $probe_strategy;
			$candidate['title_frequency'] = $title_count;
			$candidate['discriminator_token'] = $discriminator_token;
			$candidate['discriminator_frequency'] = $discriminator_frequency;

			$kind = (string) ( $candidate['source_kind'] ?? 'unknown' );
			$eligible_by_source_kind[ $kind ][] = $candidate;
		}

		foreach ( $eligible_by_source_kind as &$candidates_for_kind ) {
			usort(
				$candidates_for_kind,
				static function ( array $left, array $right ): int {
					$left_strategy = 'unique_title' === (string) ( $left['probe_strategy'] ?? '' ) ? 0 : 1;
					$right_strategy = 'unique_title' === (string) ( $right['probe_strategy'] ?? '' ) ? 0 : 1;
					$comparison = $left_strategy <=> $right_strategy;
					if ( 0 !== $comparison ) {
						return $comparison;
					}
					$comparison = (int) ( $left['title_frequency'] ?? PHP_INT_MAX )
						<=> (int) ( $right['title_frequency'] ?? PHP_INT_MAX );
					if ( 0 !== $comparison ) {
						return $comparison;
					}
					$comparison = (int) ( $left['discriminator_frequency'] ?? PHP_INT_MAX )
						<=> (int) ( $right['discriminator_frequency'] ?? PHP_INT_MAX );
					if ( 0 !== $comparison ) {
						return $comparison;
					}
					$comparison = (int) ( $left['post_id'] ?? 0 ) <=> (int) ( $right['post_id'] ?? 0 );
					return 0 !== $comparison
						? $comparison
						: (int) ( $left['source_ordinal'] ?? 0 ) <=> (int) ( $right['source_ordinal'] ?? 0 );
				}
			);
		}
		unset( $candidates_for_kind );

		ksort( $source_kinds, SORT_STRING );
		ksort( $generated_by_source_kind, SORT_STRING );
		ksort( $eligible_by_source_kind, SORT_STRING );

		$required_probe_source_kinds = array_keys(
			array_filter(
				$generated_by_source_kind,
				static fn ( int $count ): bool => $count > 0
			)
		);
		$probe_candidates = array();
		$selected_section_keys = array();
		$unprobed_source_kinds = array();

		// Primeiro garante representação de cada adapter/source kind com anchor gerável.
		foreach ( $required_probe_source_kinds as $kind ) {
			$candidates_for_kind = (array) ( $eligible_by_source_kind[ $kind ] ?? array() );
			if ( empty( $candidates_for_kind ) ) {
				$unprobed_source_kinds[] = $kind;
				continue;
			}
			$candidate = $candidates_for_kind[0];
			$key = (string) ( $candidate['section_key'] ?? '' );
			$probe_candidates[] = $candidate;
			$selected_section_keys[ $key ] = true;
		}

		// Depois preenche diversidade adicional sem repetir section_key.
		foreach ( $eligible_by_source_kind as $candidates_for_kind ) {
			foreach ( $candidates_for_kind as $candidate ) {
				if ( count( $probe_candidates ) >= self::MAX_PROBES ) {
					break 2;
				}
				$key = (string) ( $candidate['section_key'] ?? '' );
				if ( '' === $key || isset( $selected_section_keys[ $key ] ) ) {
					continue;
				}
				$probe_candidates[] = $candidate;
				$selected_section_keys[ $key ] = true;
			}
		}

		return array(
			'corpus_count' => count( $post_ids ),
			'posts_analyzed' => $posts_analyzed,
			'extractor_error_count' => count( $extractor_errors ),
			'extractor_errors' => array_slice( $extractor_errors, 0, 50 ),
			'source_kinds' => $source_kinds,
			'generated_by_source_kind' => $generated_by_source_kind,
			'required_probe_source_kinds' => $required_probe_source_kinds,
			'unprobed_source_kinds' => $unprobed_source_kinds,
			'section_count' => $total_sections,
			'generated_anchor_count' => $generated,
			'unresolved_anchor_count' => $unresolved,
			'posts_without_sections' => $posts_without_sections,
			'heading_levels' => $heading_levels,
			'numbered_non_heading_nodes' => $numbered_non_heading,
			'numbered_with_heading_context' => $numbered_with_heading,
			'numbered_without_heading_context' => $numbered_without_heading,
			'numbered_granularity_review_required' => $numbered_with_heading > 0,
			'probe_candidate_count' => count( $probe_candidates ),
			'probe_candidates' => $probe_candidates,
		);
	}

	/** @return array<string,mixed> */
	private static function section_and_anchor_probes( array $candidates ): array {
		$rows = array();
		$section_failed = 0;
		$deep_link_failed = 0;
		$visible_text_changed = 0;

		foreach ( array_slice( $candidates, 0, self::MAX_PROBES ) as $candidate ) {
			$post_id = (int) ( $candidate['post_id'] ?? 0 );
			$title = (string) ( $candidate['title'] ?? '' );
			$probe_query = trim( (string) ( $candidate['probe_query'] ?? $title ) );
			$section_key = (string) ( $candidate['section_key'] ?? '' );
			$anchor_id = (string) ( $candidate['anchor_id'] ?? '' );

			$section_response = Search_Service::search_sections( $probe_query, Search_Section_Service::MAX_PARENTS, 5 );
			$found_section = false;
			foreach ( (array) ( (array) ( $section_response['items_by_post'] ?? array() )[ $post_id ] ?? array() ) as $item ) {
				if ( hash_equals( $section_key, (string) ( $item['section_key'] ?? '' ) ) ) {
					$found_section = true;
					break;
				}
			}
			if ( ! $found_section ) {
				++$section_failed;
			}

			$post = get_post( $post_id );
			$rendered = '';
			if ( is_object( $post ) ) {
				$GLOBALS['post'] = $post;
				setup_postdata( $post );
				$rendered = (string) apply_filters( 'the_content', (string) ( $post->post_content ?? '' ) );
				wp_reset_postdata();
			}

			$before_visible = Search_Query_Normalizer::normalize_document_text( $rendered );
			$by_post = Search_Projection_Repository::sections_for_posts( array( $post_id ) );
			$sections = $by_post instanceof \WP_Error ? array() : (array) ( $by_post[ $post_id ] ?? array() );
			$anchored = Search_Anchor_Manager::inject_for_sections( $rendered, $sections );
			$after_visible = Search_Query_Normalizer::normalize_document_text( $anchored );
			$anchor_materialized = '' !== $anchor_id
				&& ( str_contains( $anchored, 'id="' . $anchor_id . '"' ) || str_contains( $anchored, "id='" . $anchor_id . "'" ) );

			if ( ! $anchor_materialized ) {
				++$deep_link_failed;
			}
			if ( $before_visible !== $after_visible ) {
				++$visible_text_changed;
			}

			$rows[] = array(
				'post_id' => $post_id,
				'source_kind' => (string) ( $candidate['source_kind'] ?? '' ),
				'query' => $probe_query,
				'query_strategy' => (string) ( $candidate['probe_strategy'] ?? '' ),
				'title_frequency' => (int) ( $candidate['title_frequency'] ?? 0 ),
				'discriminator_token' => (string) ( $candidate['discriminator_token'] ?? '' ),
				'discriminator_frequency' => (int) ( $candidate['discriminator_frequency'] ?? 0 ),
				'section_key' => $section_key,
				'section_query_state' => (string) ( $section_response['state'] ?? '' ),
				'section_query_found_expected' => $found_section,
				'anchor_id' => $anchor_id,
				'anchor_materialized' => $anchor_materialized,
				'visible_text_equal' => $before_visible === $after_visible,
			);
		}

		return array(
			'eligible_probe_count' => count( $rows ),
			'minimum_required' => self::MIN_PROBES,
			'section_query_failed' => $section_failed,
			'deep_link_failed' => $deep_link_failed,
			'visible_text_changed' => $visible_text_changed,
			'rows' => $rows,
		);
	}


	/** @return array<string,mixed> */
	private static function performance_benchmark( array $candidates ): array {
		$queries = array();
		foreach ( array_slice( $candidates, 0, self::BENCHMARK_QUERY_CAP ) as $candidate ) {
			$query = trim( (string) ( $candidate['probe_query'] ?? $candidate['title'] ?? '' ) );
			if ( '' !== $query ) {
				$queries[] = $query;
			}
		}

		$samples = array();
		$technical_failures = array();

		foreach ( $queries as $query ) {
			for ( $i = 0; $i < self::BENCHMARK_WARMUPS; ++$i ) {
				Search_Service::search_sections( $query, Search_Section_Service::MAX_PARENTS, 3 );
			}

			for ( $i = 0; $i < self::BENCHMARK_REPEATS; ++$i ) {
				$started = microtime( true );
				$response = Search_Service::search_sections( $query, Search_Section_Service::MAX_PARENTS, 3 );
				$runtime_ms = ( microtime( true ) - $started ) * 1000;
				$samples[] = $runtime_ms;

				if (
					'success' !== (string) ( $response['state'] ?? '' )
					|| (int) ( $response['section_count'] ?? 0 ) <= 0
				) {
					$technical_failures[] = array(
						'query' => $query,
						'state' => (string) ( $response['state'] ?? '' ),
						'error_code' => (string) ( $response['error_code'] ?? '' ),
					);
				}
			}
		}

		sort( $samples, SORT_NUMERIC );
		$p50 = self::percentile( $samples, 0.50 );
		$p95 = self::percentile( $samples, 0.95 );
		$max = empty( $samples ) ? 0.0 : (float) max( $samples );

		$pass = ! empty( $queries )
			&& ! empty( $samples )
			&& empty( $technical_failures )
			&& $p95 <= self::PERF_P95_BUDGET_MS
			&& $max <= self::PERF_MAX_BUDGET_MS;

		return array(
			'query_count' => count( $queries ),
			'warmups_per_query' => self::BENCHMARK_WARMUPS,
			'repeats_per_query' => self::BENCHMARK_REPEATS,
			'sample_count' => count( $samples ),
			'p50_ms' => round( $p50, 4 ),
			'p95_ms' => round( $p95, 4 ),
			'max_ms' => round( $max, 4 ),
			'p95_budget_ms' => self::PERF_P95_BUDGET_MS,
			'max_budget_ms' => self::PERF_MAX_BUDGET_MS,
			'technical_failure_count' => count( $technical_failures ),
			'technical_failures' => array_slice( $technical_failures, 0, 20 ),
			'pass' => $pass,
		);
	}

	/** @param array<int,float> $samples */
	private static function percentile( array $samples, float $percentile ): float {
		$count = count( $samples );
		if ( 0 === $count ) {
			return 0.0;
		}
		$index = (int) ceil( $percentile * $count ) - 1;
		$index = max( 0, min( $count - 1, $index ) );
		return (float) $samples[ $index ];
	}

	/** @param array<string,mixed> $state */
	private static function state_versions_current( array $state ): bool {
		return Search_Projection_Repository::SCHEMA_VERSION === (string) ( $state['schema_version'] ?? '' )
			&& Search_Document_Builder::VERSION === (string) ( $state['document_version'] ?? '' )
			&& Search_Query_Normalizer::VERSION === (string) ( $state['normalizer_version'] ?? '' )
			&& Search_Section_Projector::VERSION === (string) ( $state['section_projection_version'] ?? '' );
	}

	/** @return array<string,mixed> */
	private static function schema_contract(): array {
		$contract = Search_Projection_Repository::schema_contract();
		if ( $contract instanceof \WP_Error ) {
			return array(
				'expected_schema_version' => Search_Projection_Repository::SCHEMA_VERSION,
				'pass' => false,
				'error_code' => $contract->get_error_code(),
				'error_message' => $contract->get_error_message(),
			);
		}
		$contract['expected_schema_version'] = Search_Projection_Repository::SCHEMA_VERSION;
		return $contract;
	}

	/** @return array<string,bool|string> */
	private static function runtime_source_safety(): array {
		$paths = array(
			BDC_KB_DIR . 'includes/class-search-section-projector.php',
			BDC_KB_DIR . 'includes/class-search-section-ranker.php',
			BDC_KB_DIR . 'includes/class-search-section-service.php',
			BDC_KB_DIR . 'includes/class-search-anchor-manager.php',
			BDC_KB_DIR . 'includes/class-search-service.php',
			BDC_KB_DIR . 'includes/class-search-section-runner-g590.php',
		);
		$source = '';
		foreach ( $paths as $path ) {
			if ( ! is_readable( $path ) ) {
				continue;
			}
			$content = file_get_contents( $path );
			if ( is_string( $content ) ) {
				$source .= "\n" . $content;
			}
		}
		$source = self::strip_php_comments( $source );

		$repository = is_readable( BDC_KB_DIR . 'includes/class-search-projection-repository.php' )
			? (string) file_get_contents( BDC_KB_DIR . 'includes/class-search-projection-repository.php' )
			: '';

		$candidate_sql_start = strpos( $repository, 'private static function query_candidates' );
		$section_reader_start = strpos( $repository, 'public static function sections_for_posts' );
		$candidate_source = false !== $candidate_sql_start
			? substr( $repository, $candidate_sql_start, false !== $section_reader_start ? $section_reader_start - $candidate_sql_start : null )
			: '';

		return array(
			'no_asi' => 1 !== preg_match( '/\basi(?:4)?_/i', $source ),
			'no_network' => 1 !== preg_match( '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|(?<![A-Za-z0-9_])fsockopen\s*\(|(?<![A-Za-z0-9_])stream_socket_client\s*\(/i', $source ),
			'no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|delete_post_meta\s*\(|wp_set_object_terms\s*\(/i', $source ),
			'parent_ranker_version_frozen' => 'lexical-ranker-v1.0.0' === Lexical_Ranker::VERSION,
			'parent_ranker_sha256' => is_readable( BDC_KB_DIR . 'includes/class-lexical-ranker.php' )
				? hash_file( 'sha256', BDC_KB_DIR . 'includes/class-lexical-ranker.php' )
				: '',
			'candidate_query_does_not_load_sections' => ! str_contains( $candidate_source, 'sections_json' ),
		);
	}

	/** @return array<int,int> */
	private static function corpus_ids(): array {
		$ids = get_posts(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => self::ALLOWED_STATUSES,
				'numberposts' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => true,
			)
		);
		return array_values( array_map( 'intval', is_array( $ids ) ? $ids : array() ) );
	}

	/** @return array<int,string> */
	private static function editorial_snapshot( array $post_ids ): array {
		$out = array();
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				$out[ $post_id ] = 'missing';
				continue;
			}

			$meta = array();
			foreach ( self::GOVERNED_META_KEYS as $key ) {
				$meta[ $key ] = self::stable_value( get_post_meta( $post_id, $key, true ) );
			}

			$taxonomies = array();
			$taxonomy_names = array( 'category', 'post_tag' );
			foreach ( Classification_Contract::fields() as $definition ) {
				$taxonomy = (string) ( $definition['taxonomy'] ?? '' );
				if ( '' !== $taxonomy ) {
					$taxonomy_names[] = $taxonomy;
				}
			}
			$taxonomy_names = array_values( array_unique( $taxonomy_names ) );
			sort( $taxonomy_names, SORT_STRING );

			foreach ( $taxonomy_names as $taxonomy ) {
				$terms = get_the_terms( $post_id, $taxonomy );
				$taxonomies[ $taxonomy ] = is_array( $terms )
					? array_values( array_map( static fn ( object $term ): int => (int) $term->term_id, $terms ) )
					: array();
				sort( $taxonomies[ $taxonomy ], SORT_NUMERIC );
			}

			$review_events = get_comments(
				array(
					'post_id' => $post_id,
					'type' => Review_Contract::COMMENT_TYPE,
					'status' => 'approve',
					'number' => 0,
					'orderby' => 'comment_ID',
					'order' => 'ASC',
				)
			);
			$review_projection = array();
			foreach ( is_array( $review_events ) ? $review_events : array() as $comment ) {
				if ( ! is_object( $comment ) ) {
					continue;
				}
				$review_projection[] = array(
					'comment_ID' => (int) ( $comment->comment_ID ?? 0 ),
					'user_id' => (int) ( $comment->user_id ?? 0 ),
					'comment_date_gmt' => (string) ( $comment->comment_date_gmt ?? '' ),
					'comment_content' => (string) ( $comment->comment_content ?? '' ),
				);
			}

			$out[ $post_id ] = Canonical_JSON::hash(
				array(
					'post_title' => (string) ( $post->post_title ?? '' ),
					'post_content' => (string) ( $post->post_content ?? '' ),
					'post_excerpt' => (string) ( $post->post_excerpt ?? '' ),
					'post_status' => (string) ( $post->post_status ?? '' ),
					'post_modified_gmt' => (string) ( $post->post_modified_gmt ?? '' ),
					'meta' => $meta,
					'taxonomies' => $taxonomies,
					'review_events' => $review_projection,
				)
			);
		}
		ksort( $out, SORT_NUMERIC );
		return $out;
	}

	private static function stable_value( mixed $value ): mixed {
		if ( is_scalar( $value ) || null === $value ) {
			return $value;
		}
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}

	private static function row_count(): ?int {
		if ( ! Search_Projection_Repository::schema_exists() ) {
			return null;
		}
		$count = Search_Projection_Repository::count_rows();
		return $count instanceof \WP_Error ? null : $count;
	}

	private static function projection_snapshot_hash(): string {
		if ( ! Search_Projection_Repository::schema_exists() ) {
			return '';
		}
		$snapshot = Search_Projection_Repository::hash_snapshot();
		return $snapshot instanceof \WP_Error ? '' : Canonical_JSON::hash( $snapshot );
	}

	private static function strip_php_comments( string $source ): string {
		$tokens = token_get_all( $source );
		$out = '';
		foreach ( $tokens as $token ) {
			if ( is_array( $token ) && in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
				continue;
			}
			$out .= is_array( $token ) ? $token[1] : $token;
		}
		return $out;
	}

	private static function throwable_row( string $phase, \Throwable $error ): array {
		return array(
			'phase' => $phase,
			'class' => get_class( $error ),
			'code' => (string) $error->getCode(),
			'message' => $error->getMessage(),
		);
	}

	private static function db_version(): string {
		global $wpdb;
		return method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '';
	}
}
