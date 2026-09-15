<?php
/**
 * Diagnóstico temporário da Classificação de Conhecimento — SPEC-002.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exercita Classification Store em fixtures próprias e gera JSON sem persistir relatório.
 * Classe exclusiva de homologação. Remover antes do package final da SPEC-002.
 */
final class Classification_Diagnostics {

	public const ACTION = 'bdc_kb_run_classification_diagnostics';

	private const NONCE_ACTION   = 'bdc_kb_classification_diagnostics_v1';
	private const NONCE_FIELD    = 'bdc_kb_classification_diagnostics_nonce';
	private const SCHEMA_VERSION = '1.0.0';
	private const FIXTURE_META   = '_bdc_kb_classification_diag_fixture';
	private const FIXTURE_VALUE  = 'spec002-classification-v1';
	private const TERM_PREFIX    = 'bdc-diag-spec002-';

	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'render_panel' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function render_panel(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) && is_scalar( $_GET['page'] )
			? sanitize_key( wp_unslash( (string) $_GET['page'] ) )
			: '';
		$post_id = isset( $_GET['post_id'] ) && is_scalar( $_GET['post_id'] )
			? absint( wp_unslash( (string) $_GET['post_id'] ) )
			: 0;

		if ( Admin_Page::PAGE_SLUG !== $page || $post_id > 0 ) {
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<p><strong>' . esc_html__( 'Homologação temporária SPEC-002 — Classificação', 'bdc-knowledge-base' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Executa testes técnicos em post e termos temporários, gera JSON e remove fixtures/termos ao finalizar.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<p><button type="submit" class="button button-secondary">' . esc_html__( 'Executar diagnóstico Classificação e gerar JSON', 'bdc-knowledge-base' ) . '</button></p>';
		echo '</form></div>';
	}

