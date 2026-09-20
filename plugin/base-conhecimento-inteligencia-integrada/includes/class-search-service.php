<?php
/**
 * Serviço lexical canônico da Search SPEC-005.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Service {

	public const RESULT_VERSION = 'search-result-v1.0.0';
	public const DEFAULT_LIMIT = 20;
	public const MAX_LIMIT = 50;

	/** @var array<int,string> */
	private const ALLOWED_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	public static function is_enabled(): bool {
		$enabled = ! defined( 'BDC_KB_SEARCH_ENABLED' ) || true === (bool) BDC_KB_SEARCH_ENABLED;
		return (bool) apply_filters( 'bdc_kb_search_enabled', $enabled );
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function search( mixed $query_value, int $limit = self::DEFAULT_LIMIT ): array {
		$limit = max( 1, min( self::MAX_LIMIT, $limit ) );

		if ( ! current_user_can( 'edit_posts' ) ) {
			return self::error_response( 'search_forbidden', 'Permissão insuficiente para pesquisar a Base de Conhecimento.' );
		}

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

		if ( ! self::is_enabled() ) {
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
			return self::response(
				'zero_results',
				'projection_like',
				$query,
				array(),
				array()
			);
		}

		$results = array();
		$degraded = false;

		foreach ( $ranked as $row ) {
			$post_id = (int) $row['post_id'];
			$post = get_post( $post_id );
			if ( ! is_object( $post ) || ! self::is_authorized_post( $post_id, $post ) ) {
				continue;
			}

			$document_state = (string) ( $row['document_state'] ?? 'degraded' );
			$degraded = $degraded || 'degraded' === $document_state;

			$results[] = array(
				'post_id' => $post_id,
				'title' => (string) $post->post_title,
				'official_url' => self::official_url( $post_id ),
				'rank' => count( $results ) + 1,
				'score' => (float) $row['score'],
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

	/**
	 * @param array<string,mixed> $query
	 * @return array<string,mixed>
	 */
	private static function wordpress_fallback( array $query, int $limit, string $reason ): array {
		$wp_query = new \WP_Query(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => self::ALLOWED_STATUSES,
				'perm' => 'editable',
				'posts_per_page' => $limit,
				'fields' => 'ids',
				's' => (string) $query['original'],
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);

		$results = array();

		foreach ( array_map( 'intval', (array) $wp_query->posts ) as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) || ! self::is_authorized_post( $post_id, $post ) ) {
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

	/**
	 * @param array<int,array<string,mixed>> $documents
	 * @return array<int,array<string,mixed>>
	 */
	private static function authorized_documents( array $documents ): array {
		$authorized = array();

		foreach ( $documents as $document ) {
			$post_id = (int) ( $document['post_id'] ?? 0 );
			$post = $post_id > 0 ? get_post( $post_id ) : null;

			if ( ! is_object( $post ) || ! self::is_authorized_post( $post_id, $post ) ) {
				continue;
			}

			$authorized[] = $document;
		}

		return $authorized;
	}

	private static function is_authorized_post( int $post_id, object $post ): bool {
		return Meta_Contract::POST_TYPE === (string) ( $post->post_type ?? '' )
			&& in_array( (string) ( $post->post_status ?? '' ), self::ALLOWED_STATUSES, true )
			&& current_user_can( 'edit_post', $post_id );
	}

	private static function official_url( int $post_id ): string {
		$url = get_permalink( $post_id );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}

		$edit = get_edit_post_link( $post_id, 'raw' );
		return is_string( $edit ) ? $edit : '';
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
					'normalizer' => Search_Query_Normalizer::VERSION,
					'document' => Search_Document_Builder::VERSION,
					'algorithm' => Lexical_Ranker::VERSION,
					'result' => self::RESULT_VERSION,
				),
				'count' => count( $results ),
				'results' => $results,
			),
			$extra
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function invalid_query_response( \WP_Error $error, ?array $query = null ): array {
		$response = self::error_response( $error->get_error_code(), $error->get_error_message(), 'invalid_query' );
		if ( null !== $query ) {
			$response['query'] = array(
				'original' => (string) ( $query['original'] ?? '' ),
				'normalized' => (string) ( $query['normalized'] ?? '' ),
				'tokens' => array_values( array_map( 'strval', (array) ( $query['tokens'] ?? array() ) ) ),
			);
		}
		return $response;
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function error_response( string $code, string $message, string $state = 'technical_error' ): array {
		return array(
			'state' => $state,
			'retrieval_mode' => 'none',
			'error_code' => sanitize_key( $code ),
			'message' => $message,
			'versions' => array(
				'normalizer' => Search_Query_Normalizer::VERSION,
				'document' => Search_Document_Builder::VERSION,
				'algorithm' => Lexical_Ranker::VERSION,
				'result' => self::RESULT_VERSION,
			),
			'count' => 0,
			'results' => array(),
		);
	}
}
