<?php
/**
 * Profiler temporário read-only da SPEC-003 — Review & Governança.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coleta evidência agregada dos stores históricos de review/governança.
 *
 * Ferramenta temporária de homologação. Não pertence ao runtime final.
 */
final class Review_Profiler {

	public const ACTION = 'bdc_kb_review_profile';

	private const NONCE_ACTION   = 'bdc_kb_review_profile_v1';
	private const NONCE_FIELD    = 'bdc_kb_review_profile_nonce';
	private const SCHEMA_VERSION = '1.0.0';
	private const SAMPLE_LIMIT   = 120;

	/** @return array<string,array{concept:string,kind:string}> */
	private static function stores(): array {
		return array(
			'_kb2ops_review_state'   => array( 'concept' => 'review_state', 'kind' => 'enum' ),
			'_kb2ops_review_notes'   => array( 'concept' => 'review_notes', 'kind' => 'sensitive_text' ),
			'_kb2ops_reviewed_at'    => array( 'concept' => 'reviewed_at', 'kind' => 'timestamp' ),
			'_kb2ops_reviewed_by'    => array( 'concept' => 'reviewed_by', 'kind' => 'actor_ref' ),
			'_kb2ops_include_ai'     => array( 'concept' => 'include_ai', 'kind' => 'boolean_like' ),
			'_kb2ops_review_history' => array( 'concept' => 'review_history', 'kind' => 'history' ),
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
		return defined( 'BDC_KB_REVIEW_PROFILE_BUILD' ) && true === BDC_KB_REVIEW_PROFILE_BUILD;
	}

	public static function render_notice(): void {
		if ( ! self::is_enabled() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page = isset( $_GET['page'] ) && is_scalar( $_GET['page'] )
			? sanitize_key( wp_unslash( (string) $_GET['page'] ) )
			: '';

		if ( Admin_Page::PAGE_SLUG !== $page ) {
			return;
		}

		echo '<div class="notice notice-info">';
		echo '<p><strong>' . esc_html__( 'SPEC-003 — Profiling Review & Governança (read-only)', 'bdc-knowledge-base' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Lê somente seis stores históricos de governança, produz agregados sanitizados em JSON e não cria/edita posts, metas, taxonomias, options, usuários, transients ou tabelas.', 'bdc-knowledge-base' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">';
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );
		echo '<p><button type="submit" class="button button-primary">' . esc_html__( 'Profiling Review/Governança — gerar JSON', 'bdc-knowledge-base' ) . '</button></p>';
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
		$filename = 'bdc-kb-review-profile-' . gmdate( 'Ymd-His' ) . '.json';

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
		$statuses   = self::statuses();
		$rows       = self::read_rows( array_keys( $stores ), $statuses );
		$total_posts = self::count_posts( $statuses );

		$stats = array();
		foreach ( $stores as $key => $definition ) {
			$stats[ $key ] = array(
				'concept'           => $definition['concept'],
				'kind'              => $definition['kind'],
				'row_count'         => 0,
				'posts'             => array(),
				'nonempty_posts'    => array(),
				'value_types'       => array(),
				'serialized_rows'   => 0,
				'max_scalar_length' => 0,
			);
		}

		$state_distribution   = array();
		$state_by_post_status = array();
		$notes                = array( 'nonempty_rows' => 0, 'total_bytes' => 0, 'max_bytes' => 0 );
		$timestamps           = array( 'parseable' => 0, 'unparseable' => 0, 'earliest' => null, 'latest' => null );
		$actors               = array( 'positive_integer_refs' => 0, 'invalid_refs' => 0, 'existing_user_refs' => 0, 'missing_user_refs' => 0, 'distinct_actor_ids' => array() );
		$include_ai           = array( 'true' => 0, 'false' => 0, 'empty' => 0, 'other' => 0 );
		$history              = array(
			'array_rows'               => 0,
			'non_array_rows'           => 0,
			'event_count_total'        => 0,
			'event_count_max_per_post' => 0,
			'event_keys'               => array(),
			'event_value_types'        => array(),
			'candidate_state_values'   => array(),
		);

		$post_bundle = array();

		foreach ( $rows as $row ) {
			$key         = (string) $row->meta_key;
			$post_id     = (int) $row->post_id;
			$post_status = (string) $row->post_status;
			$raw         = (string) $row->meta_value;
			$decoded     = maybe_unserialize( $raw );

			if ( ! isset( $stats[ $key ] ) ) {
				continue;
			}

			++$stats[ $key ]['row_count'];
			$stats[ $key ]['posts'][ $post_id ] = true;
			$type = gettype( $decoded );
			$stats[ $key ]['value_types'][ $type ] = ( $stats[ $key ]['value_types'][ $type ] ?? 0 ) + 1;
			if ( is_serialized( $raw ) ) {
				++$stats[ $key ]['serialized_rows'];
			}

			$trimmed = trim( $raw );
			if ( '' !== $trimmed ) {
				$stats[ $key ]['nonempty_posts'][ $post_id ] = true;
				$stats[ $key ]['max_scalar_length'] = max( $stats[ $key ]['max_scalar_length'], strlen( $raw ) );
			}

			$post_bundle[ $post_id ]['post_status'] = $post_status;
			$post_bundle[ $post_id ][ $key ] = $decoded;

			switch ( $key ) {
				case '_kb2ops_review_state':
					$state = self::normalize_state( $decoded );
					if ( '' !== $state ) {
						$state_distribution[ $state ] = ( $state_distribution[ $state ] ?? 0 ) + 1;
						if ( ! isset( $state_by_post_status[ $state ] ) ) {
							$state_by_post_status[ $state ] = array();
						}
						$state_by_post_status[ $state ][ $post_status ] = ( $state_by_post_status[ $state ][ $post_status ] ?? 0 ) + 1;
					}
					break;

				case '_kb2ops_review_notes':
					if ( '' !== $trimmed ) {
						++$notes['nonempty_rows'];
						$bytes = strlen( $raw );
						$notes['total_bytes'] += $bytes;
						$notes['max_bytes'] = max( $notes['max_bytes'], $bytes );
					}
					break;

				case '_kb2ops_reviewed_at':
					self::collect_timestamp( $decoded, $timestamps );
					break;

				case '_kb2ops_reviewed_by':
					self::collect_actor( $decoded, $actors );
					break;

				case '_kb2ops_include_ai':
					$bucket = self::boolean_bucket( $decoded );
					++$include_ai[ $bucket ];
					break;

				case '_kb2ops_review_history':
					self::collect_history( $decoded, $history );
					break;
			}
		}

		$stores_report = array();
		foreach ( $stats as $key => $store ) {
			$posts_with_key = count( $store['posts'] );
			$nonempty_posts = count( $store['nonempty_posts'] );
			$stores_report[ $key ] = array(
				'concept'           => $store['concept'],
				'kind'              => $store['kind'],
				'row_count'         => (int) $store['row_count'],
				'posts_with_key'    => $posts_with_key,
				'nonempty_posts'    => $nonempty_posts,
				'empty_posts'       => max( 0, $posts_with_key - $nonempty_posts ),
				'coverage_percent'  => $total_posts > 0 ? round( ( $nonempty_posts / $total_posts ) * 100, 2 ) : 0.0,
				'value_types'       => $store['value_types'],
				'serialized_rows'   => (int) $store['serialized_rows'],
				'max_scalar_length' => (int) $store['max_scalar_length'],
			);
		}

		ksort( $state_distribution );
		ksort( $state_by_post_status );
		ksort( $history['event_keys'] );
		ksort( $history['event_value_types'] );
		ksort( $history['candidate_state_values'] );

		$actor_distinct = count( $actors['distinct_actor_ids'] );
		unset( $actors['distinct_actor_ids'] );
		$actors['distinct_actor_count'] = $actor_distinct;

		$coherence = self::coherence_report( $post_bundle );

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'mode'           => 'temporary_review_governance_profile_read_only',
			'generated_at'   => gmdate( 'c' ),
			'environment'    => array(
				'wordpress' => (string) get_bloginfo( 'version' ),
				'php'       => PHP_VERSION,
				'plugin'    => defined( 'BDC_KB_VERSION' ) ? BDC_KB_VERSION : 'unknown',
				'multisite' => is_multisite(),
			),
			'scope' => array(
				'post_type'   => 'post',
				'post_status' => $statuses,
				'total_posts' => $total_posts,
				'meta_rows'   => count( $rows ),
			),
			'safety' => array(
				'writes_performed'        => false,
				'persistent_report'       => false,
				'requires_capability'     => 'manage_options',
				'reads_editorial_content' => false,
				'exports_review_notes'    => false,
				'exports_user_ids'        => false,
				'uses_read_only_sql'      => true,
			),
			'stores' => $stores_report,
			'review_state' => array(
				'distribution'        => $state_distribution,
				'by_post_status'      => $state_by_post_status,
				'known_legacy_states' => array( 'unreviewed', 'in_review', 'approved', 'excluded' ),
				'contract_authorized' => false,
			),
			'notes'      => $notes + array( 'content_exported' => false ),
			'timestamps' => $timestamps,
			'actors'     => $actors,
			'include_ai' => $include_ai + array( 'belongs_to_future_ai_contract' => true ),
			'history'    => $history,
			'coherence'  => $coherence,
			'decision'   => array(
				'domain_contract'      => 'NOT_MADE',
				'migration_authorized' => false,
				'note'                 => 'O profiler mede o legado; estados, primitivas e migração exigem revisão humana no Gate R-010.',
			),
			'summary' => array(
				'status'      => 'PASS',
				'duration_ms' => (int) round( ( microtime( true ) - $started_at ) * 1000 ),
			),
			'limitations' => array(
				'O relatório não lê post_title, post_content ou _elementor_data.',
				'Notas humanas nunca são exportadas; apenas tamanho/cobertura são medidos.',
				'IDs de usuários não são exportados; apenas validade e cardinalidade agregadas.',
				'Os quatro estados históricos são referência, não contrato canônico.',
				'AI Ready não é criado nem inferido nesta SPEC.',
				'Este build é temporário e deve ser substituído por package limpo após R-001.',
			),
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
		$key_placeholders    = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );
		$status_placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );
		$sql = "SELECT pm.post_id, pm.meta_key, pm.meta_value, p.post_status FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status IN ({$status_placeholders}) AND pm.meta_key IN ({$key_placeholders}) ORDER BY pm.post_id ASC, pm.meta_key ASC";
		$args = array_merge( array( 'post' ), $statuses, $keys );
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return is_array( $rows ) ? $rows : array();
	}

