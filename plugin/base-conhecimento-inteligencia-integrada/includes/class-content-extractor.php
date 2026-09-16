<?php
/**
 * Orquestrador read-only do Content Extractor da SPEC-004.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Content_Extractor {

	/**
	 * Extrai uma projeção intermediária determinística, sem qualquer write editorial.
	 *
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function extract( int $post_id ): array|\WP_Error {
		$source = Content_Source::inspect( $post_id );
		if ( $source instanceof \WP_Error ) {
			return $source;
		}

		$flags = $source['flags'];
		$sizes = $source['sizes'];
		$warnings = array();
		$strategies = array();
		$fallback_used = false;
		$result = self::empty_result();
		$elementor_succeeded = false;
		$post_content_used = false;

		self::append_budget_warnings( 'elementor_data', (int) $sizes['elementor_data'], $warnings );
		self::append_budget_warnings( 'post_content', (int) $sizes['post_content'], $warnings );

		$elementor_hard_blocked = Content_Source::exceeds_hard_limit( (int) $sizes['elementor_data'] );
		$content_hard_blocked   = Content_Source::exceeds_hard_limit( (int) $sizes['post_content'] );

		if ( $flags['has_elementor_meta'] ) {
			if ( ! $flags['elementor_json_valid'] ) {
				$warnings[] = 'ELEMENTOR_JSON_INVALID';
				$fallback_used = true;
			} elseif ( $elementor_hard_blocked ) {
				$fallback_used = true;
			} else {
				$strategies[] = 'elementor';
				$elementor_result = Elementor_Adapter::extract( is_array( $source['elementor_data'] ) ? $source['elementor_data'] : array() );
				self::merge_result( $result, $elementor_result );
				$elementor_succeeded = self::is_semantically_sufficient( $elementor_result );
				if ( ! $elementor_succeeded ) {
					$warnings[] = 'ELEMENTOR_SEMANTIC_EMPTY';
					$fallback_used = true;
				}
			}
		}

		// Contrato mixed: Elementor válido + blocos relevantes preservam ambas as proveniências.
		if ( $elementor_succeeded && $flags['has_blocks'] && ! $content_hard_blocked ) {
			$strategies[] = 'gutenberg';
			$gutenberg_result = Gutenberg_Adapter::extract( (string) $source['post_content'] );
			if ( self::is_semantically_sufficient( $gutenberg_result ) ) {
				self::merge_result( $result, $gutenberg_result );
				$post_content_used = true;
			}
		}

		if ( ! $elementor_succeeded && ! $content_hard_blocked ) {
			$post_result = self::extract_post_content( $source );
			$strategies[] = $post_result['strategy'];
			unset( $post_result['strategy'] );
			self::merge_result( $result, $post_result );
			$post_content_used = self::is_semantically_sufficient( $post_result );
		}

		if ( empty( $result['fragments'] ) ) {
			if ( $flags['has_elementor_meta'] || '' !== trim( (string) $source['post_content'] ) ) {
				$warnings[] = 'SOURCE_EMPTY';
				$warnings[] = 'RENDER_FALLBACK_CANDIDATE';
			}
		}

		$warnings = array_merge( $warnings, $result['warnings'] );
		$warnings = self::unique_preserve_order( $warnings );
		$result['warnings'] = $warnings;
		self::reindex_fragments( $result['fragments'] );

		$source_kind = self::source_kind(
			$elementor_succeeded,
			$post_content_used,
			(bool) $flags['has_blocks'],
			(bool) $flags['has_html'],
			(bool) $flags['has_plain_text'],
			$result['fragments']
		);

		return array(
			'source_kind' => $source_kind,
			'strategies' => array_values( array_unique( $strategies ) ),
			'fallback_used' => $fallback_used,
			'warnings' => $warnings,
			'fragments' => $result['fragments'],
			'structure' => $result['structure'],
			'source_material' => array(
				'post_content_sha256' => hash( 'sha256', (string) $source['post_content'] ),
				'elementor_data_sha256' => hash( 'sha256', self::elementor_raw_string( $source['elementor_raw'] ) ),
			),
			'source_flags' => $flags,
			'source_sizes' => $sizes,
			'elementor_compatibility' => self::elementor_compatibility( $source, $warnings, $elementor_hard_blocked, $content_hard_blocked ),
		);
	}

	/**
	 * @param array<string,mixed> $source
	 * @return array{strategy:string,fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>}
	 */
	private static function extract_post_content( array $source ): array {
		$content = (string) $source['post_content'];
		$flags   = $source['flags'];

		if ( $flags['has_blocks'] ) {
			$result = Gutenberg_Adapter::extract( $content );
			$result['strategy'] = 'gutenberg';
			return $result;
		}

		if ( $flags['has_html'] || $flags['has_registered_shortcode_syntax'] ) {
			$result = Legacy_HTML_Adapter::extract( $content, 'post_content' );
			$result['strategy'] = 'legacy_html';
			return $result;
		}

		$text = Content_Normalizer::text( $content );
		$fragments = array();
		if ( '' !== $text ) {
			$fragment = Content_Normalizer::fragment( 'paragraph', $text, 'post_content', 0 );
			if ( null !== $fragment ) {
				$fragments[] = $fragment;
			}
		}

		return array(
			'strategy' => '' === $text ? 'empty' : 'plain_text',
			'fragments' => $fragments,
			'structure' => Legacy_HTML_Adapter::empty_structure(),
			'warnings' => array(),
		);
	}

	/**
	 * @return array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>}
	 */
	private static function empty_result(): array {
		return array(
			'fragments' => array(),
			'structure' => Legacy_HTML_Adapter::empty_structure(),
			'warnings' => array(),
		);
	}

	/**
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $target
	 * @param array{fragments:array<int,array<string,mixed>>,structure:array<string,int>,warnings:array<int,string>} $source
	 */
	private static function merge_result( array &$target, array $source ): void {
		$namespace = 'extract-merge-' . count( $target['fragments'] );
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

	/** @param array{fragments:array<int,array<string,mixed>>} $result */
	private static function is_semantically_sufficient( array $result ): bool {
		return ! empty( $result['fragments'] );
	}

	/** @param array<int,string> $warnings */
	private static function append_budget_warnings( string $source_name, int $bytes, array &$warnings ): void {
		if ( Content_Source::exceeds_hard_limit( $bytes ) ) {
			$warnings[] = 'SOURCE_OVERSIZE_HARD:' . $source_name;
			return;
		}
		if ( Content_Source::exceeds_soft_limit( $bytes ) ) {
			$warnings[] = 'SOURCE_OVERSIZE_SOFT:' . $source_name;
		}
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 */
	private static function reindex_fragments( array &$fragments ): void {
		foreach ( $fragments as $index => &$fragment ) {
			$fragment['ordinal'] = $index;
		}
		unset( $fragment );
	}

	/**
	 * @param array<int,array<string,mixed>> $fragments
	 */
	private static function source_kind(
		bool $elementor_succeeded,
		bool $post_content_used,
		bool $has_blocks,
		bool $has_html,
		bool $has_plain_text,
		array $fragments
	): string {
		if ( empty( $fragments ) ) {
			return 'empty';
		}
		if ( $elementor_succeeded && $post_content_used ) {
			return 'mixed';
		}
		if ( $elementor_succeeded ) {
			return 'elementor';
		}
		if ( $has_blocks ) {
			return 'gutenberg';
		}
		if ( $has_html ) {
			return 'legacy_html';
		}
		if ( $has_plain_text ) {
			return 'plain_text';
		}
		return 'empty';
	}

	/**
	 * Read-only readiness classification for a future, explicit Elementor migration.
	 * This does not build or persist Elementor data.
	 *
	 * @param array<string,mixed> $source
	 * @param array<int,string> $warnings
	 * @return array{status:string,reasons:array<int,string>}
	 */
	private static function elementor_compatibility( array $source, array $warnings, bool $elementor_hard_blocked, bool $content_hard_blocked ): array {
		$flags = $source['flags'];
		if ( $flags['elementor_json_valid'] && ! $elementor_hard_blocked ) {
			return array( 'status' => 'native', 'reasons' => array() );
		}

		if ( $content_hard_blocked && ! $flags['elementor_json_valid'] ) {
			return array( 'status' => 'blocked', 'reasons' => array( 'SOURCE_OVERSIZE_HARD:post_content' ) );
		}

		$review_prefixes = array(
			'ELEMENTOR_JSON_INVALID',
			'SHORTCODE_NOT_EXPANDED:',
			'GUTENBERG_DYNAMIC_NOT_RENDERED:',
			'GUTENBERG_BLOCK_UNSUPPORTED:',
			'ELEMENTOR_WIDGET_UNSUPPORTED:',
		);
		$reasons = array();
		foreach ( $warnings as $warning ) {
			foreach ( $review_prefixes as $prefix ) {
				if ( str_starts_with( $warning, $prefix ) ) {
					$reasons[] = $warning;
					break;
				}
		}
		}

		$reasons = self::unique_preserve_order( $reasons );
		return array(
			'status' => empty( $reasons ) ? 'projectable' : 'review_required',
			'reasons' => $reasons,
		);
	}

	private static function elementor_raw_string( mixed $raw ): string {
		if ( is_string( $raw ) ) {
			return $raw;
		}
		if ( function_exists( 'wp_json_encode' ) ) {
			$json = wp_json_encode( $raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			return is_string( $json ) ? $json : '';
		}
		$json = json_encode( $raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
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
