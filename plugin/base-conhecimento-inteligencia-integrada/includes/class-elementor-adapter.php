<?php
/**
 * Adapter read-only para estruturas Elementor válidas.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor_Adapter {

	/**
	 * @param array<int|string,mixed> $data
	 * @return array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>}
	 */
	public static function extract( array $data ): array {
		$result = array(
			'fragments' => array(),
			'structure' => Legacy_HTML_Adapter::empty_structure(),
			'warnings'  => array(),
		);

		self::walk( $data, $result );

		foreach ( $result['fragments'] as $index => &$fragment ) {
			$fragment['ordinal'] = $index;
		}
		unset( $fragment );

		$result['warnings'] = self::unique_preserve_order( $result['warnings'] );
		return $result;
	}

	/**
	 * @param array<int|string,mixed> $nodes
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $result
	 */
	private static function walk( array $nodes, array &$result ): void {
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				continue;
			}

			$el_type = isset( $node['elType'] ) && is_string( $node['elType'] ) ? $node['elType'] : '';
			$widget_type = isset( $node['widgetType'] ) && is_string( $node['widgetType'] ) ? $node['widgetType'] : '';
			$settings = isset( $node['settings'] ) && is_array( $node['settings'] ) ? $node['settings'] : array();

			if ( 'widget' === $el_type || '' !== $widget_type ) {
				self::extract_widget( $widget_type, $settings, $result );
			}

			if ( isset( $node['elements'] ) && is_array( $node['elements'] ) ) {
				self::walk( $node['elements'], $result );
			}
		}
	}

	/**
	 * @param array<string,mixed> $settings
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $result
	 */
	private static function extract_widget( string $widget_type, array $settings, array &$result ): void {
		if ( 'text-editor' === $widget_type ) {
			$editor = isset( $settings['editor'] ) && is_scalar( $settings['editor'] ) ? (string) $settings['editor'] : '';
			if ( '' !== trim( $editor ) ) {
				$partial = Legacy_HTML_Adapter::extract( $editor, 'elementor:text-editor' );
				$partial = Semantic_DOM_Expectation::apply( $editor, $partial );
				self::merge_result( $result, $partial );
			}
			return;
		}

		if ( 'shortcode' === $widget_type ) {
			$shortcode = isset( $settings['shortcode'] ) && is_scalar( $settings['shortcode'] ) ? (string) $settings['shortcode'] : '';
			$inspection = Shortcode_Inspector::inspect( $shortcode );
			$result['structure']['shortcodes'] += $inspection['total'];
			$result['warnings'] = array_merge( $result['warnings'], $inspection['warnings'] );

			foreach ( $inspection['matches'] as $match ) {
				$inner = isset( $match['inner'] ) ? (string) $match['inner'] : '';
				if ( '' === trim( $inner ) ) {
					continue;
				}
				$partial = Legacy_HTML_Adapter::extract( $inner, 'elementor:shortcode:' . (string) $match['tag'] );
				$partial = Semantic_DOM_Expectation::apply( $inner, $partial );
				self::merge_result( $result, $partial );
			}
			return;
		}

		if ( '' !== $widget_type ) {
			$result['warnings'][] = 'ELEMENTOR_WIDGET_UNSUPPORTED:' . $widget_type;
		}
	}

	/**
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $target
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $source
	 */
	private static function merge_result( array &$target, array $source ): void {
		$namespace = 'elementor-merge-' . count( $target['fragments'] );
		$source['fragments'] = Content_Normalizer::namespace_structural_ids( $source['fragments'], $namespace );
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
