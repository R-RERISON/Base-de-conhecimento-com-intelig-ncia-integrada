<?php
/**
 * Profiler temporário read-only da SPEC-002.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coleta evidência agregada dos stores classificatórios históricos.
 *
 * Ferramenta temporária de homologação. Não pertence ao runtime final.
 */
final class Classification_Profiler {

	public const ACTION = 'bdc_kb_classification_profile';

	private const NONCE_ACTION   = 'bdc_kb_classification_profile_v1';
	private const NONCE_FIELD    = 'bdc_kb_classification_profile_nonce';
	private const SCHEMA_VERSION = '1.0.0';
	private const TOP_VALUES     = 30;
	private const SAMPLE_LIMIT   = 250;

	/** @return array<string,array{concept:string,origin:string}> */
	private static function stores(): array {
		return array(
			'_bdc_es_target_audience'    => array( 'concept' => 'audiencia', 'origin' => 'GRE' ),
			'_kb2ops_target_audience'    => array( 'concept' => 'audiencia', 'origin' => 'KB2Ops' ),
			'_bdc_es_responsible_team'   => array( 'concept' => 'equipe_responsavel', 'origin' => 'GRE' ),
			'_bdc_es_catalog_item'        => array( 'concept' => 'item_catalogo', 'origin' => 'GRE' ),
			'_kb2ops_service'             => array( 'concept' => 'servico', 'origin' => 'KB2Ops' ),
			'_bdc_es_affected_service'    => array( 'concept' => 'servico_afetado', 'origin' => 'GRE' ),
			'_kb2ops_technologies'        => array( 'concept' => 'tecnologias', 'origin' => 'KB2Ops' ),
			'_bdc_es_systems_involved'    => array( 'concept' => 'sistemas_envolvidos', 'origin' => 'GRE' ),
			'_kb2ops_knowledge_type'      => array( 'concept' => 'tipo_conhecimento', 'origin' => 'KB2Ops' ),
			'_kb2ops_keywords'            => array( 'concept' => 'keywords', 'origin' => 'KB2Ops' ),
			'_kb2ops_versions'            => array( 'concept' => 'versoes', 'origin' => 'KB2Ops' ),
		);
	}

	/** @return array<int,string> */
	private static function statuses(): array {
		return array( 'publish', 'draft', 'pending', 'private', 'future' );
	}

	public static function register(): void {
		if ( ! self::is_enabled() ) {
			return;
		}
		add_action( 'admin_notices', array( self::class, 'render_notice' ) );
		add_action( 'admin_post_' . self::ACTION, array( self::class, 'handle' ) );
	}

	private static function is_enabled(): bool {
		return defined( 'BDC_KB_CLASSIFICATION_PROFILE_BUILD' ) && true === BDC_KB_CLASSIFICATION_PROFILE_BUILD;
	}