	private static function normalize_state( mixed $value ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$value = sanitize_key( trim( (string) $value ) );
		return self::truncate( $value, self::SAMPLE_LIMIT );
	}

	/** @param array<string,mixed> $stats */
	private static function collect_timestamp( mixed $value, array &$stats ): void {
		if ( ! is_scalar( $value ) || '' === trim( (string) $value ) ) {
			return;
		}

		$timestamp = strtotime( (string) $value );
		if ( false === $timestamp ) {
			++$stats['unparseable'];
			return;
		}

		++$stats['parseable'];
		$iso = gmdate( 'c', $timestamp );
		if ( null === $stats['earliest'] || $timestamp < strtotime( (string) $stats['earliest'] ) ) {
			$stats['earliest'] = $iso;
		}
		if ( null === $stats['latest'] || $timestamp > strtotime( (string) $stats['latest'] ) ) {
			$stats['latest'] = $iso;
		}
	}

	/** @param array<string,mixed> $stats */
	private static function collect_actor( mixed $value, array &$stats ): void {
		if ( ! is_scalar( $value ) || ! preg_match( '/^\d+$/', trim( (string) $value ) ) ) {
			++$stats['invalid_refs'];
			return;
		}

		$user_id = (int) $value;
		if ( $user_id <= 0 ) {
			++$stats['invalid_refs'];
			return;
		}

		++$stats['positive_integer_refs'];
		$stats['distinct_actor_ids'][ $user_id ] = true;
		if ( false !== get_userdata( $user_id ) ) {
			++$stats['existing_user_refs'];
		} else {
			++$stats['missing_user_refs'];
		}
	}

