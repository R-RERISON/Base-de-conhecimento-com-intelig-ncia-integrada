<?php
/**
 * Public Article content compatibility stage.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Public_Article_Content {

	/**
	 * Capture the canonical WordPress content pipeline while the main query is
	 * inside the loop. Integrations such as GAC are allowed to observe the same
	 * conditions they receive on the legacy single-post surface.
	 */
	public static function capture_current_loop(): string {
		global $post;

		if ( ! is_object( $post ) || 'post' !== (string) ( $post->post_type ?? '' ) ) {
			return '';
		}

		ob_start();
		the_content();
		$html = (string) ob_get_clean();

		if ( '' === trim( $html ) ) {
			return '';
		}

		return self::strip_duplicate_legacy_chrome( $html, (string) $post->post_title );
	}

	/**
	 * Compatibility fallback for callers that are not inside the loop.
	 */
	public static function render( int $post_id ): string {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || 'post' !== (string) ( $post->post_type ?? '' ) ) {
			return '';
		}

		$html = apply_filters( 'the_content', (string) $post->post_content );
		if ( ! is_string( $html ) || '' === trim( $html ) ) {
			return '';
		}

		return self::strip_duplicate_legacy_chrome( $html, (string) $post->post_title );
	}

	private static function strip_duplicate_legacy_chrome( string $html, string $title ): string {
		if ( '' === trim( $title ) || ! class_exists( '\\DOMDocument' ) ) {
			return $html;
		}

		$previous = libxml_use_internal_errors( true );
		$dom = new \DOMDocument( '1.0', 'UTF-8' );
		$wrapped = '<!doctype html><html><body><div id="bdc-reader-root">' . $html . '</div></body></html>';
		$loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
		if ( ! $loaded ) {
			return $html;
		}

		$root = $dom->getElementById( 'bdc-reader-root' );
		if ( ! $root instanceof \DOMElement ) {
			return $html;
		}

		$title_key = self::normalize( $title );
		$markers = array( 'responsavel', 'publicado', 'atualizado', 'voltar para a base' );
		$candidates = array();
		$nodes = $root->getElementsByTagName( '*' );
		$limit = min( 80, $nodes->length );

		for ( $index = 0; $index < $limit; ++$index ) {
			$node = $nodes->item( $index );
			if ( ! $node instanceof \DOMElement ) {
				continue;
			}
			$tag = strtolower( $node->tagName );
			if ( ! in_array( $tag, array( 'header', 'section', 'div' ), true ) ) {
				continue;
			}

			$text = self::normalize( (string) $node->textContent );
			if ( '' === $text || strlen( $text ) > 2200 || ! str_contains( $text, $title_key ) ) {
				continue;
			}

			$hits = 0;
			foreach ( $markers as $marker ) {
				if ( str_contains( $text, $marker ) ) {
					++$hits;
				}
			}
			if ( $hits < 2 ) {
				continue;
			}

			$candidates[] = array(
				'node' => $node,
				'length' => strlen( $text ),
				'hits' => $hits,
			);
		}

		if ( empty( $candidates ) ) {
			return $html;
		}

		usort(
			$candidates,
			static function ( array $a, array $b ): int {
				if ( (int) $a['hits'] !== (int) $b['hits'] ) {
					return (int) $b['hits'] <=> (int) $a['hits'];
				}
				return (int) $a['length'] <=> (int) $b['length'];
			}
		);

		$target = $candidates[0]['node'] ?? null;
		if ( ! $target instanceof \DOMElement || ! $target->parentNode ) {
			return $html;
		}
		$target->parentNode->removeChild( $target );

		$out = '';
		foreach ( $root->childNodes as $child ) {
			$out .= $dom->saveHTML( $child );
		}
		return '' !== trim( $out ) ? $out : $html;
	}

	private static function normalize( string $value ): string {
		$value = strtolower( remove_accents( wp_strip_all_tags( $value ) ) );
		$value = preg_replace( '/\\s+/u', ' ', $value ) ?? $value;
		return trim( $value );
	}
}
