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
		if ( '' === $text ) {
			return null;
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
}