	public static function handle_run(): never {
		$started = microtime( true );
		self::guard_request();
		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$stale         = self::cleanup_stale();
		$checks        = array();
		$post_id       = 0;
		$term_ids      = array();
		$deleted_post  = false;
		$deleted_terms = 0;

		try {
			$post_id = self::create_fixture_post();
			self::check( $checks, 'HARNESS-01', 'HARNESS', $post_id > 0, 'Fixture post temporária criada.' );

			if ( $post_id > 0 ) {
				$term_ids = self::create_fixture_terms();
				self::check( $checks, 'HARNESS-02', 'HARNESS', 8 === count( $term_ids ), 'Dois termos temporários criados para cada taxonomia canônica.' );
			}

			if ( $post_id > 0 && 8 === count( $term_ids ) ) {
				self::run_contract_checks( $checks );
				self::run_store_checks( $checks, $post_id, $term_ids );
				self::run_fault_checks( $checks, $post_id, $term_ids );
				self::run_regression_checks( $checks, $post_id );
			}
		} catch ( \Throwable $e ) {
			$checks[] = array(
				'id'      => 'HARNESS-EXCEPTION',
				'gate'    => 'HARNESS',
				'status'  => 'FAIL',
				'message' => sanitize_text_field( $e->getMessage() ),
			);
		} finally {
			if ( $post_id > 0 && self::is_fixture_post( $post_id ) ) {
				$deleted_post = (bool) wp_delete_post( $post_id, true );
			}
			$deleted_terms = self::delete_term_ids( $term_ids );
			self::cleanup_stale();
		}

		$residual_posts = self::count_fixture_posts();
		$residual_terms = self::count_fixture_terms();
		$failures       = count( array_filter( $checks, static fn ( array $row ): bool => 'PASS' !== ( $row['status'] ?? '' ) ) );
		$overall        = 0 === $failures && $deleted_post && 0 === $residual_posts && 0 === $residual_terms ? 'PASS' : 'FAIL';

		$report = array(
			'schema_version' => self::SCHEMA_VERSION,
			'mode'           => 'temporary_classification_diagnostics',
			'generated_at'   => gmdate( 'c' ),
			'environment'    => array(
				'wordpress' => (string) get_bloginfo( 'version' ),
				'php'       => PHP_VERSION,
				'plugin'    => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : 'unknown',
				'multisite' => is_multisite(),
			),
			'safety' => array(
				'fixture_marker'                 => self::FIXTURE_VALUE,
				'persistent_report'              => false,
				'real_content_modified'          => false,
				'requires_capability'            => 'manage_options',
				'stale_posts_removed_before_run' => $stale['posts'],
				'stale_terms_removed_before_run' => $stale['terms'],
			),
			'checks' => $checks,
			'summary' => array(
				'pass'        => count( $checks ) - $failures,
				'fail'        => $failures,
				'overall'     => $overall,
				'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
			),
			'cleanup' => array(
				'post_fixture_deleted' => $deleted_post,
				'terms_deleted_count'  => $deleted_terms,
				'residual_posts'       => $residual_posts,
				'residual_terms'       => $residual_terms,
			),
			'limitations' => array(
				'Fault injection B-006 usa inconsistência controlada na releitura wp_get_object_terms para exercitar compensação sobre relações reais; não simula falha física do banco.',
				'Nonce/mass-assignment do handler HTTP completo serão exercitados em um gate HTTP separado após este diagnóstico técnico.',
				'Esta classe deve ser removida do package final da SPEC-002.',
			),
		);

		$filename = 'bdc-kb-classification-diagnostics-' . gmdate( 'Ymd-His' ) . '.json';
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	private static function run_contract_checks( array &$checks ): void {
		$fields   = Classification_Contract::fields();
		$expected = array( 'bdc_kb_audience', 'bdc_kb_responsible_team', 'bdc_kb_knowledge_type', 'bdc_kb_catalog_item' );
		self::check( $checks, 'C010-01', 'C-010', $expected === array_column( $fields, 'taxonomy' ), 'Quatro taxonomias canônicas exatas.' );

		$secure = true;
		foreach ( $fields as $definition ) {
			$tax = get_taxonomy( $definition['taxonomy'] );
			$secure = $secure
				&& is_object( $tax )
				&& false === (bool) $tax->public
				&& false === (bool) $tax->publicly_queryable
				&& false === (bool) $tax->show_in_rest
				&& false === (bool) $tax->show_in_nav_menus
				&& false === (bool) $tax->show_in_quick_edit
				&& false === $tax->meta_box_cb;
		}
		self::check( $checks, 'C010-02', 'C-010', $secure, 'Taxonomias são administrativas, não públicas e sem writer paralelo via REST/meta box/quick edit.' );
	}

	private static function run_store_checks( array &$checks, int $post_id, array $term_ids ): void {
		$ids = self::term_matrix( $term_ids );
		self::set_rel( $post_id, 'audience', array( $ids['audience'][0] ) );
		self::set_rel( $post_id, 'catalog_item', array( $ids['catalog_item'][0] ) );

		$before = self::canonical_terms( $post_id );
		$read   = Classification_Store::read( $post_id );
		$after  = self::canonical_terms( $post_id );
		self::check( $checks, 'G030-01', 'G-030', ! is_wp_error( $read ) && $before === $after, 'Leitura canônica é side-effect free.' );

		$result     = Classification_Store::update( $post_id, array( 'audience' => array( $ids['audience'][1] ) ) );
		$state      = is_wp_error( $result ) ? array() : ( $result['state']['terms'] ?? array() );
		$partial_ok = ! is_wp_error( $result )
			&& array( $ids['audience'][1] ) === ( $state['audience'] ?? null )
			&& array( $ids['catalog_item'][0] ) === ( $state['catalog_item'] ?? null );
		self::check( $checks, 'G030-02', 'G-030', $partial_ok, 'Update parcial altera somente conceito enviado e preserva omitidos.' );

		$set_count = 0;
		$counter = static function ( $object_id ) use ( $post_id, &$set_count ): void {
			if ( (int) $object_id === $post_id ) {
				++$set_count;
			}
		};
		add_action( 'set_object_terms', $counter, 10, 6 );
		$noop = Classification_Store::update( $post_id, array( 'audience' => array( $ids['audience'][1], $ids['audience'][1] ) ) );
		remove_action( 'set_object_terms', $counter, 10 );
		self::check( $checks, 'G030-03', 'G-030', ! is_wp_error( $noop ) && 0 === $set_count, 'NO_CHANGE/dedupe não executa write de relação.' );

		$snapshot = self::canonical_terms( $post_id );
		$unknown  = Classification_Store::update( $post_id, array( 'intruso' => array( $ids['audience'][0] ) ) );
		self::check( $checks, 'G030-04', 'G-030', is_wp_error( $unknown ) && 'bdc_kb_classification_unknown_field' === $unknown->get_error_code() && $snapshot === self::canonical_terms( $post_id ), 'Campo fora da allowlist falha sem mutação.' );

		$invalid = Classification_Store::update( $post_id, array( 'audience' => array( 2147483647 ) ) );
		self::check( $checks, 'G030-05', 'G-030', is_wp_error( $invalid ) && 'bdc_kb_classification_term_not_found' === $invalid->get_error_code() && $snapshot === self::canonical_terms( $post_id ), 'Termo inexistente falha sem mutação.' );

		$wrong = Classification_Store::update( $post_id, array( 'audience' => array( $ids['catalog_item'][1] ) ) );
		self::check( $checks, 'G030-06', 'G-030', is_wp_error( $wrong ) && 'bdc_kb_classification_term_not_found' === $wrong->get_error_code() && $snapshot === self::canonical_terms( $post_id ), 'Termo existente em outra taxonomia é rejeitado.' );

		$single = Classification_Store::update( $post_id, array( 'knowledge_type' => array( $ids['knowledge_type'][0], $ids['knowledge_type'][1] ) ) );
		self::check( $checks, 'G030-07', 'G-030', is_wp_error( $single ) && 'bdc_kb_classification_single_only' === $single->get_error_code(), 'Tipo de conhecimento rejeita múltiplos termos.' );

		$multi       = Classification_Store::update( $post_id, array( 'responsible_team' => array( $ids['responsible_team'][1], $ids['responsible_team'][0], $ids['responsible_team'][1] ) ) );
		$multi_terms = is_wp_error( $multi ) ? array() : ( $multi['state']['terms']['responsible_team'] ?? array() );
		$expected_multi = $ids['responsible_team'];
		sort( $expected_multi, SORT_NUMERIC );
		self::check( $checks, 'G030-08', 'G-030', ! is_wp_error( $multi ) && $expected_multi === $multi_terms, 'Conceito múltiplo deduplica/ordena IDs e persiste termos existentes.' );

		$empty       = Classification_Store::update( $post_id, array( 'responsible_team' => array() ) );
		$empty_terms = is_wp_error( $empty ) ? null : ( $empty['state']['terms']['responsible_team'] ?? null );
		self::check( $checks, 'G030-09', 'G-030', ! is_wp_error( $empty ) && array() === $empty_terms, 'Valor vazio remove relações canônicas do conceito.' );

		$deny = static function ( array $caps, string $cap, int $user_id, array $args ) use ( $post_id ): array {
			unset( $user_id );
			if ( 'edit_post' === $cap && isset( $args[0] ) && (int) $args[0] === $post_id ) {
				return array( 'do_not_allow' );
			}
			return $caps;
		};
		add_filter( 'map_meta_cap', $deny, PHP_INT_MAX, 4 );
		$before_denied = self::canonical_terms( $post_id );
		$denied = Classification_Store::update( $post_id, array( 'audience' => array( $ids['audience'][0] ) ) );
		remove_filter( 'map_meta_cap', $deny, PHP_INT_MAX );
		self::check( $checks, 'G070-01', 'G-070', is_wp_error( $denied ) && 'bdc_kb_classification_forbidden' === $denied->get_error_code() && $before_denied === self::canonical_terms( $post_id ), 'Capability por objeto bloqueia write e preserva estado.' );

		$page_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => '[BDC-C2] page temporária' ), true );
		$page_ok = false;
		if ( ! is_wp_error( $page_id ) && (int) $page_id > 0 ) {
			$page_read = Classification_Store::read( (int) $page_id );
			$page_ok   = is_wp_error( $page_read ) && 'bdc_kb_classification_unsupported_post_type' === $page_read->get_error_code();
			wp_delete_post( (int) $page_id, true );
		}
		self::check( $checks, 'G070-02', 'G-070', $page_ok, 'Post type page é rejeitado pelo domínio.' );
	}

	private static function run_fault_checks( array &$checks, int $post_id, array $term_ids ): void {
		$ids = self::term_matrix( $term_ids );
		self::set_rel( $post_id, 'audience', array( $ids['audience'][0] ) );
		self::set_rel( $post_id, 'catalog_item', array( $ids['catalog_item'][0] ) );

		$aud_tax   = Classification_Contract::fields()['audience']['taxonomy'];
		$aud_calls = 0;
		$fault = static function ( $terms, $object_ids, $taxonomies, $args ) use ( $post_id, $aud_tax, &$aud_calls, $ids ) {
			unset( $args );
			if ( in_array( $post_id, array_map( 'intval', (array) $object_ids ), true ) && in_array( $aud_tax, (array) $taxonomies, true ) ) {
				++$aud_calls;
				if ( 2 === $aud_calls ) {
					return array( $ids['audience'][0] );
				}
			}
			return $terms;
		};
		add_filter( 'get_object_terms', $fault, PHP_INT_MAX, 4 );
		$fail_safe = Classification_Store::update(
			$post_id,
			array(
				'audience'     => array( $ids['audience'][1] ),
				'catalog_item' => array( $ids['catalog_item'][1] ),
			)
		);
		remove_filter( 'get_object_terms', $fault, PHP_INT_MAX );
		$fs_data  = is_wp_error( $fail_safe ) ? $fail_safe->get_error_data() : array();
		$fs_state = self::canonical_terms( $post_id );
		self::check(
			$checks,
			'B006-C01',
			'B-006',
			is_wp_error( $fail_safe )
				&& Classification_Store::STATUS_FAIL_SAFE === ( $fs_data['status'] ?? '' )
				&& array( $ids['audience'][0] ) === $fs_state['audience']
				&& array( $ids['catalog_item'][0] ) === $fs_state['catalog_item'],
			'Inconsistência de read-after-write aciona compensação e restaura snapshot real.'
		);

		self::set_rel( $post_id, 'audience', array( $ids['audience'][0] ) );
		self::set_rel( $post_id, 'catalog_item', array( $ids['catalog_item'][0] ) );
		$aud_calls = 0;
		$critical_fault = static function ( $terms, $object_ids, $taxonomies, $args ) use ( $post_id, $aud_tax, &$aud_calls, $ids ) {
			unset( $args );
			if ( in_array( $post_id, array_map( 'intval', (array) $object_ids ), true ) && in_array( $aud_tax, (array) $taxonomies, true ) ) {
				++$aud_calls;
				if ( 2 === $aud_calls ) {
					return array( $ids['audience'][0] );
				}
				if ( 3 === $aud_calls ) {
					return array( $ids['audience'][1] );
				}
			}
			return $terms;
		};
		add_filter( 'get_object_terms', $critical_fault, PHP_INT_MAX, 4 );
		$critical = Classification_Store::update(
			$post_id,
			array(
				'audience'     => array( $ids['audience'][1] ),
				'catalog_item' => array( $ids['catalog_item'][1] ),
			)
		);
		remove_filter( 'get_object_terms', $critical_fault, PHP_INT_MAX );
		$critical_data = is_wp_error( $critical ) ? $critical->get_error_data() : array();
		self::check( $checks, 'B006-C02', 'B-006', is_wp_error( $critical ) && Classification_Store::STATUS_PARTIAL_FAILURE_CRITICAL === ( $critical_data['status'] ?? '' ), 'Reread final divergente após compensação é promovido a PARTIAL_FAILURE_CRITICAL.' );

		self::set_rel( $post_id, 'audience', array( $ids['audience'][0] ) );
		self::set_rel( $post_id, 'catalog_item', array( $ids['catalog_item'][0] ) );
	}

	private static function run_regression_checks( array &$checks, int $post_id ): void {
		$post = get_post( $post_id );
		$editorial_ok = is_object( $post )
			&& '[BDC-C2] Fixture classificação temporária' === (string) $post->post_title
			&& 'BDC_SPEC002_CONTENT_SENTINEL' === (string) $post->post_content
			&& '{"bdc_spec002":"sentinel"}' === (string) get_post_meta( $post_id, '_elementor_data', true );
		self::check( $checks, 'G001-C01', 'G-001', $editorial_ok, 'Classificação não alterou título, post_content ou _elementor_data.' );

		$legacy_ok = 'LEGACY-AUDIENCE-SENTINEL' === (string) get_post_meta( $post_id, '_bdc_es_target_audience', true )
			&& 'LEGACY-TEAM-SENTINEL' === (string) get_post_meta( $post_id, '_bdc_es_responsible_team', true )
			&& 'LEGACY-CATALOG-SENTINEL' === (string) get_post_meta( $post_id, '_bdc_es_catalog_item', true );
		self::check( $checks, 'G030-10', 'G-030', $legacy_ok, 'Stores legados permanecem read-only durante writes canônicos.' );

		$before_class = self::canonical_terms( $post_id );
		$summary      = Summary_Store::update( $post_id, array( 'objective' => 'SPEC001-REGRESSION-O1' ) );
		$summary_ok   = ! is_wp_error( $summary ) && 'SPEC001-REGRESSION-O1' === (string) ( $summary['state']['objective'] ?? '' ) && $before_class === self::canonical_terms( $post_id );
		self::check( $checks, 'REG001-01', 'REGRESSION-SPEC-001', $summary_ok, 'Summary continua gravando sem alterar classificação canônica.' );
		Summary_Store::update( $post_id, array( 'objective' => 'SPEC001-O0' ) );
	}

	private static function guard_request(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para executar este diagnóstico.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
	}

	private static function create_fixture_post(): int {
		$result = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'draft',
				'post_title'   => '[BDC-C2] Fixture classificação temporária',
				'post_content' => 'BDC_SPEC002_CONTENT_SENTINEL',
				'post_author'  => get_current_user_id(),
				'meta_input'   => array(
					self::FIXTURE_META         => self::FIXTURE_VALUE,
					'_elementor_data'          => '{"bdc_spec002":"sentinel"}',
					'_bdc_es_objective'        => 'SPEC001-O0',
					'_bdc_es_escalation'       => 'SPEC001-E0',
					'_bdc_es_important'        => 'SPEC001-I0',
					'_bdc_es_target_audience'  => 'LEGACY-AUDIENCE-SENTINEL',
					'_bdc_es_responsible_team' => 'LEGACY-TEAM-SENTINEL',
					'_bdc_es_catalog_item'     => 'LEGACY-CATALOG-SENTINEL',
				),
			),
			true
		);
		return is_wp_error( $result ) ? 0 : (int) $result;
	}

	/** @return array<int,int> */
	private static function create_fixture_terms(): array {
		$created = array();
		$run = gmdate( 'YmdHis' ) . '-' . wp_rand( 1000, 9999 );
		foreach ( Classification_Contract::fields() as $field => $definition ) {
			for ( $i = 1; $i <= 2; ++$i ) {
				$slug = self::TERM_PREFIX . $run . '-' . sanitize_key( $field ) . '-' . $i;
				$result = wp_insert_term( '[BDC-C2] ' . $field . ' ' . $i . ' ' . $run, $definition['taxonomy'], array( 'slug' => $slug ) );
				if ( ! is_wp_error( $result ) ) {
					$created[] = (int) $result['term_id'];
				}
			}
		}
		return $created;
	}

	/** @return array<string,array<int,int>> */
	private static function term_matrix( array $term_ids ): array {
		$matrix = array();
		$offset = 0;
		foreach ( Classification_Contract::fields() as $field => $definition ) {
			unset( $definition );
			$matrix[ $field ] = array( (int) $term_ids[ $offset ], (int) $term_ids[ $offset + 1 ] );
			$offset += 2;
		}
		return $matrix;
	}

	private static function set_rel( int $post_id, string $field, array $ids ): void {
		$taxonomy = Classification_Contract::fields()[ $field ]['taxonomy'];
		wp_set_object_terms( $post_id, array_map( 'intval', $ids ), $taxonomy, false );
	}

	/** @return array<string,array<int,int>> */
	private static function canonical_terms( int $post_id ): array {
		$out = array();
		foreach ( Classification_Contract::fields() as $field => $definition ) {
			$ids = wp_get_object_terms( $post_id, $definition['taxonomy'], array( 'fields' => 'ids' ) );
			$ids = is_wp_error( $ids ) ? array() : array_map( 'intval', $ids );
			$ids = array_values( array_unique( $ids ) );
			sort( $ids, SORT_NUMERIC );
			$out[ $field ] = $ids;
		}
		return $out;
	}

	private static function check( array &$checks, string $id, string $gate, bool $passed, string $message ): void {
		$checks[] = array(
			'id'      => $id,
			'gate'    => $gate,
			'status'  => $passed ? 'PASS' : 'FAIL',
			'message' => $message,
		);
	}

	private static function is_fixture_post( int $post_id ): bool {
		return self::FIXTURE_VALUE === (string) get_post_meta( $post_id, self::FIXTURE_META, true );
	}

	private static function delete_term_ids( array $term_ids ): int {
		$removed = 0;
		foreach ( Classification_Contract::fields() as $definition ) {
			foreach ( $term_ids as $term_id ) {
				$term = get_term( (int) $term_id, $definition['taxonomy'] );
				if ( is_wp_error( $term ) || ! is_object( $term ) ) {
					continue;
				}
				if ( str_starts_with( (string) $term->slug, self::TERM_PREFIX ) && ! is_wp_error( wp_delete_term( (int) $term->term_id, $definition['taxonomy'] ) ) ) {
					++$removed;
				}
			}
		}
		return $removed;
	}

	/** @return array{posts:int,terms:int} */
	private static function cleanup_stale(): array {
		$posts = 0;
		$ids = get_posts(
			array(
				'post_type'        => 'post',
				'post_status'      => 'any',
				'numberposts'      => -1,
				'fields'           => 'ids',
				'meta_key'         => self::FIXTURE_META,
				'meta_value'       => self::FIXTURE_VALUE,
				'suppress_filters' => true,
			)
		);
		foreach ( $ids as $id ) {
			if ( wp_delete_post( (int) $id, true ) ) {
				++$posts;
			}
		}

		$terms = 0;
		foreach ( Classification_Contract::fields() as $definition ) {
			$list = get_terms( array( 'taxonomy' => $definition['taxonomy'], 'hide_empty' => false ) );
			if ( is_wp_error( $list ) ) {
				continue;
			}
			foreach ( $list as $term ) {
				if ( str_starts_with( (string) $term->slug, self::TERM_PREFIX ) && ! is_wp_error( wp_delete_term( (int) $term->term_id, $definition['taxonomy'] ) ) ) {
					++$terms;
				}
			}
		}
		return array( 'posts' => $posts, 'terms' => $terms );
	}

	private static function count_fixture_posts(): int {
		$query = new \WP_Query(
			array(
				'post_type'      => 'post',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => self::FIXTURE_META,
				'meta_value'     => self::FIXTURE_VALUE,
				'no_found_rows'  => false,
			)
		);
		return (int) $query->found_posts;
	}

	private static function count_fixture_terms(): int {
		$count = 0;
		foreach ( Classification_Contract::fields() as $definition ) {
			$list = get_terms( array( 'taxonomy' => $definition['taxonomy'], 'hide_empty' => false ) );
			if ( is_wp_error( $list ) ) {
				continue;
			}
			foreach ( $list as $term ) {
				if ( str_starts_with( (string) $term->slug, self::TERM_PREFIX ) ) {
					++$count;
				}
			}
		}
		return $count;
	}
}
