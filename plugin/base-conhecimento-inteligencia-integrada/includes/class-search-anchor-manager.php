<?php
/**
 * Materializa anchors efêmeros para deep-links de seções comprovadas.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Anchor_Manager {

	public static function register(): void {
		add_filter( 'the_content', array( self::class, 'filter_content' ), 25 );
	}

	public static function filter_content( string $content ): string {
		if ( ! is_singular( Meta_Contract::POST_TYPE ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( ! Search_Projection_Repository::is_ready() ) {
			return $content;
		}

		$post_id = (int) get_the_ID();
		if ( $post_id <= 0 ) {
			return $content;
		}

		$by_post = Search_Projection_Repository::sections_for_posts( array( $post_id ) );
		if ( $by_post instanceof \WP_Error ) {
			return $content;
		}

		return self::inject_for_sections( $content, (array) ( $by_post[ $post_id ] ?? array() ) );
	}

	/**
	 * Helper determinístico/testável; não acessa banco nem altera fonte editorial.
	 *
	 * @param array<int,array<string,mixed>> $sections
	 */
	public static function inject_for_sections( string $content, array $sections ): string {
		foreach ( $sections as $section ) {
			if ( 'generated' !== (string) ( $section['anchor_state'] ?? '' ) ) {
				continue;
			}

			$anchor = (string) ( $section['anchor_id'] ?? '' );
			$title_norm = (string) ( $section['title_norm'] ?? '' );

			if (
				'' === $title_norm
				|| ! preg_match( '/^' . preg_quote( Search_Section_Projector::ANCHOR_PREFIX, '/' ) . '[a-f0-9]{20}$/', $anchor )
				|| str_contains( $content, 'id="' . $anchor . '"' )
				|| str_contains( $content, "id='" . $anchor . "'" )
			) {
				continue;
			}

			$content = self::inject_unique_heading_anchor( $content, $title_norm, $anchor );
		}

		return $content;
	}

	private static function inject_unique_heading_anchor( string $content, string $title_norm, string $anchor ): string {
		$found = preg_match_all(
			'/<(h[1-6])\b([^>]*)>(.*?)<\/\1>/is',
			$content,
			$matches,
			PREG_SET_ORDER | PREG_OFFSET_CAPTURE
		);

		if ( false === $found || 0 === $found ) {
			return $content;
		}

		$candidates = array();
		foreach ( $matches as $match ) {
			$whole = (string) ( $match[0][0] ?? '' );
			$offset = (int) ( $match[0][1] ?? -1 );
			$tag = strtolower( (string) ( $match[1][0] ?? '' ) );
			$attrs = (string) ( $match[2][0] ?? '' );
			$inner = (string) ( $match[3][0] ?? '' );

			if ( '' === $whole || $offset < 0 ) {
				continue;
			}

			$rendered_title = Search_Query_Normalizer::normalize_document_text( $inner );
			if ( $rendered_title !== $title_norm ) {
				continue;
			}

			$candidates[] = array(
				'whole' => $whole,
				'offset' => $offset,
				'tag' => $tag,
				'attrs' => $attrs,
				'inner' => $inner,
			);
		}

		if ( 1 !== count( $candidates ) ) {
			return $content;
		}

		$target = $candidates[0];
		$span = '<span id="' . esc_attr( $anchor ) . '" class="bdc-kb-section-anchor" aria-hidden="true"></span>';
		$replacement = '<' . $target['tag'] . $target['attrs'] . '>' . $span . $target['inner'] . '</' . $target['tag'] . '>';

		return substr_replace(
			$content,
			$replacement,
			(int) $target['offset'],
			strlen( (string) $target['whole'] )
		);
	}
}
