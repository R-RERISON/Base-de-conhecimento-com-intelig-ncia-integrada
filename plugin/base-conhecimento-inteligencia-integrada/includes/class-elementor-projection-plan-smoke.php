<?php
/**
 * Full-corpus, read-only smoke for Elementor Projection Plan v1.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Projection_Plan_Smoke {

	public const ACTION    = 'bdc_kb_spec004_g245_projection_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-projection-smoke';

	private const NONCE_ACTION = 'bdc_kb_spec004_g245_projection_smoke_run';
	private const NONCE_FIELD  = 'bdc_kb_spec004_g245_projection_smoke_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 34 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Projection Plan G-245',
			'Projection Plan G-245',
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
		echo '<h1>' . esc_html__( 'SPEC-004 — G-245 Projection Plan read-only', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Sem writer.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Executa duas passagens sobre o corpus, calcula Projection Plans determinísticos e verifica zero mutação editorial. Não exporta corpo editorial, títulos, URLs ou IDs de posts.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar Projection Plan full-corpus e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-g245-projection-smoke-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$snapshot_before = self::editorial_snapshot( $ids_before );
		$fingerprint_before = self::aggregate_fingerprint( $snapshot_before );

		$first = self::plan_pass( $ids_before );
		$second = self::plan_pass( $ids_before );

		$hash_mismatches = 0;
		$json_mismatches = 0;
		foreach ( $ids_before as $post_id ) {
			if ( ! isset( $first['plans'][ $post_id ], $second['plans'][ $post_id ] ) ) {
				continue;
			}
			$a = $first['plans'][ $post_id ];
			$b = $second['plans'][ $post_id ];
			if ( $a['projection_hash'] !== $b['projection_hash'] ) {
				++$hash_mismatches;
			}
			if ( $a['json_sha256'] !== $b['json_sha256'] ) {
				++$json_mismatches;
			}
		}

		$ids_after = self::post_ids();
		$snapshot_after = self::editorial_snapshot( $ids_after );
		$fingerprint_after = self::aggregate_fingerprint( $snapshot_after );
		$changed_count = self::changed_snapshot_count( $snapshot_before, $snapshot_after );
		$corpus_unchanged = $ids_before === $ids_after;
		$fingerprint_equal = hash_equals( $fingerprint_before, $fingerprint_after );

		$gate_pass = $corpus_unchanged
			&& $fingerprint_equal
			&& 0 === $changed_count
			&& count( $ids_before ) === count( $first['plans'] )
			&& count( $ids_before ) === count( $second['plans'] )
			&& 0 === $first['errors']
			&& 0 === $second['errors']
			&& 0 === $first['throwables']
			&& 0 === $second['throwables']
			&& 0 === $hash_mismatches
			&& 0 === $json_mismatches
			&& 0 === $first['projection_hash_violations']
			&& 0 === $second['projection_hash_violations']
			&& 0 === $first['writer_allowed_violations']
			&& 0 === $second['writer_allowed_violations']
			&& 0 === $first['safety_violations']
			&& 0 === $second['safety_violations']
			&& 0 === $first['legacy_shortcode_review_violations']
			&& 0 === $second['legacy_shortcode_review_violations']
			&& 0 === $first['migration_warning_review_violations']
			&& 0 === $second['migration_warning_review_violations'];

		return array(
			'schema_version' => '1.1.0',
			'mode' => 'spec004_g245_projection_plan_read_only_smoke',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
				'knowledge_document_schema' => Knowledge_Document::SCHEMA_VERSION,
				'projection_plan_schema' => Elementor_Projection_Plan::SCHEMA_VERSION,
				'matrix_version' => Elementor_Projection_Plan::MATRIX_VERSION,
			),
			'plans' => array(
				'total_posts' => count( $ids_before ),
				'first_pass_plans' => count( $first['plans'] ),
				'second_pass_plans' => count( $second['plans'] ),
				'first_pass_errors' => $first['errors'],
				'second_pass_errors' => $second['errors'],
				'first_pass_throwables' => $first['throwables'],
				'second_pass_throwables' => $second['throwables'],
				'projection_hash_mismatches' => $hash_mismatches,
				'canonical_json_mismatches' => $json_mismatches,
				'first_pass_projection_hash_violations' => $first['projection_hash_violations'],
				'second_pass_projection_hash_violations' => $second['projection_hash_violations'],
				'first_pass_writer_allowed_violations' => $first['writer_allowed_violations'],
				'second_pass_writer_allowed_violations' => $second['writer_allowed_violations'],
				'first_pass_safety_violations' => $first['safety_violations'],
				'second_pass_safety_violations' => $second['safety_violations'],
				'first_pass_legacy_shortcode_review_violations' => $first['legacy_shortcode_review_violations'],
				'second_pass_legacy_shortcode_review_violations' => $second['legacy_shortcode_review_violations'],
				'first_pass_migration_warning_review_violations' => $first['migration_warning_review_violations'],
				'second_pass_migration_warning_review_violations' => $second['migration_warning_review_violations'],
				'plan_status' => $first['plan_status'],
				'projection_strategy' => $first['projection_strategy'],
				'source_kind' => $first['source_kind'],
				'requires_review' => $first['requires_review'],
				'shortcode_dependencies' => $first['shortcode_dependencies'],
				'warnings' => $first['warnings'],
			),
			'safety' => array(
				'read_only_design' => true,
				'exports_editorial_content' => false,
				'exports_post_ids' => false,
				'exports_titles_or_urls' => false,
				'persists_plans' => false,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
				'corpus_count_before' => count( $ids_before ),
				'corpus_count_after' => count( $ids_after ),
				'corpus_unchanged' => $corpus_unchanged,
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => $fingerprint_equal,
				'changed_posts_during_run' => $changed_count,
			),
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate' => array(
				't081_pass' => $gate_pass,
				'requires_zero_projection_hash_violations' => true,
				'requires_zero_writer_allowed' => true,
				'requires_zero_safety_violations' => true,
				'requires_legacy_shortcodes_review' => true,
				'requires_migration_warnings_review' => true,
				'note' => 'Projection Plan PASS never authorizes writer or migration execution.',
			),
		);
	}

	/** @param array<int,int> $ids @return array<string,mixed> */
	private static function plan_pass( array $ids ): array {
		$plans = array();
		$errors = 0;
		$throwables = 0;
		$projection_hash_violations = 0;
		$writer_allowed_violations = 0;
		$safety_violations = 0;
		$legacy_shortcode_review_violations = 0;
		$migration_warning_review_violations = 0;
		$plan_status = array();
		$projection_strategy = array();
		$source_kind = array();
		$requires_review = array( 'true' => 0, 'false' => 0 );
		$shortcode_dependencies = array();
		$warnings = array();

		foreach ( $ids as $post_id ) {
			try {
				$plan = Elementor_Projection_Plan::build( $post_id );
				if ( is_wp_error( $plan ) ) {
					++$errors;
					continue;
				}
				$json = Elementor_Projection_Plan::canonical_json( $plan );
				if ( is_wp_error( $json ) ) {
					++$errors;
					continue;
				}

				$projection_hash = (string) ( $plan['projection_hash'] ?? '' );
				if ( 1 !== preg_match( '/^[a-f0-9]{64}$/', $projection_hash ) ) {
					++$projection_hash_violations;
				}

				$plans[ $post_id ] = array(
					'projection_hash' => $projection_hash,
					'json_sha256' => hash( 'sha256', $json ),
				);
				self::increment( $plan_status, (string) ( $plan['plan_status'] ?? 'unknown' ) );
				self::increment( $projection_strategy, (string) ( $plan['projection_strategy'] ?? 'unknown' ) );
				self::increment( $source_kind, (string) ( $plan['source_kind'] ?? 'unknown' ) );
				$review = true === ( $plan['requires_review'] ?? false );
				++$requires_review[ $review ? 'true' : 'false' ];

				if ( true === ( $plan['writer_allowed'] ?? false ) ) {
					++$writer_allowed_violations;
				}
				if ( ! self::safety_is_read_only( $plan ) ) {
					++$safety_violations;
				}

				$deps = is_array( $plan['dependencies']['shortcodes'] ?? null ) ? $plan['dependencies']['shortcodes'] : array();
				foreach ( $deps as $dependency ) {
					if ( ! is_array( $dependency ) ) {
						continue;
					}
					$tag = (string) ( $dependency['tag'] ?? '' );
					if ( '' !== $tag ) {
						self::increment( $shortcode_dependencies, $tag );
					}
					if ( in_array( $tag, array( 'faq_wd', 'wpt' ), true ) && ! $review ) {
						++$legacy_shortcode_review_violations;
					}
				}

				foreach ( (array) ( $plan['warnings'] ?? array() ) as $warning ) {
					$warning = (string) $warning;
					self::increment( $warnings, $warning );
					if ( ! $review && self::warning_requires_review( $warning ) ) {
						++$migration_warning_review_violations;
					}
				}
			} catch ( \Throwable $error ) {
				++$throwables;
			}
		}

		ksort( $plan_status, SORT_STRING );
		ksort( $projection_strategy, SORT_STRING );
		ksort( $source_kind, SORT_STRING );
		ksort( $shortcode_dependencies, SORT_STRING );
		ksort( $warnings, SORT_STRING );

		return array(
			'plans' => $plans,
			'errors' => $errors,
			'throwables' => $throwables,
			'projection_hash_violations' => $projection_hash_violations,
			'writer_allowed_violations' => $writer_allowed_violations,
			'safety_violations' => $safety_violations,
			'legacy_shortcode_review_violations' => $legacy_shortcode_review_violations,
			'migration_warning_review_violations' => $migration_warning_review_violations,
			'plan_status' => $plan_status,
			'projection_strategy' => $projection_strategy,
			'source_kind' => $source_kind,
			'requires_review' => $requires_review,
			'shortcode_dependencies' => $shortcode_dependencies,
			'warnings' => $warnings,
		);
	}

	/** @param array<string,mixed> $plan */
	private static function safety_is_read_only( array $plan ): bool {
		$safety = is_array( $plan['safety'] ?? null ) ? $plan['safety'] : array();
		foreach ( array( 'persists_plan', 'executes_shortcodes', 'calls_external_network', 'writes_post_content', 'writes_elementor_data' ) as $key ) {
			if ( false !== ( $safety[ $key ] ?? null ) ) {
				return false;
			}
		}
		return false === ( $plan['writer_allowed'] ?? null );
	}

	private static function warning_requires_review( string $warning ): bool {
		foreach ( array( 'ELEMENTOR_JSON_INVALID', 'GUTENBERG_DYNAMIC_NOT_RENDERED:', 'GUTENBERG_BLOCK_UNSUPPORTED:', 'ELEMENTOR_WIDGET_UNSUPPORTED:', 'SOURCE_OVERSIZE_HARD:' ) as $prefix ) {
			if ( str_starts_with( $warning, $prefix ) ) {
				return true;
			}
		}
		return false;
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

	/** @param array<int,int> $ids @return array<int,string> */
	private static function editorial_snapshot( array $ids ): array {
		$out = array();
		foreach ( $ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$elementor = get_post_meta( $post_id, '_elementor_data', true );
			$payload = array(
				'ID' => $post_id,
				'post_title' => (string) ( $post->post_title ?? '' ),
				'post_content' => (string) ( $post->post_content ?? '' ),
				'post_status' => (string) ( $post->post_status ?? '' ),
				'post_modified_gmt' => (string) ( $post->post_modified_gmt ?? '' ),
				'elementor_data' => $elementor,
			);
			try {
				$out[ $post_id ] = Canonical_JSON::hash( $payload );
			} catch ( \JsonException ) {
				$out[ $post_id ] = hash( 'sha256', serialize( $payload ) );
			}
		}
		return $out;
	}

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		ksort( $snapshot, SORT_NUMERIC );
		try {
			return Canonical_JSON::hash( $snapshot );
		} catch ( \JsonException ) {
			return hash( 'sha256', serialize( $snapshot ) );
		}
	}

	/** @param array<int,string> $before @param array<int,string> $after */
	private static function changed_snapshot_count( array $before, array $after ): int {
		$ids = array_values( array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) ) );
		$changed = 0;
		foreach ( $ids as $post_id ) {
			if ( ( $before[ $post_id ] ?? null ) !== ( $after[ $post_id ] ?? null ) ) {
				++$changed;
			}
		}
		return $changed;
	}

	/** @param array<string,int> $map */
	private static function increment( array &$map, string $key ): void {
		if ( '' === $key ) {
			$key = 'unknown';
		}
		$map[ $key ] = (int) ( $map[ $key ] ?? 0 ) + 1;
	}
}
