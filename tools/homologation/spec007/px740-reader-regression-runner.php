<?php
/**
 * Plugin Name: BDC SPEC-007 PX-740 Reader Regression Runner
 * Description: Read-only technical regression runner for PX-740 Reader / Tips / Rail.
 * Version: 1.0.0
 * Author: BDC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BDC_SPEC007_PX740_Reader_Regression_Runner {
	private const ACTION = 'bdc_spec007_px740_reader_regression';
	private const NONCE  = 'bdc_spec007_px740_reader_regression_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'run_download' ) );
	}

	public static function menu(): void {
		add_management_page(
			'SPEC-007 PX-740 Reader Regression',
			'SPEC-007 PX-740',
			'manage_options',
			'bdc-spec007-px740-reader-regression',
			array( self::class, 'page' )
		);
	}

	public static function page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-px740-runner' ), '', array( 'response' => 403 ) );
		}
		echo '<div class="wrap"><h1>SPEC-007 PX-740 — Reader Regression</h1>';
		echo '<p>Runner somente leitura. Valida corpus/source kinds, Tips/Summary, pipeline de hooks e assets do Reader sem executar <code>the_content</code> e sem alterar conteúdo, metadados, tema ou plugins.</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE, self::NONCE );
		submit_button( 'Executar PX-740 e baixar JSON', 'primary' );
		echo '</form></div>';
	}

	public static function run_download(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( 'Método HTTP não permitido.', '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permissão insuficiente.', '', array( 'response' => 403 ) );
		}
		$nonce = isset( $_POST[ self::NONCE ] ) && is_scalar( $_POST[ self::NONCE ] )
			? wp_unslash( (string) $_POST[ self::NONCE ] )
			: '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			wp_die( 'Nonce inválido ou expirado.', '', array( 'response' => 403 ) );
		}

		$report = self::run();
		$json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			wp_die( 'Falha ao serializar relatório.', '', array( 'response' => 500 ) );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="spec007-px740-reader-regression-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	private static function run(): array {
		$started = microtime( true );
		$ids = get_posts(
			array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);

		$source_counts = array();
		$source_samples = array();
		$tips_samples = array();
		$summary_samples = array();
		$no_summary_samples = array();
		$errors = array();

		foreach ( (array) $ids as $raw_id ) {
			$post_id = absint( $raw_id );
			if ( $post_id <= 0 ) {
				continue;
			}

			$source_kind = self::source_kind( $post_id );
			$source_counts[ $source_kind ] = (int) ( $source_counts[ $source_kind ] ?? 0 ) + 1;
			if ( count( $source_samples[ $source_kind ] ?? array() ) < 3 ) {
				$source_samples[ $source_kind ][] = $post_id;
			}

			$tips_count = self::tips_count( $post_id );
			$summary_count = self::summary_count( $post_id );

			if ( $tips_count > 0 && count( $tips_samples ) < 5 ) {
				$tips_samples[] = array( 'post_id' => $post_id, 'tips_count' => $tips_count, 'source_kind' => $source_kind );
			}
			if ( $summary_count > 0 && count( $summary_samples ) < 5 ) {
				$summary_samples[] = array( 'post_id' => $post_id, 'summary_items' => $summary_count, 'source_kind' => $source_kind );
			}
			if ( 0 === $summary_count && count( $no_summary_samples ) < 5 ) {
				$no_summary_samples[] = array( 'post_id' => $post_id, 'source_kind' => $source_kind );
			}
		}

		ksort( $source_counts, SORT_STRING );
		ksort( $source_samples, SORT_STRING );

		$hooks = self::content_hook_inventory();
		$assets = self::reader_asset_inventory();

		$expected_kinds = array( 'legacy_html', 'plain_text', 'elementor', 'mixed', 'gutenberg' );
		$missing_kinds = array_values(
			array_filter(
				$expected_kinds,
				static fn ( string $kind ): bool => empty( $source_counts[ $kind ] )
			)
		);

		$required_hook_signals = array(
			'gac' => self::contains_any( $hooks['callables'], array( 'GAC\\', 'PostActions', 'KnowledgeBridge' ) ),
			'wpui' => self::contains_any( $hooks['callables'], array( 'WPUI', 'Unified', 'inject_anchors' ) ),
			'bdc_anchor' => self::contains_any( $hooks['callables'], array( 'Search_Anchor_Manager', 'BDC\\KnowledgeBase' ) ),
			'gre_tips' => self::contains_any( $hooks['callables'], array( 'Helpful_Tips_Renderer' ) ),
			'gre_summary' => self::contains_any( $hooks['callables'], array( 'Frontend_Renderer' ) ),
		);

		$technical_pass =
			empty( $missing_kinds )
			&& ! empty( $tips_samples )
			&& ! empty( $summary_samples )
			&& ! empty( $no_summary_samples )
			&& ! in_array( false, $required_hook_signals, true )
			&& ! in_array( false, $assets['checks'], true );

		return array(
			'schema_version' => '1.0.0',
			'spec' => 'SPEC-007',
			'gate' => 'PX-740',
			'mode' => 'reader_regression_read_only',
			'generated_at' => gmdate( 'c' ),
			'status' => $technical_pass ? 'TECHNICAL_PASS_VISUAL_SAMPLE_PENDING' : 'TECHNICAL_FAIL',
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'bdc_version' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'published_posts' => count( (array) $ids ),
			),
			'corpus' => array(
				'source_kind_counts' => $source_counts,
				'source_kind_samples' => $source_samples,
				'missing_required_source_kinds' => $missing_kinds,
			),
			'structured_reader_data' => array(
				'tips_samples' => $tips_samples,
				'summary_samples' => $summary_samples,
				'no_summary_samples' => $no_summary_samples,
				'values_exported' => false,
			),
			'the_content_pipeline' => array(
				'callables' => $hooks['callables'],
				'required_signals' => $required_hook_signals,
			),
			'reader_assets' => $assets,
			'manual_visual_sample' => self::manual_visual_sample( $source_samples, $tips_samples, $summary_samples, $no_summary_samples ),
			'safety' => array(
				'read_only' => true,
				'writes_post_content' => false,
				'writes_post_meta' => false,
				'writes_options' => false,
				'writes_theme' => false,
				'changes_plugins' => false,
				'executes_the_content' => false,
				'executes_shortcodes' => false,
				'calls_external_network' => false,
				'exports_editorial_content' => false,
			),
			'errors' => $errors,
			'runner' => array(
				'version' => '1.0.0',
				'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 3 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'next_action' => $technical_pass
				? 'Visually open the returned representative post IDs in the BDC candidate Reader. If all source kinds/data states render correctly, PX-740 can close.'
				: 'Review failed corpus/hook/asset signals before PX-740 closeout.',
		);
	}

	private static function source_kind( int $post_id ): string {
		if ( class_exists( '\\BDC\\KnowledgeBase\\Content_Extractor' ) ) {
			try {
				$extracted = \BDC\KnowledgeBase\Content_Extractor::extract( $post_id );
				if ( is_array( $extracted ) && ! empty( $extracted['source_kind'] ) ) {
					return sanitize_key( (string) $extracted['source_kind'] );
				}
			} catch ( \Throwable $error ) {
				// Fall through to the local read-only heuristic.
			}
		}

		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return 'unknown';
		}
		$content = (string) ( $post->post_content ?? '' );
		$elementor_data = get_post_meta( $post_id, '_elementor_data', true );
		$has_elementor = ( is_string( $elementor_data ) && '' !== trim( $elementor_data ) )
			|| 'builder' === (string) get_post_meta( $post_id, '_elementor_edit_mode', true );
		$has_blocks = function_exists( 'has_blocks' ) && has_blocks( $content );
		$has_html = $content !== wp_strip_all_tags( $content );
		$plain = '' !== trim( wp_strip_all_tags( $content ) );

		if ( $has_elementor && ( $has_blocks || $has_html || $plain ) ) {
			return 'elementor';
		}
		if ( $has_blocks && $has_html ) {
			return 'mixed';
		}
		if ( $has_blocks ) {
			return 'gutenberg';
		}
		if ( $has_html ) {
			return 'legacy_html';
		}
		return $plain ? 'plain_text' : 'empty';
	}

	private static function tips_count( int $post_id ): int {
		if ( class_exists( '\\BDC\\KnowledgeBase\\Helpful_Tips_Store' ) ) {
			try {
				$tips = \BDC\KnowledgeBase\Helpful_Tips_Store::read( $post_id );
				return is_array( $tips ) ? count( $tips ) : 0;
			} catch ( \Throwable $error ) {
				return 0;
			}
		}
		$value = get_post_meta( $post_id, '_bdc_es_helpful_tips', true );
		return is_array( $value ) ? count( $value ) : 0;
	}

	private static function summary_count( int $post_id ): int {
		if ( class_exists( '\\BDC\\KnowledgeBase\\Public_Article_Read_Model' ) ) {
			try {
				$model = \BDC\KnowledgeBase\Public_Article_Read_Model::read( $post_id );
				return is_array( $model ) ? count( (array) ( $model['summary_items'] ?? array() ) ) : 0;
			} catch ( \Throwable $error ) {
				return 0;
			}
		}

		$keys = array(
			'_bdc_es_objective',
			'_bdc_es_escalation',
			'_bdc_es_important',
			'_bdc_es_affected_service',
			'_bdc_es_systems_involved',
		);
		$count = 0;
		foreach ( $keys as $key ) {
			$value = get_post_meta( $post_id, $key, true );
			if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
				++$count;
			}
		}
		return $count;
	}

	private static function content_hook_inventory(): array {
		global $wp_filter;
		$callables = array();
		$hook = $wp_filter['the_content'] ?? null;
		if ( $hook instanceof WP_Hook ) {
			foreach ( (array) $hook->callbacks as $priority => $rows ) {
				foreach ( (array) $rows as $row ) {
					$callable = $row['function'] ?? null;
					$callables[] = array(
						'priority' => (int) $priority,
						'callable' => self::callable_name( $callable ),
					);
				}
			}
		}
		return array( 'callables' => $callables );
	}

	private static function callable_name( mixed $callable ): string {
		if ( is_string( $callable ) ) {
			return $callable;
		}
		if ( is_array( $callable ) && 2 === count( $callable ) ) {
			$owner = is_object( $callable[0] ) ? get_class( $callable[0] ) : (string) $callable[0];
			return $owner . '::' . (string) $callable[1];
		}
		if ( $callable instanceof Closure ) {
			return 'Closure';
		}
		if ( is_object( $callable ) ) {
			return get_class( $callable );
		}
		return gettype( $callable );
	}

	private static function contains_any( array $rows, array $needles ): bool {
		foreach ( $rows as $row ) {
			$value = strtolower( (string) ( $row['callable'] ?? '' ) );
			foreach ( $needles as $needle ) {
				if ( str_contains( $value, strtolower( $needle ) ) ) {
					return true;
				}
			}
		}
		return false;
	}

	private static function reader_asset_inventory(): array {
		$base = defined( 'BDC_KB_DIR' ) ? trailingslashit( BDC_KB_DIR ) : '';
		$targets = array(
			'public_reader_js' => 'assets/js/public-reader.js',
			'public_search_js' => 'assets/js/public-search.js',
			'public_article_css' => 'assets/css/public-article.css',
			'article_template' => 'templates/public-article-preview.php',
			'article_content' => 'includes/class-public-article-content.php',
		);
		$files = array();
		foreach ( $targets as $key => $relative ) {
			$path = $base . $relative;
			$files[ $key ] = array(
				'relative' => $relative,
				'exists' => '' !== $base && is_file( $path ),
				'bytes' => '' !== $base && is_file( $path ) ? (int) filesize( $path ) : 0,
				'sha256' => '' !== $base && is_file( $path ) ? hash_file( 'sha256', $path ) : '',
			);
		}

		$reader_source = '';
		if ( ! empty( $files['public_reader_js']['exists'] ) ) {
			$reader_source = (string) file_get_contents( $base . $targets['public_reader_js'] );
		}

		return array(
			'files' => $files,
			'checks' => array(
				'all_required_files_present' => ! in_array( false, array_map( static fn ( array $row ): bool => (bool) $row['exists'], $files ), true ),
				'reader_has_flow_state' => str_contains( $reader_source, "setState('flow')" ),
				'reader_has_fixed_state' => str_contains( $reader_source, "setState('fixed'" ),
				'reader_has_bottom_state' => str_contains( $reader_source, "setState('bottom')" ),
				'reader_has_no_translate3d' => ! str_contains( $reader_source, 'translate3d(' ),
			),
		);
	}

	private static function manual_visual_sample( array $source_samples, array $tips_samples, array $summary_samples, array $no_summary_samples ): array {
		$out = array(
			'by_source_kind' => array(),
			'with_tips' => $tips_samples[0] ?? null,
			'with_summary' => $summary_samples[0] ?? null,
			'without_summary' => $no_summary_samples[0] ?? null,
		);
		foreach ( array( 'legacy_html', 'plain_text', 'elementor', 'mixed', 'gutenberg' ) as $kind ) {
			$out['by_source_kind'][ $kind ] = $source_samples[ $kind ][0] ?? null;
		}
		return $out;
	}
}

add_action( 'plugins_loaded', array( BDC_SPEC007_PX740_Reader_Regression_Runner::class, 'register' ) );
