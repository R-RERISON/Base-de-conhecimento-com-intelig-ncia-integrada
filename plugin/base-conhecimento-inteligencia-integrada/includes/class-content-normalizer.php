<?php
/**
 * Normalização textual determinística da SPEC-004.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Content_Normalizer {

	/**
	 * Normaliza texto sem alterar significado editorial.
	 */
	public static function text( string $value, bool $preserve_whitespace = false ): string {
		$value = str_replace( array( "\r\n", "\r" ), "\n", $value );
		$value = preg_replace( '/\x00/u', '', $value ) ?? '';
		$value = preg_replace( '/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value ) ?? '';

		if ( $preserve_whitespace ) {
			$lines = explode( "\n", $value );
			$lines = array_map(
				static fn ( string $line ): string => rtrim( $line, " \t" ),
				$lines
			);
			$value = implode( "\n", $lines );
			return trim( $value, "\n" );
		}

		$value = preg_replace( '/[\t ]+/u', ' ', $value ) ?? '';
		$value = preg_replace( '/ *\n */u', "\n", $value ) ?? '';
		$value = preg_replace( '/\n{3,}/u', "\n\n", $value ) ?? '';

		return trim( $value );
	}

	/**
	 * Cria fragmento canônico, ou null quando vazio após normalização.
	 *
	 * @param array<string,mixed> $meta
	 * @return array<string,mixed>|null
	 */
	public static function fragment(
		string $kind,
		string $text,
		string $source,
		int $ordinal,
		array $meta = array(),
		bool $preserve_whitespace = false
	): ?array {
		$text = self::text( $text, $preserve_whitespace );
		$structural_anchor = 'list_item' === $kind
			&& '' === $text
			&& '' !== (string) ( $meta['list_id'] ?? '' )
			&& '' !== (string) ( $meta['item_id'] ?? '' );

		if ( '' === $text && ! $structural_anchor ) {
			return null;
		}
		if ( $structural_anchor ) {
			$meta['structural_anchor'] = true;
		}

		$fragment = array(
			'kind'    => $kind,
			'text'    => $text,
			'source'  => $source,
			'ordinal' => $ordinal,
		);

		if ( ! empty( $meta ) ) {
			ksort( $meta );
			$fragment['meta'] = $meta;
		}

		return $fragment;
	}

	/**
	 * Namespaces structural IDs before independently parsed fragments are merged.
	 * Prevents local deterministic IDs (list-0/table-0/...) from colliding across
	 * Elementor widgets, Gutenberg blocks and mixed source adapters.
	 *
	 * @param array<int,array<string,mixed>> $fragments
	 * @return array<int,array<string,mixed>>
	 */
	public static function namespace_structural_ids( array $fragments, string $namespace ): array {
		$namespace = preg_replace( '/[^A-Za-z0-9_.:-]+/', '-', $namespace ) ?? '';
		$namespace = trim( $namespace, '-' );
		if ( '' === $namespace ) {
			$namespace = 'merge';
		}
		$prefix = $namespace . '::';

		foreach ( $fragments as &$fragment ) {
			if ( ! is_array( $fragment ) || ! is_array( $fragment['meta'] ?? null ) ) {
				continue;
			}
			foreach ( array( 'list_id', 'item_id', 'parent_item_id', 'table_id', 'image_id' ) as $key ) {
				$value = isset( $fragment['meta'][ $key ] ) ? (string) $fragment['meta'][ $key ] : '';
				if ( '' !== $value ) {
					$fragment['meta'][ $key ] = $prefix . $value;
				}
			}
		}
		unset( $fragment );

		return $fragments;
	}
}
