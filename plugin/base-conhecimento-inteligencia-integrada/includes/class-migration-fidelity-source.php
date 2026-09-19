<?php
/**
 * Lossless, read-only editorial source for Block Migration.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Migration_Fidelity_Source {

	public const SCHEMA_VERSION = '1.0.0';

	/** @return array<string,mixed>|\WP_Error */
	public static function build( int $post_id ): array|\WP_Error {
		$source = Content_Source::inspect( $post_id );
		if ( $source instanceof \WP_Error ) {
			return $source;
		}
		$extraction = Content_Extractor::extract( $post_id );
		if ( $extraction instanceof \WP_Error ) {
			return $extraction;
		}
		return self::from_sources( $post_id, $source, $extraction );
	}

	/**
	 * Pure constructor used by unit tests and runtime.
	 * Raw payload exists only in-memory and MUST NOT be exported by diagnostic runners.
	 *
	 * @param array<string,mixed> $source
	 * @param array<string,mixed> $extraction
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function from_sources( int $post_id, array $source, array $extraction ): array|\WP_Error {
		if ( $post_id <= 0 ) {
			return new \WP_Error( 'bdc_kb_migration_source_invalid_post', 'Post inválido para Migration Fidelity Source.' );
		}

		$source_kind = (string) ( $extraction['source_kind'] ?? 'empty' );
		$post_content = isset( $source['post_content'] ) && is_string( $source['post_content'] ) ? $source['post_content'] : '';
		$elementor_data = is_array( $source['elementor_data'] ?? null ) ? $source['elementor_data'] : array();
		$flags = is_array( $source['flags'] ?? null ) ? $source['flags'] : array();
		$sizes = is_array( $source['sizes'] ?? null ) ? $source['sizes'] : array();
		$warnings = array();
		$units = array();
		$status = 'ready';
		$strategy = '';

		if ( 'empty' === $source_kind ) {
			$status = 'not_applicable';
			$strategy = 'noop_empty';
		} elseif ( 'gutenberg' === $source_kind ) {
			$strategy = 'native_core_blocks';
			$units[] = self::unit( 'native_core_blocks', $post_content );
		} elseif ( 'legacy_html' === $source_kind ) {
			$strategy = 'preserve_post_content_lossless';
			$units[] = self::unit( 'post_content_rich_html', $post_content );
		} elseif ( 'plain_text' === $source_kind ) {
			$strategy = 'preserve_post_content_lossless';
			$units[] = self::unit( 'post_content_plain_text', $post_content );
		} elseif ( 'elementor' === $source_kind ) {
			$strategy = 'extract_elementor_editorial_units';
			self::collect_elementor_units( $elementor_data, $units, $warnings );
			if ( empty( $units ) ) {
				$status = 'review_required';
				$warnings[] = 'MIGRATION_SOURCE_ELEMENTOR_NO_EDITORIAL_UNITS';
			}
		} elseif ( 'mixed' === $source_kind ) {
			$strategy = 'preserve_dual_source_for_review';
			self::collect_elementor_units( $elementor_data, $units, $warnings );
			if ( '' !== $post_content ) {
				$units[] = self::unit( 'native_core_blocks', $post_content );
			}
			$status = 'review_required';
			$warnings[] = 'MIGRATION_SOURCE_MIXED_REQUIRES_HUMAN_SELECTION';
		} else {
			$status = 'review_required';
			$strategy = 'preserve_unknown_source_for_review';
			$warnings[] = 'MIGRATION_SOURCE_UNKNOWN_KIND:' . ( '' !== $source_kind ? $source_kind : 'unknown' );
			if ( '' !== $post_content ) {
				$units[] = self::unit( 'post_content_unknown', $post_content );
			}
		}

		if ( ! empty( $warnings ) && 'ready' === $status ) {
			foreach ( $warnings as $warning ) {
				if ( str_starts_with( $warning, 'MIGRATION_SOURCE_ELEMENTOR_UNSUPPORTED_WIDGET:' ) ) {
					$status = 'review_required';
					break;
				}
			}
		}

		foreach ( $units as $index => &$unit ) {
			$unit['ordinal'] = $index;
		}
		unset( $unit );

		$manifest_units = array();
		foreach ( $units as $unit ) {
			$manifest_units[] = array(
				'ordinal' => (int) ( $unit['ordinal'] ?? 0 ),
				'kind' => (string) ( $unit['kind'] ?? '' ),
				'raw_sha256' => (string) ( $unit['raw_sha256'] ?? '' ),
				'bytes' => (int) ( $unit['bytes'] ?? 0 ),
			);
		}

		$manifest = array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => $post_id,
			'source_kind' => $source_kind,
			'status' => $status,
			'strategy' => $strategy,
			'units' => $manifest_units,
			'warnings' => array_values( array_unique( array_map( 'strval', $warnings ) ) ),
			'source_material' => array(
				'post_content_sha256' => hash( 'sha256', $post_content ),
				'elementor_data_sha256' => self::elementor_hash( $source ),
			),
		);

		try {
			$fidelity_hash = Canonical_JSON::hash( $manifest );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_migration_source_hash_failed', 'Falha ao calcular fidelity_hash.' );
		}

		return array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => $post_id,
			'source_kind' => $source_kind,
			'status' => $status,
			'strategy' => $strategy,
			'units' => $units,
			'warnings' => $manifest['warnings'],
			'source_material' => $manifest['source_material'],
			'fidelity_hash' => $fidelity_hash,
			'safety' => array(
				'read_only' => true,
				'persists_state' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
				'renders_dynamic_blocks' => false,
				'depends_on_gutenberg_plugin' => false,
			),
			'diagnostics' => array(
				'has_blocks' => (bool) ( $flags['has_blocks'] ?? false ),
				'has_elementor_meta' => (bool) ( $flags['has_elementor_meta'] ?? false ),
				'post_content_bytes' => max( 0, (int) ( $sizes['post_content'] ?? strlen( $post_content ) ) ),
				'elementor_data_bytes' => max( 0, (int) ( $sizes['elementor_data'] ?? 0 ) ),
			),
		);
	}

	/** @return array<string,mixed> */
	private static function unit( string $kind, string $raw ): array {
		return array(
			'kind' => $kind,
			'ordinal' => 0,
			'raw' => $raw,
			'raw_sha256' => hash( 'sha256', $raw ),
			'bytes' => strlen( $raw ),
		);
	}

	/** @param array<int|string,mixed> $nodes @param array<int,array<string,mixed>> $units @param array<int,string> $warnings */
	private static function collect_elementor_units( array $nodes, array &$units, array &$warnings ): void {
		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) ) {
				continue;
			}
			$widget_type = isset( $node['widgetType'] ) && is_string( $node['widgetType'] ) ? $node['widgetType'] : '';
			$settings = is_array( $node['settings'] ?? null ) ? $node['settings'] : array();
			if ( 'text-editor' === $widget_type ) {
				$raw = isset( $settings['editor'] ) && is_scalar( $settings['editor'] ) ? (string) $settings['editor'] : '';
				if ( '' !== $raw ) {
					$units[] = self::unit( 'elementor_text_editor_html', $raw );
				}
			} elseif ( 'shortcode' === $widget_type ) {
				$raw = isset( $settings['shortcode'] ) && is_scalar( $settings['shortcode'] ) ? (string) $settings['shortcode'] : '';
				if ( '' !== $raw ) {
					$units[] = self::unit( 'elementor_shortcode', $raw );
				}
			} elseif ( '' !== $widget_type ) {
				$warnings[] = 'MIGRATION_SOURCE_ELEMENTOR_UNSUPPORTED_WIDGET:' . $widget_type;
			}
			if ( isset( $node['elements'] ) && is_array( $node['elements'] ) ) {
				self::collect_elementor_units( $node['elements'], $units, $warnings );
			}
		}
	}

	/** @param array<string,mixed> $source */
	private static function elementor_hash( array $source ): string {
		$raw = $source['elementor_raw'] ?? '';
		if ( is_string( $raw ) ) {
			return hash( 'sha256', $raw );
		}
		if ( function_exists( 'wp_json_encode' ) ) {
			$json = wp_json_encode( $raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		} else {
			$json = json_encode( $raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		}
		return hash( 'sha256', is_string( $json ) ? $json : '' );
	}
}
