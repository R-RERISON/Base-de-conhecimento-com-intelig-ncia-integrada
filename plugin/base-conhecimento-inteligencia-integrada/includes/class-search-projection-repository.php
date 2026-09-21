<?php
/**
 * Persistência reconstruível da Search Retrieval Projection.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Projection_Repository {

	public const SCHEMA_VERSION = '1.0.0';
	public const STATE_OPTION = 'bdc_kb_search_projection_state';
	public const CANDIDATE_CAP = 200;
	public const UPSERT_WRITTEN = 'WRITTEN';
	public const UPSERT_NO_CHANGE = 'NO_CHANGE';

	/** @var array<int,string> */
	private const SEARCH_FIELDS = array(
		'title_norm',
		'summary_norm',
		'headings_norm',
		'taxonomy_norm',
		'body_norm',
	);

	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'bdc_kb_search_documents';
	}

	/**
	 * Criação explícita do schema. Não é chamada em page load.
	 */
	public static function ensure_schema(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			post_id BIGINT UNSIGNED NOT NULL,
			document_state VARCHAR(16) NOT NULL,
			source_kind VARCHAR(32) NOT NULL,
			title_norm TEXT NOT NULL,
			summary_norm LONGTEXT NOT NULL,
			headings_norm LONGTEXT NOT NULL,
			taxonomy_norm LONGTEXT NOT NULL,
			body_norm LONGTEXT NOT NULL,
			source_hash CHAR(64) NOT NULL,
			document_hash CHAR(64) NOT NULL,
			document_version VARCHAR(32) NOT NULL,
			normalizer_version VARCHAR(32) NOT NULL,
			post_modified_gmt DATETIME NULL,
			indexed_at_gmt DATETIME NOT NULL,
			PRIMARY KEY  (post_id),
			KEY document_state (document_state),
			KEY document_version (document_version),
			KEY source_hash (source_hash)
		) {$charset};";

		dbDelta( $sql );
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function state(): array {
		$state = get_option( self::STATE_OPTION, array() );
		return is_array( $state ) ? $state : array();
	}

	public static function is_ready(): bool {
		$state = self::state();

		return 'ready' === (string) ( $state['status'] ?? '' )
			&& self::SCHEMA_VERSION === (string) ( $state['schema_version'] ?? '' )
			&& Search_Document_Builder::VERSION === (string) ( $state['document_version'] ?? '' )
			&& Search_Query_Normalizer::VERSION === (string) ( $state['normalizer_version'] ?? '' );
	}

	/**
	 * @param array<string,mixed> $state
	 */
	public static function write_state( array $state ): bool {
		$status = sanitize_key( (string) ( $state['status'] ?? 'not_built' ) );
		if ( ! in_array( $status, array( 'not_built', 'building', 'ready', 'degraded', 'failed' ), true ) ) {
			return false;
		}

		$payload = array(
			'schema_version' => self::SCHEMA_VERSION,
			'status' => $status,
			'document_version' => Search_Document_Builder::VERSION,
			'normalizer_version' => Search_Query_Normalizer::VERSION,
			'corpus_count' => max( 0, (int) ( $state['corpus_count'] ?? 0 ) ),
			'source_fingerprint' => preg_replace( '/[^a-f0-9]/', '', strtolower( (string) ( $state['source_fingerprint'] ?? '' ) ) ) ?? '',
			'last_success_at_gmt' => (string) ( $state['last_success_at_gmt'] ?? '' ),
			'last_error_code' => sanitize_key( (string) ( $state['last_error_code'] ?? '' ) ),
		);

		return update_option( self::STATE_OPTION, $payload, false );
	}

	/**
	 * @param array<string,mixed> $document
	 * @return string|\WP_Error WRITTEN|NO_CHANGE
	 */
	public static function upsert( array $document ): string|\WP_Error {
		global $wpdb;

		$data = array(
			'post_id' => (int) ( $document['post_id'] ?? 0 ),
			'document_state' => (string) ( $document['document_state'] ?? 'degraded' ),
			'source_kind' => (string) ( $document['source_kind'] ?? 'unknown' ),
			'title_norm' => (string) ( $document['title_norm'] ?? '' ),
			'summary_norm' => (string) ( $document['summary_norm'] ?? '' ),
			'headings_norm' => (string) ( $document['headings_norm'] ?? '' ),
			'taxonomy_norm' => (string) ( $document['taxonomy_norm'] ?? '' ),
			'body_norm' => (string) ( $document['body_norm'] ?? '' ),
			'source_hash' => (string) ( $document['source_hash'] ?? '' ),
			'document_hash' => (string) ( $document['document_hash'] ?? '' ),
			'document_version' => (string) ( $document['document_version'] ?? '' ),
			'normalizer_version' => (string) ( $document['normalizer_version'] ?? '' ),
			'post_modified_gmt' => self::nullable_datetime( (string) ( $document['post_modified_gmt'] ?? '' ) ),
			'indexed_at_gmt' => (string) ( $document['indexed_at_gmt'] ?? '' ),
		);

		if ( $data['post_id'] <= 0 || ! preg_match( '/^[a-f0-9]{64}$/', $data['source_hash'] ) || ! preg_match( '/^[a-f0-9]{64}$/', $data['document_hash'] ) ) {
			return new \WP_Error( 'search_projection_invalid_document', 'Search Document inválido para persistência.' );
		}

		$existing_sql = $wpdb->prepare(
			'SELECT source_hash, document_hash, document_version, normalizer_version FROM ' . self::table_name() . ' WHERE post_id = %d LIMIT 1',
			$data['post_id']
		);
		$existing = $wpdb->get_row( $existing_sql, ARRAY_A );

		if ( '' !== (string) $wpdb->last_error ) {
			return new \WP_Error( 'search_projection_read_failed', 'Falha ao verificar Search Document existente.' );
		}

		if (
			is_array( $existing )
			&& $data['source_hash'] === (string) ( $existing['source_hash'] ?? '' )
			&& $data['document_hash'] === (string) ( $existing['document_hash'] ?? '' )
			&& $data['document_version'] === (string) ( $existing['document_version'] ?? '' )
			&& $data['normalizer_version'] === (string) ( $existing['normalizer_version'] ?? '' )
		) {
			return self::UPSERT_NO_CHANGE;
		}

		$result = $wpdb->replace(
			self::table_name(),
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			return new \WP_Error( 'search_projection_write_failed', 'Falha ao persistir Search Document.' );
		}

		return self::UPSERT_WRITTEN;
	}

	/**
	 * @param array<int,string> $tokens
	 * @return array<int,array<string,mixed>>|\WP_Error
	 */
	public static function retrieve_candidates( array $tokens, int $cap = self::CANDIDATE_CAP ): array|\WP_Error {
		global $wpdb;

		$tokens = array_values( array_unique( array_filter( array_map( 'strval', $tokens ) ) ) );
		$cap = max( 1, min( self::CANDIDATE_CAP, $cap ) );

		if ( empty( $tokens ) ) {
			return array();
		}
		if ( ! self::is_ready() ) {
			return new \WP_Error( 'search_projection_not_ready', 'Search Projection não está pronta.', array( 'status' => 503 ) );
		}

		$strict = self::query_candidates( $tokens, $cap, true, array() );
		if ( $strict instanceof \WP_Error ) {
			return $strict;
		}
		if ( count( $strict ) >= $cap ) {
			return array_slice( $strict, 0, $cap );
		}

		$strict_ids = array_values( array_map( static fn ( array $row ): int => (int) $row['post_id'], $strict ) );
		$relaxed = self::query_candidates( $tokens, $cap - count( $strict ), false, $strict_ids );
		if ( $relaxed instanceof \WP_Error ) {
			return $relaxed;
		}

		return array_merge( $strict, $relaxed );
	}

	/**
	 * @param array<int,string> $tokens
	 * @param array<int,int> $exclude_ids
	 * @return array<int,array<string,mixed>>|\WP_Error
	 */
	private static function query_candidates( array $tokens, int $limit, bool $strict, array $exclude_ids ): array|\WP_Error {
		global $wpdb;

		$token_groups = array();
		$args = array();

		foreach ( $tokens as $token ) {
			// Campos da Projection já são normalizados para tokens separados por espaço.
			// A borda artificial evita "estrutura" casar "infraestrutura" antes do ranker.
			$like = '%' . $wpdb->esc_like( ' ' . $token . ' ' ) . '%';
			$parts = array();
			foreach ( self::SEARCH_FIELDS as $field ) {
				$parts[] = "CONCAT(' ', {$field}, ' ') LIKE %s";
				$args[] = $like;
			}
			$token_groups[] = '(' . implode( ' OR ', $parts ) . ')';
		}

		$where = implode( $strict ? ' AND ' : ' OR ', $token_groups );

		if ( ! empty( $exclude_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $exclude_ids ), '%d' ) );
			$where = '(' . $where . ") AND post_id NOT IN ({$placeholders})";
			foreach ( $exclude_ids as $post_id ) {
				$args[] = $post_id;
			}
		}

		$args[] = $limit;
		$table = self::table_name();
		$sql = "SELECT post_id, document_state, source_kind, title_norm, summary_norm, headings_norm, taxonomy_norm, body_norm
			FROM {$table}
			WHERE {$where}
			ORDER BY post_id ASC
			LIMIT %d";

		$prepared = $wpdb->prepare( $sql, ...$args );
		$rows = $wpdb->get_results( $prepared, ARRAY_A );

		if ( '' !== (string) $wpdb->last_error ) {
			return new \WP_Error( 'search_projection_read_failed', 'Falha ao consultar Search Projection.' );
		}

		return is_array( $rows ) ? $rows : array();
	}

	public static function schema_exists(): bool {
		global $wpdb;

		$table = self::table_name();
		$sql = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) );
		$found = $wpdb->get_var( $sql );

		return is_string( $found ) && $table === $found;
	}

	/**
	 * @return int|\WP_Error
	 */
	public static function count_rows(): int|\WP_Error {
		global $wpdb;

		$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table_name() );
		if ( '' !== (string) $wpdb->last_error ) {
			return new \WP_Error( 'search_projection_count_failed', 'Falha ao contar Search Documents.' );
		}

		return max( 0, (int) $count );
	}

	/**
	 * @return array<int,array{post_id:int,source_hash:string,document_hash:string,document_state:string,source_kind:string}>|\WP_Error
	 */
	public static function hash_snapshot(): array|\WP_Error {
		global $wpdb;

		$rows = $wpdb->get_results(
			'SELECT post_id, source_hash, document_hash, document_state, source_kind FROM ' . self::table_name() . ' ORDER BY post_id ASC',
			ARRAY_A
		);

		if ( '' !== (string) $wpdb->last_error ) {
			return new \WP_Error( 'search_projection_snapshot_failed', 'Falha ao ler snapshot da Search Projection.' );
		}

		$out = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			$out[] = array(
				'post_id' => (int) ( $row['post_id'] ?? 0 ),
				'source_hash' => (string) ( $row['source_hash'] ?? '' ),
				'document_hash' => (string) ( $row['document_hash'] ?? '' ),
				'document_state' => (string) ( $row['document_state'] ?? '' ),
				'source_kind' => (string) ( $row['source_kind'] ?? '' ),
			);
		}
		return $out;
	}

	/**
	 * Remove somente rows que não pertencem ao corpus canônico atual.
	 * Chamada autorizada apenas após uma passagem completa bem-sucedida.
	 *
	 * @param array<int,int> $valid_post_ids
	 * @return int|\WP_Error
	 */
	public static function delete_stale_rows( array $valid_post_ids ): int|\WP_Error {
		global $wpdb;

		$valid_post_ids = array_values(
			array_unique(
				array_filter(
					array_map( 'intval', $valid_post_ids ),
					static fn ( int $post_id ): bool => $post_id > 0
				)
			)
		);

		if ( empty( $valid_post_ids ) ) {
			return new \WP_Error(
				'search_projection_empty_corpus_cleanup_blocked',
				'Limpeza stale bloqueada para corpus vazio.'
			);
		}

		$placeholders = implode( ',', array_fill( 0, count( $valid_post_ids ), '%d' ) );
		$sql = $wpdb->prepare(
			'DELETE FROM ' . self::table_name() . " WHERE post_id NOT IN ({$placeholders})",
			...$valid_post_ids
		);
		$result = $wpdb->query( $sql );

		if ( false === $result || '' !== (string) $wpdb->last_error ) {
			return new \WP_Error( 'search_projection_stale_cleanup_failed', 'Falha ao remover Search Documents stale.' );
		}

		return max( 0, (int) $result );
	}

	private static function nullable_datetime( string $value ): ?string {
		$value = trim( $value );
		return '' === $value || '0000-00-00 00:00:00' === $value ? null : $value;
	}
}
