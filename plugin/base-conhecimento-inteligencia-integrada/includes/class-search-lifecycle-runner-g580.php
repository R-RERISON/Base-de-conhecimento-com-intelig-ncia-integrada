<?php
/**
 * G-580 — Lifecycle / rebuild / retention environmental runner.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Lifecycle_Runner_G580 {

	public const ACTION = 'bdc_kb_spec005_g580_lifecycle';
	public const PAGE_SLUG = 'bdc-kb-spec005-g580-lifecycle';

	private const NONCE_ACTION = 'bdc_kb_spec005_g580_lifecycle';
	private const NONCE_FIELD = 'bdc_kb_spec005_g580_lifecycle_nonce';

	/** @var array<int,string> */
	private const ALLOWED_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 47 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Lifecycle G-580',
			'Lifecycle G-580',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — G-580 Lifecycle', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p>';
		echo esc_html__( 'Valida activation/update leve, fallback, disable, rebuild explícito e retenção de uninstall. O runner escreve somente na Search Projection derivada e na Option de estado; conteúdo editorial permanece read-only.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar G-580 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-g580-lifecycle-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	public static function disable_search(): bool {
		return false;
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function run(): array {
		$started = microtime( true );
		$errors = array();
		$throwables = array();
		$post_ids_before = self::corpus_ids();
		$editorial_before = self::editorial_snapshot( $post_ids_before );
		$editorial_fingerprint_before = Canonical_JSON::hash( $editorial_before );

		$state_before = Search_Projection_Repository::state();
		$schema_before = Search_Projection_Repository::schema_exists();
		$rows_before = self::row_count();
		$snapshot_before = self::projection_snapshot_hash();

		$lifecycle = Search_Lifecycle::prepare_schema();
		$rows_after_prepare = self::row_count();
		$snapshot_after_prepare = self::projection_snapshot_hash();

		$prepare_did_not_reindex = $schema_before
			? $rows_before === $rows_after_prepare && $snapshot_before === $snapshot_after_prepare
			: 0 === (int) $rows_after_prepare;

		$t580 = true === (bool) ( $lifecycle['schema_after'] ?? false )
			&& false === (bool) ( $lifecycle['implicit_rebuild'] ?? true )
			&& $prepare_did_not_reindex
			&& empty( $lifecycle['errors'] )
			&& empty( $lifecycle['throwables'] );

		$state_for_probes = Search_Projection_Repository::state();
		$fallback_probe = array();
		$disable_probe = array();

		try {
			Search_Projection_Repository::write_state(
				array(
					'status' => 'degraded',
					'corpus_count' => (int) ( $state_for_probes['corpus_count'] ?? 0 ),
					'source_fingerprint' => (string) ( $state_for_probes['source_fingerprint'] ?? '' ),
					'last_success_at_gmt' => (string) ( $state_for_probes['last_success_at_gmt'] ?? '' ),
					'last_error_code' => 'g580_fallback_probe',
				)
			);
			$fallback_probe = Search_Service::search( 'Windows 11', 5 );
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'fallback_probe', $error );
		} finally {
			update_option( Search_Projection_Repository::STATE_OPTION, $state_for_probes, false );
		}

		try {
			add_filter( 'bdc_kb_search_enabled', array( self::class, 'disable_search' ), PHP_INT_MAX );
			$disable_probe = Search_Service::search( 'Windows 11', 5 );
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'disable_probe', $error );
		} finally {
			remove_filter( 'bdc_kb_search_enabled', array( self::class, 'disable_search' ), PHP_INT_MAX );
		}

		$t581_fallback = 'degraded' === (string) ( $fallback_probe['state'] ?? '' )
			&& 'wordpress_fallback' === (string) ( $fallback_probe['retrieval_mode'] ?? '' )
			&& 'projection_not_ready' === (string) ( $fallback_probe['degraded_reason'] ?? '' );

		$t582 = 'degraded' === (string) ( $disable_probe['state'] ?? '' )
			&& 'wordpress_fallback' === (string) ( $disable_probe['retrieval_mode'] ?? '' )
			&& 'search_module_disabled' === (string) ( $disable_probe['degraded_reason'] ?? '' );

		$rebuild = array();
		try {
			$rebuild = Search_Rebuild_Service::rebuild();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'explicit_rebuild', $error );
		}

		$rebuild_state_after = is_array( $rebuild['state_after'] ?? null ) ? $rebuild['state_after'] : array();
		$rebuild_pass2 = is_array( $rebuild['pass2'] ?? null ) ? $rebuild['pass2'] : array();
		$rebuild_determinism = is_array( $rebuild['determinism'] ?? null ) ? $rebuild['determinism'] : array();

		$t581_rebuild = 'PASS' === (string) ( $rebuild['status'] ?? '' )
			&& 'ready' === (string) ( $rebuild_state_after['status'] ?? '' )
			&& 0 === (int) ( $rebuild_pass2['written'] ?? -1 )
			&& (int) ( $rebuild['corpus_count'] ?? 0 ) === (int) ( $rebuild_pass2['no_change'] ?? -1 )
			&& 0 === (int) ( $rebuild_determinism['mismatch_count'] ?? -1 );

		$t581 = $t581_fallback && $t581_rebuild;

		$retention = self::retention_contract();
		$t583 = ! empty( $retention['uninstall_file_exists'] )
			&& ! empty( $retention['uninstall_non_destructive'] )
			&& ! empty( $retention['deactivation_non_destructive'] );

		$post_ids_after = self::corpus_ids();
		$editorial_after = self::editorial_snapshot( $post_ids_after );
		$editorial_fingerprint_after = Canonical_JSON::hash( $editorial_after );
		$editorial_equal = hash_equals( $editorial_fingerprint_before, $editorial_fingerprint_after )
			&& $post_ids_before === $post_ids_after;

		$runtime_safety = self::runtime_source_safety();
		$state_after = Search_Projection_Repository::state();

		$t584 = $t580
			&& $t581
			&& $t582
			&& $t583
			&& $editorial_equal
			&& 'ready' === (string) ( $state_after['status'] ?? '' )
			&& empty( $errors )
			&& empty( $throwables )
			&& ! empty( $runtime_safety['no_asi'] )
			&& ! empty( $runtime_safety['no_network'] )
			&& ! empty( $runtime_safety['no_query_logging'] )
			&& ! empty( $runtime_safety['ranker_version_frozen'] );

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'G-580',
			'mode' => 'spec005_lifecycle_rebuild_environmental',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
				'db_server' => self::db_version(),
			),
			'contract' => array(
				'reference' => 'specs/005-search-lexical-golden-queries/g580-lifecycle-rebuild-contract-v1.md',
				'activation_mass_rebuild_allowed' => false,
				'update_mass_rebuild_allowed' => false,
				'uninstall_retention_default' => true,
				'new_persistent_enable_option' => false,
			),
			'activation_update' => array(
				'schema_before' => $schema_before,
				'rows_before' => $rows_before,
				'projection_snapshot_before' => $snapshot_before,
				'result' => $lifecycle,
				'rows_after_prepare' => $rows_after_prepare,
				'projection_snapshot_after_prepare' => $snapshot_after_prepare,
				'prepare_did_not_reindex' => $prepare_did_not_reindex,
			),
			'fallback_probe' => self::public_search_response( $fallback_probe ),
			'disable_probe' => self::public_search_response( $disable_probe ),
			'explicit_rebuild' => $rebuild,
			'retention' => $retention,
			'safety' => array(
				'editorial_fingerprint_before' => $editorial_fingerprint_before,
				'editorial_fingerprint_after' => $editorial_fingerprint_after,
				'editorial_fingerprint_equal' => $editorial_equal,
				'corpus_ids_equal' => $post_ids_before === $post_ids_after,
				'state_before' => $state_before,
				'state_after' => $state_after,
				'runtime_source' => $runtime_safety,
				'calls_external_network' => false,
				'persists_query_log' => false,
				'depends_on_asi' => false,
			),
			'errors' => $errors,
			'throwables' => $throwables,
			'runner' => array(
				'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_result' => array(
				't580_activation_update_pass' => $t580,
				't581_rebuild_fallback_pass' => $t581,
				't582_disable_module_pass' => $t582,
				't583_uninstall_retention_pass' => $t583,
				't584_g580_pass' => $t584,
				'next_gate' => $t584 ? 'G-585' : 'G-580',
			),
			'interpretation_rules' => array(
				'Activation/update prepara schema, mas nunca reconstrói o corpus implicitamente.',
				'Fallback é obrigatório quando Projection não está ready ou quando o módulo Search está desabilitado.',
				'Uninstall v1 preserva tabela e Option por default; limpeza destrutiva exige gate futuro.',
				'Rebuild explícito pode escrever apenas na Search Projection derivada e na Option de estado.',
				'G-580 PASS não equivale a RC ou produção; G-585 continua obrigatório.',
			),
		);
	}

	/**
	 * @param array<string,mixed> $response
	 * @return array<string,mixed>
	 */
	private static function public_search_response( array $response ): array {
		return array(
			'state' => (string) ( $response['state'] ?? '' ),
			'retrieval_mode' => (string) ( $response['retrieval_mode'] ?? '' ),
			'count' => (int) ( $response['count'] ?? 0 ),
			'degraded_reason' => (string) ( $response['degraded_reason'] ?? '' ),
			'error_code' => (string) ( $response['error_code'] ?? '' ),
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function retention_contract(): array {
		$uninstall_path = BDC_KB_DIR . 'uninstall.php';
		$lifecycle_path = BDC_KB_DIR . 'includes/class-search-lifecycle.php';
		$uninstall = is_readable( $uninstall_path ) ? file_get_contents( $uninstall_path ) : false;
		$lifecycle = is_readable( $lifecycle_path ) ? file_get_contents( $lifecycle_path ) : false;

		$destructive = '/DROP\s+TABLE|TRUNCATE\s+TABLE|delete_option\s*\(|wp_delete_post\s*\(|delete_post_meta\s*\(|wp_delete_term\s*\(/i';

		return array(
			'uninstall_file_exists' => is_string( $uninstall ),
			'uninstall_non_destructive' => is_string( $uninstall )
				&& 1 !== preg_match( $destructive, self::strip_php_comments( $uninstall ) ),
			'deactivation_non_destructive' => is_string( $lifecycle )
				&& str_contains( $lifecycle, 'public static function deactivate(): void' )
				&& 1 !== preg_match( $destructive, self::strip_php_comments( $lifecycle ) ),
			'table_retained_by_default' => true,
			'state_option_retained_by_default' => true,
		);
	}

	/**
	 * @return array<string,bool|string>
	 */
	private static function runtime_source_safety(): array {
		$paths = array(
			BDC_KB_DIR . 'includes/class-search-lifecycle.php',
			BDC_KB_DIR . 'includes/class-search-rebuild-service.php',
			BDC_KB_DIR . 'includes/class-search-service.php',
			BDC_KB_DIR . 'includes/class-search-lifecycle-runner-g580.php',
		);
		$source = '';

		foreach ( $paths as $path ) {
			if ( is_readable( $path ) ) {
				$content = file_get_contents( $path );
				if ( is_string( $content ) ) {
					$source .= "\n" . $content;
				}
			}
		}

		$source_without_comments = self::strip_php_comments( $source );

		return array(
			'no_asi' => 1 !== preg_match( '/\basi(?:4)?_/i', $source_without_comments ),
			'no_network' => 1 !== preg_match( '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|(?<![A-Za-z0-9_])fsockopen\s*\(|(?<![A-Za-z0-9_])stream_socket_client\s*\(/i', $source_without_comments ),
			'no_query_logging' => ! str_contains( $source_without_comments, 'bdc_kb_search_query_log' ),
			'ranker_version_frozen' => 'lexical-ranker-v1.0.0' === Lexical_Ranker::VERSION,
			'ranker_sha256' => is_readable( BDC_KB_DIR . 'includes/class-lexical-ranker.php' )
				? hash_file( 'sha256', BDC_KB_DIR . 'includes/class-lexical-ranker.php' )
				: '',
		);
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

	/**
	 * @param array<int,int> $post_ids
	 * @return array<int,string>
	 */
	private static function editorial_snapshot( array $post_ids ): array {
		$out = array();

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				$out[ $post_id ] = 'missing';
				continue;
			}

			$summary = array();
			foreach ( Meta_Contract::fields() as $definition ) {
				$summary[ (string) $definition['key'] ] = self::stable_value(
					get_post_meta( $post_id, (string) $definition['key'], true )
				);
			}

			$taxonomies = array();
			foreach ( Classification_Contract::fields() as $definition ) {
				$taxonomy = (string) $definition['taxonomy'];
				$term_ids = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
				if ( is_wp_error( $term_ids ) ) {
					$taxonomies[ $taxonomy ] = array( 'error' => $term_ids->get_error_code() );
				} else {
					$ids = array_values( array_map( 'intval', is_array( $term_ids ) ? $term_ids : array() ) );
					sort( $ids, SORT_NUMERIC );
					$taxonomies[ $taxonomy ] = $ids;
				}
			}

			$out[ $post_id ] = Canonical_JSON::hash(
				array(
					'post_id' => $post_id,
					'post_type' => (string) $post->post_type,
					'post_status' => (string) $post->post_status,
					'post_modified_gmt' => (string) $post->post_modified_gmt,
					'post_title' => (string) $post->post_title,
					'post_excerpt' => (string) $post->post_excerpt,
					'post_content' => (string) $post->post_content,
					'elementor_data' => self::stable_value( get_post_meta( $post_id, '_elementor_data', true ) ),
					'summary' => $summary,
					'taxonomies' => $taxonomies,
				)
			);
		}

		ksort( $out, SORT_NUMERIC );
		return $out;
	}

	private static function stable_value( mixed $value ): string {
		if ( is_string( $value ) ) {
			return $value;
		}
		if ( is_scalar( $value ) || null === $value ) {
			return (string) $value;
		}
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : maybe_serialize( $value );
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
