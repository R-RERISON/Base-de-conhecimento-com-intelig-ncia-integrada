<?php
/**
 * Descoberta read-only de Golden Queries legadas do ASI para R-510/T510.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lê expectativas legadas do ASI sem importar, persistir ou executar ranking.
 *
 * ENGINEERING ONLY: habilitado apenas em build de homologação R-510.
 */
final class Legacy_Golden_Discovery {

	public const ACTION    = 'bdc_kb_spec005_r510_legacy_golden';
	public const PAGE_SLUG = 'bdc-kb-spec005-r510-legacy-golden';

	private const NONCE_ACTION = 'bdc_kb_spec005_r510_legacy_golden_run';
	private const NONCE_FIELD  = 'bdc_kb_spec005_r510_legacy_golden_nonce';
	private const MAX_ROWS     = 2000;

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 41 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Golden Discovery R-510',
			'Golden Discovery R-510',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — Legacy Golden Discovery R-510', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Build temporário de engenharia.', 'bdc-knowledge-base' ) . '</strong> ';
		echo esc_html__( 'Este diagnóstico somente lê a tabela legada de Golden Queries do ASI, se ela existir. Não importa dados, não executa ranking e não altera ASI, WordPress ou conteúdo editorial.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<p>' . esc_html__( 'O JSON exporta apenas expectativas Golden explicitamente curadas no ASI e valida se os posts esperados ainda existem no corpus atual. Identidades de usuários não são exportadas.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Descobrir Golden Queries legadas e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-r510-legacy-golden-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		global $wpdb;

		$started = microtime( true );
		$ids_before = self::post_ids();
		$snapshot_before = self::editorial_snapshot( $ids_before );
		$fingerprint_before = self::aggregate_fingerprint( $snapshot_before );

		$table = $wpdb->prefix . 'asi_golden_queries';
		$table_name_safe = 1 === preg_match( '/^[A-Za-z0-9_]+$/', $table );
		$table_exists = false;
		$columns = array();
		$errors = array();
		$candidates = array();
		$legacy_last_run = array();

		if ( ! $table_name_safe ) {
			$errors[] = 'UNSAFE_LEGACY_TABLE_NAME';
		} else {
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
			$table_exists = is_string( $found ) && hash_equals( $table, $found );

			if ( $table_exists ) {
				$column_rows = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}`", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- internal validated identifier.
				foreach ( (array) $column_rows as $row ) {
					$field = isset( $row['Field'] ) ? (string) $row['Field'] : '';
					if ( '' !== $field ) {
						$columns[ $field ] = true;
					}
				}

				$required = array( 'id', 'query_text', 'expected_post_id', 'max_rank', 'severity', 'active' );
				$missing = array_values( array_filter( $required, static fn ( string $column ): bool => ! isset( $columns[ $column ] ) ) );
				if ( ! empty( $missing ) ) {
					$errors[] = 'LEGACY_GOLDEN_MISSING_COLUMNS:' . implode( ',', $missing );
				} else {
					$select = array( 'id', 'query_text', 'expected_post_id', 'max_rank', 'severity', 'active' );
					foreach ( array( 'query_norm', 'expected_item_key', 'source', 'notes' ) as $optional ) {
						if ( isset( $columns[ $optional ] ) ) {
							$select[] = $optional;
						}
					}
					$sql = 'SELECT ' . implode( ',', $select ) . " FROM `{$table}` ORDER BY active DESC, id ASC LIMIT " . self::MAX_ROWS;
					$rows = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- fixed columns/internal validated identifier/constant limit.

					foreach ( (array) $rows as $row ) {
						$candidates[] = self::candidate_from_row( is_array( $row ) ? $row : array() );
					}
				}
			}
		}

		$last_run = get_option( 'asi4_golden_last_run', array() );
		if ( is_array( $last_run ) ) {
			foreach ( array( 'status', 'count', 'blocking_failed', 'warning_failed', 'set_hash', 'algorithm_version', 'item_algorithm_version', 'ran_at_gmt' ) as $key ) {
				if ( isset( $last_run[ $key ] ) && is_scalar( $last_run[ $key ] ) ) {
					$legacy_last_run[ $key ] = $last_run[ $key ];
				}
			}
		}

		$ids_after = self::post_ids();
		$snapshot_after = self::editorial_snapshot( $ids_after );
		$fingerprint_after = self::aggregate_fingerprint( $snapshot_after );
		$changed = self::changed_snapshot_count( $snapshot_before, $snapshot_after );

		$counts = self::candidate_counts( $candidates );
		$candidate_hash = self::candidate_hash( $candidates );
		$safety_pass = hash_equals( $fingerprint_before, $fingerprint_after )
			&& 0 === $changed
			&& count( $ids_before ) === count( $ids_after )
			&& empty( $errors );

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'R-510/T510',
			'mode' => 'spec005_read_only_legacy_golden_discovery',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
				'db_server' => method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '',
			),
			'safety' => array(
				'read_only_design' => true,
				'creates_or_alters_schema' => false,
				'writes_legacy_asi' => false,
				'writes_wordpress' => false,
				'persists_candidates' => false,
				'executes_search_or_ranking' => false,
				'calls_external_network' => false,
				'exports_user_identity' => false,
				'exports_legacy_golden_queries' => true,
				'editorial_fingerprint_before' => $fingerprint_before,
				'editorial_fingerprint_after' => $fingerprint_after,
				'editorial_fingerprint_equal' => hash_equals( $fingerprint_before, $fingerprint_after ),
				'changed_posts_during_run' => $changed,
				'corpus_count_before' => count( $ids_before ),
				'corpus_count_after' => count( $ids_after ),
			),
			'legacy' => array(
				'expected_table_suffix' => 'asi_golden_queries',
				'table_exists' => $table_exists,
				'table_name_safe' => $table_name_safe,
				'columns_detected' => array_keys( $columns ),
				'last_run_metadata' => $legacy_last_run,
			),
			'candidates' => array(
				'count' => count( $candidates ),
				'set_hash' => $candidate_hash,
				'counts' => $counts,
				'items' => $candidates,
			),
			'errors' => $errors,
			'performance' => array(
				'runtime_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'interpretation_rules' => array(
				'Legacy rows are candidates, not automatically accepted Golden truth.',
				'Inactive legacy rows remain visible for review but are not candidates for an active v1 suite unless explicitly re-approved.',
				'Item-level expectations are preserved as evidence but remain outside the first post-level runtime scope.',
				'A missing legacy table is a valid discovery result and does not justify inventing Golden expectations.',
				'Any active v1 suite still requires human review in T513.',
			),
			'gate_result' => array(
				't510_read_only_safety_pass' => $safety_pass,
				'legacy_candidates_found' => count( $candidates ) > 0,
				'r510_ready' => false,
				'reason' => 'T510 discovers candidates only; R-510 requires human review, diversity, suite hash/version and explicit acceptance.',
			),
		);
	}

	/** @param array<string,mixed> $row @return array<string,mixed> */
	private static function candidate_from_row( array $row ): array {
		$post_id = absint( $row['expected_post_id'] ?? 0 );
		$post = $post_id > 0 ? get_post( $post_id ) : null;
		$exists = is_object( $post );
		$post_type = $exists ? (string) ( $post->post_type ?? '' ) : '';
		$post_status = $exists ? (string) ( $post->post_status ?? '' ) : '';
		$query = trim( sanitize_text_field( (string) ( $row['query_text'] ?? '' ) ) );
		$severity = in_array( (string) ( $row['severity'] ?? '' ), array( 'blocking', 'warning' ), true )
			? (string) $row['severity']
			: 'warning';
		$max_rank = max( 1, min( 50, absint( $row['max_rank'] ?? 3 ) ) );
		$item_key = isset( $row['expected_item_key'] ) ? trim( (string) $row['expected_item_key'] ) : '';
		$active = 1 === absint( $row['active'] ?? 0 );

		$review_flags = array();
		if ( '' === $query ) {
			$review_flags[] = 'empty_query';
		}
		if ( ! $exists ) {
			$review_flags[] = 'expected_post_missing';
		} elseif ( Meta_Contract::POST_TYPE !== $post_type ) {
			$review_flags[] = 'unsupported_post_type';
		}
		if ( ! $active ) {
			$review_flags[] = 'legacy_inactive';
		}
		if ( '' !== $item_key ) {
			$review_flags[] = 'item_level_expectation_outside_v1';
		}

		return array(
			'legacy_id' => absint( $row['id'] ?? 0 ),
			'query' => $query,
			'query_norm_legacy' => isset( $row['query_norm'] ) ? (string) $row['query_norm'] : '',
			'expected_post_id' => $post_id,
			'expected_item_key' => $item_key,
			'max_rank' => $max_rank,
			'severity' => $severity,
			'active' => $active,
			'source' => isset( $row['source'] ) ? sanitize_key( (string) $row['source'] ) : 'legacy_asi',
			'notes' => isset( $row['notes'] ) ? sanitize_textarea_field( (string) $row['notes'] ) : '',
			'current_post' => array(
				'exists' => $exists,
				'post_type' => $post_type,
				'post_status' => $post_status,
			),
			'review_flags' => $review_flags,
			'requires_human_review' => true,
		);
	}

	/** @param array<int,array<string,mixed>> $candidates @return array<string,int> */
	private static function candidate_counts( array $candidates ): array {
		$out = array(
			'active' => 0,
			'inactive' => 0,
			'blocking' => 0,
			'warning' => 0,
			'expected_post_missing' => 0,
			'unsupported_post_type' => 0,
			'item_level' => 0,
			'clean_post_level_candidates' => 0,
		);
		foreach ( $candidates as $candidate ) {
			$out[ ! empty( $candidate['active'] ) ? 'active' : 'inactive' ]++;
			$severity = (string) ( $candidate['severity'] ?? 'warning' );
			if ( isset( $out[ $severity ] ) ) {
				$out[ $severity ]++;
			}
			$flags = (array) ( $candidate['review_flags'] ?? array() );
			if ( in_array( 'expected_post_missing', $flags, true ) ) {
				$out['expected_post_missing']++;
			}
			if ( in_array( 'unsupported_post_type', $flags, true ) ) {
				$out['unsupported_post_type']++;
			}
			if ( in_array( 'item_level_expectation_outside_v1', $flags, true ) ) {
				$out['item_level']++;
			}
			if ( ! empty( $candidate['active'] ) && empty( $flags ) ) {
				$out['clean_post_level_candidates']++;
			}
		}
		return $out;
	}

	/** @param array<int,array<string,mixed>> $candidates */
	private static function candidate_hash( array $candidates ): string {
		$stable = array();
		foreach ( $candidates as $candidate ) {
			$stable[] = array(
				'legacy_id' => (int) ( $candidate['legacy_id'] ?? 0 ),
				'query' => (string) ( $candidate['query'] ?? '' ),
				'expected_post_id' => (int) ( $candidate['expected_post_id'] ?? 0 ),
				'expected_item_key' => (string) ( $candidate['expected_item_key'] ?? '' ),
				'max_rank' => (int) ( $candidate['max_rank'] ?? 0 ),
				'severity' => (string) ( $candidate['severity'] ?? '' ),
				'active' => (bool) ( $candidate['active'] ?? false ),
			);
		}
		$json = wp_json_encode( $stable, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return hash( 'sha256', is_string( $json ) ? $json : '' );
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
