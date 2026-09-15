<?php
/**
 * Smoke ambiental temporário e read-only do G-220.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TEMPORÁRIO: remover/desabilitar após evidência ambiental do G-220.
 */
final class Content_Extractor_Smoke {

	public const ACTION    = 'bdc_kb_spec004_g220_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-g220-smoke';

	private const NONCE_ACTION = 'bdc_kb_spec004_g220_smoke_run';
	private const NONCE_FIELD  = 'bdc_kb_spec004_g220_smoke_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 31 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Smoke G-220',
			'Smoke G-220',
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
		echo '<h1>' . esc_html__( 'SPEC-004 — Smoke G-220', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Build temporário de homologação.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'O runner percorre o corpus com o Content Extractor read-only, não exporta conteúdo editorial e mede fingerprint antes/depois.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'Evite edição concorrente de posts durante a execução para que o fingerprint represente apenas o comportamento do extractor.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar smoke read-only e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
			wp_die( esc_html__( 'Falha ao serializar o relatório.', 'bdc-knowledge-base' ), '', array( 'response' => 500 ) );
		}

		$filename = 'bdc-kb-spec004-g220-smoke-' . gmdate( 'Ymd-His' ) . '.json';
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$snapshot_before = self::editorial_snapshot( $ids_before );
		$fingerprint_before = self::aggregate_fingerprint( $snapshot_before );

		$source_kinds = array();
		$compatibility = array();
		$strategies = array();
		$warnings = array();
		$structure = Legacy_HTML_Adapter::empty_structure();
		$fragments_total = 0;
		$extractor_errors = 0;
		$throwables = 0;

		foreach ( $ids_before as $post_id ) {
			try {
				$result = Content_Extractor::extract( $post_id );
				if ( is_wp_error( $result ) ) {
					++$extractor_errors;
					self::increment( $warnings, 'EXTRACTOR_ERROR:' . self::safe_token( $result->get_error_code() ) );
					continue;
				}

				self::increment( $source_kinds, (string) ( $result['source_kind'] ?? 'unknown' ) );
				$status = isset( $result['elementor_compatibility']['status'] )
					? (string) $result['elementor_compatibility']['status']
					: 'unknown';
				self::increment( $compatibility, $status );

				foreach ( (array) ( $result['strategies'] ?? array() ) as $strategy ) {
					self::increment( $strategies, self::safe_token( (string) $strategy ) );
				}
				foreach ( (array) ( $result['warnings'] ?? array() ) as $warning ) {
					self::increment( $warnings, self::safe_warning( (string) $warning ) );
				}
				foreach ( $structure as $key => $value ) {
					$structure[ $key ] = $value + (int) ( $result['structure'][ $key ] ?? 0 );
				}
				$fragments_total += count( (array) ( $result['fragments'] ?? array() ) );
			} catch ( \Throwable $error ) {
				unset( $error );
				++$throwables;
				self::increment( $warnings, 'EXTRACTOR_THROWABLE' );
			}
		}

		self::sort_counts( $source_kinds );
		self::sort_counts( $compatibility );
		self::sort_counts( $strategies );
		self::sort_counts( $warnings );

		$ids_after = self::post_ids();
		$snapshot_after = self::editorial_snapshot( $ids_after );
		$fingerprint_after = self::aggregate_fingerprint( $snapshot_after );
		$changed_count = self::changed_snapshot_count( $snapshot_before, $snapshot_after );

		return array(
			'schema_version' => '1.0.0',
			'mode' => 'temporary_spec004_g220_read_only_smoke',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
				'domdocument_available' => class_exists( '\DOMDocument' ),
				'multisite' => is_multisite(),
			),
			'safety' => array(
				'read_only_design' => true,
				'exports_editorial_content' => false,
				'exports_post_ids' => false,
				'exports_titles_or_urls' => false,
				'executes_shortcodes' => false,
				'renders_elementor' => false,
				'renders_dynamic_blocks' => false,
				'persists_progress_or_results' => false,
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => hash_equals( $fingerprint_before, $fingerprint_after ),
				'changed_posts_during_run' => $changed_count,
				'corpus_count_before' => count( $ids_before ),
				'corpus_count_after' => count( $ids_after ),
			),
			'extraction' => array(
				'total_posts' => count( $ids_before ),
				'source_kinds' => $source_kinds,
				'elementor_compatibility' => $compatibility,
				'strategies' => $strategies,
				'warnings' => $warnings,
				'structure' => $structure,
				'fragments_total' => $fragments_total,
				'extractor_errors' => $extractor_errors,
				'throwables' => $throwables,
			),
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_expectations' => array(
				'editorial_fingerprint_equal' => true,
				'changed_posts_during_run' => 0,
				'corpus_count_unchanged' => true,
				'extractor_errors' => 0,
				'throwables' => 0,
			),
		);
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts(
			array(
				'post_type' => 'post',
				'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);

		if ( ! is_array( $ids ) ) {
			return array();
		}
		return array_values( array_map( 'intval', $ids ) );
	}

	/** @param array<int,int> $ids @return array<int,string> */
	private static function editorial_snapshot( array $ids ): array {
		$snapshot = array();
		foreach ( $ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$elementor = get_post_meta( $post_id, '_elementor_data', true );
			$elementor_string = is_string( $elementor ) ? $elementor : self::stable_json( $elementor );
			$snapshot[ $post_id ] = hash(
				'sha256',
				implode(
					"\n",
					array(
						(string) $post_id,
						(string) ( $post->post_status ?? '' ),
						(string) ( $post->post_modified_gmt ?? '' ),
						hash( 'sha256', (string) ( $post->post_content ?? '' ) ),
						hash( 'sha256', $elementor_string ),
					)
				)
			);
		}
		ksort( $snapshot, SORT_NUMERIC );
		return $snapshot;
	}

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		$parts = array();
		foreach ( $snapshot as $post_id => $fingerprint ) {
			$parts[] = $post_id . ':' . $fingerprint;
		}
		return hash( 'sha256', implode( "\n", $parts ) );
	}

	/** @param array<int,string> $before @param array<int,string> $after */
	private static function changed_snapshot_count( array $before, array $after ): int {
		$keys = array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) );
		$changed = 0;
		foreach ( $keys as $key ) {
			if ( ( $before[ $key ] ?? null ) !== ( $after[ $key ] ?? null ) ) {
				++$changed;
			}
		}
		return $changed;
	}

	/** @param array<string,int> $counts */
	private static function increment( array &$counts, string $key ): void {
		if ( '' === $key ) {
			$key = 'unknown';
		}
		$counts[ $key ] = (int) ( $counts[ $key ] ?? 0 ) + 1;
	}

	/** @param array<string,int> $counts */
	private static function sort_counts( array &$counts ): void {
		arsort( $counts, SORT_NUMERIC );
	}

	private static function safe_warning( string $warning ): string {
		$warning = trim( $warning );
		if ( '' === $warning ) {
			return 'UNKNOWN_WARNING';
		}
		if ( strlen( $warning ) > 160 ) {
			return substr( $warning, 0, 160 );
		}
		return preg_replace( '/[^A-Za-z0-9_:\/.-]/', '_', $warning ) ?? 'UNKNOWN_WARNING';
	}

	private static function safe_token( string $value ): string {
		$value = preg_replace( '/[^A-Za-z0-9_:\/.-]/', '_', $value ) ?? '';
		return '' === $value ? 'unknown' : substr( $value, 0, 120 );
	}

	private static function stable_json( mixed $value ): string {
		$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}
}
