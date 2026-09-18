<?php
/**
 * Baseline independente das Golden Candidates para R-510/T511.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Executa a fixture própria contra três semânticas WP_Query sem persistência.
 *
 * ENGINEERING ONLY: build temporário R-510.
 */
final class Golden_Baseline_Runner {

	public const ACTION    = 'bdc_kb_spec005_r510_golden_baseline';
	public const PAGE_SLUG = 'bdc-kb-spec005-r510-golden-baseline';

	private const NONCE_ACTION = 'bdc_kb_spec005_r510_golden_baseline_run';
	private const NONCE_FIELD  = 'bdc_kb_spec005_r510_golden_baseline_nonce';
	private const RESULT_LIMIT = 20;

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 42 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Golden Baseline R-510',
			'Golden Baseline R-510',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — Golden Baseline Independente R-510/T511', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Build temporário de engenharia.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Usa exclusivamente a Golden Candidate Fixture própria da SPEC-005. Não consulta plugin, tabela, option, hook ou função do ASI.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'Compara o comportamento administrativo atual com a relevância nativa do WordPress mantendo expected post/max rank da fixture governada.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar Golden baseline independente e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-r510-golden-baseline-independent-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$before = self::editorial_snapshot( $ids_before );
		$before_hash = self::aggregate_fingerprint( $before );

		$rows = Golden_Candidate_Seed::all();
		$errors = array();
		$results = array();
		$mode_totals = array(
			'admin_current' => self::empty_totals(),
			'admin_relevance' => self::empty_totals(),
			'publish_native' => self::empty_totals(),
		);

		foreach ( $rows as $row ) {
			$query = trim( sanitize_text_field( (string) ( $row['query'] ?? '' ) ) );
			$expected = absint( $row['expected_post_id'] ?? 0 );
			$max_rank = max( 1, min( 50, absint( $row['max_rank'] ?? 3 ) ) );

			if ( '' === $query || $expected <= 0 ) {
				$errors[] = 'INVALID_CANDIDATE:' . sanitize_key( (string) ( $row['id'] ?? 'unknown' ) );
				continue;
			}

			$modes = array();
			foreach ( array_keys( $mode_totals ) as $mode ) {
				$probe = self::probe( $query, $expected, $max_rank, $mode );
				$modes[ $mode ] = $probe;
				self::accumulate( $mode_totals[ $mode ], $probe );
			}

			$results[] = array(
				'id' => (string) ( $row['id'] ?? '' ),
				'legacy_id' => absint( $row['legacy_id'] ?? 0 ),
				'query' => $query,
				'expected_post_id' => $expected,
				'max_rank' => $max_rank,
				'legacy_severity' => (string) ( $row['legacy_severity'] ?? 'warning' ),
				'expected_item_key' => (string) ( $row['expected_item_key'] ?? '' ),
				'modes' => $modes,
				'baseline_classification' => self::classify( $modes ),
			);
		}

		foreach ( $mode_totals as &$totals ) {
			$totals = self::finalize_totals( $totals );
		}
		unset( $totals );

		$ids_after = self::post_ids();
		$after = self::editorial_snapshot( $ids_after );
		$after_hash = self::aggregate_fingerprint( $after );
		$changed = self::changed_snapshot_count( $before, $after );

		$safety_pass = hash_equals( $before_hash, $after_hash )
			&& 0 === $changed
			&& count( $ids_before ) === count( $ids_after )
			&& empty( $errors );

