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

	/** @var array<int,string> */
	private const ALLOWED_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 48 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
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

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SPEC-005 — G-590 Section Retrieval & Deep-Link', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p>';
		echo esc_html__( 'Executa rebuild explícito, regressão Golden, cobertura estrutural do corpus, probes section-level/deep-link e fingerprint editorial. Não grava conteúdo editorial.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar G-590 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-g590-section-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
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
		$rows_before = self::row_count();
		$snapshot_before = self::projection_snapshot_hash();

		$lifecycle = Search_Lifecycle::prepare_schema();
		$schema_contract = self::schema_contract();
		$rows_after_prepare = self::row_count();
		$snapshot_after_prepare = self::projection_snapshot_hash();

		$prepare_did_not_reindex = $rows_before === $rows_after_prepare
			&& $snapshot_before === $snapshot_after_prepare
			&& false === (bool) ( $lifecycle['implicit_rebuild'] ?? true );

		$rebuild = array();
		try {
			$rebuild = Search_Rebuild_Service::rebuild();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'explicit_rebuild', $error );
		}

		$coverage = self::coverage_audit( $post_ids );
		$probes = self::section_and_anchor_probes( (array) ( $coverage['probe_candidates'] ?? array() ) );

		$golden = array();
		try {
			$golden = Golden_Gate_Runner_G550::run();
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'golden_regression', $error );
		}

		$editorial_after = self::editorial_snapshot( self::corpus_ids() );
		$editorial_fingerprint_after = Canonical_JSON::hash( $editorial_after );
		$editorial_equal = hash_equals( $editorial_fingerprint_before, $editorial_fingerprint_after );

		$rebuild_pass = 'PASS' === (string) ( $rebuild['status'] ?? '' )
			&& 'ready' === (string) ( (array) ( $rebuild['state_after'] ?? array() )['status'] ?? '' )
			&& 0 === (int) ( (array) ( $rebuild['determinism'] ?? array() )['mismatch_count'] ?? -1 );

		$golden_pass = 'PASS' === (string) ( $golden['status'] ?? '' )
			&& 0 === (int) ( $golden['blocking_failed'] ?? -1 )
			&& 0 === (int) ( $golden['technical_failed'] ?? -1 )
			&& empty( $golden['technical_errors'] ?? array() );

		$coverage_complete = count( $post_ids ) === (int) ( $coverage['posts_analyzed'] ?? -1 )
			&& 0 === (int) ( $coverage['extractor_error_count'] ?? -1 );

		$no_uncontextual_numbered_gap = 0 === (int) ( $coverage['numbered_without_heading_context'] ?? -1 );

		$probe_pass = (int) ( $probes['eligible_probe_count'] ?? 0 ) >= self::MIN_PROBES
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
			&& $rebuild_pass;
		$t59018 = $editorial_equal
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
				'rows_before' => $rows_before,
				'projection_snapshot_before' => $snapshot_before,
				'prepare_schema' => $lifecycle,
				'schema_contract' => $schema_contract,
				'rows_after_prepare' => $rows_after_prepare,
				'projection_snapshot_after_prepare' => $snapshot_after_prepare,
				'prepare_did_not_reindex' => $prepare_did_not_reindex,
				'explicit_rebuild' => $rebuild,
			),
			'coverage' => $coverage,
			'section_deep_link_probes' => $probes,
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
	private static function coverage_audit( array $post_ids ): array {
		$posts_analyzed = 0;
		$extractor_errors = array();
		$source_kinds = array();
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
				} else {
					++$unresolved;
				}

				$title_norm = (string) ( $section['title_norm'] ?? '' );
				if ( '' !== $title_norm ) {
					$title_frequency[ $title_norm ] = (int) ( $title_frequency[ $title_norm ] ?? 0 ) + 1;
					$unique_title_candidates[] = array(
						'post_id' => $post_id,
						'post_status' => (string) get_post_status( $post_id ),
						'source_kind' => $source_kind,
						'section_key' => (string) ( $section['section_key'] ?? '' ),
						'title' => (string) ( $section['title'] ?? '' ),
						'title_norm' => $title_norm,
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

		$probe_candidates = array();
		foreach ( $unique_title_candidates as $candidate ) {
			$title_norm = (string) $candidate['title_norm'];
			if (
				'publish' !== (string) $candidate['post_status']
				|| 'generated' !== (string) $candidate['anchor_state']
				|| 1 !== (int) ( $title_frequency[ $title_norm ] ?? 0 )
			) {
				continue;
			}
			$query = Search_Query_Normalizer::normalize( (string) $candidate['title'] );
			if ( $query instanceof \WP_Error || count( (array) $query['tokens'] ) < 2 ) {
				continue;
			}
			$probe_candidates[] = $candidate;
			if ( count( $probe_candidates ) >= self::MAX_PROBES ) {
				break;
			}
		}

		ksort( $source_kinds, SORT_STRING );

		return array(
			'corpus_count' => count( $post_ids ),
			'posts_analyzed' => $posts_analyzed,
			'extractor_error_count' => count( $extractor_errors ),
			'extractor_errors' => array_slice( $extractor_errors, 0, 50 ),
			'source_kinds' => $source_kinds,
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
			$section_key = (string) ( $candidate['section_key'] ?? '' );
			$anchor_id = (string) ( $candidate['anchor_id'] ?? '' );

			$section_response = Search_Service::search_sections( $title, Search_Section_Service::MAX_PARENTS, 5 );
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
				$rendered = apply_filters( 'the_content', (string) ( $post->post_content ?? '' ) );
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
				'query' => $title,
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
	private static function schema_contract(): array {
		global $wpdb;

		$required = array(
			'post_id','document_state','source_kind','title_norm','summary_norm','headings_norm',
			'taxonomy_norm','body_norm','sections_json','section_projection_version','source_hash',
			'document_hash','document_version','normalizer_version','post_modified_gmt','indexed_at_gmt',
		);

		$rows = $wpdb->get_results( 'SHOW COLUMNS FROM ' . Search_Projection_Repository::table_name(), ARRAY_A );
		$actual = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$actual[] = (string) ( $row['Field'] ?? '' );
		}
		$missing = array_values( array_diff( $required, $actual ) );

		return array(
			'expected_schema_version' => Search_Projection_Repository::SCHEMA_VERSION,
			'required_columns' => $required,
			'actual_columns' => $actual,
			'missing_columns' => $missing,
			'pass' => empty( $missing ) && '' === (string) $wpdb->last_error,
		);
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
			foreach ( Meta_Contract::fields() as $definition ) {
				$key = (string) ( $definition['key'] ?? '' );
				if ( '' !== $key ) {
					$meta[ $key ] = self::stable_value( get_post_meta( $post_id, $key, true ) );
				}
			}
			$meta['_elementor_data'] = self::stable_value( get_post_meta( $post_id, '_elementor_data', true ) );

			$taxonomies = array();
			foreach ( Classification_Contract::fields() as $definition ) {
				$taxonomy = (string) ( $definition['taxonomy'] ?? '' );
				if ( '' === $taxonomy ) {
					continue;
				}
				$terms = get_the_terms( $post_id, $taxonomy );
				$taxonomies[ $taxonomy ] = is_array( $terms )
					? array_values( array_map( static fn ( object $term ): int => (int) $term->term_id, $terms ) )
					: array();
				sort( $taxonomies[ $taxonomy ], SORT_NUMERIC );
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
