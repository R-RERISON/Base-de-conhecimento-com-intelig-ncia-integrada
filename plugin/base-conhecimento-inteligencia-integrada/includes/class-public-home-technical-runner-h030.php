<?php
/**
 * UX-004 H-030 — Public Home technical acceptance environmental runner.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Public_Home_Technical_Runner_H030 {

	public const ACTION = 'bdc_kb_ux004_h030_technical';
	public const PAGE_SLUG = 'bdc-kb-ux004-h030-technical';

	private const NONCE_ACTION = 'bdc_kb_ux004_h030_technical';
	private const NONCE_FIELD = 'bdc_kb_ux004_h030_nonce';

	/** @var array<int,string> */
	private const SEARCH_PROBES = array( 'Windows 10', 'Autran', 'BitLocker' );

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 57 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Aceite Técnico H-030',
			'Aceite Técnico H-030',
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
		echo '<h1>UX-004 — H-030 Aceite Técnico</h1>';
		echo '<p>Valida paridade funcional, segurança, Home sem dependência runtime do legado, ausência de shortcodes crus e regressão Search/Golden.</p>';
		echo '<div class="notice notice-warning inline"><p><strong>H032 exige o Advanced Search Intelligence desativado manualmente em homologação.</strong> O runner nunca desativa plugins.</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( 'Executar H-030 e baixar JSON', 'primary' );
		echo '</form></div>';
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-ux004-h030-technical-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	public static function run(): array {
		$started = microtime( true );
		$errors = array();
		$throwables = array();
		$editorial_before = self::editorial_fingerprint();

		$legacy = self::legacy_activation_probe();
		$source_contract = self::source_contract();
		$functional = array();
		$security = array();
		$asi_off = array();
		$shortcodes = self::legacy_shortcode_scan();
		$golden = array();

		try {
			$functional = self::functional_matrix();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'functional_matrix', $error );
		}

		try {
			$security = self::security_matrix();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'security_matrix', $error );
		}

		$h030 = true === (bool) ( $functional['pass'] ?? false );
		$h031 = true === (bool) ( $security['pass'] ?? false );
		$h032 = false;
		$h034 = false;

		if ( ! empty( $legacy['active_plugins'] ) ) {
			$status = 'BLOCKED_ASI_ACTIVE';
			$asi_off = array(
				'pass' => false,
				'reason' => 'legacy_active',
				'search_probe' => array(),
				'word_cloud' => array(),
				'home_components' => array(),
			);
		} else {
			try {
				$asi_off = self::asi_off_smoke();
				$h032 = true === (bool) ( $asi_off['pass'] ?? false );
			} catch ( \Throwable $error ) {
				$throwables[] = self::throwable_row( 'asi_off_smoke', $error );
			}

			try {
				$golden_raw = Golden_Gate_Runner_G550::run();
				$golden = self::golden_summary( $golden_raw );
				$h034 = 'PASS' === (string) ( $golden['status'] ?? '' )
					&& 0 === (int) ( $golden['blocking_failed'] ?? -1 )
					&& 0 === (int) ( $golden['technical_failed'] ?? -1 )
					&& 0 === (int) ( $golden['technical_error_count'] ?? -1 );
			} catch ( \Throwable $error ) {
				$throwables[] = self::throwable_row( 'golden', $error );
			}
			$status = 'FAIL';
		}

		$h033 = true === (bool) ( $shortcodes['pass'] ?? false );
		$editorial_after = self::editorial_fingerprint();
		$editorial_equal = hash_equals( $editorial_before, $editorial_after );

		$pass = $h030 && $h031 && $h032 && $h033 && $h034
			&& $editorial_equal
			&& empty( $errors )
			&& empty( $throwables );

		if ( empty( $legacy['active_plugins'] ) ) {
			$status = $pass ? 'PASS' : 'FAIL';
		}

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'UX-004/H-030',
			'mode' => 'public_home_technical_acceptance',
			'generated_at' => gmdate( 'c' ),
			'status' => $status,
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'db_server' => self::db_version(),
				'multisite' => is_multisite(),
			),
			'contracts' => array(
				'public_search_facade' => Public_Search_Facade::VERSION,
				'normalizer' => Search_Query_Normalizer::VERSION,
				'document' => Search_Document_Builder::VERSION,
				'ranker' => Lexical_Ranker::VERSION,
				'result' => Search_Service::RESULT_VERSION,
				'word_cloud' => Word_Cloud_Contract::VERSION,
				'word_cloud_quality' => Word_Cloud_Contract::QUALITY_PROFILE,
			),
			'legacy_activation' => $legacy,
			'source_contract' => $source_contract,
			'functional_parity' => $functional,
			'security_capability' => $security,
			'asi_off_smoke' => $asi_off,
			'legacy_shortcode_scan' => $shortcodes,
			'golden' => $golden,
			'editorial_fingerprint' => array(
				'before' => $editorial_before,
				'after' => $editorial_after,
				'equal' => $editorial_equal,
			),
			'mutations' => array(
				'automatic_legacy_deactivation' => false,
				'editorial_write' => false,
				'external_network' => false,
				'query_logging' => false,
				'search_rebuild' => false,
			),
			'errors' => $errors,
			'throwables' => $throwables,
			'gate_result' => array(
				'h030_functional_parity' => $h030,
				'h031_security_capability' => $h031,
				'h032_asi_off_smoke' => $h032,
				'h033_no_raw_legacy_shortcode' => $h033,
				'h034_search_golden_regression' => $h034,
				'editorial_unchanged' => $editorial_equal,
				'h030_technical_pass' => $pass,
				'next_gate' => $pass ? 'H-050/G-585 readiness' : 'H-030',
			),
			'runner' => array(
				'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
		);
	}

	/** @return array<string,mixed> */
	private static function functional_matrix(): array {
		$categories = Public_Home_Read_Model::categories();
		$latest = Public_Home_Read_Model::latest( 0, 4 );
		$popular = Public_Home_Read_Model::popular( 0, 4 );
		$cloud = Word_Cloud_Service::public_terms( 32 );
		$health = Word_Cloud_Service::health();
		$search_rows = array();
		$search_pass = true;

		foreach ( self::SEARCH_PROBES as $query ) {
			$response = Public_Search_Facade::search( $query, 8 );
			$row = array(
				'query' => $query,
				'state' => (string) ( $response['state'] ?? '' ),
				'retrieval_mode' => (string) ( $response['retrieval_mode'] ?? '' ),
				'count' => (int) ( $response['count'] ?? 0 ),
				'error_code' => (string) ( $response['error_code'] ?? '' ),
			);
			$row['pass'] = ! in_array( $row['state'], array( 'technical_error', 'invalid_query' ), true )
				&& '' === $row['error_code']
				&& $row['count'] > 0;
			$search_pass = $search_pass && $row['pass'];
			$search_rows[] = $row;
		}

		$auth = self::auth_bridge_probe();
		$source = self::source_contract();

		$checks = array(
			'header_links' => true === (bool) ( $source['header_links'] ?? false ),
			'auth_bridge' => true === (bool) ( $auth['pass'] ?? false ),
			'live_search_contract' => true === (bool) ( $source['live_search'] ?? false ),
			'public_search_facade' => $search_pass,
			'word_cloud_current' => ! empty( $cloud ) && true === (bool) ( $health['snapshot_current'] ?? false ),
			'categories' => count( $categories ) > 0,
			'latest' => count( $latest ) > 0,
			'popular' => count( $popular ) > 0,
			'states_responsive_a11y' => true === (bool) ( $source['states_responsive_a11y'] ?? false ),
		);

		return array(
			'pass' => ! in_array( false, array_values( $checks ), true ),
			'checks' => $checks,
			'counts' => array(
				'categories' => count( $categories ),
				'latest' => count( $latest ),
				'popular' => count( $popular ),
				'word_cloud_public_terms' => count( $cloud ),
			),
			'search_probes' => $search_rows,
			'auth_bridge' => $auth,
			'word_cloud_health' => array(
				'status' => (string) ( $health['status'] ?? '' ),
				'freshness' => (string) ( $health['freshness'] ?? '' ),
				'snapshot_current' => (bool) ( $health['snapshot_current'] ?? false ),
				'quality_profile' => (string) ( $health['quality_profile'] ?? '' ),
			),
		);
	}

	/** @return array<string,mixed> */
	private static function security_matrix(): array {
		$original_user_id = get_current_user_id();
		$anonymous = array();
		try {
			wp_set_current_user( 0 );
			foreach ( self::SEARCH_PROBES as $query ) {
				$response = Public_Search_Facade::search( $query, 20 );
				$rows = array();
				$probe_pass = true;
				foreach ( (array) ( $response['results'] ?? array() ) as $result ) {
					$post_id = (int) ( $result['post_id'] ?? 0 );
					$post = $post_id > 0 ? get_post( $post_id ) : null;
					$public = $post instanceof \WP_Post
						&& 'publish' === (string) $post->post_status
						&& is_post_publicly_viewable( $post )
						&& ! post_password_required( $post );
					$probe_pass = $probe_pass && $public;
					$rows[] = array(
						'post_id' => $post_id,
						'status' => $post instanceof \WP_Post ? (string) $post->post_status : '',
						'public' => $public,
					);
				}
				$anonymous[] = array(
					'query' => $query,
					'count' => count( $rows ),
					'pass' => $probe_pass,
					'rows' => $rows,
				);
			}
		} finally {
			wp_set_current_user( $original_user_id );
		}

		$anonymous_pass = true;
		foreach ( $anonymous as $probe ) {
			$anonymous_pass = $anonymous_pass && true === (bool) ( $probe['pass'] ?? false );
		}

		$source = self::source_contract();
		$hooks = array(
			'auth_ajax' => has_action( 'wp_ajax_' . Public_Search_Facade::AJAX_ACTION ),
			'nopriv_ajax' => has_action( 'wp_ajax_nopriv_' . Public_Search_Facade::AJAX_ACTION ),
		);

		$checks = array(
			'anonymous_results_public_only' => $anonymous_pass,
			'hard_status_guard' => true === (bool) ( $source['public_search_status_guard'] ?? false ),
			'password_guard' => true === (bool) ( $source['public_search_password_guard'] ?? false ),
			'nonce' => true === (bool) ( $source['public_search_nonce'] ?? false ),
			'bounded_query' => true === (bool) ( $source['public_search_bounds'] ?? false ),
			'rate_limit' => true === (bool) ( $source['public_search_rate_limit'] ?? false ),
			'auth_ajax_registered' => false !== $hooks['auth_ajax'],
			'nopriv_ajax_registered' => false !== $hooks['nopriv_ajax'],
		);

		return array(
			'pass' => ! in_array( false, array_values( $checks ), true ),
			'checks' => $checks,
			'anonymous_probes' => $anonymous,
			'ajax_hooks' => $hooks,
			'raw_ip_persisted' => false,
			'query_log' => false,
		);
	}

	/** @return array<string,mixed> */
	private static function asi_off_smoke(): array {
		$search = Public_Search_Facade::search( 'Windows 10', 8 );
		$cloud = Word_Cloud_Service::public_terms( 12 );
		$health = Word_Cloud_Service::health();
		$categories = Public_Home_Read_Model::categories();
		$latest = Public_Home_Read_Model::latest( 0, 4 );
		$popular = Public_Home_Read_Model::popular( 0, 4 );

		$checks = array(
			'search' => ! in_array( (string) ( $search['state'] ?? '' ), array( 'technical_error', 'invalid_query' ), true )
				&& (int) ( $search['count'] ?? 0 ) > 0,
			'word_cloud' => count( $cloud ) > 0 && true === (bool) ( $health['snapshot_current'] ?? false ),
			'categories' => count( $categories ) > 0,
			'latest' => count( $latest ) > 0,
			'popular' => count( $popular ) > 0,
		);

		return array(
			'pass' => ! in_array( false, array_values( $checks ), true ),
			'checks' => $checks,
			'search' => array(
				'state' => (string) ( $search['state'] ?? '' ),
				'retrieval_mode' => (string) ( $search['retrieval_mode'] ?? '' ),
				'count' => (int) ( $search['count'] ?? 0 ),
			),
			'word_cloud' => array(
				'count' => count( $cloud ),
				'status' => (string) ( $health['status'] ?? '' ),
				'snapshot_current' => (bool) ( $health['snapshot_current'] ?? false ),
			),
			'home_components' => array(
				'categories' => count( $categories ),
				'latest' => count( $latest ),
				'popular' => count( $popular ),
			),
		);
	}

	/** @return array<string,mixed> */
	private static function source_contract(): array {
		$experience = self::read_plugin_source( 'includes/class-public-experience.php' );
		$auth = self::read_plugin_source( 'includes/class-public-auth-bridge.php' );
		$facade = self::read_plugin_source( 'includes/class-public-search-facade.php' );
		$js = self::read_plugin_source( 'assets/js/public-search.js' );
		$foundation = self::read_plugin_source( 'assets/css/public-foundation.css' );
		$header_css = self::read_plugin_source( 'assets/css/public-header.css' );
		$home_css = self::read_plugin_source( 'assets/css/public-home.css' );

		$header_links = true;
		foreach ( array( 'Página Inicial', 'Consulta Avançada', 'Telefones', 'Links Úteis', 'POSTI' ) as $label ) {
			$header_links = $header_links && str_contains( $experience, $label );
		}

		$a11y_responsive = ( str_contains( $foundation, 'aria-live' ) || str_contains( $experience, 'aria-live' ) )
			&& str_contains( $header_css, '@media' )
			&& str_contains( $home_css, '@media' )
			&& str_contains( $experience, 'aria-label' );

		return array(
			'header_links' => $header_links,
			'entra_profile_contract' => str_contains( $auth, 'mode="profile-menu"' )
				&& str_contains( $auth, 'show_department="true"' )
				&& str_contains( $auth, 'show_job_title="true"' )
				&& str_contains( $auth, 'show_logout="true"' )
				&& str_contains( $auth, 'show_admin_link="auto"' ),
			'live_search' => str_contains( $js, "document.addEventListener('input'" )
				&& str_contains( $js, 'AbortController' )
				&& str_contains( $experience, 'debounceMs' ),
			'states_responsive_a11y' => $a11y_responsive,
			'public_search_status_guard' => str_contains( $facade, "array( 'publish', 'private' )" )
				&& str_contains( $facade, "if ( ! in_array( $status" ),
			'public_search_password_guard' => str_contains( $facade, 'post_password_required( $post )' ),
			'public_search_nonce' => str_contains( $facade, 'check_ajax_referer( self::NONCE_ACTION' ),
			'public_search_bounds' => str_contains( $facade, 'MAX_QUERY_LENGTH = 160' )
				&& str_contains( $facade, 'MAX_LIMIT = 20' ),
			'public_search_rate_limit' => str_contains( $facade, 'RATE_LIMIT = 60' )
				&& str_contains( $facade, 'hash_hmac' ),
			'no_second_ranker' => ! str_contains( $facade, 'class Public_Lexical_Ranker' )
				&& str_contains( $facade, 'Lexical_Ranker::rank' ),
		);
	}

	/** @return array<string,mixed> */
	private static function auth_bridge_probe(): array {
		$exists = shortcode_exists( 'bdc_entra_login' );
		$rendered = '';
		if ( $exists ) {
			$rendered = do_shortcode( '[bdc_entra_login mode="profile-menu" show_department="true" show_job_title="true" show_logout="true" show_admin_link="auto"]' );
		}
		return array(
			'shortcode_registered' => $exists,
			'output_nonempty' => '' !== trim( wp_strip_all_tags( (string) $rendered ) ) || '' !== trim( (string) $rendered ),
			'output_bytes' => strlen( (string) $rendered ),
			'output_sha256' => hash( 'sha256', (string) $rendered ),
			'pass' => $exists && '' !== trim( (string) $rendered ),
		);
	}

	/** @return array<string,mixed> */
	private static function legacy_shortcode_scan(): array {
		$files = array(
			'templates/public-home-preview.php',
			'includes/class-public-experience.php',
			'includes/class-public-home-read-model.php',
			'includes/class-public-search-facade.php',
		);
		$markers = array(
			'[asi_search_form]',
			'[bdc_word_cloud]',
			'[bc_home_config]',
			'[bc_ultimas]',
			'[bc_populares]',
		);
		$matches = array();
		foreach ( $files as $relative ) {
			$source = self::read_plugin_source( $relative );
			foreach ( $markers as $marker ) {
				if ( str_contains( $source, $marker ) ) {
					$matches[] = array( 'file' => $relative, 'marker' => $marker );
				}
			}
		}
		return array(
			'files_scanned' => $files,
			'matches' => $matches,
			'pass' => empty( $matches ),
		);
	}

	/** @return array<string,mixed> */
	private static function legacy_activation_probe(): array {
		$active = array_values( array_map( 'strval', (array) get_option( 'active_plugins', array() ) ) );
		$network = is_multisite()
			? array_values( array_map( 'strval', array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) ) )
			: array();
		$all = array_values( array_unique( array_merge( $active, $network ) ) );
		$legacy = array_values(
			array_filter(
				$all,
				static function ( string $path ): bool {
					$lower = strtolower( $path );
					return str_contains( $lower, 'advanced-search-intelligence' )
						|| str_contains( $lower, 'advanced_search_intelligence' );
				}
			)
		);
		sort( $legacy, SORT_STRING );
		return array(
			'active_plugins' => $legacy,
			'legacy_inactive' => empty( $legacy ),
			'automatic_deactivation' => false,
		);
	}

	/** @return array<string,mixed> */
	private static function golden_summary( array $golden ): array {
		$gate = is_array( $golden['gate_result'] ?? null ) ? $golden['gate_result'] : array();
		$errors = is_array( $golden['technical_errors'] ?? null ) ? $golden['technical_errors'] : array();
		return array(
			'status' => (string) ( $golden['status'] ?? '' ),
			'blocking_failed' => (int) ( $gate['blocking_failed'] ?? $golden['blocking_failed'] ?? -1 ),
			'technical_failed' => (int) ( $gate['technical_failed'] ?? $golden['technical_failed'] ?? -1 ),
			'technical_error_count' => count( $errors ),
			'algorithm_version' => (string) ( $golden['algorithm_version'] ?? Lexical_Ranker::VERSION ),
			'golden_set_hash' => (string) ( $golden['golden_set_hash'] ?? '' ),
			'challenge_set_hash' => (string) ( $golden['challenge_set_hash'] ?? '' ),
		);
	}

	private static function editorial_fingerprint(): string {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_status, post_modified_gmt, post_title, post_content FROM {$wpdb->posts} WHERE post_type = %s ORDER BY ID ASC",
				Meta_Contract::POST_TYPE
			),
			ARRAY_A
		);
		$out = array();
		foreach ( (array) $rows as $row ) {
			$post_id = (int) ( $row['ID'] ?? 0 );
			$out[] = array(
				'id' => $post_id,
				'status' => (string) ( $row['post_status'] ?? '' ),
				'modified' => (string) ( $row['post_modified_gmt'] ?? '' ),
				'title_sha256' => hash( 'sha256', (string) ( $row['post_title'] ?? '' ) ),
				'content_sha256' => hash( 'sha256', (string) ( $row['post_content'] ?? '' ) ),
				'elementor_sha256' => hash( 'sha256', (string) get_post_meta( $post_id, '_elementor_data', true ) ),
			);
		}
		return hash( 'sha256', wp_json_encode( $out ) ?: '' );
	}

	private static function read_plugin_source( string $relative ): string {
		$path = BDC_KB_DIR . ltrim( $relative, '/' );
		$content = is_file( $path ) ? file_get_contents( $path ) : false;
		return is_string( $content ) ? $content : '';
	}

	/** @return array<string,string> */
	private static function throwable_row( string $phase, \Throwable $error ): array {
		return array(
			'phase' => $phase,
			'class' => get_class( $error ),
			'message' => $error->getMessage(),
		);
	}

	private static function db_version(): string {
		global $wpdb;
		$value = $wpdb->get_var( 'SELECT VERSION()' );
		return is_scalar( $value ) ? (string) $value : '';
	}
}