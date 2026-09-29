<?php
/**
 * Diagnóstico read-only R-500/T502 da SPEC-005.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compara a busca WordPress atual com o conteúdo semântico extraído, sem persistência.
 *
 * ENGINEERING ONLY: habilitado apenas em build de homologação R-500.
 */
final class Search_Baseline_Diagnostic {

	public const ACTION    = 'bdc_kb_spec005_r500_baseline';
	public const PAGE_SLUG = 'bdc-kb-spec005-r500-baseline';

	private const NONCE_ACTION = 'bdc_kb_spec005_r500_baseline_run';
	private const NONCE_FIELD  = 'bdc_kb_spec005_r500_baseline_nonce';
	private const PROBE_LIMIT  = 60;
	private const RESULT_LIMIT = 20;
	private const TOKENIZER_VERSION = 'r500-diagnostic-1.0.0';

	/** @var array<int,string> */
	private const STOPWORDS = array(
		'a', 'ao', 'aos', 'as', 'com', 'como', 'da', 'das', 'de', 'do', 'dos',
		'e', 'em', 'na', 'nas', 'no', 'nos', 'o', 'os', 'ou', 'para', 'por',
		'que', 'se', 'sem', 'um', 'uma', 'uns', 'umas',
	);

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 40 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Diagnóstico Search R-500',
			'Diagnóstico Search R-500',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — Diagnóstico Search R-500', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Build temporário de engenharia.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Este diagnóstico é somente leitura. Não cria índice, tabela, Golden Query, telemetria, cache persistente ou alteração editorial.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'O relatório compara a pesquisa administrativa atual, a busca nativa do WordPress e a cobertura semântica fornecida pelo Content Extractor. O JSON exporta somente métricas agregadas, sem títulos, conteúdo ou consultas derivadas do corpus.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar diagnóstico read-only e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-r500-baseline-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$post_ids = self::post_ids();
		$before = self::editorial_snapshot( $post_ids );
		$before_hash = self::aggregate_fingerprint( $before );

