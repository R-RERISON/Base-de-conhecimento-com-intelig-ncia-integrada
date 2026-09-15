<?php
/**
 * Reconhecimento de shortcodes sem execução.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Shortcode_Inspector {

	/** @var array<int,string> */
	private const ALLOWLIST = array(
		'table',
		'n2',
		'wpt',
		'caption',
		'aaaammdd',
		'dbc_table',
		'faq_wd',
		'bdc_resumo_executivo',
	);

	/**
	 * @return array{matches:array<int,array<string,mixed>>,warnings:array<int,string>,total:int}
	 */
	public static function inspect( string $content ): array {
		$candidates = self::candidate_tags();
		if ( empty( $candidates ) || '' === $content || ! function_exists( 'get_shortcode_regex' ) ) {
			return array( 'matches' => array(), 'warnings' => array(), 'total' => 0 );
		}

		$regex = get_shortcode_regex( $candidates );
		if ( ! is_string( $regex ) || '' === $regex ) {
			return array( 'matches' => array(), 'warnings' => array(), 'total' => 0 );
		}

		$matches = array();
		$warnings = array();
		$found = preg_match_all( '/' . $regex . '/s', $content, $raw_matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
		if ( false === $found || 0 === $found ) {
			return array( 'matches' => array(), 'warnings' => array(), 'total' => 0 );
		}

		foreach ( $raw_matches as $raw ) {
			$tag = isset( $raw[2][0] ) ? strtolower( (string) $raw[2][0] ) : '';
			if ( '' === $tag || ! in_array( $tag, $candidates, true ) ) {
				continue;
			}

			$inner = isset( $raw[5][0] ) ? (string) $raw[5][0] : '';
			$whole = isset( $raw[0][0] ) ? (string) $raw[0][0] : '';
			$offset = isset( $raw[0][1] ) ? (int) $raw[0][1] : 0;
			$registered = function_exists( 'shortcode_exists' ) ? shortcode_exists( $tag ) : false;
			$allowlisted = in_array( $tag, self::ALLOWLIST, true );

			if ( ! $registered && ! $allowlisted ) {
				continue;
			}

			$matches[] = array(
				'tag'         => $tag,
				'whole'       => $whole,
				'inner'       => $inner,
				'offset'      => $offset,
				'registered'  => $registered,
				'allowlisted' => $allowlisted,
			);
			$warnings[] = 'SHORTCODE_NOT_EXPANDED:' . $tag;
		}

		return array(
			'matches'  => $matches,
			'warnings' => self::unique_preserve_order( $warnings ),
			'total'    => count( $matches ),
		);
	}

	/**
	 * Remove somente a marcação de shortcodes reconhecidos, preservando conteúdo interno.
	 */
	public static function unwrap_without_execution( string $content ): string {
		$inspection = self::inspect( $content );
		if ( empty( $inspection['matches'] ) ) {
			return $content;
		}

		$matches = $inspection['matches'];
		usort(
			$matches,
			static fn ( array $a, array $b ): int => $b['offset'] <=> $a['offset']
		);

		foreach ( $matches as $match ) {
			$replacement = (string) ( $match['inner'] ?? '' );
			$whole       = (string) ( $match['whole'] ?? '' );
			$offset      = (int) ( $match['offset'] ?? 0 );
			if ( '' === $whole ) {
				continue;
			}
			$content = substr_replace( $content, $replacement, $offset, strlen( $whole ) );
		}

		return $content;
	}

	/** @return array<int,string> */
	public static function candidate_tags(): array {
		global $shortcode_tags;

		$registered = array();
		if ( is_array( $shortcode_tags ) ) {
			foreach ( array_keys( $shortcode_tags ) as $tag ) {
				if ( is_string( $tag ) && '' !== $tag ) {
					$registered[] = strtolower( $tag );
				}
			}
		}

		$tags = array_values( array_unique( array_merge( self::ALLOWLIST, $registered ) ) );
		sort( $tags, SORT_STRING );
		return $tags;
	}

	/** @param array<int,string> $values @return array<int,string> */
	private static function unique_preserve_order( array $values ): array {
		$out = array();
		$seen = array();
		foreach ( $values as $value ) {
			if ( isset( $seen[ $value ] ) ) {
				continue;
			}
			$seen[ $value ] = true;
			$out[] = $value;
		}
		return $out;
	}
}