	private static function boolean_bucket( mixed $value ): string {
		if ( null === $value ) {
			return 'empty';
		}
		if ( ! is_scalar( $value ) ) {
			return 'other';
		}
		$scalar = trim( (string) $value );
		if ( '' === $scalar ) {
			return 'empty';
		}
		$lower = strtolower( $scalar );

		if ( true === $value || 1 === $value || '1' === $scalar || 'true' === $lower || 'yes' === $lower || 'on' === $lower ) {
			return 'true';
		}

		if ( false === $value || 0 === $value || '0' === $scalar || 'false' === $lower || 'no' === $lower || 'off' === $lower ) {
			return 'false';
		}

		return 'other';
	}

	/** @param array<string,mixed> $stats */
	private static function collect_history( mixed $value, array &$stats ): void {
		if ( ! is_array( $value ) ) {
			++$stats['non_array_rows'];
			return;
		}

		++$stats['array_rows'];
		$event_count = 0;
		foreach ( $value as $event ) {
			if ( ! is_array( $event ) ) {
				continue;
			}
			++$event_count;
			foreach ( $event as $event_key => $event_value ) {
				$key = sanitize_key( (string) $event_key );
				if ( '' === $key ) {
					continue;
				}
				$stats['event_keys'][ $key ] = ( $stats['event_keys'][ $key ] ?? 0 ) + 1;
				$type_key = $key . ':' . gettype( $event_value );
				$stats['event_value_types'][ $type_key ] = ( $stats['event_value_types'][ $type_key ] ?? 0 ) + 1;
				if ( in_array( $key, array( 'state', 'from', 'to', 'review_state', 'previous_state', 'new_state' ), true ) ) {
					$state = self::normalize_state( $event_value );
					if ( '' !== $state ) {
						$stats['candidate_state_values'][ $state ] = ( $stats['candidate_state_values'][ $state ] ?? 0 ) + 1;
					}
				}
			}
		}

		$stats['event_count_total'] += $event_count;
		$stats['event_count_max_per_post'] = max( $stats['event_count_max_per_post'], $event_count );
	}

