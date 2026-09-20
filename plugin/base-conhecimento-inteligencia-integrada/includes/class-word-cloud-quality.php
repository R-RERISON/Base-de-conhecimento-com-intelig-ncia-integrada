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
		$value = html_entity_decode( $value, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$value = remove_accents( strtolower( $value ) );
		$value = preg_replace( '/[^a-z0-9]+/u', ' ', $value ) ?? '';
		$value = preg_replace( '/\s+/u', ' ', $value ) ?? '';
		return trim( $value );
	}

	/** @return array<string,bool> */
	public static function stopwords(): array {
		$words = array(
			'a','ao','aos','as','de','da','das','do','dos','e','em','na','nas','no','nos','o','os','ou','por','que','se','sem',
			'sua','suas','seu','seus','um','uns','uma','umas','para','com','como','mais','menos','tambem','também','entre','quando',
			'onde','qual','quais','quem','ser','ter','foi','sao','são','esta','este','essa','esse','isso','isto','ate','até','apos',
			'após','antes','durante','caso','cada','todos','todas','todo','toda','pelo','pela','pelos','pelas','num','numa','nosso',
			'nossa','nossos','nossas','sera','será','serao','serão','sendo','tem','têm','nao','não','sim','ja','já','ainda',
		);
		$out = array();
		foreach ( $words as $word ) {
			$key = self::canonical( $word );
			if ( '' !== $key ) {
				$out[ $key ] = true;
			}
		}
		return $out;
	}

	/** @return array<int,string> */
	public static function tokens( string $value, int $limit = 120 ): array {
		$canonical = self::canonical( $value );
		if ( '' === $canonical ) {
			return array();
		}
		$stop = self::stopwords();
		$tokens = preg_split( '/\s+/u', $canonical, -1, PREG_SPLIT_NO_EMPTY );
		$out = array();
		foreach ( (array) $tokens as $token ) {
			if ( strlen( $token ) < 3 || ctype_digit( $token ) || isset( $stop[ $token ] ) || self::looks_like_fragment( $token ) ) {
				continue;
			}
			$out[] = $token;
			if ( count( $out ) >= max( 1, $limit ) ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * @param array<string,string> $block_map
	 * @return array{label:string,canonical:string}|null
	 */
	public static function phrase_candidate( string $value, array $block_map ): ?array {
		$label = trim( wp_strip_all_tags( $value ) );
		$label = preg_replace( '/^\s*\d+(?:\.\d+)*\s*[\-.:)]*\s*/u', '', $label ) ?? $label;
		$label = trim( preg_replace( '/\s+/u', ' ', $label ) ?? $label );
		$canonical = self::canonical( $label );
		if ( '' === $canonical || strlen( $canonical ) < 5 || strlen( $canonical ) > 96 ) {
			return null;
		}
		$parts = preg_split( '/\s+/u', $canonical, -1, PREG_SPLIT_NO_EMPTY );
		$count = count( (array) $parts );
		if ( $count < 1 || $count > 10 || self::is_generic_heading( $canonical ) ) {
			return null;
		}

		$stop = self::stopwords();
		$meaningful = 0;
		$has_numeric = false;
		foreach ( (array) $parts as $part ) {
			if ( ctype_digit( $part ) ) {
				$has_numeric = true;
				continue;
			}
			if ( isset( $stop[ $part ] ) || isset( $block_map[ $part ] ) || self::looks_like_fragment( $part ) ) {
				continue;
			}
			++$meaningful;
		}
		$minimum_meaningful = ( 1 === $count || $has_numeric ) ? 1 : 2;
		if ( $meaningful < $minimum_meaningful ) {
			return null;
		}

		return array( 'label' => $label, 'canonical' => $canonical );
	}

	public static function looks_like_fragment( string $token ): bool {
		if ( preg_match( '/^[a-f0-9]{16,}$/', $token ) ) {
			return true;
		}
		if ( preg_match( '/^\d+[a-z]+$/', $token ) && strlen( $token ) > 12 ) {
			return true;
		}
		if ( strlen( $token ) <= 4 && in_array( $token, array( 'ncia','mail','html','http','https' ), true ) ) {
			return true;
		}
		return str_starts_with( $token, 'wp-' ) || str_starts_with( $token, 'elementor-' );
	}

	public static function is_generic_heading( string $canonical ): bool {
		return in_array(
			$canonical,
			array(
				'objetivo','abrangencia','conceitos e definicoes','conceitos definicoes','regras','atividades','acesso',
				'perguntas frequentes','informacoes gerais','informacao geral','procedimento','procedimentos','observacoes',
				'introducao','conclusao','referencias',
			),
			true
		);
	}

	/**
	 * @param array<string,mixed> $row
	 * @return array<string,mixed>
	 */
	public static function classify( array $row, bool $allowlisted ): array {
		$score = (float) ( $row['score'] ?? 0 );
		$sources = array_keys( (array) ( $row['sources'] ?? array() ) );
		$documents = array_keys( (array) ( $row['documents'] ?? array() ) );
		$document_count = count( $documents );
		$taxonomy_count = (int) ( $row['taxonomy_count'] ?? 0 );
		$is_phrase = ! empty( $row['is_phrase'] );

		$has_title = in_array( 'title', $sources, true );
		$has_heading = in_array( 'heading', $sources, true );
		$has_title_phrase = in_array( 'title_phrase', $sources, true );
		$has_heading_phrase = in_array( 'heading_phrase', $sources, true );
		$has_taxonomy = in_array( 'taxonomy', $sources, true );
		$content_only = 1 === count( $sources ) && in_array( 'content', $sources, true );

		$status = 'candidate';
		$reason = 'insufficient_semantic_signal';
		if ( $allowlisted ) {
			$status = 'promoted';
			$reason = 'governed_allowlist';
		} elseif ( $has_taxonomy && $taxonomy_count >= 2 ) {
			$status = 'mature';
			$reason = 'taxonomy_signal';
		} elseif ( $is_phrase && ( $has_title_phrase || $has_heading_phrase ) && $score >= 10 ) {
			$status = 'mature';
			$reason = $has_title_phrase ? 'title_phrase' : 'heading_phrase';
		} elseif ( ( $has_title || $has_heading ) && $document_count >= 3 && $score >= 18 ) {
			$status = 'mature';
			$reason = 'repeated_structural_signal';
		} elseif ( ( $has_title || $has_heading ) && $document_count >= 2 && $score >= 10 ) {
			$status = 'observed';
			$reason = 'observed_structural_signal';
		} elseif ( $content_only ) {
			$reason = 'content_only_not_public';
		}

		$row['status'] = $status;
		$row['quality_reason'] = $reason;
		$row['public_allowed'] = in_array( $status, array( 'observed', 'mature', 'promoted' ), true );
		$row['source_count'] = count( $sources );
		$row['document_count'] = $document_count;
		unset( $row['documents'], $row['label_priority'] );
		return $row;
	}
}
