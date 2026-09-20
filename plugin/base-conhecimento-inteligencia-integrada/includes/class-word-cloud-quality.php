<?php
/**
 * BDC Word Cloud deterministic quality rules.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Word_Cloud_Quality {

	public static function canonical( string $value ): string {
		$value = wp_strip_all_tags( $value );
		$value = remove_accents( strtolower( $value ) );
		$value = preg_replace( '/[^a-z0-9]+/u', ' ', $value ) ?? '';
		$value = preg_replace( '/\s+/u', ' ', $value ) ?? '';
		return trim( $value );
	}

	/** @return array<int,string> */
	public static function tokens( string $value, int $limit = 120 ): array {
		$canonical = self::canonical( $value );
		if ( '' === $canonical ) {
			return array();
		}
		$tokens = preg_split( '/\s+/u', $canonical, -1, PREG_SPLIT_NO_EMPTY );
		$out = array();
		foreach ( (array) $tokens as $token ) {
			if ( strlen( $token ) < 3 || ctype_digit( $token ) || self::looks_like_fragment( $token ) ) {
				continue;
			}
			$out[] = $token;
			if ( count( $out ) >= max( 1, $limit ) ) {
				break;
			}
		}
		return $out;
	}

	public static function looks_like_fragment( string $token ): bool {
		if ( preg_match( '/^[a-f0-9]{16,}$/', $token ) ) {
			return true;
		}
		if ( preg_match( '/^\d+[a-z]+$/', $token ) && strlen( $token ) > 12 ) {
			return true;
		}
		return str_starts_with( $token, 'wp-') || str_starts_with( $token, 'elementor-');
	}

	/**
	 * @param array<string,mixed> $row
	 * @return array<string,mixed>
	 */
	public static function classify( array $row, bool $allowlisted ): array {
		$score = (float) ( $row['score'] ?? 0 );
		$count = (int) ( $row['count'] ?? 0 );
		$sources = array_keys( (array) ( $row['sources'] ?? array() ) );
		$source_count = count( $sources );

		$status = 'candidate';
		if ( $allowlisted ) {
			$status = 'promoted';
		} elseif ( $score >= 30 || ( $source_count >= 2 && $count >= 3 ) ) {
			$status = 'mature';
		} elseif ( $score >= 12 || $count >= 2 ) {
			$status = 'observed';
		}

		$row['status'] = $status;
		$row['public_allowed'] = in_array( $status, array( 'observed', 'mature', 'promoted' ), true );
		$row['source_count'] = $source_count;
		return $row;
	}
}