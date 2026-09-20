<?php
/**
 * G-585 — ASI independence / decommission readiness environmental runner.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Independence_Runner_G585 {

	public const ACTION = 'bdc_kb_spec005_g585_independence';
	public const PAGE_SLUG = 'bdc-kb-spec005-g585-independence';

	private const NONCE_ACTION = 'bdc_kb_spec005_g585_independence';
	private const NONCE_FIELD = 'bdc_kb_spec005_g585_independence_nonce';

	/** @var array<int,string> */
	private const SEARCH_PROBES = array( 'Windows 11', 'Pendrive', 'MSTeams' );

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 48 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Independência ASI G-585',
			'Independência G-585',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — G-585 Independência do ASI', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p>';
		echo esc_html__( 'Antes de executar, desative manualmente o Advanced Search Intelligence em homologação. Este runner nunca desativa nem remove plugins ou dados legados automaticamente.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar G-585 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-g585-independence-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	public static function disable_search(): bool {
		return false;
	}

	/** @return array<string,mixed> */
	public static function run(): array {
		$started = microtime( true );
		$errors = array();
		$throwables = array();

		$static_scan = self::runtime_static_scan();
		$environment_probe = self::legacy_environment_probe();

		$t585 = empty( $static_scan['matches'] )
			&& empty( $environment_probe['loaded_symbols'] )
			&& empty( $environment_probe['loaded_hooks'] );
		$t586 = empty( $environment_probe['active_plugins'] );

		$base = self::base_report( $static_scan, $environment_probe );

		if ( ! $t585 || ! $t586 ) {
			$base['status'] = $t586 ? 'FAIL_DEPENDENCY_FOUND' : 'BLOCKED_LEGACY_ACTIVE';
			$base['gate_result'] = self::gate_result( $t585, $t586, false, false, false, false );
			$base['runner']['total_runtime_ms'] = round( ( microtime( true ) - $started ) * 1000, 4 );
			return $base;
		}

		$rebuild = array();
		try {
			$rebuild = Search_Rebuild_Service::rebuild();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'rebuild', $error );
		}

		$rebuild_pass2 = is_array( $rebuild['pass2'] ?? null ) ? $rebuild['pass2'] : array();
		$rebuild_determinism = is_array( $rebuild['determinism'] ?? null ) ? $rebuild['determinism'] : array();
		$rebuild_state = is_array( $rebuild['state_after'] ?? null ) ? $rebuild['state_after'] : array();
		$t588 = 'PASS' === (string) ( $rebuild['status'] ?? '' )
			&& 'ready' === (string) ( $rebuild_state['status'] ?? '' )
			&& (int) ( $rebuild['corpus_count'] ?? -1 ) === (int) ( $rebuild['row_count'] ?? -2 )
			&& 0 === (int) ( $rebuild_pass2['written'] ?? -1 )
			&& (int) ( $rebuild['corpus_count'] ?? 0 ) === (int) ( $rebuild_pass2['no_change'] ?? -1 )
			&& 0 === (int) ( $rebuild_determinism['mismatch_count'] ?? -1 )
			&& empty( $rebuild['errors'] )
			&& empty( $rebuild['throwables'] );

		$search_probes = array();
		$search_probe_pass = true;
		foreach ( self::SEARCH_PROBES as $query ) {
			try {
				$response = Search_Service::search( $query, 10 );
				$row = self::public_search_response( $query, $response );
				$row['pass'] = 'projection_like' === $row['retrieval_mode']
					&& ! in_array( $row['state'], array( 'technical_error', 'invalid_query' ), true )
					&& '' === $row['error_code']
					&& $row['count'] > 0;
				$search_probe_pass = $search_probe_pass && $row['pass'];
				$search_probes[] = $row;
			} catch ( \Throwable $error ) {
				$search_probe_pass = false;
				$throwables[] = self::throwable_row( 'search_probe', $error );
			}
		}

		$golden = array();
		try {
			$golden = Golden_Gate_Runner_G550::run();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'golden', $error );
		}
		$golden_summary = self::golden_summary( $golden );
		$golden_pass = 'PASS' === $golden_summary['status']
			&& 0 === $golden_summary['blocking_failed']
			&& 0 === $golden_summary['technical_failed']
			&& 0 === $golden_summary['technical_error_count']
			&& false === $golden_summary['depends_on_legacy'];

		$t587 = $search_probe_pass && $golden_pass;

		$lifecycle = array();
		$disable_probe = array();
		$deactivation = array();
		try {
			$lifecycle = Search_Lifecycle::prepare_schema();

			add_filter( 'bdc_kb_search_enabled', array( self::class, 'disable_search' ), PHP_INT_MAX );
			try {
				$disable_probe = Search_Service::search( 'Windows 11', 5 );
			} finally {
				remove_filter( 'bdc_kb_search_enabled', array( self::class, 'disable_search' ), PHP_INT_MAX );
			}

			$rows_before_deactivate = self::row_count();
			$state_before_deactivate = Search_Projection_Repository::state();
			Search_Lifecycle::deactivate();
			$rows_after_deactivate = self::row_count();
			$state_after_deactivate = Search_Projection_Repository::state();

			$deactivation = array(
				'rows_before' => $rows_before_deactivate,
				'rows_after' => $rows_after_deactivate,
				'state_before' => $state_before_deactivate,
				'state_after' => $state_after_deactivate,
				'rows_retained' => $rows_before_deactivate === $rows_after_deactivate,
				'state_retained' => $state_before_deactivate === $state_after_deactivate,
			);
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'lifecycle_rollback', $error );
		}

		$t589 = true === (bool) ( $lifecycle['schema_after'] ?? false )
			&& false === (bool) ( $lifecycle['implicit_rebuild'] ?? true )
			&& 'wordpress_fallback' === (string) ( $disable_probe['retrieval_mode'] ?? '' )
			&& 'search_module_disabled' === (string) ( $disable_probe['degraded_reason'] ?? '' )
			&& true === (bool) ( $deactivation['rows_retained'] ?? false )
			&& true === (bool) ( $deactivation['state_retained'] ?? false );

		$t589_2 = $t585
			&& $t586
			&& $t587
			&& $t588
			&& $t589
			&& empty( $errors )
			&& empty( $throwables )
			&& 'lexical-ranker-v1.0.0' === Lexical_Ranker::VERSION;

		$base['status'] = $t589_2 ? 'PASS' : 'FAIL';
		$base['rebuild'] = self::rebuild_summary( $rebuild );
		$base['search_probes'] = $search_probes;
		$base['golden'] = $golden_summary;
		$base['lifecycle_rollback'] = array(
			'prepare_schema' => $lifecycle,
			'disable_probe' => self::public_search_response( 'Windows 11', $disable_probe ),
			'deactivation' => $deactivation,
		);
		$base['errors'] = $errors;
		$base['throwables'] = $throwables;
		$base['gate_result'] = self::gate_result( $t585, $t586, $t587, $t588, $t589, $t589_2 );
		$base['runner']['total_runtime_ms'] = round( ( microtime( true ) - $started ) * 1000, 4 );
		$base['runner']['peak_memory_bytes'] = memory_get_peak_usage( true );

		return $base;
	}

	/** @return array<string,mixed> */
	private static function base_report( array $static_scan, array $environment_probe ): array {
		return array(
			'schema_version' => '1.0.0',
			'gate' => 'G-585',
			'mode' => 'spec005_independence_decommission_environmental',
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
				'golden_runner' => Golden_Suite_Loader::RUNNER_VERSION,
			),
			'static_runtime_scan' => $static_scan,
			'legacy_environment' => $environment_probe,
			'rebuild' => array(),
			'search_probes' => array(),
			'golden' => array(),
			'lifecycle_rollback' => array(),
			'mutations' => array(
				'automatic_legacy_deactivation' => false,
				'legacy_data_cleanup' => false,
				'external_network' => false,
				'query_logging' => false,
				'editorial_write' => false,
				'projection_rebuild_explicit' => true,
			),
			'errors' => array(),
			'throwables' => array(),
			'runner' => array(
				'total_runtime_ms' => 0.0,
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_result' => self::gate_result( false, false, false, false, false, false ),
		);
	}

	/** @return array<string,mixed> */
	private static function runtime_static_scan(): array {
		$plugin_root = wp_normalize_path( BDC_KB_DIR );
		$self = wp_normalize_path( __FILE__ );
		$files = array();
		$matches = array();

		foreach ( get_included_files() as $path ) {
			$normalized = wp_normalize_path( $path );
			if ( ! str_starts_with( $normalized, $plugin_root ) || ! str_ends_with( $normalized, '.php' ) ) {
				continue;
			}
			if ( $normalized === $self ) {
				continue;
			}
			$files[] = $normalized;
		}
		sort( $files, SORT_STRING );

		foreach ( $files as $path ) {
			$content = file_get_contents( $path );
			if ( ! is_string( $content ) ) {
				$matches[] = array(
					'file' => self::relative_plugin_path( $path ),
					'marker' => 'unreadable_runtime_source',
				);
				continue;
			}
			$source = self::strip_php_comments( $content );
			$source = str_replace( 'BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD', '', $source );
			foreach ( self::legacy_marker_labels( $source ) as $marker ) {
				$matches[] = array(
					'file' => self::relative_plugin_path( $path ),
					'marker' => $marker,
				);
			}
		}

		return array(
			'files_scanned' => count( $files ),
			'files' => array_map( array( self::class, 'relative_plugin_path' ), $files ),
			'excluded_gate_runner' => self::relative_plugin_path( $self ),
			'matches' => $matches,
			'dependency_zero' => empty( $matches ),
		);
	}

	/** @return array<string,mixed> */
	private static function legacy_environment_probe(): array {
		global $wp_filter;

		$active = array_values( array_map( 'strval', (array) get_option( 'active_plugins', array() ) ) );
		$network = array();
		if ( is_multisite() ) {
			$network = array_values( array_map( 'strval', array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) ) );
		}
		$plugin_paths = array_values( array_unique( array_merge( $active, $network ) ) );
		sort( $plugin_paths, SORT_STRING );

		$legacy_plugins = array_values(
			array_filter(
				$plugin_paths,
				static fn ( string $path ): bool => self::looks_like_legacy_plugin( $path )
			)
		);

		$symbols = array();
		foreach ( get_declared_classes() as $class ) {
			if ( self::looks_like_legacy_symbol( (string) $class ) ) {
				$symbols[] = self::describe_class_symbol( (string) $class );
			}
		}
		$functions = get_defined_functions();
		foreach ( (array) ( $functions['user'] ?? array() ) as $function ) {
			if ( self::looks_like_legacy_symbol( (string) $function ) ) {
				$symbols[] = self::describe_function_symbol( (string) $function );
			}
		}
		usort(
			$symbols,
			static fn ( array $a, array $b ): int => strcmp(
				(string) ( $a['symbol'] ?? '' ),
				(string) ( $b['symbol'] ?? '' )
			)
		);

		$hooks = array();
		foreach ( array_keys( is_array( $wp_filter ) ? $wp_filter : array() ) as $hook ) {
			if ( self::looks_like_legacy_symbol( (string) $hook ) ) {
				$hooks[] = (string) $hook;
			}
		}
		sort( $hooks, SORT_STRING );

		return array(
			'active_plugins' => $legacy_plugins,
			'loaded_symbols' => $symbols,
			'loaded_hooks' => $hooks,
			'legacy_inactive' => empty( $legacy_plugins ),
			'no_loaded_legacy_symbols' => empty( $symbols ),
			'no_loaded_legacy_hooks' => empty( $hooks ),
			'physical_legacy_storage_removal_required' => false,
		);
	}

	/** @return array<string,string> */
	private static function describe_class_symbol( string $class ): array {
		try {
			$reflection = new \ReflectionClass( $class );
			$file = $reflection->getFileName();
			$source = self::describe_source_file( is_string( $file ) ? $file : '' );
		} catch ( \Throwable $error ) {
			$source = array(
				'source_scope' => 'unknown',
				'source_path' => '',
			);
		}

		return array(
			'type' => 'class',
			'symbol' => $class,
			'source_scope' => (string) ( $source['source_scope'] ?? 'unknown' ),
			'source_path' => (string) ( $source['source_path'] ?? '' ),
		);
	}

	/** @return array<string,string> */
	private static function describe_function_symbol( string $function ): array {
		try {
			$reflection = new \ReflectionFunction( $function );
			$file = $reflection->getFileName();
			$source = self::describe_source_file( is_string( $file ) ? $file : '' );
		} catch ( \Throwable $error ) {
			$source = array(
				'source_scope' => 'unknown',
				'source_path' => '',
			);
		}

		return array(
			'type' => 'function',
			'symbol' => $function,
			'source_scope' => (string) ( $source['source_scope'] ?? 'unknown' ),
			'source_path' => (string) ( $source['source_path'] ?? '' ),
		);
	}

	/** @return array{source_scope:string,source_path:string} */
	private static function describe_source_file( string $file ): array {
		if ( '' === $file ) {
			return array(
				'source_scope' => 'internal_or_unknown',
				'source_path' => '',
			);
		}

		$normalized = wp_normalize_path( $file );
		$roots = array(
			'bdc_plugin' => wp_normalize_path( BDC_KB_DIR ),
			'mu_plugin' => defined( 'WPMU_PLUGIN_DIR' ) ? trailingslashit( wp_normalize_path( WPMU_PLUGIN_DIR ) ) : '',
			'plugin' => defined( 'WP_PLUGIN_DIR' ) ? trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) : '',
			'theme' => trailingslashit( wp_normalize_path( get_theme_root() ) ),
			'wordpress_core' => trailingslashit( wp_normalize_path( ABSPATH ) ),
		);

		foreach ( $roots as $scope => $root ) {
			if ( '' === $root || ! str_starts_with( $normalized, $root ) ) {
				continue;
			}

			return array(
				'source_scope' => $scope,
				'source_path' => ltrim( substr( $normalized, strlen( $root ) ), '/' ),
			);
		}

		return array(
			'source_scope' => 'external_or_unknown',
			'source_path' => basename( $normalized ),
		);
	}

	private static function looks_like_legacy_plugin( string $path ): bool {
		$lower = strtolower( $path );
		$slug = 'advanced' . '-search-' . 'intelligence';
		$short_v4 = 'a' . 'si4';
		$short = 'a' . 'si';

		if ( str_contains( $lower, $slug ) ) {
			return true;
		}

		$parts = preg_split( '#[/\\\\]+#', $lower );
		$first = is_array( $parts ) && isset( $parts[0] ) ? (string) $parts[0] : '';
		return $first === $short
			|| $first === $short_v4
			|| str_starts_with( $first, $short . '-' )
			|| str_starts_with( $first, $short_v4 . '-' );
	}

	private static function looks_like_legacy_symbol( string $symbol ): bool {
		$lower = strtolower( $symbol );
		$prefix = 'a' . 'si_';
		$prefix_v4 = 'a' . 'si4_';
		$long_symbol = 'advanced' . '_search_' . 'intelligence';
		$long_slug = 'advanced' . '-search-' . 'intelligence';

		return str_contains( $lower, $prefix )
			|| str_contains( $lower, $prefix_v4 )
			|| str_contains( $lower, $long_symbol )
			|| str_contains( $lower, $long_slug );
	}

	/** @return array<int,string> */
	private static function legacy_marker_labels( string $source ): array {
		$lower = strtolower( $source );
		$markers = array(
			'legacy_prefix' => 'a' . 'si_',
			'legacy_v4_prefix' => 'a' . 'si4_',
			'legacy_symbol' => 'advanced' . '_search_' . 'intelligence',
			'legacy_slug' => 'advanced' . '-search-' . 'intelligence',
		);
		$found = array();
		foreach ( $markers as $label => $marker ) {
			if ( str_contains( $lower, $marker ) ) {
				$found[] = $label;
			}
		}
		return $found;
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

	private static function relative_plugin_path( string $path ): string {
		$normalized = wp_normalize_path( $path );
		$root = wp_normalize_path( BDC_KB_DIR );
		return str_starts_with( $normalized, $root ) ? ltrim( substr( $normalized, strlen( $root ) ), '/' ) : $normalized;
	}

	/** @return array<string,mixed> */
	private static function public_search_response( string $query, array $response ): array {
		return array(
			'query' => $query,
			'state' => (string) ( $response['state'] ?? '' ),
			'retrieval_mode' => (string) ( $response['retrieval_mode'] ?? '' ),
			'count' => (int) ( $response['count'] ?? 0 ),
			'degraded_reason' => (string) ( $response['degraded_reason'] ?? '' ),
			'error_code' => (string) ( $response['error_code'] ?? '' ),
		);
	}

	/** @return array<string,mixed> */
	private static function golden_summary( array $golden ): array {
		$privacy = is_array( $golden['privacy'] ?? null ) ? $golden['privacy'] : array();
		$performance = is_array( $golden['performance'] ?? null ) ? $golden['performance'] : array();
		return array(
			'status' => (string) ( $golden['status'] ?? '' ),
			'blocking_failed' => (int) ( $golden['blocking_failed'] ?? -1 ),
			'warning_failed' => (int) ( $golden['warning_failed'] ?? -1 ),
			'technical_failed' => (int) ( $golden['technical_failed'] ?? -1 ),
			'technical_error_count' => count( (array) ( $golden['technical_errors'] ?? array() ) ),
			'result_count' => (int) ( $performance['count'] ?? 0 ),
			'suites' => is_array( $golden['suites'] ?? null ) ? $golden['suites'] : array(),
			'depends_on_legacy' => (bool) ( $privacy['depends_on_asi'] ?? true ),
			'calls_external_network' => (bool) ( $privacy['calls_external_network'] ?? true ),
			'persists_query_log' => (bool) ( $privacy['persists_query_log'] ?? true ),
		);
	}

	/** @return array<string,mixed> */
	private static function rebuild_summary( array $rebuild ): array {
		$pass1 = is_array( $rebuild['pass1'] ?? null ) ? $rebuild['pass1'] : array();
		$pass2 = is_array( $rebuild['pass2'] ?? null ) ? $rebuild['pass2'] : array();
		$determinism = is_array( $rebuild['determinism'] ?? null ) ? $rebuild['determinism'] : array();
		$state = is_array( $rebuild['state_after'] ?? null ) ? $rebuild['state_after'] : array();
		return array(
			'status' => (string) ( $rebuild['status'] ?? '' ),
			'corpus_count' => (int) ( $rebuild['corpus_count'] ?? 0 ),
			'row_count' => (int) ( $rebuild['row_count'] ?? 0 ),
			'stale_rows_deleted' => (int) ( $rebuild['stale_rows_deleted'] ?? 0 ),
			'pass1' => $pass1,
			'pass2' => $pass2,
			'determinism' => $determinism,
			'state_after' => $state,
			'errors' => (array) ( $rebuild['errors'] ?? array() ),
			'throwables' => (array) ( $rebuild['throwables'] ?? array() ),
			'runtime_ms' => (float) ( $rebuild['runtime_ms'] ?? 0.0 ),
		);
	}

	/** @return array<string,bool|string> */
	private static function gate_result(
		bool $t585,
		bool $t586,
		bool $t587,
		bool $t588,
		bool $t589,
		bool $t589_2
	): array {
		return array(
			't585_static_runtime_dependency_zero' => $t585,
			't586_legacy_inactive' => $t586,
			't587_search_golden_without_legacy' => $t587,
			't588_rebuild_without_legacy' => $t588,
			't589_lifecycle_rollback_without_legacy' => $t589,
			't589_1_dependency_zero_evidence_generated' => true,
			't589_2_g585_pass' => $t589_2,
			'next_gate' => $t589_2 ? 'G-590' : 'G-585',
		);
	}

	private static function row_count(): ?int {
		if ( ! Search_Projection_Repository::schema_exists() ) {
			return null;
		}
		$count = Search_Projection_Repository::count_rows();
		return $count instanceof \WP_Error ? null : $count;
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

	private static function db_version(): string {
		global $wpdb;
		return method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '';
	}
}
