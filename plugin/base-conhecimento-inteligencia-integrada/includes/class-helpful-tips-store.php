<?php
/**
 * Structured Helpful Tips read model/store compatibility.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Helpful_Tips_Store {

	public const META_KEY = '_bdc_es_helpful_tips';

	/**
	 * @return array<int,array{title:string,content:string}>|\WP_Error
	 */
	public static function read( mixed $post_id ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$raw = get_post_meta( (int) $post->ID, self::META_KEY, true );
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$items = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$title = isset( $row['title'] ) && is_scalar( $row['title'] )
				? trim( sanitize_text_field( (string) $row['title'] ) )
				: '';
			$content = isset( $row['content'] ) && is_scalar( $row['content'] )
				? trim( sanitize_textarea_field( (string) $row['content'] ) )
				: '';

			if ( '' === $title && '' === $content ) {
				continue;
			}

			$items[] = array(
				'title' => $title,
				'content' => $content,
			);
		}

		return $items;
	}

	private static function validate_post( mixed $post_id ): object {
		$id = is_int( $post_id ) || ( is_string( $post_id ) && ctype_digit( $post_id ) )
			? (int) $post_id
			: 0;
		if ( $id <= 0 ) {
			return new \WP_Error( 'bdc_kb_tips_invalid_post', 'O artigo informado é inválido.' );
		}

		$post = get_post( $id );
		if ( ! is_object( $post ) || 'post' !== (string) ( $post->post_type ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_tips_invalid_post', 'O artigo informado não existe ou está fora do escopo.' );
		}

		return $post;
	}
}
