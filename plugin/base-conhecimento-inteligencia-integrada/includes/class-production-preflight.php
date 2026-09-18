<?php
/**
 * G-245 production preflight read-only.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Production_Preflight {

	public const ACTION    = 'bdc_kb_spec004_g245_preflight';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-preflight';

	private const NONCE_ACTION = 'bdc_kb_spec004_g245_preflight_run';
	private const NONCE_FIELD  = 'bdc_kb_spec004_g245_preflight_nonce';

	/** @var array<int,string> */
	private const HOMOLOGATED_ELEMENTOR_VERSIONS = array( '4.1.0' );

	public static function register(): void {
		// Ferramenta de engenharia: permanece disponível programaticamente,
		// sem ocupar a navegação principal do produto.
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Verificação técnica do ambiente',
			'Preflight G-245',
			'manage_options',
			self::PAGE_SLUG,
			array( self::class, 'render_page' )
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Verificação técnica do ambiente', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Somente leitura.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Coleta somente fatos do ambiente e dependências observáveis. Não executa shortcodes, não chama rede externa e não altera conteúdo.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<p><label for="bdc-kb-target"><strong>' . esc_html__( 'Ambiente alvo', 'bdc-knowledge-base' ) . '</strong></label><br>';
		echo '<select id="bdc-kb-target" name="target_environment">';
		echo '<option value="homologation">' . esc_html__( 'Homologação', 'bdc-knowledge-base' ) . '</option>';
		echo '<option value="production">' . esc_html__( 'Produção', 'bdc-knowledge-base' ) . '</option>';
		echo '</select></p>';
		echo '<p><label><input type="checkbox" name="backup_confirmed" value="1"> ';
		echo esc_html__( 'Existe cópia de segurança externa, recente e validada do ambiente de destino.', 'bdc-knowledge-base' );
		echo '</label></p>';
		submit_button( __( 'Executar verificação e baixar relatório', 'bdc-knowledge-base' ), 'primary' );
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

		$target = isset( $_POST['target_environment'] ) && is_scalar( $_POST['target_environment'] )
			? sanitize_key( wp_unslash( (string) $_POST['target_environment'] ) )
			: 'homologation';
		if ( ! in_array( $target, array( 'homologation', 'production' ), true ) ) {
			$target = 'homologation';
		}
		$backup_confirmed = isset( $_POST['backup_confirmed'] ) && '1' === (string) $_POST['backup_confirmed'];

		$report = self::run( $target, $backup_confirmed );
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( esc_html__( 'Falha ao serializar o relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-g245-preflight-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	public static function run( string $target_environment = 'homologation', bool $backup_confirmed = false ): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$fingerprint_before = self::editorial_fingerprint( $ids_before );
		$facts = self::collect_facts( $target_environment, $backup_confirmed, $ids_before );
		$assessment = self::assess( $facts );
		$ids_after = self::post_ids();
		$fingerprint_after = self::editorial_fingerprint( $ids_after );

		return array(
			'schema_version' => '1.0.0',
			'mode' => 'spec004_g245_production_preflight_read_only',
			'generated_at' => gmdate( 'c' ),
			'facts' => $facts,
			'assessment' => $assessment,
			'safety' => array(
				'read_only_design' => true,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
				'executes_shortcodes' => false,
				'calls_external_network' => false,
				'persists_results' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'corpus_count_before' => count( $ids_before ),
				'corpus_count_after' => count( $ids_after ),
				'corpus_unchanged' => $ids_before === $ids_after,
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => hash_equals( $fingerprint_before, $fingerprint_after ),
			),
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate' => array(
				'g245_pass' => false,
				'preflight_status' => $assessment['status'],
				'writer_allowed' => false,
				'note' => 'Preflight v1 is evidence only. G-245 requires additional subgates before any writer can be enabled.',
			),
		);
	}

	/**
	 * Pure deterministic assessment for unit testing.
	 *
	 * @param array<string,mixed> $facts
	 * @return array{status:string,blocking_count:int,review_required_count:int,checks:array<int,array<string,string>>}
	 */
	public static function assess( array $facts ): array {
		$checks = array();

		self::add_check(
			$checks,
			'wordpress_version',
			version_compare( (string) ( $facts['wordpress'] ?? '0' ), '6.6', '>=' ) ? 'compatible' : 'blocking',
			version_compare( (string) ( $facts['wordpress'] ?? '0' ), '6.6', '>=' ) ? 'WordPress meets plugin minimum.' : 'WordPress is below plugin minimum 6.6.'
		);
		self::add_check(
			$checks,
			'php_version',
			version_compare( (string) ( $facts['php'] ?? '0' ), '8.1', '>=' ) ? 'compatible' : 'blocking',
			version_compare( (string) ( $facts['php'] ?? '0' ), '8.1', '>=' ) ? 'PHP meets plugin minimum.' : 'PHP is below plugin minimum 8.1.'
		);
		self::add_check(
			$checks,
			'domdocument',
			true === (bool) ( $facts['domdocument'] ?? false ) ? 'compatible' : 'blocking',
			true === (bool) ( $facts['domdocument'] ?? false ) ? 'DOMDocument available.' : 'DOMDocument is required by the validated extraction path.'
		);

		$elementor_loaded = true === (bool) ( $facts['elementor_loaded'] ?? false );
		$elementor_version = (string) ( $facts['elementor_version'] ?? '' );
		if ( ! $elementor_loaded || '' === $elementor_version ) {
			self::add_check( $checks, 'elementor_runtime', 'blocking', 'Elementor is not loaded; editorial migration must fail closed.' );
		} elseif ( in_array( $elementor_version, self::HOMOLOGATED_ELEMENTOR_VERSIONS, true ) ) {
			self::add_check( $checks, 'elementor_runtime', 'compatible', 'Elementor version is in the initial homologated matrix.' );
		} else {
			self::add_check( $checks, 'elementor_runtime', 'review_required', 'Elementor version is not yet in the homologated matrix.' );
		}

		$target = (string) ( $facts['target_environment'] ?? 'homologation' );
		$backup_confirmed = true === (bool) ( $facts['backup_confirmed'] ?? false );
		if ( 'production' === $target && ! $backup_confirmed ) {
			self::add_check( $checks, 'backup', 'blocking', 'Production editorial migration requires a recent externally validated backup.' );
		} else {
			self::add_check( $checks, 'backup', 'compatible', 'Backup requirement is satisfied for the selected preflight context.' );
		}

		$shortcodes = is_array( $facts['shortcode_dependencies'] ?? null ) ? $facts['shortcode_dependencies'] : array();
		$unregistered = is_array( $shortcodes['unregistered_used_tags'] ?? null ) ? $shortcodes['unregistered_used_tags'] : array();
		$unresolved = is_array( $shortcodes['provider_unresolved_tags'] ?? null ) ? $shortcodes['provider_unresolved_tags'] : array();
		if ( ! empty( $unregistered ) ) {
			self::add_check( $checks, 'shortcode_dependencies', 'review_required', 'Used shortcode tags exist without a registered runtime handler.' );
		} elseif ( ! empty( $unresolved ) ) {
			self::add_check( $checks, 'shortcode_dependencies', 'review_required', 'Shortcode handlers are registered but provider mapping is incomplete.' );
		} else {
			self::add_check( $checks, 'shortcode_dependencies', 'compatible', 'Observed shortcode dependencies are registered and provider mapping is resolved.' );
		}

		if ( true === (bool) ( $facts['wp_cron_disabled'] ?? false ) ) {
			self::add_check( $checks, 'wp_cron', 'review_required', 'WP-Cron is disabled; future resumable jobs need an explicit scheduler strategy.' );
		} else {
			self::add_check( $checks, 'wp_cron', 'compatible', 'WP-Cron is not disabled by configuration.' );
		}

		self::add_check( $checks, 'loopback', 'review_required', 'Loopback/network availability is intentionally not exercised by preflight v1.' );

		$blocking = 0;
		$review = 0;
		foreach ( $checks as $check ) {
			if ( 'blocking' === $check['status'] ) {
				++$blocking;
			} elseif ( 'review_required' === $check['status'] ) {
				++$review;
			}
		}
		$status = $blocking > 0 ? 'blocking' : ( $review > 0 ? 'review_required' : 'compatible' );

		return array(
			'status' => $status,
			'blocking_count' => $blocking,
			'review_required_count' => $review,
			'checks' => $checks,
		);
	}

	/** @param array<int,array<string,string>> $checks */
	private static function add_check( array &$checks, string $name, string $status, string $reason ): void {
		$checks[] = array( 'name' => $name, 'status' => $status, 'reason' => $reason );
	}

	/** @param array<int,int> $post_ids @return array<string,mixed> */
	private static function collect_facts( string $target_environment, bool $backup_confirmed, array $post_ids ): array {
		global $wpdb;

		$db_version = method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '';
		$db_server = '';
		if ( method_exists( $wpdb, 'get_var' ) ) {
			$value = $wpdb->get_var( 'SELECT VERSION()' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- constant read-only query.
			$db_server = is_scalar( $value ) ? (string) $value : '';
		}
		$db_probe = strtolower( $db_server . ' ' . $db_version );
		$db_engine = false !== strpos( $db_probe, 'mariadb' ) ? 'mariadb' : ( '' !== $db_probe ? 'mysql-compatible' : 'unknown' );

		return array(
			'target_environment' => in_array( $target_environment, array( 'homologation', 'production' ), true ) ? $target_environment : 'homologation',
			'backup_confirmed' => $backup_confirmed,
			'wordpress' => get_bloginfo( 'version' ),
			'php' => PHP_VERSION,
			'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
			'elementor_loaded' => defined( 'ELEMENTOR_VERSION' ),
			'elementor_version' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
			'homologated_elementor_versions' => self::HOMOLOGATED_ELEMENTOR_VERSIONS,
			'domdocument' => class_exists( '\\DOMDocument' ),
			'multisite' => is_multisite(),
			'memory_limit' => (string) ini_get( 'memory_limit' ),
			'max_execution_time' => (string) ini_get( 'max_execution_time' ),
			'wp_cron_disabled' => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON,
			'database' => array(
				'engine' => $db_engine,
				'client_version' => $db_version,
				'server_version' => $db_server,
			),
			'active_plugins' => self::active_plugin_inventory(),
			'shortcode_dependencies' => self::shortcode_dependency_inventory( $post_ids ),
			'loopback' => array(
				'status' => 'not_tested',
				'reason' => 'Preflight v1 performs no external or loopback network request.',
			),
		);
	}

	/** @return array<int,array{plugin:string,version:string,network_active:bool}> */
	private static function active_plugin_inventory(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all = function_exists( 'get_plugins' ) ? get_plugins() : array();
		$site_active = get_option( 'active_plugins', array() );
		$site_active = is_array( $site_active ) ? array_values( array_map( 'strval', $site_active ) ) : array();
		$network = is_multisite() ? get_site_option( 'active_sitewide_plugins', array() ) : array();
		$network = is_array( $network ) ? array_keys( $network ) : array();
		$active = array_values( array_unique( array_merge( $site_active, $network ) ) );
		sort( $active, SORT_STRING );
		$out = array();
		foreach ( $active as $plugin ) {
			$meta = is_array( $all[ $plugin ] ?? null ) ? $all[ $plugin ] : array();
			$out[] = array(
				'plugin' => $plugin,
				'version' => (string) ( $meta['Version'] ?? '' ),
				'network_active' => in_array( $plugin, $network, true ),
			);
		}
		return $out;
	}

	/** @param array<int,int> $post_ids @return array<string,mixed> */
	private static function shortcode_dependency_inventory( array $post_ids ): array {
		$used = array();
		foreach ( $post_ids as $post_id ) {
			$post_content = (string) get_post_field( 'post_content', $post_id, 'raw' );
			$elementor = get_post_meta( $post_id, '_elementor_data', true );
			if ( is_array( $elementor ) ) {
				$elementor = wp_json_encode( $elementor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			}
			$combined = $post_content . "\n" . ( is_string( $elementor ) ? $elementor : '' );
			$inspection = Shortcode_Inspector::inspect( $combined );
			foreach ( (array) ( $inspection['matches'] ?? array() ) as $match ) {
				$tag = strtolower( (string) ( $match['tag'] ?? '' ) );
				if ( '' !== $tag ) {
					$used[ $tag ] = true;
				}
			}
		}
		$tags = array_keys( $used );
		sort( $tags, SORT_STRING );
		$registered = array();
		$unregistered = array();
		$providers = array();
		$unresolved = array();
		foreach ( $tags as $tag ) {
			if ( shortcode_exists( $tag ) ) {
				$registered[] = $tag;
				$provider = self::shortcode_provider( $tag );
				$providers[ $tag ] = $provider;
				if ( 'unknown' === $provider ) {
					$unresolved[] = $tag;
				}
			} else {
				$unregistered[] = $tag;
			}
		}
		return array(
			'used_tags' => $tags,
			'registered_used_tags' => $registered,
			'unregistered_used_tags' => $unregistered,
			'providers' => $providers,
			'provider_unresolved_tags' => $unresolved,
		);
	}

	private static function shortcode_provider( string $tag ): string {
		global $shortcode_tags;
		$callback = is_array( $shortcode_tags ) && isset( $shortcode_tags[ $tag ] ) ? $shortcode_tags[ $tag ] : null;
		try {
			if ( is_string( $callback ) && function_exists( $callback ) ) {
				$reflection = new \ReflectionFunction( $callback );
				return self::provider_from_file( (string) $reflection->getFileName() );
			}
			if ( is_array( $callback ) && 2 === count( $callback ) ) {
				$reflection = new \ReflectionMethod( $callback[0], (string) $callback[1] );
				return self::provider_from_file( (string) $reflection->getFileName() );
			}
			if ( $callback instanceof \Closure ) {
				$reflection = new \ReflectionFunction( $callback );
				return self::provider_from_file( (string) $reflection->getFileName() );
			}
		} catch ( \Throwable $e ) {
			return 'unknown';
		}
		return 'unknown';
	}

	private static function provider_from_file( string $file ): string {
		if ( '' === $file ) {
			return 'unknown';
		}
		$normalized = wp_normalize_path( $file );
		$plugins = defined( 'WP_PLUGIN_DIR' ) ? trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) : '';
		if ( '' !== $plugins && 0 === strpos( $normalized, $plugins ) ) {
			$relative = substr( $normalized, strlen( $plugins ) );
			$parts = explode( '/', $relative );
			return 'plugin:' . (string) ( $parts[0] ?? 'unknown' );
		}
		return 0 === strpos( $normalized, wp_normalize_path( ABSPATH ) ) ? 'wordpress-core' : 'unknown';
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts(
			array(
				'post_type' => 'post',
				'post_status' => 'any',
				'numberposts' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'suppress_filters' => false,
			)
		);
		return array_values( array_map( 'intval', is_array( $ids ) ? $ids : array() ) );
	}

	/** @param array<int,int> $post_ids */
	private static function editorial_fingerprint( array $post_ids ): string {
		$ctx = hash_init( 'sha256' );
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}
			$elementor = get_post_meta( $post_id, '_elementor_data', true );
			if ( is_array( $elementor ) ) {
				$elementor = wp_json_encode( $elementor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			}
			$line = implode(
				'|',
				array(
					(string) $post_id,
					(string) $post->post_modified_gmt,
					hash( 'sha256', (string) $post->post_content ),
					hash( 'sha256', is_string( $elementor ) ? $elementor : '' ),
				)
			);
			hash_update( $ctx, $line . "\n" );
		}
		return hash_final( $ctx );
	}
}
