<?php
/**
 * Full-corpus, read-only Block Projection smoke for SPEC-004 / G-245 / T093.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Block_Projection_Plan_Smoke {

	public const ACTION = 'bdc_kb_spec004_g245_block_projection_smoke';
	public const PAGE_SLUG = 'bdc-kb-spec004-g245-block-projection-smoke';

	private const NONCE_ACTION = 'bdc_kb_spec004_g245_block_projection_smoke_run';
	private const NONCE_FIELD = 'bdc_kb_spec004_g245_block_projection_smoke_nonce';

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 36 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Block Projection G-245',
			'Block Projection G-245',
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
		echo '<h1>' . esc_html__( 'SPEC-004 — T093 Block Projection full-corpus', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Read-only.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Executa duas passagens sobre o corpus, sem serialize_blocks e sem persistência editorial. Exporta métricas agregadas, motivos do Knowledge Document e matriz source/status.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar T093 e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec004-t093-block-projection-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$ids_before = self::post_ids();
		$fingerprint_before = self::editorial_fingerprint( $ids_before );
		$first = self::projection_pass( $ids_before );
		$second = self::projection_pass( $ids_before );

		$hash_mismatches = 0;
		$canonical_mismatches = 0;
		foreach ( $ids_before as $post_id ) {
			if ( ! isset( $first['plans'][ $post_id ], $second['plans'][ $post_id ] ) ) {
				continue;
			}
			$a = $first['plans'][ $post_id ];
			$b = $second['plans'][ $post_id ];
			if ( ! hash_equals( (string) $a['block_projection_hash'], (string) $b['block_projection_hash'] ) ) {
				++$hash_mismatches;
			}
			if ( ! hash_equals( (string) $a['canonical_hash'], (string) $b['canonical_hash'] ) ) {
				++$canonical_mismatches;
			}
		}

		$ids_after = self::post_ids();
		$fingerprint_after = self::editorial_fingerprint( $ids_after );
		$corpus_unchanged = $ids_before === $ids_after;
		$fingerprint_equal = hash_equals( $fingerprint_before, $fingerprint_after );

		$gate = $corpus_unchanged
			&& $fingerprint_equal
			&& count( $ids_before ) === count( $first['plans'] )
			&& count( $ids_before ) === count( $second['plans'] )
			&& 0 === $first['errors']
			&& 0 === $second['errors']
			&& 0 === $first['throwables']
			&& 0 === $second['throwables']
			&& 0 === $hash_mismatches
			&& 0 === $canonical_mismatches
			&& 0 === $first['safety_violations']
			&& 0 === $second['safety_violations'];

		return array(
			'schema_version' => '1.1.0',
			'gate' => 'T093',
			'mode' => 'block_projection_full_corpus_read_only',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'knowledge_document_schema' => Knowledge_Document::SCHEMA_VERSION,
				'block_projection_schema' => Block_Projection_Plan::SCHEMA_VERSION,
				'elementor' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
				'gutenberg_plugin_dependency' => false,
			),
			'corpus' => array(
				'total_posts' => count( $ids_before ),
				'first_pass_plans' => count( $first['plans'] ),
				'second_pass_plans' => count( $second['plans'] ),
				'first_pass_errors' => $first['errors'],
				'second_pass_errors' => $second['errors'],
				'first_pass_throwables' => $first['throwables'],
				'second_pass_throwables' => $second['throwables'],
				'block_projection_hash_mismatches' => $hash_mismatches,
				'canonical_hash_mismatches' => $canonical_mismatches,
				'first_pass_safety_violations' => $first['safety_violations'],
				'second_pass_safety_violations' => $second['safety_violations'],
			),
			'distribution' => array(
				'plan_status' => $first['plan_status'],
				'source_kind' => $first['source_kind'],
				'knowledge_document_readiness' => $first['kd_readiness_status'],
				'knowledge_document_reasons' => $first['kd_reasons'],
				'source_plan_matrix' => $first['source_plan_matrix'],
				'warnings' => $first['warnings'],
				'projected_block_names' => $first['block_names'],
			),
			'safety' => array(
				'read_only_design' => true,
				'serialized_post_content' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'writer_allowed' => false,
				'migration_execution_allowed' => false,
				'depends_on_gutenberg_plugin' => false,
				'exports_editorial_content' => false,
				'exports_post_ids' => false,
				'corpus_unchanged' => $corpus_unchanged,
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => $fingerprint_equal,
			),
			'gate_result' => array( 't093_block_projection_pass' => $gate ),
			'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
	}

	/** @param array<int,int> $post_ids @return array<string,mixed> */
	private static function projection_pass( array $post_ids ): array {
		$out = array(
			'plans' => array(),
			'errors' => 0,
			'throwables' => 0,
			'safety_violations' => 0,
			'plan_status' => array(),
			'source_kind' => array(),
			'kd_readiness_status' => array(),
			'kd_reasons' => array(),
			'source_plan_matrix' => array(),
			'warnings' => array(),
			'block_names' => array(),
		);
		foreach ( $post_ids as $post_id ) {
			try {
				$document = Knowledge_Document::build( $post_id );
				if ( is_wp_error( $document ) ) {
					++$out['errors'];
					continue;
				}
				$plan = Block_Projection_Plan::from_document( $document );
				if ( is_wp_error( $plan ) ) {
					++$out['errors'];
					continue;
				}
				$canonical_hash = Canonical_JSON::hash( $plan );
				$out['plans'][ $post_id ] = array(
					'block_projection_hash' => (string) ( $plan['block_projection_hash'] ?? '' ),
					'canonical_hash' => $canonical_hash,
				);
				self::inc( $out['plan_status'], (string) ( $plan['plan_status'] ?? 'unknown' ) );
				self::inc( $out['source_kind'], (string) ( $plan['source_kind'] ?? 'unknown' ) );

				$kd_readiness = is_array( $document['ai_readiness'] ?? null ) ? $document['ai_readiness'] : array();
				$kd_status = (string) ( $kd_readiness['status'] ?? 'unknown' );
				self::inc( $out['kd_readiness_status'], $kd_status );
				if ( in_array( $kd_status, array( 'review_required', 'not_ready' ), true ) ) {
					foreach ( (array) ( $kd_readiness['reasons'] ?? array() ) as $reason ) {
						self::inc( $out['kd_reasons'], (string) $reason );
					}
				}

				$source_key = (string) ( $plan['source_kind'] ?? 'unknown' );
				$plan_key = (string) ( $plan['plan_status'] ?? 'unknown' );
				if ( ! isset( $out['source_plan_matrix'][ $source_key ] ) || ! is_array( $out['source_plan_matrix'][ $source_key ] ) ) {
					$out['source_plan_matrix'][ $source_key ] = array();
				}
				self::inc( $out['source_plan_matrix'][ $source_key ], $plan_key );

				foreach ( (array) ( $plan['warnings'] ?? array() ) as $warning ) {
					self::inc( $out['warnings'], (string) $warning );
				}
				foreach ( (array) ( $plan['blocks'] ?? array() ) as $block ) {
					if ( is_array( $block ) ) {
						self::collect_block_names( $block, $out['block_names'] );
					}
				}
				if ( true === ( $plan['writer_allowed'] ?? false )
					|| true === ( $plan['migration_execution_allowed'] ?? false )
					|| null !== ( $plan['serialized_post_content'] ?? null )
					|| true === ( $plan['safety']['persists_state'] ?? true )
					|| true === ( $plan['safety']['writes_post_content'] ?? true )
					|| true === ( $plan['safety']['writes_elementor_data'] ?? true )
					|| true === ( $plan['safety']['depends_on_gutenberg_plugin'] ?? true ) ) {
					++$out['safety_violations'];
				}
			} catch ( \Throwable $error ) {
				++$out['throwables'];
			}
		}
		ksort( $out['plan_status'] );
		ksort( $out['source_kind'] );
		ksort( $out['kd_readiness_status'] );
		ksort( $out['kd_reasons'] );
		ksort( $out['source_plan_matrix'] );
		foreach ( $out['source_plan_matrix'] as &$matrix ) {
			if ( is_array( $matrix ) ) {
				ksort( $matrix );
			}
		}
		unset( $matrix );
		ksort( $out['warnings'] );
		ksort( $out['block_names'] );
		return $out;
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
		$ids = is_array( $ids ) ? array_map( 'intval', $ids ) : array();
		sort( $ids, SORT_NUMERIC );
		return array_values( array_filter( $ids, static fn( int $id ): bool => $id > 0 ) );
	}

	/** @param array<int,int> $post_ids */
	private static function editorial_fingerprint( array $post_ids ): string {
		$rows = array();
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$rows[] = array(
				'id' => $post_id,
				'content_sha256' => hash( 'sha256', (string) ( $post->post_content ?? '' ) ),
				'elementor_sha256' => hash( 'sha256', (string) get_post_meta( $post_id, '_elementor_data', true ) ),
				'status' => (string) ( $post->post_status ?? '' ),
				'modified_gmt' => (string) ( $post->post_modified_gmt ?? '' ),
			);
		}
		return Canonical_JSON::hash( $rows );
	}

	/** @param array<string,int> $bucket */
	private static function inc( array &$bucket, string $key ): void {
		$key = '' !== $key ? $key : 'unknown';
		$bucket[ $key ] = (int) ( $bucket[ $key ] ?? 0 ) + 1;
	}

	/** @param array<string,mixed> $block @param array<string,int> $bucket */
	private static function collect_block_names( array $block, array &$bucket ): void {
		self::inc( $bucket, (string) ( $block['block_name'] ?? 'unknown' ) );
		foreach ( (array) ( $block['inner_blocks'] ?? array() ) as $child ) {
			if ( is_array( $child ) ) {
				self::collect_block_names( $child, $bucket );
			}
		}
	}
}
