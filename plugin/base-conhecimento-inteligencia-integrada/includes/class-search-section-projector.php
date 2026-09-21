<?php
/**
 * Projeção determinística de seções para Search SPEC-005 / G-590.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Section_Projector {

	public const VERSION = 'search-section-projection-v1.0.0';
	public const MAX_SECTIONS = 64;
	public const MAX_TEXT_CHARS = 4000;
	public const ANCHOR_PREFIX = 'bdc-kb-section-';

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 * @return array<int,array<string,mixed>>
	 */
	public static function project( int $post_id, array $fragments ): array {
		if ( $post_id <= 0 || empty( $fragments ) ) {
			return array();
		}

		$sections = array();
		$heading_stack = array();
		$current = null;
		$identity_occurrences = array();

		foreach ( $fragments as $index => $fragment ) {
			if ( ! is_array( $fragment ) ) {
				continue;
			}

			$kind = sanitize_key( (string) ( $fragment['kind'] ?? '' ) );
			$text = trim( (string) ( $fragment['text'] ?? '' ) );
			$source_ordinal = max( 0, (int) ( $fragment['ordinal'] ?? $index ) );

			if ( 'heading' === $kind ) {
				if ( is_array( $current ) ) {
					$sections[] = self::finalize( $current );
					if ( count( $sections ) >= self::MAX_SECTIONS ) {
						$current = null;
						break;
					}
				}

				$title = trim( wp_strip_all_tags( html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
				$title_norm = Search_Query_Normalizer::normalize_document_text( $title );
				if ( '' === $title_norm ) {
					$current = null;
					continue;
				}

				$meta = is_array( $fragment['meta'] ?? null ) ? $fragment['meta'] : array();
				$level = max( 1, min( 6, (int) ( $meta['level'] ?? 2 ) ) );

				foreach ( array_keys( $heading_stack ) as $existing_level ) {
					if ( (int) $existing_level >= $level ) {
						unset( $heading_stack[ $existing_level ] );
					}
				}
				$heading_stack[ $level ] = $title_norm;
				ksort( $heading_stack, SORT_NUMERIC );

				$path = array_values( $heading_stack );
				$path_norm = implode( ' > ', $path );
				$identity_base = $level . '|' . $path_norm;
				$occurrence = (int) ( $identity_occurrences[ $identity_base ] ?? 0 ) + 1;
				$identity_occurrences[ $identity_base ] = $occurrence;

				$section_key = hash(
					'sha256',
					$post_id . '|' . $level . '|' . $path_norm . '|' . $occurrence
				);

				$current = array(
					'section_key' => $section_key,
					'ordinal' => count( $sections ),
					'source_ordinal' => $source_ordinal,
					'level' => $level,
					'title' => $title,
					'title_norm' => $title_norm,
					'path_norm' => $path_norm,
					'text_parts' => array(),
					'anchor_id' => self::ANCHOR_PREFIX . substr( $section_key, 0, 20 ),
					'anchor_state' => 'generated',
					'projection_version' => self::VERSION,
				);
				continue;
			}

			if ( ! is_array( $current ) || '' === $text ) {
				continue;
			}

			if ( in_array( $kind, array( 'paragraph', 'list_item', 'table_caption', 'table_row', 'quote', 'code', 'image' ), true ) ) {
				$normalized = Search_Query_Normalizer::normalize_document_text( $text );
				if ( '' !== $normalized ) {
					$current['text_parts'][] = $normalized;
				}
			}
		}

		if ( is_array( $current ) && count( $sections ) < self::MAX_SECTIONS ) {
			$sections[] = self::finalize( $current );
		}

		$title_counts = array();
		foreach ( $sections as $section ) {
			$title_norm = (string) ( $section['title_norm'] ?? '' );
			if ( '' !== $title_norm ) {
				$title_counts[ $title_norm ] = (int) ( $title_counts[ $title_norm ] ?? 0 ) + 1;
			}
		}

		foreach ( $sections as &$section ) {
			$title_norm = (string) ( $section['title_norm'] ?? '' );
			if ( '' === $title_norm || 1 !== (int) ( $title_counts[ $title_norm ] ?? 0 ) ) {
				$section['anchor_state'] = 'unresolved';
				$section['anchor_id'] = '';
			}
		}
		unset( $section );

		return array_values( $sections );
	}

	/**
	 * @param array<string,mixed> $section
	 * @return array<string,mixed>
	 */
	private static function finalize( array $section ): array {
		$text_norm = trim( implode( ' ', array_map( 'strval', (array) ( $section['text_parts'] ?? array() ) ) ) );
		if ( strlen( $text_norm ) > self::MAX_TEXT_CHARS ) {
			$text_norm = substr( $text_norm, 0, self::MAX_TEXT_CHARS );
			$text_norm = rtrim( $text_norm );
		}

		unset( $section['text_parts'] );
		$section['text_norm'] = $text_norm;
		return $section;
	}
}