	/** @param array<int,array<string,mixed>> $bundle @return array<string,mixed> */
	private static function coherence_report( array $bundle ): array {
		$result = array(
			'posts_with_any_review_data' => count( $bundle ),
			'state_without_timestamp' => 0,
			'state_without_actor' => 0,
			'timestamp_without_state' => 0,
			'actor_without_state' => 0,
			'history_without_state' => 0,
			'approved_not_published' => 0,
			'published_with_other_review_data_but_without_state' => 0,
		);

		foreach ( $bundle as $post ) {
			$state = self::normalize_state( $post['_kb2ops_review_state'] ?? '' );
			$has_state = '' !== $state;
			$timestamp_value = $post['_kb2ops_reviewed_at'] ?? null;
			$actor_value = $post['_kb2ops_reviewed_by'] ?? null;
			$has_timestamp = is_scalar( $timestamp_value ) && '' !== trim( (string) $timestamp_value );
			$has_actor = is_scalar( $actor_value ) && 1 === preg_match( '/^[1-9]\d*$/', trim( (string) $actor_value ) );
			$has_history = isset( $post['_kb2ops_review_history'] ) && is_array( $post['_kb2ops_review_history'] ) && ! empty( $post['_kb2ops_review_history'] );
			$post_status = (string) ( $post['post_status'] ?? '' );

			if ( $has_state && ! $has_timestamp ) { ++$result['state_without_timestamp']; }
			if ( $has_state && ! $has_actor ) { ++$result['state_without_actor']; }
			if ( ! $has_state && $has_timestamp ) { ++$result['timestamp_without_state']; }
			if ( ! $has_state && $has_actor ) { ++$result['actor_without_state']; }
			if ( ! $has_state && $has_history ) { ++$result['history_without_state']; }
			if ( 'approved' === $state && 'publish' !== $post_status ) { ++$result['approved_not_published']; }
			if ( 'publish' === $post_status && ! $has_state ) { ++$result['published_with_other_review_data_but_without_state']; }
		}

		return $result;
	}

	private static function truncate( string $value, int $max ): string {
		if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ) {
			return mb_strlen( $value, 'UTF-8' ) > $max ? mb_substr( $value, 0, $max, 'UTF-8' ) . '…' : $value;
		}
		return strlen( $value ) > $max ? substr( $value, 0, $max ) . '…' : $value;
	}
}
