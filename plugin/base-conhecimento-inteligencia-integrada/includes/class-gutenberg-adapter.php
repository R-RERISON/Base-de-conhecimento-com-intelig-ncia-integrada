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

	/** @return array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} */
	public static function extract( string $content ): array {
		$result = array( 'fragments' => array(), 'structure' => Legacy_HTML_Adapter::empty_structure(), 'warnings' => array() );
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
		foreach ( $result['fragments'] as $index => &$fragment ) { $fragment['ordinal'] = $index; }
		unset( $fragment );
		$result['warnings'] = array_values( array_unique( $result['warnings'] ) );
		return $result;
	}

	/** @param array<int,mixed> $blocks @param array<string,mixed> $result */
	private static function walk_blocks( array $blocks, array &$result ): void {
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) { continue; }
			$name = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
			$html = isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ? $block['innerHTML'] : '';
			$children = isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ? $block['innerBlocks'] : array();
			if ( '' === $name ) {
				if ( '' !== trim( $html ) ) { self::merge( $result, Legacy_HTML_Adapter::extract( $html, 'gutenberg:freeform' ) ); }
				if ( $children ) { self::walk_blocks( $children, $result ); }
				continue;
			}
			if ( in_array( $name, array( 'core/freeform', 'core/heading', 'core/paragraph', 'core/list', 'core/table' ), true ) ) {
				if ( '' !== trim( $html ) ) { self::merge( $result, Legacy_HTML_Adapter::extract( $html, 'gutenberg:' . $name ) ); }
				if ( $children ) { self::walk_blocks( $children, $result ); }
				continue;
			}
			if ( $children ) {
				$result['warnings'][] = 'GUTENBERG_BLOCK_UNSUPPORTED:' . $name;
				self::walk_blocks( $children, $result );
				if ( '' !== trim( $html ) ) { self::merge( $result, Legacy_HTML_Adapter::extract( $html, 'gutenberg:' . $name ) ); }
				continue;
			}
			if ( '' !== trim( $html ) ) {
				$result['warnings'][] = 'GUTENBERG_BLOCK_UNSUPPORTED:' . $name;
				self::merge( $result, Legacy_HTML_Adapter::extract( $html, 'gutenberg:' . $name ) );
			} else {
				$result['warnings'][] = 'GUTENBERG_DYNAMIC_NOT_RENDERED:' . $name;
			}
		}
	}

	/** @param array<string,mixed> $target @param array<string,mixed> $source */
	private static function merge( array &$target, array $source ): void {
		foreach ( $source['fragments'] as $fragment ) { $fragment['ordinal'] = count( $target['fragments'] ); $target['fragments'][] = $fragment; }
		foreach ( $target['structure'] as $key => $value ) { $target['structure'][ $key ] = $value + (int) ( $source['structure'][ $key ] ?? 0 ); }
		$target['warnings'] = array_merge( $target['warnings'], $source['warnings'] );
	}
}