		$status_counts = array();
		$source_counts = array();
		$coverage_by_source = array();
		$warnings = array();
		$errors = array();
		$extraction_ms = array();
		$semantic_posts = 0;
		$semantic_gap_posts = 0;
		$summary_signal_posts = 0;
		$summary_gap_posts = 0;
		$taxonomy_signal_posts = 0;
		$taxonomy_gap_posts = 0;
		$published_ids = array();

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				self::increment( $errors, 'POST_MISSING' );
				continue;
			}

			self::increment( $status_counts, (string) $post->post_status );
			if ( 'publish' === (string) $post->post_status ) {
				$published_ids[] = $post_id;
			}

			try {
				$t0 = microtime( true );
				$extraction = Content_Extractor::extract( $post_id );
				$extraction_ms[] = ( microtime( true ) - $t0 ) * 1000;
				if ( $extraction instanceof \WP_Error ) {
					self::increment( $errors, 'EXTRACTOR:' . $extraction->get_error_code() );
					continue;
				}

				$source_kind = sanitize_key( (string) ( $extraction['source_kind'] ?? 'unknown' ) );
				if ( '' === $source_kind ) {
					$source_kind = 'unknown';
				}
				self::increment( $source_counts, $source_kind );

				foreach ( (array) ( $extraction['warnings'] ?? array() ) as $warning ) {
					self::increment( $warnings, self::warning_family( (string) $warning ) );
				}

				$semantic_text = self::extraction_text( $extraction );
				$semantic_tokens = self::token_set( $semantic_text );
				$native_text = implode(
					' ',
					array(
						(string) $post->post_title,
						(string) $post->post_excerpt,
						wp_strip_all_tags( strip_shortcodes( (string) $post->post_content ) ),
					)
				);
				$native_tokens = self::token_set( $native_text );

				if ( ! empty( $semantic_tokens ) ) {
					++$semantic_posts;
					$coverage = self::coverage_percent( $semantic_tokens, $native_tokens );
					self::coverage_add( $coverage_by_source, $source_kind, $coverage );
					if ( $coverage < 99.999 ) {
						++$semantic_gap_posts;
					}
				}

				$summary_tokens = self::summary_tokens( $post_id );
				if ( ! empty( $summary_tokens ) ) {
					++$summary_signal_posts;
					if ( self::coverage_percent( $summary_tokens, $native_tokens ) < 99.999 ) {
						++$summary_gap_posts;
					}
				}

				$taxonomy_tokens = self::taxonomy_tokens( $post_id );
				if ( ! empty( $taxonomy_tokens ) ) {
					++$taxonomy_signal_posts;
					if ( self::coverage_percent( $taxonomy_tokens, $native_tokens ) < 99.999 ) {
						++$taxonomy_gap_posts;
					}
				}
			} catch ( \Throwable $error ) {
				self::increment( $errors, 'THROWABLE:' . get_class( $error ) );
			}
		}

		$probe_ids = self::sample_evenly( $published_ids, self::PROBE_LIMIT );
		$admin_probe = self::run_self_retrieval_probes( $probe_ids, 'admin_current' );
		$native_probe = self::run_self_retrieval_probes( $probe_ids, 'native_default' );

		$after_ids = self::post_ids();
		$after = self::editorial_snapshot( $after_ids );
		$after_hash = self::aggregate_fingerprint( $after );
		$changed = self::changed_snapshot_count( $before, $after );

		ksort( $status_counts );
		arsort( $source_counts );
		arsort( $warnings );
		ksort( $errors );
		$coverage_summary = self::coverage_summary( $coverage_by_source );

		$pass = hash_equals( $before_hash, $after_hash )
			&& 0 === $changed
			&& count( $post_ids ) === count( $after_ids )
			&& empty( $errors );

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'R-500/T502',
			'mode' => 'spec005_read_only_search_baseline',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
				'db_server' => self::db_server_info(),
			),
			'safety' => array(
				'read_only_design' => true,
				'creates_schema_or_table' => false,
				'persists_index' => false,
				'persists_golden_queries' => false,
				'persists_query_log' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
				'renders_elementor' => false,
				'renders_dynamic_blocks' => false,
				'exports_titles' => false,
				'exports_editorial_content' => false,
				'exports_generated_probe_queries' => false,
				'editorial_fingerprint_before' => $before_hash,
				'editorial_fingerprint_after' => $after_hash,
				'editorial_fingerprint_equal' => hash_equals( $before_hash, $after_hash ),
				'changed_posts_during_run' => $changed,
				'corpus_count_before' => count( $post_ids ),
				'corpus_count_after' => count( $after_ids ),
			),
			'corpus' => array(
				'total_posts' => count( $post_ids ),
				'published_posts' => count( $published_ids ),
				'by_status' => $status_counts,
				'source_kinds' => $source_counts,
			),
			'semantic_coverage' => array(
				'diagnostic_tokenizer_version' => self::TOKENIZER_VERSION,
				'posts_with_semantic_text' => $semantic_posts,
				'posts_with_semantic_tokens_not_fully_native_searchable' => $semantic_gap_posts,
				'by_source_kind' => $coverage_summary,
				'summary' => array(
					'posts_with_signal' => $summary_signal_posts,
					'posts_with_signal_not_fully_native_searchable' => $summary_gap_posts,
				),
				'taxonomy' => array(
					'posts_with_signal' => $taxonomy_signal_posts,
					'posts_with_signal_not_fully_native_searchable' => $taxonomy_gap_posts,
				),
			),
			'wordpress_search_baseline' => array(
				'probe_method' => 'deterministic_title_token_self_retrieval',
				'probe_limit' => self::PROBE_LIMIT,
				'result_limit' => self::RESULT_LIMIT,
				'current_admin_semantics' => $admin_probe,
				'native_default_semantics' => $native_probe,
			),
			'extractor' => array(
				'warnings' => $warnings,
				'errors' => $errors,
				'performance_ms' => self::timing_summary( $extraction_ms ),
			),
			'performance' => array(
				'total_runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'interpretation_rules' => array(
				'Coverage below 100% means Content Extractor exposes semantic tokens not represented in the native title/excerpt/post_content token set.',
				'Self-retrieval probes are diagnostic only and are not Golden Queries.',
				'The admin-current probe intentionally reproduces the Knowledge List ordering by modified DESC.',
				'The native-default probe removes that explicit ordering to isolate WordPress native search behavior.',
				'No storage decision is made by this report alone; R-500 + R-510 + G-520 remain required.',
			),
			'gate_result' => array(
				't502_read_only_safety_pass' => $pass,
				'r500_ready' => false,
				'reason' => 'T502 is baseline evidence only; R-500 also requires gap review, surface decision, benchmark interpretation and R-510 Golden Dataset.',
			),
		);
	}

	/** @return array<int,int> */
	private static function post_ids(): array {
		$ids = get_posts(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => 'any',
				'numberposts' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);
		return array_values( array_map( 'intval', is_array( $ids ) ? $ids : array() ) );
	}

	/** @param array<string,mixed> $extraction */
	private static function extraction_text( array $extraction ): string {
		$parts = array();
		foreach ( (array) ( $extraction['fragments'] ?? array() ) as $fragment ) {
			if ( ! is_array( $fragment ) ) {
				continue;
			}
			$text = isset( $fragment['text'] ) ? (string) $fragment['text'] : '';
			if ( '' !== trim( $text ) ) {
				$parts[] = $text;
			}
		}
		return implode( ' ', $parts );
	}

	/** @return array<string,bool> */
	private static function token_set( string $value ): array {
		$value = wp_strip_all_tags( $value );
		$value = function_exists( 'remove_accents' ) ? remove_accents( $value ) : $value;
		$value = function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
		$value = preg_replace( '/[^a-z0-9]+/u', ' ', $value ) ?? '';
		$tokens = preg_split( '/\s+/', trim( $value ) ) ?: array();
		$out = array();
		foreach ( $tokens as $token ) {
			if ( strlen( $token ) < 3 || in_array( $token, self::STOPWORDS, true ) ) {
				continue;
			}
			$out[ $token ] = true;
		}
		return $out;
	}

	/** @param array<string,bool> $expected @param array<string,bool> $available */
	private static function coverage_percent( array $expected, array $available ): float {
		if ( empty( $expected ) ) {
			return 100.0;
		}
		$matched = count( array_intersect_key( $expected, $available ) );
		return round( 100 * $matched / count( $expected ), 4 );
	}

	/** @return array<string,bool> */
	private static function summary_tokens( int $post_id ): array {
		$parts = array();
		foreach ( Meta_Contract::fields() as $definition ) {
			$value = get_post_meta( $post_id, (string) $definition['key'], true );
			if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
				$parts[] = (string) $value;
			}
		}
		return self::token_set( implode( ' ', $parts ) );
	}

	/** @return array<string,bool> */
	private static function taxonomy_tokens( int $post_id ): array {
		$parts = array();
		foreach ( Classification_Contract::fields() as $definition ) {
			$taxonomy = (string) ( $definition['taxonomy'] ?? '' );
			if ( '' === $taxonomy ) {
				continue;
			}
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
				continue;
			}
			foreach ( $terms as $term ) {
				if ( is_object( $term ) ) {
					$parts[] = (string) ( $term->name ?? '' );
					$parts[] = (string) ( $term->slug ?? '' );
				}
			}
		}
		return self::token_set( implode( ' ', $parts ) );
	}

	/** @param array<string,array<int,float>> $store */
	private static function coverage_add( array &$store, string $source_kind, float $coverage ): void {
		if ( ! isset( $store[ $source_kind ] ) ) {
			$store[ $source_kind ] = array();
		}
		$store[ $source_kind ][] = $coverage;
	}

	/** @param array<string,array<int,float>> $store @return array<string,array<string,float|int>> */
	private static function coverage_summary( array $store ): array {
		$out = array();
		foreach ( $store as $source => $values ) {
			sort( $values, SORT_NUMERIC );
			$out[ $source ] = array(
				'posts' => count( $values ),
				'mean_percent' => empty( $values ) ? 0.0 : round( array_sum( $values ) / count( $values ), 4 ),
				'p50_percent' => self::percentile( $values, 0.50 ),
				'p05_percent' => self::percentile( $values, 0.05 ),
				'min_percent' => empty( $values ) ? 0.0 : round( (float) min( $values ), 4 ),
			);
		}
		ksort( $out );
		return $out;
	}

	/** @param array<int,int> $post_ids @return array<string,mixed> */
	private static function run_self_retrieval_probes( array $post_ids, string $mode ): array {
		$latencies = array();
		$executed = 0;
		$skipped = 0;
		$top1 = 0;
		$top3 = 0;
		$top10 = 0;
		$top20 = 0;
		$missed = 0;

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				++$skipped;
				continue;
			}
			$query_text = self::probe_query_from_title( (string) $post->post_title );
			if ( '' === $query_text ) {
				++$skipped;
				continue;
			}

			$args = array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => self::RESULT_LIMIT,
				'fields' => 'ids',
				's' => $query_text,
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
				'suppress_filters' => false,
			);
			if ( 'admin_current' === $mode ) {
				$args['post_status'] = array( 'publish', 'draft', 'pending', 'private', 'future' );
				$args['orderby'] = 'modified';
				$args['order'] = 'DESC';
				$args['perm'] = 'editable';
			}

			$t0 = microtime( true );
			$query = new \WP_Query( $args );
			$latencies[] = ( microtime( true ) - $t0 ) * 1000;
			++$executed;

			$rank = 0;
			foreach ( (array) $query->posts as $index => $found_id ) {
				if ( (int) $found_id === $post_id ) {
					$rank = $index + 1;
					break;
				}
			}
			wp_reset_postdata();

			if ( 1 === $rank ) {
				++$top1;
			}
			if ( $rank > 0 && $rank <= 3 ) {
				++$top3;
			}
			if ( $rank > 0 && $rank <= 10 ) {
				++$top10;
			}
			if ( $rank > 0 && $rank <= self::RESULT_LIMIT ) {
				++$top20;
			}
			if ( 0 === $rank ) {
				++$missed;
			}
		}

		return array(
			'executed' => $executed,
			'skipped' => $skipped,
			'top1' => $top1,
			'top3' => $top3,
			'top10' => $top10,
			'top20' => $top20,
			'missed' => $missed,
			'top1_percent' => self::ratio( $top1, $executed ),
			'top3_percent' => self::ratio( $top3, $executed ),
			'top10_percent' => self::ratio( $top10, $executed ),
			'top20_percent' => self::ratio( $top20, $executed ),
			'latency_ms' => self::timing_summary( $latencies ),
		);
	}

	private static function probe_query_from_title( string $title ): string {
		$set = self::token_set( $title );
		$tokens = array_keys( $set );
		usort(
			$tokens,
			static function ( string $a, string $b ): int {
				$length = strlen( $b ) <=> strlen( $a );
				return 0 !== $length ? $length : strcmp( $a, $b );
			}
		);
		return implode( ' ', array_slice( $tokens, 0, 2 ) );
	}

	/** @param array<int,int> $ids @return array<int,int> */
	private static function sample_evenly( array $ids, int $limit ): array {
		$count = count( $ids );
		if ( $count <= $limit ) {
			return $ids;
		}
		$out = array();
		for ( $i = 0; $i < $limit; ++$i ) {
			$index = (int) floor( $i * ( $count - 1 ) / max( 1, $limit - 1 ) );
			$out[] = (int) $ids[ $index ];
		}
		return array_values( array_unique( $out ) );
	}

	/** @param array<int,float> $values @return array<string,float|int> */
	private static function timing_summary( array $values ): array {
		sort( $values, SORT_NUMERIC );
		return array(
			'count' => count( $values ),
			'mean' => empty( $values ) ? 0.0 : round( array_sum( $values ) / count( $values ), 3 ),
			'p50' => self::percentile( $values, 0.50 ),
			'p95' => self::percentile( $values, 0.95 ),
			'max' => empty( $values ) ? 0.0 : round( (float) max( $values ), 3 ),
		);
	}

	/** @param array<int,float> $values */
	private static function percentile( array $values, float $p ): float {
		if ( empty( $values ) ) {
			return 0.0;
		}
		sort( $values, SORT_NUMERIC );
		$index = (int) ceil( $p * count( $values ) ) - 1;
		$index = max( 0, min( count( $values ) - 1, $index ) );
		return round( (float) $values[ $index ], 4 );
	}

	private static function ratio( int $part, int $total ): float {
		return $total > 0 ? round( 100 * $part / $total, 2 ) : 0.0;
	}

	/** @param array<string,int> $counts */
	private static function increment( array &$counts, string $key ): void {
		$key = '' !== $key ? $key : '(empty)';
		$counts[ $key ] = (int) ( $counts[ $key ] ?? 0 ) + 1;
	}

	private static function warning_family( string $warning ): string {
		$pos = strpos( $warning, ':' );
		return false === $pos ? $warning : substr( $warning, 0, $pos );
	}

	/** @param array<int,int> $post_ids @return array<int,string> */
	private static function editorial_snapshot( array $post_ids ): array {
		$out = array();
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				$out[ $post_id ] = 'missing';
				continue;
			}
			$parts = array(
				(string) $post_id,
				(string) $post->post_status,
				(string) $post->post_modified_gmt,
				hash( 'sha256', (string) $post->post_title ),
				hash( 'sha256', (string) $post->post_excerpt ),
				hash( 'sha256', (string) $post->post_content ),
				hash( 'sha256', self::stable_value( get_post_meta( $post_id, '_elementor_data', true ) ) ),
			);
			foreach ( Meta_Contract::fields() as $definition ) {
				$parts[] = hash( 'sha256', self::stable_value( get_post_meta( $post_id, (string) $definition['key'], true ) ) );
			}
			foreach ( Classification_Contract::fields() as $definition ) {
				$ids = wp_get_object_terms( $post_id, (string) $definition['taxonomy'], array( 'fields' => 'ids' ) );
				$ids = is_wp_error( $ids ) ? array() : array_map( 'intval', (array) $ids );
				sort( $ids, SORT_NUMERIC );
				$parts[] = hash( 'sha256', wp_json_encode( $ids ) ?: '' );
			}
			$out[ $post_id ] = hash( 'sha256', implode( '|', $parts ) );
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

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		$ctx = hash_init( 'sha256' );
		foreach ( $snapshot as $post_id => $signature ) {
			hash_update( $ctx, (string) $post_id . ':' . $signature . "\n" );
		}
		return hash_final( $ctx );
	}

	/** @param array<int,string> $before @param array<int,string> $after */
	private static function changed_snapshot_count( array $before, array $after ): int {
		$count = 0;
		foreach ( array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) ) as $post_id ) {
			if ( ! isset( $before[ $post_id ], $after[ $post_id ] ) || $before[ $post_id ] !== $after[ $post_id ] ) {
				++$count;
			}
		}
		return $count;
	}

	private static function db_server_info(): string {
		global $wpdb;
		if ( isset( $wpdb ) && method_exists( $wpdb, 'db_version' ) ) {
			return (string) $wpdb->db_version();
		}
		return '';
	}
}
