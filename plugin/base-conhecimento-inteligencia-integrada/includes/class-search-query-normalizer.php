<?php
/**
 * Normalização lexical determinística da Search SPEC-005.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Query_Normalizer {

	public const VERSION = 'search-normalizer-v1.0.0';
	public const MAX_QUERY_CHARS = 256;
	public const MAX_QUERY_BYTES = 1024;
	public const MAX_TOKENS = 16;
	public const MAX_TOKEN_CHARS = 128;

	/**
	 * @return array{original:string,normalized:string,tokens:array<int,string>,detected_type:string,normalizer_version:string}|\WP_Error
	 */
	public static function normalize( mixed $value ): array|\WP_Error {
		if ( ! is_string( $value ) ) {
			return self::error( 'search_invalid_query_type', 'A consulta deve ser uma string.' );
		}

		if ( strlen( $value ) > self::MAX_QUERY_BYTES || self::char_length( $value ) > self::MAX_QUERY_CHARS ) {
			return self::error( 'search_query_too_long', 'A consulta excede o limite permitido.' );
		}

		$original = self::clean_original( $value );
		$normalized = self::normalize_document_text( $original );
		$tokens = self::tokens_from_normalized( $normalized );

		if ( count( $tokens ) > self::MAX_TOKENS ) {
			return self::error( 'search_too_many_tokens', 'A consulta excede o limite de tokens.' );
		}

		foreach ( $tokens as $token ) {
			if ( self::char_length( $token ) > self::MAX_TOKEN_CHARS ) {
				return self::error( 'search_token_too_long', 'Um termo da consulta excede o limite permitido.' );
			}
		}

		return array(
			'original' => $original,
			'normalized' => $normalized,
			'tokens' => $tokens,
			'detected_type' => empty( $tokens ) ? 'empty' : ( 1 === count( $tokens ) ? 'single_token' : 'multi_token' ),
			'normalizer_version' => self::VERSION,
		);
	}

	/**
	 * Perfil lexical compartilhado entre query e Search Document.
	 */
	public static function normalize_document_text( string $value ): string {
		$value = self::remove_controls( $value );
		$value = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$value = wp_strip_all_tags( $value );
		$value = function_exists( 'remove_accents' ) ? remove_accents( $value ) : $value;
		$value = function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
		$value = preg_replace( '/[^a-z0-9]+/u', ' ', $value ) ?? '';
		$value = preg_replace( '/\s+/u', ' ', $value ) ?? '';
		return trim( $value );
	}

	/**
	 * @return array<int,string>
	 */
	public static function tokens_from_normalized( string $normalized ): array {
		if ( '' === $normalized ) {
			return array();
		}

		$parts = preg_split( '/\s+/u', $normalized ) ?: array();
		$out = array();
		$seen = array();

		foreach ( $parts as $part ) {
			if ( '' === $part || isset( $seen[ $part ] ) ) {
				continue;
			}
			$seen[ $part ] = true;
			$out[] = $part;
		}

		return $out;
	}

	private static function clean_original( string $value ): string {
		$value = self::remove_controls( $value );
		$value = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$value = wp_strip_all_tags( $value );
		$value = preg_replace( '/\s+/u', ' ', $value ) ?? '';
		return trim( $value );
	}

	private static function remove_controls( string $value ): string {
		$value = str_replace( array( "\r\n", "\r" ), "\n", $value );
		$value = preg_replace( '/\x00/u', '', $value ) ?? '';
		$value = preg_replace( '/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value ) ?? '';
		return $value;
	}

	private static function char_length( string $value ): int {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $value, 'UTF-8' );
		}
		$count = preg_match_all( '/./us', $value, $unused );
		return false === $count ? strlen( $value ) : $count;
	}

	private static function error( string $code, string $message ): \WP_Error {
		return new \WP_Error( $code, $message, array( 'status' => 400 ) );
	}
}
