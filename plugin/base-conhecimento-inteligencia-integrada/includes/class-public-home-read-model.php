<?php
/**
 * Public Home read models for the preview/candidate surface.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Public_Home_Read_Model {

	/** @var array<string,string> */
	private const CURATED_CATEGORIES = array(
		'Email' => 'dashicons-email',
		'Externo' => 'dashicons-external',
		'Hardware' => 'dashicons-desktop',
		'Rede' => 'dashicons-networking',
		'Segurança' => 'dashicons-shield',
		'Sistemas' => 'dashicons-admin-generic',
		'Software' => 'dashicons-editor-code',
	);

	/**
	 * @return array<int,array{id:int,name:string,icon:string}>
	 */
	public static function categories(): array {
		$out = array();
		foreach ( self::CURATED_CATEGORIES as $name => $icon ) {
			$term = get_term_by( 'name', $name, 'category' );
			if ( ! is_object( $term ) || ! isset( $term->term_id, $term->name ) ) {
				continue;
			}
			$out[] = array(
				'id' => (int) $term->term_id,
				'name' => (string) $term->name,
				'icon' => $icon,
			);
		}
		return $out;
	}


	public static function published_count(): int {
		$counts = wp_count_posts( 'post' );
		return is_object( $counts ) && isset( $counts->publish ) ? max( 0, (int) $counts->publish ) : 0;
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function latest( int $category_id = 0, int $limit = 4 ): array {
		return self::query_posts(
			$category_id,
			$limit,
			array(
				'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ),
			)
		);
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function popular( int $category_id = 0, int $limit = 4 ): array {
		return self::query_posts(
			$category_id,
			$limit,
			array(
				'orderby' => array( 'comment_count' => 'DESC', 'date' => 'DESC', 'ID' => 'DESC' ),
			)
		);
	}

	/**
	 * Preview-only lexical search. Public authorization facade is a separate H-020 task.
	 *
	 * @return array<string,mixed>|null
	 */
	public static function preview_search( string $query ): ?array {
		$query = trim( $query );
		if ( '' === $query ) {
			return null;
		}
		return Search_Service::search( $query, 8 );
	}

	/**
	 * Provisional content-only cloud for admin preview.
	 *
	 * @return array<int,array{term:string,count:int}>
	 */
	public static function preview_word_cloud(): array {
		$ids = get_posts(
			array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => 120,
				'fields' => 'ids',
				'orderby' => 'date',
				'order' => 'DESC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);

		$stop = array_fill_keys(
			array(
				'para','com','sem','uma','das','dos','que','como','mais','por','nos','nas','de','do','da',
				'em','no','na','os','as','ao','aos','e','ou','se','um','uma','base','conhecimento',
			),
			true
		);
		$count = array();

		foreach ( (array) $ids as $raw_id ) {
			$post = get_post( (int) $raw_id );
			if ( ! is_object( $post ) ) {
				continue;
			}
			$text = remove_accents( strtolower( (string) $post->post_title ) );
			$tokens = preg_split( '/[^a-z0-9]+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
			foreach ( (array) $tokens as $token ) {
				if ( strlen( $token ) < 4 || isset( $stop[ $token ] ) || ctype_digit( $token ) ) {
					continue;
				}
				$count[ $token ] = (int) ( $count[ $token ] ?? 0 ) + 1;
			}
		}

		arsort( $count, SORT_NUMERIC );
		$out = array();
		foreach ( array_slice( $count, 0, 20, true ) as $term => $frequency ) {
			$out[] = array( 'term' => (string) $term, 'count' => (int) $frequency );
		}
		return $out;
	}

	/**
	 * @param array<string,mixed> $extra
	 * @return array<int,array<string,mixed>>
	 */
	private static function query_posts( int $category_id, int $limit, array $extra ): array {
		$args = array_merge(
			array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => max( 1, min( 12, $limit ) ),
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
				'suppress_filters' => false,
			),
			$extra
		);

		if ( $category_id > 0 ) {
			$args['cat'] = $category_id;
		}

		$query = new \WP_Query( $args );
		$rows = array();
		foreach ( (array) $query->posts as $post ) {
			if ( ! is_object( $post ) || 'publish' !== (string) ( $post->post_status ?? '' ) ) {
				continue;
			}
			$post_id = (int) $post->ID;
			$categories = get_the_category( $post_id );
			$rows[] = array(
				'post_id' => $post_id,
				'title' => (string) $post->post_title,
				'url' => (string) get_permalink( $post_id ),
				'date' => get_the_date( 'd/m/Y', $post_id ),
				'category' => ! empty( $categories ) && is_object( $categories[0] ) ? (string) $categories[0]->name : '',
				'comment_count' => (int) ( $post->comment_count ?? 0 ),
			);
		}
		wp_reset_postdata();
		return $rows;
	}
}