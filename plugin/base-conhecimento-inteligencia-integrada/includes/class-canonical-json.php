<?php
/**
 * Serialização JSON canônica e determinística da SPEC-004.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Canonical_JSON {

	/**
	 * Codifica valor com ordenação determinística de chaves associativas.
	 * Arrays posicionais preservam a ordem editorial.
	 *
	 * @throws \JsonException Quando a serialização falhar.
	 */
	public static function encode( mixed $value ): string {
		$normalized = self::normalize( $value );
		return json_encode(
			$normalized,
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
		);
	}

	/**
	 * Calcula SHA-256 sobre UTF-8 JSON canônico.
	 *
	 * @throws \JsonException Quando a serialização falhar.
	 */
	public static function hash( mixed $value ): string {
		return hash( 'sha256', self::encode( $value ) );
	}

	private static function normalize( mixed $value ): mixed {
		if ( is_object( $value ) ) {
			$value = get_object_vars( $value );
		}

		if ( ! is_array( $value ) ) {
			return $value;
		}

		if ( array_is_list( $value ) ) {
			$out = array();
			foreach ( $value as $item ) {
				$out[] = self::normalize( $item );
			}
			return $out;
		}

		ksort( $value, SORT_STRING );
		foreach ( $value as $key => $item ) {
			$value[ $key ] = self::normalize( $item );
		}
		return $value;
	}
}
