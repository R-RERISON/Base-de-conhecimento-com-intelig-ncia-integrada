<?php
/**
 * Adapter estrutural para Gutenberg sem renderização dinâmica.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Gutenberg_Adapter {

	/**
	 * @return array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>}
	 */
	public static function extract( string $content ): array {
		$result = array(
			'fragments' => array(),
			'structure' => Legacy_HTML_Adapter::empty_structure(),
			'warnings'  => array(),
		);

		if ( ! function_exists( 'parse_blocks' ) ) {
			$result['warnings'][] = 'GUTENBERG_PARSE_UNAVAILABLE';
			return $result;
		}

		$blocks = parse_blocks( $content );
		if ( ! is_array( $blocks ) ) {
			$result['warnings'][] = 'GUTENBERG_PARSE_UNAVAILABLE';
			return $result;
		}

		self::walk_blocks( $blocks, $result );
		foreach ( $result['fragments'] as $index => &$fragment ) {
			$fragment['ordinal'] = $index;
		}
		unset( $fragment );
		$result['warnings'] = self::unique_preserve_order( $result['warnings'] );

		return $result;
	}

	/**
	 * @param array<int,mixed> $blocks
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $result
	 */
	private static function walk_blocks( array $blocks, array &$result ): void {
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			$name = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
			$inner_html = isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ? $block['innerHTML'] : '';
			$inner_blocks = isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ? $block['innerBlocks'] : array();

			if ( '' === $name ) {
				if ( '' !== trim( $inner_html ) ) {
					self::merge_result( $result, Legacy_HTML_Adapter::extract( $inner_html, 'gutenberg:freeform' ) );
				}
				if ( ! empty( $inner_blocks ) ) {
					self::walk_blocks( $inner_blocks, $result );
				}
				continue;
			}

			if ( in_array( $name, array( 'core/freeform', 'core/heading', 'core/paragraph', 'core/list', 'core/table' ), true ) ) {
				if ( '' !== trim( $inner_html ) ) {
					$partial = Legacy_HTML_Adapter::extract( $inner_html, 'gutenberg:' . $name );
					self::merge_result( $result, $partial );
				}
				if ( ! empty( $inner_blocks ) ) {
					self::walk_blocks( $inner_blocks, $result );
				}
				continue;
			}

			if ( ! empty( $inner_blocks ) ) {
				$result['warnings'][] = 'GUTENBERG_BLOCK_UNSUPPORTED:' . $name;
				self::walk_blocks( $inner_blocks, $result );
				if ( '' !== trim( $inner_html ) ) {
					self::merge_result( $result, Legacy_HTML_Adapter::extract( $inner_html, 'gutenberg:' . $name ) );
				}
				continue;
			}

			if ( '' !== trim( $inner_html ) ) {
				$result['warnings'][] = 'GUTENBERG_BLOCK_UNSUPPORTED:' . $name;
				self::merge_result( $result, Legacy_HTML_Adapter::extract( $inner_html, 'gutenberg:' . $name ) );
			} else {
				$result['warnings'][] = 'GUTENBERG_DYNAMIC_NOT_RENDERED:' . $name;
			}
		}
	}

	/**
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $target
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $source
	 */
	private static function merge_result( array &$target, array $source ): void {
		$namespace = 'gutenberg-merge-' . count( $target['fragments'] );
		$source['fragments'] = Legacy_HTML_Adapter::namespace_fragments( $source['fragments'], $namespace );
		foreach ( $source['fragments'] as $fragment ) {
			$fragment['ordinal'] = count( $target['fragments'] );
			$target['fragments'][] = $fragment;
		}
		foreach ( $target['structure'] as $key => $value ) {
			$target['structure'][ $key ] = $value + (int) ( $source['structure'][ $key ] ?? 0 );
		}
		$target['warnings'] = array_merge( $target['warnings'], $source['warnings'] );
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