	public static function render_notice(): void {
		if ( ! self::is_enabled() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) && is_scalar( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
		if ( Admin_Page::PAGE_SLUG !== $page ) {
			return;
		}
		echo '<div class="notice notice-info">';
		echo '<p><strong>' . esc_html__( 'SPEC-002 — Profiling classificatório read-only', 'bdc-knowledge-base' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Lê somente os metadados classificatórios históricos, gera agregados em JSON e não cria/edita posts, metas, taxonomias, options, transients ou tabelas.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<p><button type="submit" class="button button-primary">' . esc_html__( 'Profiling classificatório — gerar JSON', 'bdc-knowledge-base' ) . '</button></p>';
		echo '</form></div>';
	}

	public static function handle(): never {
		if ( ! self::is_enabled() ) {
			wp_die( esc_html__( 'Profiler desabilitado.', 'bdc-knowledge-base' ), '', array( 'response' => 404 ) );
		}
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			wp_die( esc_html__( 'Método HTTP não permitido.', 'bdc-knowledge-base' ), '', array( 'response' => 405 ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permissão insuficiente.', 'bdc-knowledge-base' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );
		$report   = self::run();
		$filename = 'bdc-kb-classification-profile-' . gmdate( 'Ymd-His' ) . '.json';
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/** @return array<string,mixed> */
	private static function run(): array {
		$started_at = microtime( true );
		$stores     = self::stores();
		$keys       = array_keys( $stores );
		$statuses   = self::statuses();
		$total_posts = self::count_posts( $statuses );
		$rows        = self::read_rows( $keys, $statuses );
		$working  = array();
		$per_post = array();
		foreach ( $stores as $key => $definition ) {
			$working[ $key ] = array(
				'concept' => $definition['concept'], 'origin' => $definition['origin'], 'row_count' => 0,
				'posts' => array(), 'nonempty_posts' => array(), 'row_count_by_post' => array(), 'value_types' => array(),
				'serialized_rows' => 0, 'json_rows' => 0, 'delimiters' => array( 'comma' => 0, 'semicolon' => 0, 'pipe' => 0, 'newline' => 0 ),
				'raw_fingerprints' => array(), 'normalized_frequency' => array(), 'normalized_sample' => array(), 'max_scalar_length' => 0,
			);
			$per_post[ $key ] = array();
		}
		foreach ( $rows as $row ) {
			$key = (string) $row->meta_key;
			if ( ! isset( $working[ $key ] ) ) { continue; }
			$post_id = (int) $row->post_id;
			$raw = (string) $row->meta_value;
			$decoded = maybe_unserialize( $raw );
			$type = gettype( $decoded );
			++$working[ $key ]['row_count'];
			$working[ $key ]['posts'][ $post_id ] = true;
			$working[ $key ]['row_count_by_post'][ $post_id ] = ( $working[ $key ]['row_count_by_post'][ $post_id ] ?? 0 ) + 1;
			$working[ $key ]['value_types'][ $type ] = ( $working[ $key ]['value_types'][ $type ] ?? 0 ) + 1;
			if ( is_serialized( $raw ) ) { ++$working[ $key ]['serialized_rows']; }
			if ( self::looks_like_json( $raw ) ) { ++$working[ $key ]['json_rows']; }
			foreach ( self::flatten_values( $decoded ) as $value ) {
				$scalar = trim( (string) $value );
				if ( '' === $scalar ) { continue; }
				$working[ $key ]['nonempty_posts'][ $post_id ] = true;
				$working[ $key ]['max_scalar_length'] = max( $working[ $key ]['max_scalar_length'], strlen( $scalar ) );
				$working[ $key ]['raw_fingerprints'][ hash( 'sha256', $scalar ) ] = true;
				self::count_delimiters( $working[ $key ]['delimiters'], $scalar );
				$normalized = self::normalize( $scalar );
				if ( '' === $normalized ) { continue; }
				$working[ $key ]['normalized_frequency'][ $normalized ] = ( $working[ $key ]['normalized_frequency'][ $normalized ] ?? 0 ) + 1;
				if ( ! isset( $working[ $key ]['normalized_sample'][ $normalized ] ) ) {
					$working[ $key ]['normalized_sample'][ $normalized ] = self::truncate( $scalar, self::SAMPLE_LIMIT );
				}
				$per_post[ $key ][ $post_id ][ $normalized ] = true;
			}
		}
		$report_stores = array();
		foreach ( $working as $key => $stats ) {
			$nonempty_posts = count( $stats['nonempty_posts'] );
			$posts_with_key = count( $stats['posts'] );
			$distinct_norm = count( $stats['normalized_frequency'] );
			$duplicate_posts = count( array_filter( $stats['row_count_by_post'], static fn ( int $count ): bool => $count > 1 ) );
			$multi_posts = 0;
			foreach ( $per_post[ $key ] as $tokens ) { if ( count( $tokens ) > 1 ) { ++$multi_posts; } }
			$frequency = $stats['normalized_frequency']; arsort( $frequency ); $top_values = array();
			foreach ( array_slice( $frequency, 0, self::TOP_VALUES, true ) as $normalized => $count ) {
				$top_values[] = array( 'value' => $stats['normalized_sample'][ $normalized ] ?? $normalized, 'normalized' => $normalized, 'count' => (int) $count );
			}
			$report_stores[ $key ] = array(
				'concept' => $stats['concept'], 'origin' => $stats['origin'], 'row_count' => (int) $stats['row_count'],
				'posts_with_key' => $posts_with_key, 'nonempty_posts' => $nonempty_posts, 'empty_posts' => max( 0, $posts_with_key - $nonempty_posts ),
				'coverage_percent' => $total_posts > 0 ? round( ( $nonempty_posts / $total_posts ) * 100, 2 ) : 0.0,
				'distinct_raw_count' => count( $stats['raw_fingerprints'] ), 'distinct_normalized' => $distinct_norm,
				'cardinality_ratio' => $nonempty_posts > 0 ? round( $distinct_norm / $nonempty_posts, 4 ) : 0.0,
				'multi_value_posts' => $multi_posts, 'duplicate_meta_posts' => $duplicate_posts, 'max_scalar_length' => (int) $stats['max_scalar_length'],
				'value_types' => $stats['value_types'], 'serialized_rows' => (int) $stats['serialized_rows'], 'json_rows' => (int) $stats['json_rows'],
				'delimiter_hints' => $stats['delimiters'], 'top_values' => $top_values,
			);
		}
		return array(
			'schema_version' => self::SCHEMA_VERSION, 'mode' => 'temporary_classification_profile_read_only', 'generated_at' => gmdate( 'c' ),
			'environment' => array( 'wordpress' => (string) get_bloginfo( 'version' ), 'php' => PHP_VERSION, 'plugin' => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : 'unknown', 'multisite' => is_multisite() ),
			'scope' => array( 'post_type' => 'post', 'post_status' => $statuses, 'total_posts' => $total_posts, 'meta_rows' => count( $rows ) ),
			'safety' => array( 'writes_performed' => false, 'persistent_report' => false, 'requires_capability' => 'manage_options', 'reads_editorial_content' => false, 'uses_read_only_sql' => true ),
			'stores' => $report_stores,
			'comparisons' => array(
				'audience_gre_vs_kb2ops' => self::compare_stores( '_bdc_es_target_audience', '_kb2ops_target_audience', $total_posts, $per_post ),
				'service_vs_affected' => self::compare_stores( '_kb2ops_service', '_bdc_es_affected_service', $total_posts, $per_post ),
				'technologies_vs_systems' => self::compare_stores( '_kb2ops_technologies', '_bdc_es_systems_involved', $total_posts, $per_post ),
			),
			'decision' => array( 'primitive_decision' => 'NOT_MADE', 'note' => 'Relatório produz sinais de profiling; Taxonomy vs Post Meta exige revisão humana e não é decidido automaticamente.' ),
			'summary' => array( 'status' => 'PASS', 'duration_ms' => (int) round( ( microtime( true ) - $started_at ) * 1000 ) ),
			'limitations' => array( 'Normalização é apenas analítica e não define contrato de persistência.', 'Overlap entre stores não autoriza merge sem análise semântica.', 'Este build de profiling é temporário e deve ser substituído pela baseline limpa após a coleta.' ),
		);
	}

	/** @param array<int,string> $statuses */
	private static function count_posts( array $statuses ): int {
		global $wpdb;
		$status_placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );
		$sql = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ({$status_placeholders})";
		$args = array_merge( array( 'post' ), $statuses );
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/** @param array<int,string> $keys @param array<int,string> $statuses @return array<int,object> */
	private static function read_rows( array $keys, array $statuses ): array {
		global $wpdb;
		$key_placeholders = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );
		$status_placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );
		$sql = "SELECT pm.post_id, pm.meta_key, pm.meta_value FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status IN ({$status_placeholders}) AND pm.meta_key IN ({$key_placeholders})";
		$args = array_merge( array( 'post' ), $statuses, $keys );
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return is_array( $rows ) ? $rows : array();
	}

	/** @return array<int,string|int|float|bool> */
	private static function flatten_values( mixed $value ): array {
		if ( is_array( $value ) ) { $result = array(); foreach ( $value as $item ) { $result = array_merge( $result, self::flatten_values( $item ) ); } return $result; }
		if ( is_object( $value ) ) { return self::flatten_values( get_object_vars( $value ) ); }
		if ( is_scalar( $value ) ) { return array( $value ); }
		return array();
	}

	private static function normalize( string $value ): string {
		$value = html_entity_decode( wp_strip_all_tags( $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$value = trim( preg_replace( '/\s+/u', ' ', $value ) ?? $value );
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
	}

	private static function looks_like_json( string $value ): bool {
		$trimmed = trim( $value );
		if ( '' === $trimmed || ! in_array( $trimmed[0], array( '[', '{' ), true ) ) { return false; }
		json_decode( $trimmed, true ); return JSON_ERROR_NONE === json_last_error();
	}

	/** @param array<string,int> $counters */
	private static function count_delimiters( array &$counters, string $value ): void {
		if ( str_contains( $value, ',' ) ) { ++$counters['comma']; }
		if ( str_contains( $value, ';' ) ) { ++$counters['semicolon']; }
		if ( str_contains( $value, '|' ) ) { ++$counters['pipe']; }
		if ( str_contains( $value, "\n" ) || str_contains( $value, "\r" ) ) { ++$counters['newline']; }
	}

	private static function truncate( string $value, int $max ): string {
		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) { return mb_strlen( $value, 'UTF-8' ) > $max ? mb_substr( $value, 0, $max, 'UTF-8' ) . '…' : $value; }
		return strlen( $value ) > $max ? substr( $value, 0, $max ) . '…' : $value;
	}

	/** @param array<string,array<int,array<string,bool>>> $per_post @return array<string,mixed> */
	private static function compare_stores( string $left, string $right, int $total_posts, array $per_post ): array {
		$left_posts = $per_post[ $left ] ?? array(); $right_posts = $per_post[ $right ] ?? array();
		$ids = array_unique( array_merge( array_keys( $left_posts ), array_keys( $right_posts ) ) );
		$only_left = 0; $only_right = 0; $both = 0; $equal = 0; $conflict = 0; $left_global = array(); $right_global = array();
		foreach ( $left_posts as $tokens ) { foreach ( array_keys( $tokens ) as $token ) { $left_global[ $token ] = true; } }
		foreach ( $right_posts as $tokens ) { foreach ( array_keys( $tokens ) as $token ) { $right_global[ $token ] = true; } }
		foreach ( $ids as $post_id ) {
			$has_left = isset( $left_posts[ $post_id ] ) && ! empty( $left_posts[ $post_id ] );
			$has_right = isset( $right_posts[ $post_id ] ) && ! empty( $right_posts[ $post_id ] );
			if ( $has_left && ! $has_right ) { ++$only_left; continue; }
			if ( ! $has_left && $has_right ) { ++$only_right; continue; }
			if ( $has_left && $has_right ) { ++$both; $l = array_keys( $left_posts[ $post_id ] ); $r = array_keys( $right_posts[ $post_id ] ); sort( $l ); sort( $r ); if ( $l === $r ) { ++$equal; } else { ++$conflict; } }
		}
		$intersection = count( array_intersect_key( $left_global, $right_global ) ); $union = count( $left_global + $right_global );
		return array( 'left_key' => $left, 'right_key' => $right, 'only_left_posts' => $only_left, 'only_right_posts' => $only_right, 'both_posts' => $both, 'equal_normalized_sets_posts' => $equal, 'conflicting_sets_posts' => $conflict, 'neither_posts' => max( 0, $total_posts - $only_left - $only_right - $both ), 'global_distinct_left' => count( $left_global ), 'global_distinct_right' => count( $right_global ), 'global_intersection' => $intersection, 'global_jaccard' => $union > 0 ? round( $intersection / $union, 4 ) : 0.0, 'merge_authorized' => false );
	}
}
