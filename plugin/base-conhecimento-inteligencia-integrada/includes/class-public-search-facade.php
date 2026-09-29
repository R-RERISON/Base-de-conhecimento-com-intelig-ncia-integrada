<?php
/**
 * Public authorization facade over the frozen SPEC-005 lexical engine.
 *
 * This class does not implement a second ranker. It reuses the canonical
 * normalizer, Projection repository and Lexical_Ranker, while enforcing a
 * frontend visibility policy before ranking/results are returned.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Public_Search_Facade {

	public const VERSION = 'public-search-facade-v1.0.0';
	public const AJAX_ACTION = 'bdc_kb_public_search';
	public const NONCE_ACTION = 'bdc_kb_public_search';
	public const DEFAULT_LIMIT = 8;
	public const MAX_LIMIT = 20;
	public const MIN_QUERY_LENGTH = 2;
	public const MAX_QUERY_LENGTH = 160;

	private const RATE_LIMIT = 60;
	private const RATE_WINDOW = MINUTE_IN_SECONDS;

	public static function register(): void {
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( self::class, 'ajax_search' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( self::class, 'ajax_search' ) );
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function search( mixed $query_value, int $limit = self::DEFAULT_LIMIT ): array {
		$limit = max( 1, min( self::MAX_LIMIT, $limit ) );

		$query = Search_Query_Normalizer::normalize( $query_value );
		if ( $query instanceof \WP_Error ) {
			return self::invalid_query_response( $query );
		}

		if ( empty( $query['tokens'] ) ) {
			return self::invalid_query_response(
				new \WP_Error( 'search_empty_query', 'Informe ao menos um termo para pesquisa.', array( 'status' => 400 ) ),
				$query
			);
		}

		$original = trim( (string) ( $query['original'] ?? '' ) );
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $original ) : strlen( $original );
		if ( $length < self::MIN_QUERY_LENGTH ) {
			return self::invalid_query_response(
				new \WP_Error( 'search_query_too_short', 'Informe ao menos dois caracteres.', array( 'status' => 400 ) ),
				$query
			);
		}
		if ( $length > self::MAX_QUERY_LENGTH ) {
			return self::invalid_query_response(
				new \WP_Error( 'search_query_too_long', 'A consulta excede o limite permitido.', array( 'status' => 400 ) ),
				$query
			);
		}

		if ( ! Search_Service::is_enabled() ) {
			return self::wordpress_fallback( $query, $limit, 'search_module_disabled' );
		}

		if ( ! Search_Projection_Repository::is_ready() ) {
			return self::wordpress_fallback( $query, $limit, 'projection_not_ready' );
		}

		$candidates = Search_Projection_Repository::retrieve_candidates(
			(array) $query['tokens'],
			Search_Projection_Repository::CANDIDATE_CAP
		);

		if ( $candidates instanceof \WP_Error ) {
			return self::wordpress_fallback( $query, $limit, $candidates->get_error_code() );
		}

		$authorized = self::authorized_documents( $candidates );
		$ranked = Lexical_Ranker::rank( $query, $authorized, $limit );

		if ( empty( $ranked ) ) {
			return self::response( 'zero_results', 'projection_like', $query, array(), array() );
		}

		$results = array();
		$degraded = false;

		foreach ( $ranked as $row ) {
			$post_id = (int) ( $row['post_id'] ?? 0 );
			$post = $post_id > 0 ? get_post( $post_id ) : null;
			if ( ! $post instanceof \WP_Post || ! self::is_authorized_post( $post ) ) {
				continue;
			}

			$document_state = (string) ( $row['document_state'] ?? 'degraded' );
			$degraded = $degraded || 'degraded' === $document_state;

			$results[] = array(
				'post_id' => $post_id,
				'title' => (string) $post->post_title,
				'official_url' => self::official_url( $post_id ),
				'rank' => count( $results ) + 1,
				'score' => (float) ( $row['score'] ?? 0.0 ),
				'matched_signals' => array_values( array_map( 'strval', (array) ( $row['matched_signals'] ?? array() ) ) ),
				'source_kind' => (string) ( $row['source_kind'] ?? 'unknown' ),
				'document_state' => $document_state,
				'visibility_revalidated' => true,
			);
		}

		if ( empty( $results ) ) {
			return self::response( 'zero_results', 'projection_like', $query, array(), array() );
		}

		return self::response(
			$degraded ? 'degraded' : 'success',
			'projection_like',
			$query,
			$results,
			array()
		);
	}

	public static function ajax_search(): void {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! self::rate_limit_pass() ) {
			wp_send_json_error(
				array(
					'message' => 'Muitas consultas em sequência. Aguarde alguns instantes.',
					'state' => 'rate_limited',
				),
				429
			);
		}

		$query = isset( $_POST['query'] ) && is_scalar( $_POST['query'] )
			? sanitize_text_field( wp_unslash( (string) $_POST['query'] ) )
			: '';
		$query = trim( $query );

		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $query ) : strlen( $query );
		if ( $length < self::MIN_QUERY_LENGTH ) {
			wp_send_json_success(
				array(
					'query' => $query,
					'count' => 0,
					'results' => array(),
					'state' => 'min_chars',
				)
			);
		}
		if ( $length > self::MAX_QUERY_LENGTH ) {
			wp_send_json_error(
				array(
					'message' => 'A consulta excede o limite permitido.',
					'state' => 'invalid_query',
				),
				400
			);
		}

		$search = self::search( $query, self::DEFAULT_LIMIT );
		$rows = array();
		$preview = ! empty( $_POST['preview'] ) && current_user_can( 'manage_options' );

		foreach ( (array) ( $search['results'] ?? array() ) as $result ) {
			$post_id = (int) ( $result['post_id'] ?? 0 );
			$post = $post_id > 0 ? get_post( $post_id ) : null;
			if ( ! $post instanceof \WP_Post || ! self::is_authorized_post( $post ) ) {
				continue;
			}

			$categories = get_the_category( $post_id );
			$category = ! empty( $categories ) && is_object( $categories[0] ) ? (string) $categories[0]->name : '';
			$raw_excerpt = '' !== trim( (string) $post->post_excerpt ) ? (string) $post->post_excerpt : (string) $post->post_content;
			$url = $preview && class_exists( Public_Experience::class )
				? Public_Experience::article_preview_url( $post_id )
				: (string) ( $result['official_url'] ?? get_permalink( $post_id ) );

			$rows[] = array(
				'postId' => $post_id,
				'title' => (string) $post->post_title,
				'category' => $category,
				'excerpt' => wp_trim_words( wp_strip_all_tags( strip_shortcodes( $raw_excerpt ) ), 22, '…' ),
				'url' => $url,
				'rank' => (int) ( $result['rank'] ?? 0 ),
			);
		}

		$state = (string) ( $search['state'] ?? 'technical_error' );
		wp_send_json_success(
			array(
				'query' => $query,
				'count' => count( $rows ),
				'results' => $rows,
				'state' => empty( $rows ) && 'technical_error' !== $state ? 'empty' : $state,
				'retrievalMode' => (string) ( $search['retrieval_mode'] ?? 'none' ),
			)
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $documents
	 * @return array<int,array<string,mixed>>
	 */
	private static function authorized_documents( array $documents ): array {
		$authorized = array();
		foreach ( $documents as $document ) {
			$post_id = (int) ( $document['post_id'] ?? 0 );
			$post = $post_id > 0 ? get_post( $post_id ) : null;
			if ( $post instanceof \WP_Post && self::is_authorized_post( $post ) ) {
				$authorized[] = $document;
			}
		}
		return $authorized;
	}

	private static function is_authorized_post( \WP_Post $post ): bool {
		if ( Meta_Contract::POST_TYPE !== (string) $post->post_type ) {
			return false;
		}

		$status = (string) $post->post_status;
		if ( ! in_array( $status, array( 'publish', 'private' ), true ) ) {
			return false;
		}
		if ( '' !== (string) $post->post_password || post_password_required( $post ) ) {
			return false;
		}

		$allowed = false;
		if ( 'publish' === $status ) {
			$allowed = is_post_publicly_viewable( $post ) || current_user_can( 'read_post', (int) $post->ID );
		} elseif ( 'private' === $status ) {
			$allowed = current_user_can( 'read_post', (int) $post->ID );
		}

		/**
		 * Filters the final public Search authorization decision.
		 *
		 * Plugins may tighten policy; widening it must still respect the hard
		 * status/password guards above.
		 */
		return (bool) apply_filters( 'bdc_kb_public_search_post_allowed', $allowed, $post );
	}

	/**
	 * @param array<string,mixed> $query
	 * @return array<string,mixed>
	 */
	private static function wordpress_fallback( array $query, int $limit, string $reason ): array {
		$statuses = array( 'publish' );
		$type = get_post_type_object( Meta_Contract::POST_TYPE );
		if ( is_object( $type ) && isset( $type->cap->read_private_posts ) && current_user_can( (string) $type->cap->read_private_posts ) ) {
			$statuses[] = 'private';
		}

		$wp_query = new \WP_Query(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => $statuses,
				'posts_per_page' => $limit,
				'fields' => 'ids',
				's' => (string) ( $query['original'] ?? '' ),
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);

		$results = array();
		foreach ( array_map( 'intval', (array) $wp_query->posts ) as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post instanceof \WP_Post || ! self::is_authorized_post( $post ) ) {
				continue;
			}
			$results[] = array(
				'post_id' => $post_id,
				'title' => (string) $post->post_title,
				'official_url' => self::official_url( $post_id ),
				'rank' => count( $results ) + 1,
				'score' => null,
				'matched_signals' => array( 'wordpress_native_relevance' ),
				'source_kind' => 'wordpress_native',
				'document_state' => 'degraded',
				'visibility_revalidated' => true,
			);
		}
		wp_reset_postdata();

		return self::response(
			'degraded',
			'wordpress_fallback',
			$query,
			$results,
			array( 'degraded_reason' => sanitize_key( $reason ) )
		);
	}

	private static function official_url( int $post_id ): string {
		$url = get_permalink( $post_id );
		return is_string( $url ) ? $url : '';
	}

	/**
	 * @param array<string,mixed> $query
	 * @param array<int,array<string,mixed>> $results
	 * @param array<string,mixed> $extra
	 * @return array<string,mixed>
	 */
	private static function response( string $state, string $mode, array $query, array $results, array $extra ): array {
		return array_merge(
			array(
				'state' => $state,
				'retrieval_mode' => $mode,
				'query' => array(
					'original' => (string) ( $query['original'] ?? '' ),
					'normalized' => (string) ( $query['normalized'] ?? '' ),
					'tokens' => array_values( array_map( 'strval', (array) ( $query['tokens'] ?? array() ) ) ),
				),
				'versions' => array(
					'facade' => self::VERSION,
					'normalizer' => Search_Query_Normalizer::VERSION,
					'document' => Search_Document_Builder::VERSION,
					'algorithm' => Lexical_Ranker::VERSION,
					'result' => Search_Service::RESULT_VERSION,
				),
				'count' => count( $results ),
				'results' => $results,
			),
			$extra
		);
	}

	private static function invalid_query_response( \WP_Error $error, ?array $query = null ): array {
		$response = array(
			'state' => 'invalid_query',
			'retrieval_mode' => 'none',
			'error_code' => sanitize_key( $error->get_error_code() ),
			'message' => $error->get_error_message(),
			'versions' => array(
				'facade' => self::VERSION,
				'normalizer' => Search_Query_Normalizer::VERSION,
				'document' => Search_Document_Builder::VERSION,
				'algorithm' => Lexical_Ranker::VERSION,
				'result' => Search_Service::RESULT_VERSION,
			),
			'count' => 0,
			'results' => array(),
		);
		if ( null !== $query ) {
			$response['query'] = array(
				'original' => (string) ( $query['original'] ?? '' ),
				'normalized' => (string) ( $query['normalized'] ?? '' ),
				'tokens' => array_values( array_map( 'strval', (array) ( $query['tokens'] ?? array() ) ) ),
			);
		}
		return $response;
	}

	private static function rate_limit_pass(): bool {
		$remote = isset( $_SERVER['REMOTE_ADDR'] ) && is_scalar( $_SERVER['REMOTE_ADDR'] )
			? (string) $_SERVER['REMOTE_ADDR']
			: 'unknown';
		$fingerprint = hash_hmac( 'sha256', $remote, wp_salt( 'nonce' ) );
		$key = 'bdc_kb_ps_rl_' . substr( $fingerprint, 0, 32 );
		$count = absint( get_transient( $key ) );
		if ( $count >= self::RATE_LIMIT ) {
			return false;
		}
		set_transient( $key, $count + 1, self::RATE_WINDOW );
		return true;
	}
}