<?php
/**
 * Orquestra Section Retrieval apenas após autorização/ranking do parent post.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Section_Service {

	public const VERSION = 'search-section-result-v1.0.0';
	public const MAX_PARENTS = 20;

	/**
	 * O caller deve fornecer exclusivamente resultados parent já autorizados pelo
	 * Search_Service ou Public_Search_Facade. Este método não é endpoint público.
	 *
	 * @param array<string,mixed> $query
	 * @param array<int,array<string,mixed>> $parent_results
	 * @return array<string,mixed>
	 */
	public static function rank_for_authorized_results( array $query, array $parent_results, int $per_parent_limit = 3 ): array {
		$parents = array_slice( array_values( $parent_results ), 0, self::MAX_PARENTS );
		$post_ids = array();

		foreach ( $parents as $parent ) {
			$post_id = (int) ( $parent['post_id'] ?? 0 );
			if ( $post_id > 0 ) {
				$post_ids[] = $post_id;
			}
		}

		$sections_by_post = Search_Projection_Repository::sections_for_posts( $post_ids );
		if ( $sections_by_post instanceof \WP_Error ) {
			return array(
				'state' => 'technical_error',
				'error_code' => $sections_by_post->get_error_code(),
				'count' => 0,
				'items_by_post' => array(),
			);
		}

		$items_by_post = array();
		$total = 0;

		foreach ( $parents as $parent ) {
			$post_id = (int) ( $parent['post_id'] ?? 0 );
			if ( $post_id <= 0 ) {
				continue;
			}

			$parent_rank = max( 1, (int) ( $parent['rank'] ?? 1 ) );
			$ranked = Search_Section_Ranker::rank(
				$query,
				(array) ( $sections_by_post[ $post_id ] ?? array() ),
				$parent_rank,
				$per_parent_limit
			);

			$rows = array();
			foreach ( $ranked as $section ) {
				$anchor = (string) ( $section['anchor_id'] ?? '' );
				if ( '' === $anchor ) {
					continue;
				}
				$permalink = get_permalink( $post_id );
				$base_url = is_string( $permalink ) ? $permalink : '';
				$rows[] = array(
					'post_id' => $post_id,
					'parent_rank' => $parent_rank,
					'section_key' => (string) ( $section['section_key'] ?? '' ),
					'title' => (string) ( $section['title'] ?? '' ),
					'url' => '' === $base_url ? '' : $base_url . '#' . rawurlencode( $anchor ),
					'score' => (float) ( $section['score'] ?? 0.0 ),
					'matched_signals' => array_values( array_map( 'strval', (array) ( $section['matched_signals'] ?? array() ) ) ),
					'anchor_state' => 'generated',
				);
			}

			if ( ! empty( $rows ) ) {
				$items_by_post[ $post_id ] = $rows;
				$total += count( $rows );
			}
		}

		return array(
			'state' => 'ready',
			'count' => $total,
			'items_by_post' => $items_by_post,
			'versions' => array(
				'projection' => Search_Section_Projector::VERSION,
				'ranker' => Search_Section_Ranker::VERSION,
				'result' => self::VERSION,
			),
		);
	}
}
