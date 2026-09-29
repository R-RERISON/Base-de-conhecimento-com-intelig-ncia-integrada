<?php
/**
 * Structured Helpful Tips canonical store.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns the historical GRE Helpful Tips physical key under the BDC runtime.
 */
final class Helpful_Tips_Store {

	public const META_KEY          = '_bdc_es_helpful_tips';
	public const MAX_ITEMS         = 20;
	public const MAX_TITLE_BYTES   = 512;
	public const MAX_CONTENT_BYTES = 8192;
	public const STATUS_SUCCESS    = 'SUCCESS';
	public const STATUS_FAIL_SAFE  = 'FAIL_SAFE';

	/**
	 * Register the existing physical key under the BDC owner.
	 */
	public static function register(): void {
		register_post_meta(
			'post',
			self::META_KEY,
			array(
				'type'              => 'array',
				'single'            => true,
				'default'           => array(),
				'sanitize_callback' => array( self::class, 'sanitize_registered_value' ),
				'auth_callback'     => array( self::class, 'authorize_meta' ),
				'show_in_rest'      => false,
				'revisions_enabled' => false,
			)
		);
	}

	/**
	 * Read the ordered Helpful Tips snapshot.
	 *
	 * @param mixed $post_id Post ID candidate.
	 * @return array<int,array{title:string,content:string}>|\WP_Error
	 */
	public static function read( mixed $post_id ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		return self::normalize_items( get_post_meta( (int) $post->ID, self::META_KEY, true ) );
	}

	/**
	 * Replace the ordered Helpful Tips snapshot and confirm persistence.
	 *
	 * @param mixed            $post_id Post ID candidate.
	 * @param array<int,mixed> $items   Ordered tips.
	 * @return array{status:string,state:array<int,array{title:string,content:string}>}|\WP_Error
	 */
	public static function update( mixed $post_id, array $items ): array|\WP_Error {
		$post = self::validate_post( $post_id );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$id = (int) $post->ID;
		if ( ! current_user_can( 'edit_post', $id ) ) {
			return new \WP_Error( 'bdc_kb_tips_forbidden', 'Você não tem permissão para editar as dicas deste artigo.' );
		}

		$prepared = self::prepare_items( $items );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$snapshot = get_post_meta( $id, self::META_KEY, true );
		update_post_meta( $id, self::META_KEY, $prepared );

		$confirmed = self::read( $id );
		if ( ! is_wp_error( $confirmed ) && $confirmed === $prepared ) {
			return array(
				'status' => self::STATUS_SUCCESS,
				'state'  => $confirmed,
			);
		}

		update_post_meta( $id, self::META_KEY, $snapshot );

		return new \WP_Error(
			'bdc_kb_tips_persistence_failed',
			'Não foi possível confirmar as dicas. O estado anterior foi restaurado.',
			array( 'status' => self::STATUS_FAIL_SAFE )
		);
	}

	/**
	 * Metadata API sanitizer.
	 *
	 * @param mixed $value Raw value.
	 * @return array<int,array{title:string,content:string}>
	 */
	public static function sanitize_registered_value( mixed $value ): array {
		$normalized = self::normalize_items( $value );
		return is_wp_error( $normalized ) ? array() : $normalized;
	}

	/**
	 * Metadata authorization.
	 *
	 * @param bool   $allowed   Previous result.
	 * @param string $meta_key  Meta key.
	 * @param int    $object_id Post ID.
	 * @param int    $user_id   User ID.
	 * @param string $cap       Capability.
	 * @param array  $caps      Primitive capabilities.
	 */
	public static function authorize_meta(
		bool $allowed,
		string $meta_key,
		int $object_id,
		int $user_id,
		string $cap = '',
		array $caps = array()
	): bool {
		unset( $allowed, $meta_key, $cap, $caps );

		return user_can( $user_id, 'edit_post', $object_id );
	}

	/**
	 * Validate and sanitize all tips before the first write.
	 *
	 * @param array<int,mixed> $items Raw items.
	 * @return array<int,array{title:string,content:string}>|\WP_Error
	 */
	private static function prepare_items( array $items ): array|\WP_Error {
		if ( count( $items ) > self::MAX_ITEMS ) {
			return new \WP_Error( 'bdc_kb_tips_too_many', 'A quantidade de dicas excede o limite permitido.' );
		}

		$prepared = array();
		foreach ( $items as $row ) {
			if ( ! is_array( $row ) ) {
				return new \WP_Error( 'bdc_kb_tips_invalid_item', 'Uma dica possui formato inválido.' );
			}

			$title   = isset( $row['title'] ) && is_scalar( $row['title'] ) ? (string) $row['title'] : '';
			$content = isset( $row['content'] ) && is_scalar( $row['content'] ) ? (string) $row['content'] : '';

			if ( strlen( $title ) > self::MAX_TITLE_BYTES || strlen( $content ) > self::MAX_CONTENT_BYTES ) {
				return new \WP_Error( 'bdc_kb_tips_value_too_large', 'Uma dica excede o limite permitido.' );
			}

			$title   = trim( sanitize_text_field( $title ) );
			$content = trim( sanitize_textarea_field( $content ) );

			if ( '' === $title && '' === $content ) {
				continue;
			}

			$prepared[] = array(
				'title'   => $title,
				'content' => $content,
			);
		}

		return $prepared;
	}

	/**
	 * Normalize an existing stored snapshot for safe reads.
	 *
	 * @param mixed $raw Raw stored value.
	 * @return array<int,array{title:string,content:string}>
	 */
	private static function normalize_items( mixed $raw ): array {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$items = array();
		foreach ( array_slice( $raw, 0, self::MAX_ITEMS ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$title   = isset( $row['title'] ) && is_scalar( $row['title'] )
				? trim( sanitize_text_field( (string) $row['title'] ) )
				: '';
			$content = isset( $row['content'] ) && is_scalar( $row['content'] )
				? trim( sanitize_textarea_field( (string) $row['content'] ) )
				: '';

			if ( '' === $title && '' === $content ) {
				continue;
			}

			$items[] = array(
				'title'   => $title,
				'content' => $content,
			);
		}

		return $items;
	}

	/**
	 * Validate the target article.
	 *
	 * @param mixed $post_id Post ID candidate.
	 * @return object WP_Post-like object or WP_Error; inspect with is_wp_error().
	 */
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
