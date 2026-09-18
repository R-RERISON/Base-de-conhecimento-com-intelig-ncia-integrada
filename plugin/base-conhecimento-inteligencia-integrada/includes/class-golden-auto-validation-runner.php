<?php
/**
 * Runner automático T513/T514 da SPEC-005.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Golden_Auto_Validation_Runner {

	public const ACTION    = 'bdc_kb_spec005_r510_golden_auto_validate';
	public const PAGE_SLUG = 'bdc-kb-spec005-r510-golden-auto-validator';

	private const NONCE_ACTION = 'bdc_kb_spec005_r510_golden_auto_validate';
	private const NONCE_FIELD  = 'bdc_kb_spec005_r510_golden_auto_validate_nonce';
	private const RESULT_LIMIT = 20;
	private const COMPETITOR_LIMIT = 5;

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 43 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Golden Auto Validator',
			'Golden Auto Validator',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — Golden Auto Validator T513/T514', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p>';
		echo esc_html__( 'Validação automática read-only. Confirma expectativas existentes quando evidências objetivas são inequívocas e envia apenas ambiguidades para revisão humana.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'Também mede diversidade e variantes sintéticas de robustez. Casos sintéticos são marcados explicitamente e não são tratados como consulta real de usuário.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar validadores automáticos e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-r510-golden-auto-validator-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$before = self::editorial_snapshot( $ids_before );
		$before_hash = self::aggregate_fingerprint( $before );

		$cases = Golden_Candidate_Seed::all();
		$results = array();
		$diversity_cases = array();
		$counts = array(
			'AUTO_PASS' => 0,
			'REVIEW_REQUIRED' => 0,
			'AUTO_FAIL' => 0,
		);
		$errors = array();

		foreach ( $cases as $case ) {
			$validated = self::validate_case( $case );
			$results[] = $validated;
			$status = (string) ( $validated['decision']['status'] ?? 'AUTO_FAIL' );
			if ( isset( $counts[ $status ] ) ) {
				$counts[ $status ]++;
			}

			$diversity_cases[] = array(
				'id' => (string) ( $case['id'] ?? '' ),
				'query' => (string) ( $case['query'] ?? '' ),
				'origin' => 'real',
				'declared_classes' => array(),
				'summary_dependent' => (bool) ( $validated['expected']['summary_dependent'] ?? false ),
				'elementor_semantic_gap' => (bool) ( $validated['expected']['elementor_semantic_gap'] ?? false ),
			);
		}

		$diversity = Golden_Diversity_Validator::assess( $diversity_cases );
		$synthetic = self::synthetic_robustness( $cases );

		$ids_after = self::post_ids();
		$after = self::editorial_snapshot( $ids_after );
		$after_hash = self::aggregate_fingerprint( $after );
		$changed = self::changed_snapshot_count( $before, $after );
		$safety_pass = hash_equals( $before_hash, $after_hash )
			&& 0 === $changed
			&& count( $ids_before ) === count( $ids_after )
			&& empty( $errors );

		$t513_status = 0 === $counts['AUTO_FAIL']
			? ( 0 === $counts['REVIEW_REQUIRED'] ? 'PASS_AUTOMATED' : 'PASS_WITH_REVIEW_ITEMS' )
			: 'FAIL';

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'R-510/T513-T514',
			'mode' => 'spec005_automated_golden_validation',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
			),
			'independence' => array(
				'legacy_search_runtime_dependency' => false,
				'candidate_source' => 'spec005_owned_fixture',
				'candidate_fixture_version' => Golden_Candidate_Seed::VERSION,
				'candidate_source_hash' => Golden_Candidate_Seed::SOURCE_SET_HASH,
				'reads_external_search_storage' => false,
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
			't513' => array(
				'validator_contract_version' => Golden_Candidate_Validator::CONTRACT_VERSION,
				'policy' => array(
					'can_confirm_existing_human_origin_expectation' => true,
					'can_create_new_expected_post' => false,
					'can_replace_expected_post' => false,
					'can_auto_accept_ambiguous_case' => false,
					'auto_pass_recommended_severity' => 'blocking',
				),
				'counts' => $counts,
				'status' => $t513_status,
				'results' => $results,
			),
			't514' => array(
				'diversity' => $diversity,
				'synthetic_robustness' => $synthetic,
			),
			'errors' => $errors,
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_result' => array(
				't513_automated_validation_pass' => $safety_pass && 0 === $counts['AUTO_FAIL'],
				't513_requires_human_review' => $counts['REVIEW_REQUIRED'] > 0,
				't513_review_required_count' => $counts['REVIEW_REQUIRED'],
				't514_diversity_status' => (string) ( $diversity['status'] ?? 'INCOMPLETE' ),
				't514_synthetic_robustness_pass' => 0 === (int) ( $synthetic['failed'] ?? 0 ),
				'r510_ready' => $safety_pass
					&& 0 === $counts['AUTO_FAIL']
					&& 0 === $counts['REVIEW_REQUIRED']
					&& 'PASS' === (string) ( $diversity['status'] ?? '' ),
			),
			'interpretation_rules' => array(
				'AUTO_PASS confirma continuidade de expected/max_rank já originados de curadoria humana histórica; não inventa nova verdade Golden.',
				'REVIEW_REQUIRED é obrigatório quando expected não é Top-1, existe concorrente material ou a cobertura é inconclusiva.',
				'AUTO_FAIL indica quebra objetiva: post ausente/não publicado, extração falhou, resultado não recuperado ou fora de max_rank.',
				'validation_score serve somente para detectar ambiguidade; não é Search ranking e não pode ser promovido como ranker.',
				'Synthetic robustness mede normalização; não é consulta real e não fecha classes real-world de typo/alias.',
			),
		);
	}

	/** @param array<string,mixed> $case @return array<string,mixed> */
	private static function validate_case( array $case ): array {
		$query = trim( sanitize_text_field( (string) ( $case['query'] ?? '' ) ) );
		$expected_id = absint( $case['expected_post_id'] ?? 0 );
		$max_rank = max( 1, min( 50, absint( $case['max_rank'] ?? 3 ) ) );

		$query_result = self::query_admin_relevance( $query );
		$rank = self::rank_of( $expected_id, $query_result );
		$expected = self::post_evidence( $expected_id, $query );

		$strongest = array();
		$competitors = array();
		foreach ( array_slice( $query_result, 0, self::COMPETITOR_LIMIT ) as $index => $post_id ) {
			if ( $post_id === $expected_id ) {
				continue;
			}
			$evidence = self::post_evidence( $post_id, $query );
			$evidence['rank'] = $index + 1;
			$evidence['ahead_of_expected'] = $rank > 0 && ( $index + 1 ) < $rank;
			$competitors[] = $evidence;
			if (
				empty( $strongest )
				|| (float) ( $evidence['validation_score'] ?? 0.0 ) > (float) ( $strongest['validation_score'] ?? 0.0 )
			) {
				$strongest = $evidence;
			}
		}

		$assessment_input = array_merge(
			$expected,
			array(
				'expected_post_id' => $expected_id,
				'expected_rank' => $rank,
				'max_rank' => $max_rank,
				'query_token_count' => count( Golden_Candidate_Validator::tokens( $query ) ),
				'expected_validation_score' => (float) ( $expected['validation_score'] ?? 0.0 ),
				'strongest_competitor' => $strongest,
			)
		);

		$decision = Golden_Candidate_Validator::assess( $assessment_input );

		return array(
			'id' => (string) ( $case['id'] ?? '' ),
			'query' => $query,
			'expected_post_id' => $expected_id,
			'max_rank' => $max_rank,
			'legacy_severity' => (string) ( $case['legacy_severity'] ?? 'warning' ),
			'expected_rank' => $rank,
			'expected' => $expected,
			'strongest_competitor' => $strongest,
			'top_competitors' => $competitors,
			'decision' => $decision,
			'automated_rationale' => self::rationale( $decision, $expected, $strongest ),
		);
	}

	/** @return array<string,mixed> */
	private static function post_evidence( int $post_id, string $query ): array {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return array(
				'post_id' => $post_id,
				'expected_exists' => false,
				'expected_published' => false,
				'extractor_error' => false,
				'validation_score' => 0.0,
			);
		}

		$title = (string) ( $post->post_title ?? '' );
		$excerpt = (string) ( $post->post_excerpt ?? '' );
		$content = (string) ( $post->post_content ?? '' );
		$summary_parts = array();
		foreach ( Meta_Contract::fields() as $definition ) {
			$value = get_post_meta( $post_id, (string) $definition['key'], true );
			if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
				$summary_parts[] = (string) $value;
			}
		}
		$summary = implode( ' ', $summary_parts );
		$native = implode( ' ', array( $title, $excerpt, wp_strip_all_tags( strip_shortcodes( $content ) ) ) );

		$extraction = Content_Extractor::extract( $post_id );
		$extractor_error = $extraction instanceof \WP_Error;
		$semantic = '';
		$source_kind = 'unknown';
		if ( is_array( $extraction ) ) {
			$source_kind = sanitize_key( (string) ( $extraction['source_kind'] ?? 'unknown' ) );
			$parts = array();
			foreach ( (array) ( $extraction['fragments'] ?? array() ) as $fragment ) {
				if ( is_array( $fragment ) && isset( $fragment['text'] ) ) {
					$parts[] = (string) $fragment['text'];
				}
			}
			$semantic = implode( ' ', $parts );
		}

		$signals = array(
			'title_coverage_percent' => Golden_Candidate_Validator::coverage_percent( $query, $title ),
			'summary_coverage_percent' => Golden_Candidate_Validator::coverage_percent( $query, $summary ),
			'semantic_coverage_percent' => Golden_Candidate_Validator::coverage_percent( $query, $semantic ),
			'native_coverage_percent' => Golden_Candidate_Validator::coverage_percent( $query, $native ),
			'exact_title_phrase' => Golden_Candidate_Validator::exact_phrase( $query, $title ),
		);
		$score = Golden_Candidate_Validator::validation_score( $signals );

		return array_merge(
			array(
				'post_id' => $post_id,
				'title' => $title,
				'post_status' => (string) ( $post->post_status ?? '' ),
				'source_kind' => $source_kind,
				'expected_exists' => true,
				'expected_published' => 'publish' === (string) ( $post->post_status ?? '' ),
				'extractor_error' => $extractor_error,
				'summary_signal_present' => '' !== trim( $summary ),
				'summary_dependent' => $signals['summary_coverage_percent'] > $signals['native_coverage_percent'],
				'elementor_semantic_gap' => 'elementor' === $source_kind
					&& $signals['semantic_coverage_percent'] > $signals['native_coverage_percent'],
				'validation_score' => $score,
			),
			$signals
		);
	}

	/** @param array<int,array<string,mixed>> $cases @return array<string,mixed> */
	private static function synthetic_robustness( array $cases ): array {
		$results = array();
		$passed = 0;
		$failed = 0;

		foreach ( $cases as $case ) {
			$expected = absint( $case['expected_post_id'] ?? 0 );
			$max_rank = max( 1, absint( $case['max_rank'] ?? 3 ) );
			$query = (string) ( $case['query'] ?? '' );

			foreach ( Golden_Diversity_Validator::synthetic_variants( $query ) as $variant ) {
				$ids = self::query_admin_relevance( (string) $variant['query'] );
				$rank = self::rank_of( $expected, $ids );
				$pass = $rank > 0 && $rank <= $max_rank;
				$pass ? ++$passed : ++$failed;
				$results[] = array(
					'base_id' => (string) ( $case['id'] ?? '' ),
					'kind' => (string) $variant['kind'],
					'origin' => 'synthetic',
					'query' => (string) $variant['query'],
					'expected_post_id' => $expected,
					'max_rank' => $max_rank,
					'actual_rank' => $rank,
					'pass' => $pass,
				);
			}
		}

		return array(
			'count' => count( $results ),
			'passed' => $passed,
			'failed' => $failed,
			'status' => 0 === $failed ? 'PASS' : 'FAIL',
			'results' => $results,
		);
	}

	/** @return array<int,int> */
	private static function query_admin_relevance( string $query ): array {
		$wp_query = new \WP_Query(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'perm' => 'editable',
				'posts_per_page' => self::RESULT_LIMIT,
				'fields' => 'ids',
				's' => $query,
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);
		$ids = array_map( 'intval', (array) $wp_query->posts );
		wp_reset_postdata();
		return $ids;
	}

	/** @param array<int,int> $ids */
	private static function rank_of( int $post_id, array $ids ): int {
		foreach ( $ids as $index => $id ) {
			if ( $id === $post_id ) {
				return $index + 1;
			}
		}
		return 0;
	}

	/** @param array<string,mixed> $decision @param array<string,mixed> $expected @param array<string,mixed> $competitor */
	private static function rationale( array $decision, array $expected, array $competitor ): string {
		$status = (string) ( $decision['status'] ?? 'AUTO_FAIL' );
		if ( 'AUTO_PASS' === $status ) {
			return sprintf(
				'Expectativa histórica confirmada objetivamente: post publicado, dentro do max_rank, cobertura semântica %.2f%% e sem concorrente material à frente.',
				(float) ( $expected['semantic_coverage_percent'] ?? 0.0 )
			);
		}
		if ( 'REVIEW_REQUIRED' === $status ) {
			return sprintf(
				'Revisão necessária por ambiguidade objetiva. Expected score %.4f; concorrente %d score %.4f.',
				(float) ( $expected['validation_score'] ?? 0.0 ),
				(int) ( $competitor['post_id'] ?? 0 ),
				(float) ( $competitor['validation_score'] ?? 0.0 )
			);
		}
		return 'Falha objetiva na expectativa existente; consultar reasons antes de qualquer alteração de Golden.';
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