		return array(
			'schema_version' => '1.1.0',
			'gate' => 'R-510/T511',
			'mode' => 'spec005_independent_golden_baseline',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
			),
			'independence' => array(
				'asi_runtime_dependency' => false,
				'reads_asi_tables' => false,
				'reads_asi_options' => false,
				'calls_asi_classes_or_functions' => false,
				'uses_asi_hooks' => false,
				'candidate_source' => 'spec005_owned_fixture',
				'candidate_fixture_version' => Golden_Candidate_Seed::VERSION,
				'candidate_source_hash' => Golden_Candidate_Seed::SOURCE_SET_HASH,
			),
			'safety' => array(
				'read_only_design' => true,
				'creates_or_alters_schema' => false,
				'writes_wordpress' => false,
				'persists_results' => false,
				'changes_global_search_hooks' => false,
				'calls_external_network' => false,
				'editorial_fingerprint_before' => $before_hash,
				'editorial_fingerprint_after' => $after_hash,
				'editorial_fingerprint_equal' => hash_equals( $before_hash, $after_hash ),
				'changed_posts_during_run' => $changed,
				'corpus_count_before' => count( $ids_before ),
				'corpus_count_after' => count( $ids_after ),
			),
			'candidate_reference' => array(
				'count' => count( $rows ),
				'version' => Golden_Candidate_Seed::VERSION,
				'source_hash' => Golden_Candidate_Seed::SOURCE_SET_HASH,
			),
			'baseline' => array(
				'result_limit' => self::RESULT_LIMIT,
				'modes' => $mode_totals,
				'results' => $results,
			),
			'errors' => $errors,
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'interpretation_rules' => array(
				'This runner uses only the SPEC-005-owned candidate fixture; ASI may be absent or disabled.',
				'admin_current reproduces Knowledge List: editable statuses + modified DESC.',
				'admin_relevance keeps the same admin scope but removes modified DESC.',
				'publish_native uses publish-only native WordPress search as reference.',
				'If admin_relevance fixes a failure, the gap is primarily ordering/ranking.',
				'If all modes fail, the case remains a retrieval/coverage/normalization candidate for the new Search Document.',
			),
			'gate_result' => array(
				't511_read_only_safety_pass' => $safety_pass,
				'asi_independence_pass' => true,
				'golden_candidates_measured' => count( $rows ),
				'r510_ready' => false,
				'reason' => 'T511 measures candidate behavior only; human review/severity/diversity/set version remain required.',
			),
		);
	}

	/** @return array<string,mixed> */
	private static function probe( string $query_text, int $expected_post_id, int $max_rank, string $mode ): array {
		$args = array(
			'post_type' => Meta_Contract::POST_TYPE,
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
		} elseif ( 'admin_relevance' === $mode ) {
			$args['post_status'] = array( 'publish', 'draft', 'pending', 'private', 'future' );
			$args['perm'] = 'editable';
		} else {
			$args['post_status'] = 'publish';
		}

		$t0 = microtime( true );
		$wp_query = new \WP_Query( $args );
		$latency = ( microtime( true ) - $t0 ) * 1000;
		$ids = array_map( 'intval', (array) $wp_query->posts );
		wp_reset_postdata();

		$rank = 0;
		foreach ( $ids as $index => $id ) {
			if ( $id === $expected_post_id ) {
				$rank = $index + 1;
				break;
			}
		}

		return array(
			'actual_rank' => $rank,
			'pass_legacy_max_rank' => $rank > 0 && $rank <= $max_rank,
			'found_top20' => $rank > 0,
			'result_count_sampled' => count( $ids ),
			'latency_ms' => round( $latency, 4 ),
			'top_post_ids' => array_slice( $ids, 0, 10 ),
		);
	}

	/** @param array<string,array<string,mixed>> $modes */
	private static function classify( array $modes ): string {
		if ( ! empty( $modes['admin_current']['pass_legacy_max_rank'] ) ) {
			return 'already_passes_current_admin';
		}
		if ( ! empty( $modes['admin_relevance']['pass_legacy_max_rank'] ) ) {
			return 'ordering_ranking_gap';
		}
		if ( ! empty( $modes['publish_native']['pass_legacy_max_rank'] ) ) {
			return 'admin_scope_or_ordering_gap';
		}
		return 'retrieval_coverage_or_normalization_gap';
	}

	/** @return array<string,mixed> */
	private static function empty_totals(): array {
		return array( 'count' => 0, 'pass' => 0, 'fail' => 0, 'found_top20' => 0, 'missed_top20' => 0, 'latencies' => array() );
	}

	/** @param array<string,mixed> $totals @param array<string,mixed> $probe */
	private static function accumulate( array &$totals, array $probe ): void {
		$totals['count']++;
		! empty( $probe['pass_legacy_max_rank'] ) ? $totals['pass']++ : $totals['fail']++;
		! empty( $probe['found_top20'] ) ? $totals['found_top20']++ : $totals['missed_top20']++;
		$totals['latencies'][] = (float) ( $probe['latency_ms'] ?? 0.0 );
	}

	/** @param array<string,mixed> $totals @return array<string,mixed> */
	private static function finalize_totals( array $totals ): array {
		$latencies = (array) $totals['latencies'];
		unset( $totals['latencies'] );
		$count = (int) $totals['count'];
		$totals['pass_percent'] = $count > 0 ? round( 100 * (int) $totals['pass'] / $count, 2 ) : 0.0;
		$totals['latency_ms'] = self::timing_summary( array_map( 'floatval', $latencies ) );
		return $totals;
	}

	/** @param array<int,float> $values @return array<string,float|int> */
	private static function timing_summary( array $values ): array {
		sort( $values, SORT_NUMERIC );
		return array(
			'count' => count( $values ),
			'mean' => empty( $values ) ? 0.0 : round( array_sum( $values ) / count( $values ), 4 ),
			'p50' => self::percentile( $values, 0.50 ),
			'p95' => self::percentile( $values, 0.95 ),
			'max' => empty( $values ) ? 0.0 : round( (float) max( $values ), 4 ),
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
}
