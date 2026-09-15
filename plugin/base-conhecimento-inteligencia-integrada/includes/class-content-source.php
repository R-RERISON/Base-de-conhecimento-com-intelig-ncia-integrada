<?php
/**
 * Inspeção read-only da fonte editorial da SPEC-004.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Content_Source {

	public const SOFT_LIMIT_BYTES = 262144; // 256 KiB.
	public const HARD_LIMIT_BYTES = 1048576; // 1 MiB.

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function inspect( int $post_id ): array|\WP_Error {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) ) {
			return new \WP_Error( 'bdc_kb_post_not_found', 'Post não encontrado.' );
		}

		$post_type = isset( $post->post_type ) ? (string) $post->post_type : '';
		if ( 'post' !== $post_type ) {
			return new \WP_Error( 'bdc_kb_unsupported_post_type', 'A SPEC-004 suporta somente post.' );
		}

		$content = isset( $post->post_content ) ? (string) $post->post_content : '';
		$raw_elementor = get_post_meta( $post_id, '_elementor_data', true );
		$elementor_string = is_string( $raw_elementor ) ? $raw_elementor : '';
		$has_elementor_meta = '' !== trim( $elementor_string ) || ( is_array( $raw_elementor ) && ! empty( $raw_elementor ) );
		$elementor_data = null;
		$elementor_valid = false;

		if ( is_array( $raw_elementor ) ) {
			$elementor_data = $raw_elementor;
			$elementor_valid = true;
		} elseif ( '' !== trim( $elementor_string ) ) {
			try {
				$decoded = json_decode( $elementor_string, true, 512, JSON_THROW_ON_ERROR );
				if ( is_array( $decoded ) ) {
					$elementor_data = $decoded;
					$elementor_valid = true;
				}
			} catch ( \JsonException ) {
				$elementor_valid = false;
			}
		}

		$has_blocks = function_exists( 'has_blocks' ) ? (bool) has_blocks( $content ) : str_contains( $content, '<!-- wp:' );
		$has_html = 1 === preg_match( '/<\s*[a-z][^>]*>/i', $content );
		$plain = function_exists( 'wp_strip_all_tags' ) ? (string) wp_strip_all_tags( $content ) : strip_tags( $content );
		$has_plain_text = '' !== trim( $plain );
		$shortcodes = Shortcode_Inspector::inspect( $content );

		return array(
			'post' => $post,
			'post_content' => $content,
			'elementor_raw' => $raw_elementor,
			'elementor_string' => $elementor_string,
			'elementor_data' => $elementor_data,
			'flags' => array(
				'has_elementor_meta' => $has_elementor_meta,
				'elementor_json_valid' => $elementor_valid,
				'has_blocks' => $has_blocks,
				'has_html' => $has_html,
				'has_registered_shortcode_syntax' => $shortcodes['total'] > 0,
				'has_plain_text' => $has_plain_text,
				'is_empty' => ! $has_elementor_meta && '' === trim( $content ),
			),
			'sizes' => array(
				'post_content' => strlen( $content ),
				'elementor_data' => is_string( $raw_elementor ) ? strlen( $raw_elementor ) : strlen( self::stable_json( $raw_elementor ) ),
			),
			'shortcodes' => $shortcodes,
		);
	}

	public static function exceeds_soft_limit( int $bytes ): bool {
		return $bytes > self::SOFT_LIMIT_BYTES;
	}

	public static function exceeds_hard_limit( int $bytes ): bool {
		return $bytes > self::HARD_LIMIT_BYTES;
	}

	private static function stable_json( mixed $value ): string {
		if ( function_exists( 'wp_json_encode' ) ) {
			$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			return is_string( $json ) ? $json : '';
		}
		$json = json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}
}
