<?php
/**
 * G-540 — Full-corpus / determinism / idempotence runner.
 *
 * Engineering-only environmental validator. Writes only derived Search Projection
 * state/table and never WordPress editorial content.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Corpus_Runner_G540 {

	public const ACTION = 'bdc_kb_spec005_g540_corpus';
	public const PAGE_SLUG = 'bdc-kb-spec005-g540-corpus';

	private const NONCE_ACTION = 'bdc_kb_spec005_g540_corpus';
	private const NONCE_FIELD = 'bdc_kb_spec005_g540_corpus_nonce';

	/** @var array<int,string> */
	private const ALLOWED_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	public static function register(): void {
		add_action( 'admin_menu', array( self::class, 'register_page' ), 44 );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle_run' ) );
	}

	public static function register_page(): void {
		add_submenu_page(
			Admin_Page::PAGE_SLUG,
			'Search Corpus G-540',
			'Search Corpus G-540',
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
		echo '<h1>' . esc_html__( 'SPEC-005 — G-540 Full Corpus', 'bdc-knowledge-base' ) . '</h1>';
		echo '<div class="notice notice-info inline"><p>';
		echo esc_html__( 'Validação automatizada de engenharia. Cria/atualiza somente a Search Projection derivada, executa duas passagens e baixa evidência JSON. Não altera conteúdo editorial.', 'bdc-knowledge-base' );
		echo '</p></div>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		submit_button( __( 'Executar G-540 automaticamente e baixar JSON', 'bdc-knowledge-base' ), 'primary' );
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
		header( 'Content-Disposition: attachment; filename="bdc-kb-spec005-g540-corpus-' . gmdate( 'Ymd-His' ) . '.json"' );
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started = microtime( true );
		$errors = array();
		$throwables = array();
		$schema_exists = false;
		$stale_deleted = null;
		$db_count = null;
		$db_mismatches = array();

		$ids_before = self::corpus_ids();
		$editorial_before = self::editorial_snapshot( $ids_before );
		$editorial_fingerprint_before = self::aggregate_fingerprint( $editorial_before );

		if ( empty( $ids_before ) ) {
			$errors[] = array( 'code' => 'EMPTY_CORPUS', 'message' => 'Corpus indexável vazio; execução bloqueada.' );
		}

		try {
			Search_Projection_Repository::ensure_schema();
			$schema_exists = Search_Projection_Repository::schema_exists();
			if ( ! $schema_exists ) {
				$errors[] = array( 'code' => 'SCHEMA_NOT_FOUND_AFTER_ENSURE' );
			}
		} catch ( \Throwable $error ) {
			$throwables[] = self::throwable_row( 'ensure_schema', 0, $error );
		}

		Search_Projection_Repository::write_state(
			array(
				'status' => 'building',
				'corpus_count' => count( $ids_before ),
				'source_fingerprint' => '',
				'last_error_code' => '',
			)
		);

		$pass1 = empty( $errors ) && empty( $throwables )
			? self::run_pass( $ids_before, 'pass1' )
			: self::empty_pass( 'pass1' );

		if ( ! empty( $pass1['errors'] ) ) {
			$errors = array_merge( $errors, $pass1['errors'] );
		}
		if ( ! empty( $pass1['throwables'] ) ) {
			$throwables = array_merge( $throwables, $pass1['throwables'] );
		}

		if ( empty( $errors ) && empty( $throwables ) ) {
			$cleanup = Search_Projection_Repository::delete_stale_rows( $ids_before );
			if ( $cleanup instanceof \WP_Error ) {
				$errors[] = array(
					'code' => $cleanup->get_error_code(),
					'message' => $cleanup->get_error_message(),
				);
			} else {
				$stale_deleted = $cleanup;
			}
		}

		$pass2 = empty( $errors ) && empty( $throwables )
			? self::run_pass( $ids_before, 'pass2' )
			: self::empty_pass( 'pass2' );

		if ( ! empty( $pass2['errors'] ) ) {
			$errors = array_merge( $errors, $pass2['errors'] );
		}
		if ( ! empty( $pass2['throwables'] ) ) {
			$throwables = array_merge( $throwables, $pass2['throwables'] );
		}

		$hash_compare = self::compare_passes( (array) $pass1['documents'], (array) $pass2['documents'] );

		if ( empty( $errors ) && empty( $throwables ) ) {
			$count = Search_Projection_Repository::count_rows();
			if ( $count instanceof \WP_Error ) {
				$errors[] = array( 'code' => $count->get_error_code(), 'message' => $count->get_error_message() );
			} else {
				$db_count = $count;
			}

			$db_snapshot = Search_Projection_Repository::hash_snapshot();
			if ( $db_snapshot instanceof \WP_Error ) {
				$errors[] = array( 'code' => $db_snapshot->get_error_code(), 'message' => $db_snapshot->get_error_message() );
			} else {
				$db_mismatches = self::compare_db_snapshot( (array) $pass2['documents'], $db_snapshot );
			}
		}

		$ids_after = self::corpus_ids();
		$editorial_after = self::editorial_snapshot( $ids_after );
		$editorial_fingerprint_after = self::aggregate_fingerprint( $editorial_after );
		$changed_posts = self::changed_snapshot_count( $editorial_before, $editorial_after );

		$safety_pass = hash_equals( $editorial_fingerprint_before, $editorial_fingerprint_after )
			&& 0 === $changed_posts
			&& $ids_before === $ids_after;

		$source_fingerprint = self::source_fingerprint( (array) $pass2['documents'] );

		$g540_pass = $schema_exists
			&& $safety_pass
			&& empty( $errors )
			&& empty( $throwables )
			&& 0 === (int) $hash_compare['mismatch_count']
			&& count( $ids_before ) === (int) ( $pass1['processed'] ?? 0 )
			&& count( $ids_before ) === (int) ( $pass2['processed'] ?? 0 )
			&& count( $ids_before ) === (int) ( $pass2['no_change'] ?? -1 )
			&& 0 === (int) ( $pass2['written'] ?? -1 )
			&& count( $ids_before ) === (int) $db_count
			&& empty( $db_mismatches );

		Search_Projection_Repository::write_state(
			array(
				'status' => $g540_pass ? 'ready' : 'failed',
				'corpus_count' => count( $ids_before ),
				'source_fingerprint' => $source_fingerprint,
				'last_success_at_gmt' => $g540_pass ? gmdate( 'Y-m-d H:i:s' ) : '',
				'last_error_code' => $g540_pass ? '' : self::first_error_code( $errors, $throwables, $hash_compare, $db_mismatches ),
			)
		);

		return array(
			'schema_version' => '1.0.0',
			'gate' => 'G-540',
			'mode' => 'spec005_search_projection_full_corpus',
			'generated_at' => gmdate( 'c' ),
			'environment' => array(
				'wordpress' => get_bloginfo( 'version' ),
				'php' => PHP_VERSION,
				'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : '',
				'multisite' => is_multisite(),
				'db_server' => self::db_version(),
			),
			'versions' => array(
				'normalizer' => Search_Query_Normalizer::VERSION,
				'document' => Search_Document_Builder::VERSION,
				'ranker' => Lexical_Ranker::VERSION,
				'result' => Search_Service::RESULT_VERSION,
				'projection_schema' => Search_Projection_Repository::SCHEMA_VERSION,
			),
			'safety' => array(
				'editorial_write_design' => false,
				'derived_projection_write' => true,
				'calls_external_network' => false,
				'query_logging' => false,
				'editorial_fingerprint_before' => $editorial_fingerprint_before,
				'editorial_fingerprint_after' => $editorial_fingerprint_after,
				'editorial_fingerprint_equal' => hash_equals( $editorial_fingerprint_before, $editorial_fingerprint_after ),
				'changed_posts_during_run' => $changed_posts,
				'corpus_ids_equal_before_after' => $ids_before === $ids_after,
			),
			'corpus' => array(
				'count_before' => count( $ids_before ),
				'count_after' => count( $ids_after ),
				'statuses' => self::status_counts( $ids_before ),
			),
			'projection' => array(
				'table' => Search_Projection_Repository::table_name(),
				'schema_exists' => $schema_exists,
				'stale_rows_deleted' => $stale_deleted,
				'row_count' => $db_count,
				'source_fingerprint' => $source_fingerprint,
				'db_snapshot_mismatch_count' => count( $db_mismatches ),
				'db_snapshot_mismatch_post_ids' => array_slice( $db_mismatches, 0, 50 ),
				'state_after' => Search_Projection_Repository::state(),
			),
			'pass1' => self::public_pass( $pass1 ),
			'pass2' => self::public_pass( $pass2 ),
			'determinism' => $hash_compare,
			'coverage' => self::coverage( (array) $pass2['documents'] ),
			'errors' => $errors,
			'throwables' => $throwables,
			'performance' => array(
				'total_runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
				'peak_memory_bytes' => memory_get_peak_usage( true ),
			),
			'gate_result' => array(
				't540_full_corpus_pass' => $g540_pass && count( $ids_before ) === (int) ( $pass2['processed'] ?? 0 ),
				't541_two_pass_determinism_pass' => 0 === (int) $hash_compare['mismatch_count'],
				't542_coverage_recorded' => ! empty( $pass2['documents'] ),
				't543_idempotence_pass' => count( $ids_before ) === (int) ( $pass2['no_change'] ?? -1 ) && 0 === (int) ( $pass2['written'] ?? -1 ),
				't544_zero_fatal_throwable_pass' => empty( $errors ) && empty( $throwables ),
				't545_g540_pass' => $g540_pass,
				'next_gate' => $g540_pass ? 'G-550' : 'G-540',
			),
			'interpretation_rules' => array(
				'Projection fica building durante as duas passagens; Search deve usar wordpress_fallback nesse período.',
				'Pass2 deve ser 100% NO_CHANGE; qualquer WRITTEN indica não determinismo ou fonte alterada.',
				'Search Projection é derivada e pode ser reconstruída; WordPress continua fonte editorial e autoridade de acesso.',
				'G-540 PASS não equivale a Golden PASS, UX PASS, performance PASS ou produção.',
			),
		);
	}

	/**
	 * @param array<int,int> $post_ids
	 * @return array<string,mixed>
	 */
	private static function run_pass( array $post_ids, string $name ): array {
		$started = microtime( true );
		$durations = array();
		$documents = array();
		$errors = array();
		$throwables = array();
		$written = 0;
		$no_change = 0;

		foreach ( $post_ids as $post_id ) {
			$item_started = microtime( true );
			try {
				$document = Search_Document_Builder::build( $post_id );
				if ( $document instanceof \WP_Error ) {
					$errors[] = array(
						'pass' => $name,
						'post_id' => $post_id,
						'code' => $document->get_error_code(),
						'message' => $document->get_error_message(),
					);
					continue;
				}

				$result = Search_Projection_Repository::upsert( $document );
				if ( $result instanceof \WP_Error ) {
					$errors[] = array(
						'pass' => $name,
						'post_id' => $post_id,
						'code' => $result->get_error_code(),
						'message' => $result->get_error_message(),
					);
					continue;
				}

				if ( Search_Projection_Repository::UPSERT_NO_CHANGE === $result ) {
					++$no_change;
				} else {
					++$written;
				}

				$documents[ $post_id ] = array(
					'source_hash' => (string) $document['source_hash'],
					'document_hash' => (string) $document['document_hash'],
					'document_state' => (string) $document['document_state'],
					'source_kind' => (string) $document['source_kind'],
					'field_presence' => array(
						'title' => '' !== (string) $document['title_norm'],
						'summary' => '' !== (string) $document['summary_norm'],
						'headings' => '' !== (string) $document['headings_norm'],
						'taxonomy' => '' !== (string) $document['taxonomy_norm'],
						'body' => '' !== (string) $document['body_norm'],
					),
					'diagnostics' => is_array( $document['diagnostics'] ?? null ) ? $document['diagnostics'] : array(),
				);
			} catch ( \Throwable $error ) {
				$throwables[] = self::throwable_row( $name, $post_id, $error );
			} finally {
				$durations[] = ( microtime( true ) - $item_started ) * 1000;
			}
		}

		return array(
			'name' => $name,
			'processed' => count( $documents ),
			'written' => $written,
			'no_change' => $no_change,
			'documents' => $documents,
			'errors' => $errors,
			'throwables' => $throwables,
			'performance_ms' => self::stats( $durations ),
			'runtime_ms' => round( ( microtime( true ) - $started ) * 1000, 4 ),
		);
	}

	/** @return array<string,mixed> */
	private static function empty_pass( string $name ): array {
		return array(
			'name' => $name,
			'processed' => 0,
			'written' => 0,
			'no_change' => 0,
			'documents' => array(),
			'errors' => array(),
			'throwables' => array(),
			'performance_ms' => self::stats( array() ),
			'runtime_ms' => 0.0,
		);
	}

	/**
	 * @param array<string,mixed> $pass
	 * @return array<string,mixed>
	 */
	private static function public_pass( array $pass ): array {
		return array(
			'name' => (string) ( $pass['name'] ?? '' ),
			'processed' => (int) ( $pass['processed'] ?? 0 ),
			'written' => (int) ( $pass['written'] ?? 0 ),
			'no_change' => (int) ( $pass['no_change'] ?? 0 ),
			'error_count' => count( (array) ( $pass['errors'] ?? array() ) ),
			'throwable_count' => count( (array) ( $pass['throwables'] ?? array() ) ),
			'performance_ms' => (array) ( $pass['performance_ms'] ?? array() ),
			'runtime_ms' => (float) ( $pass['runtime_ms'] ?? 0.0 ),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $a
	 * @param array<int,array<string,mixed>> $b
	 * @return array<string,mixed>
	 */
	private static function compare_passes( array $a, array $b ): array {
		$post_ids = array_values( array_unique( array_merge( array_keys( $a ), array_keys( $b ) ) ) );
		sort( $post_ids, SORT_NUMERIC );
		$mismatches = array();

		foreach ( $post_ids as $post_id ) {
			if ( ! isset( $a[ $post_id ], $b[ $post_id ] ) ) {
				$mismatches[] = (int) $post_id;
				continue;
			}
			if (
				(string) $a[ $post_id ]['source_hash'] !== (string) $b[ $post_id ]['source_hash']
				|| (string) $a[ $post_id ]['document_hash'] !== (string) $b[ $post_id ]['document_hash']
			) {
				$mismatches[] = (int) $post_id;
			}
		}

		return array(
			'compared' => count( $post_ids ),
			'mismatch_count' => count( $mismatches ),
			'mismatch_post_ids' => array_slice( $mismatches, 0, 50 ),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $documents
	 * @param array<int,array<string,mixed>> $db_rows
	 * @return array<int,int>
	 */
	private static function compare_db_snapshot( array $documents, array $db_rows ): array {
		$db = array();
		foreach ( $db_rows as $row ) {
			$db[ (int) $row['post_id'] ] = $row;
		}
		$mismatches = array();

		foreach ( $documents as $post_id => $document ) {
			if ( ! isset( $db[ $post_id ] ) ) {
				$mismatches[] = (int) $post_id;
				continue;
			}
			if (
				(string) $document['source_hash'] !== (string) $db[ $post_id ]['source_hash']
				|| (string) $document['document_hash'] !== (string) $db[ $post_id ]['document_hash']
			) {
				$mismatches[] = (int) $post_id;
			}
		}

		foreach ( $db as $post_id => $row ) {
			unset( $row );
			if ( ! isset( $documents[ $post_id ] ) ) {
				$mismatches[] = (int) $post_id;
			}
		}

		$mismatches = array_values( array_unique( $mismatches ) );
		sort( $mismatches, SORT_NUMERIC );
		return $mismatches;
	}

	/**
	 * @param array<int,array<string,mixed>> $documents
	 * @return array<string,mixed>
	 */
	private static function coverage( array $documents ): array {
		$states = array();
		$source_kinds = array();
		$fields = array( 'title' => 0, 'summary' => 0, 'headings' => 0, 'taxonomy' => 0, 'body' => 0 );
		$extractor_warning_total = 0;
		$extractor_error_codes = array();
		$taxonomy_error_codes = array();

		foreach ( $documents as $document ) {
			$state = (string) ( $document['document_state'] ?? 'unknown' );
			$kind = (string) ( $document['source_kind'] ?? 'unknown' );
			$states[ $state ] = (int) ( $states[ $state ] ?? 0 ) + 1;
			$source_kinds[ $kind ] = (int) ( $source_kinds[ $kind ] ?? 0 ) + 1;

			foreach ( $fields as $field => $count ) {
				unset( $count );
				if ( ! empty( $document['field_presence'][ $field ] ) ) {
					++$fields[ $field ];
				}
			}

			$diagnostics = is_array( $document['diagnostics'] ?? null ) ? $document['diagnostics'] : array();
			$extractor_warning_total += (int) ( $diagnostics['extractor_warning_count'] ?? 0 );

			$extractor_code = (string) ( $diagnostics['extractor_error_code'] ?? '' );
			if ( '' !== $extractor_code ) {
				$extractor_error_codes[ $extractor_code ] = (int) ( $extractor_error_codes[ $extractor_code ] ?? 0 ) + 1;
			}
			$taxonomy_code = (string) ( $diagnostics['taxonomy_error_code'] ?? '' );
			if ( '' !== $taxonomy_code ) {
				$taxonomy_error_codes[ $taxonomy_code ] = (int) ( $taxonomy_error_codes[ $taxonomy_code ] ?? 0 ) + 1;
			}
		}

		ksort( $states );
		ksort( $source_kinds );
		ksort( $extractor_error_codes );
		ksort( $taxonomy_error_codes );

		return array(
			'document_states' => $states,
			'source_kinds' => $source_kinds,
			'nonempty_fields' => $fields,
			'extractor_warning_total' => $extractor_warning_total,
			'extractor_error_codes' => $extractor_error_codes,
			'taxonomy_error_codes' => $taxonomy_error_codes,
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

	/**
	 * @param array<int,int> $post_ids
	 * @return array<string,int>
	 */
	private static function status_counts( array $post_ids ): array {
		$counts = array();
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$status = (string) ( $post->post_status ?? 'unknown' );
			$counts[ $status ] = (int) ( $counts[ $status ] ?? 0 ) + 1;
		}
		ksort( $counts );
		return $counts;
	}

	/**
	 * @param array<int,int> $post_ids
	 * @return array<int,string>
	 */
	private static function editorial_snapshot( array $post_ids ): array {
		$out = array();

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				$out[ $post_id ] = 'missing';
				continue;
			}

			$summary = array();
			foreach ( Meta_Contract::fields() as $definition ) {
				$summary[ (string) $definition['key'] ] = self::stable_value(
					get_post_meta( $post_id, (string) $definition['key'], true )
				);
			}

			$taxonomies = array();
			foreach ( Classification_Contract::fields() as $definition ) {
				$taxonomy = (string) $definition['taxonomy'];
				$term_ids = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
				if ( is_wp_error( $term_ids ) ) {
					$taxonomies[ $taxonomy ] = array( 'error' => $term_ids->get_error_code() );
				} else {
					$ids = array_values( array_map( 'intval', is_array( $term_ids ) ? $term_ids : array() ) );
					sort( $ids, SORT_NUMERIC );
					$taxonomies[ $taxonomy ] = $ids;
				}
			}

			$out[ $post_id ] = Canonical_JSON::hash(
				array(
					'post_id' => $post_id,
					'post_type' => (string) $post->post_type,
					'post_status' => (string) $post->post_status,
					'post_modified_gmt' => (string) $post->post_modified_gmt,
					'post_title' => (string) $post->post_title,
					'post_excerpt' => (string) $post->post_excerpt,
					'post_content' => (string) $post->post_content,
					'elementor_data' => self::stable_value( get_post_meta( $post_id, '_elementor_data', true ) ),
					'summary' => $summary,
					'taxonomies' => $taxonomies,
				)
			);
		}

		ksort( $out, SORT_NUMERIC );
		return $out;
	}

	/** @param array<int,string> $snapshot */
	private static function aggregate_fingerprint( array $snapshot ): string {
		return Canonical_JSON::hash( $snapshot );
	}

	/**
	 * @param array<int,string> $before
	 * @param array<int,string> $after
	 */
	private static function changed_snapshot_count( array $before, array $after ): int {
		$count = 0;
		$ids = array_values( array_unique( array_merge( array_keys( $before ), array_keys( $after ) ) ) );
		foreach ( $ids as $post_id ) {
			if ( ! isset( $before[ $post_id ], $after[ $post_id ] ) || $before[ $post_id ] !== $after[ $post_id ] ) {
				++$count;
			}
		}
		return $count;
	}

	/** @param array<int,array<string,mixed>> $documents */
	private static function source_fingerprint( array $documents ): string {
		$source = array();
		ksort( $documents, SORT_NUMERIC );
		foreach ( $documents as $post_id => $document ) {
			$source[ (string) $post_id ] = (string) ( $document['source_hash'] ?? '' );
		}
		return empty( $source ) ? '' : Canonical_JSON::hash( $source );
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

	/**
	 * @param array<int,float> $values
	 * @return array<string,float|int>
	 */
	private static function stats( array $values ): array {
		if ( empty( $values ) ) {
			return array( 'count' => 0, 'mean' => 0.0, 'p50' => 0.0, 'p95' => 0.0, 'max' => 0.0 );
		}

		sort( $values, SORT_NUMERIC );
		$count = count( $values );
		return array(
			'count' => $count,
			'mean' => round( array_sum( $values ) / $count, 4 ),
			'p50' => round( self::percentile( $values, 0.50 ), 4 ),
			'p95' => round( self::percentile( $values, 0.95 ), 4 ),
			'max' => round( (float) $values[ $count - 1 ], 4 ),
		);
	}

	/** @param array<int,float> $sorted */
	private static function percentile( array $sorted, float $p ): float {
		$count = count( $sorted );
		if ( 0 === $count ) {
			return 0.0;
		}
		$index = (int) ceil( $p * $count ) - 1;
		$index = max( 0, min( $count - 1, $index ) );
		return (float) $sorted[ $index ];
	}

	private static function throwable_row( string $phase, int $post_id, \Throwable $error ): array {
		return array(
			'phase' => $phase,
			'post_id' => $post_id,
			'class' => get_class( $error ),
			'code' => is_int( $error->getCode() ) || is_string( $error->getCode() ) ? (string) $error->getCode() : '',
			'message' => $error->getMessage(),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $errors
	 * @param array<int,array<string,mixed>> $throwables
	 * @param array<string,mixed> $hash_compare
	 * @param array<int,int> $db_mismatches
	 */
	private static function first_error_code( array $errors, array $throwables, array $hash_compare, array $db_mismatches ): string {
		if ( ! empty( $errors ) ) {
			return sanitize_key( (string) ( $errors[0]['code'] ?? 'g540_error' ) );
		}
		if ( ! empty( $throwables ) ) {
			return 'g540_throwable';
		}
		if ( (int) ( $hash_compare['mismatch_count'] ?? 0 ) > 0 ) {
			return 'g540_hash_mismatch';
		}
		if ( ! empty( $db_mismatches ) ) {
			return 'g540_db_snapshot_mismatch';
		}
		return 'g540_gate_failed';
	}

	private static function db_version(): string {
		global $wpdb;
		return method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '';
	}
}
