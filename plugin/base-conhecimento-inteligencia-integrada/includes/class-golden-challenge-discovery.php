<?php
/**
 * Descoberta read-only de Technical Challenge cases para T514.
 *
 * Não cria Golden real. Deriva casos do corpus publicado para provar que
 * Search Document futuro cobre Summary, Content Extractor e linguagem natural.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Golden_Challenge_Discovery {

	public const VERSION = '1.0.0';
	private const MAX_PER_CLASS = 3;

	/**
	 * @return array<string,mixed>
	 */
	public static function discover(): array {
		$post_ids = get_posts(
			array(
				'post_type' => Meta_Contract::POST_TYPE,
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields' => 'ids',
				'orderby' => 'ID',
				'order' => 'ASC',
				'no_found_rows' => true,
				'suppress_filters' => false,
			)
		);

		$records = array();
		$errors = array();
		$df = array();

		foreach ( array_map( 'intval', is_array( $post_ids ) ? $post_ids : array() ) as $post_id ) {
			$post = get_post( $post_id );
			if ( ! is_object( $post ) ) {
				continue;
			}

			$title = (string) ( $post->post_title ?? '' );
			$excerpt = (string) ( $post->post_excerpt ?? '' );
			$content = (string) ( $post->post_content ?? '' );
			$native = implode(
				' ',
				array(
					$title,
					$excerpt,
					wp_strip_all_tags( strip_shortcodes( $content ) ),
				)
			);

			$summary_parts = array();
			foreach ( Meta_Contract::fields() as $definition ) {
				$value = get_post_meta( $post_id, (string) $definition['key'], true );
				if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
					$summary_parts[] = (string) $value;
				}
			}
			$summary = implode( ' ', $summary_parts );

			$extraction = Content_Extractor::extract( $post_id );
			if ( $extraction instanceof \WP_Error ) {
				$errors[] = array(
					'post_id' => $post_id,
					'code' => $extraction->get_error_code(),
				);
				continue;
			}

			$semantic_parts = array();
			foreach ( (array) ( $extraction['fragments'] ?? array() ) as $fragment ) {
				if ( is_array( $fragment ) && isset( $fragment['text'] ) ) {
					$semantic_parts[] = (string) $fragment['text'];
				}
			}
			$semantic = implode( ' ', $semantic_parts );
			$source_kind = sanitize_key( (string) ( $extraction['source_kind'] ?? 'unknown' ) );

			$native_tokens = self::candidate_tokens( $native );
			$summary_tokens = self::candidate_tokens( $summary );
			$semantic_tokens = self::candidate_tokens( $semantic );
			$all_tokens = array_values( array_unique( array_merge( $native_tokens, $summary_tokens, $semantic_tokens ) ) );

			foreach ( $all_tokens as $token ) {
				$df[ $token ] = (int) ( $df[ $token ] ?? 0 ) + 1;
			}

			$records[] = array(
				'post_id' => $post_id,
				'title' => $title,
				'source_kind' => $source_kind,
				'native_tokens' => $native_tokens,
				'summary_tokens' => $summary_tokens,
				'semantic_tokens' => $semantic_tokens,
			);
		}

		$natural = array();
		$summary = array();
		$elementor = array();

		foreach ( $records as $record ) {
			if (
				count( $natural ) < self::MAX_PER_CLASS
				&& in_array( 'natural_language', Golden_Diversity_Validator::classify_query( (string) $record['title'] ), true )
			) {
				$natural[] = self::case_row(
					'NL',
					count( $natural ) + 1,
					(string) $record['title'],
					(int) $record['post_id'],
					'title',
					(string) $record['source_kind'],
					array( 'natural_language' ),
					false,
					false,
					1
				);
			}

			if ( count( $summary ) < self::MAX_PER_CLASS ) {
				$token = self::best_unique_gap_token(
					(array) $record['summary_tokens'],
					(array) $record['native_tokens'],
					$df
				);
				if ( '' !== $token ) {
					$summary[] = self::case_row(
						'SUM',
						count( $summary ) + 1,
						$token,
						(int) $record['post_id'],
						'summary',
						(string) $record['source_kind'],
						array( 'summary_dependent' ),
						true,
						false,
						(int) ( $df[ $token ] ?? 0 )
					);
				}
			}

			if (
				count( $elementor ) < self::MAX_PER_CLASS
				&& in_array( (string) $record['source_kind'], array( 'elementor', 'mixed' ), true )
			) {
				$token = self::best_unique_gap_token(
					(array) $record['semantic_tokens'],
					(array) $record['native_tokens'],
					$df
				);
				if ( '' !== $token ) {
					$elementor[] = self::case_row(
						'ELM',
						count( $elementor ) + 1,
						$token,
						(int) $record['post_id'],
						'content_extractor',
						(string) $record['source_kind'],
						array( 'elementor_semantic_gap' ),
						false,
						true,
						(int) ( $df[ $token ] ?? 0 )
					);
				}
			}

			if (
				count( $natural ) >= self::MAX_PER_CLASS
				&& count( $summary ) >= self::MAX_PER_CLASS
				&& count( $elementor ) >= self::MAX_PER_CLASS
			) {
				break;
			}
		}

		$natural_fallback = false;
		if ( empty( $natural ) ) {
			foreach ( $records as $record ) {
				$title = trim( (string) ( $record['title'] ?? '' ) );
				if ( '' === $title || count( Golden_Candidate_Validator::tokens( $title ) ) > 8 ) {
					continue;
				}
				$natural[] = array(
					'id' => 'CH-NL-SYN-001',
					'query' => 'Como consultar ' . $title . '?',
					'expected_post_id' => (int) $record['post_id'],
					'origin' => 'synthetic',
					'declared_classes' => array( 'natural_language' ),
					'source_field' => 'title_synthetic_wrapper',
					'source_kind' => (string) $record['source_kind'],
					'summary_dependent' => false,
					'elementor_semantic_gap' => false,
					'document_frequency' => 0,
					'active_for_golden_blocking' => false,
				);
				$natural_fallback = true;
				break;
			}
		}

		$cases = array_merge( $natural, $summary, $elementor );

		return array(
			'version' => self::VERSION,
			'mode' => 'corpus_derived_technical_challenge',
			'corpus_publish_count' => count( $records ),
			'errors' => $errors,
			'counts' => array(
				'natural_language' => count( $natural ),
				'summary_dependent' => count( $summary ),
				'elementor_semantic_gap' => count( $elementor ),
				'total' => count( $cases ),
			),
			'cases' => $cases,
			'natural_language_fallback_synthetic' => $natural_fallback,
			'complete' => ! empty( $natural ) && ! empty( $summary ) && ! empty( $elementor ),
			'rules' => array(
				'origin is always corpus_derived_challenge',
				'cases are technical coverage, never represented as real user queries',
				'summary/elementor gap token must be absent from native text and unique across the analyzed projection corpus',
				'natural-language prefers an existing published title already written in natural-language form',
				'if none exists, a synthetic wrapper is allowed only as Technical Challenge and remains origin=synthetic',
			),
		);
	}

	/**
	 * Tokens elegíveis para discovery.
	 *
	 * @return array<int,string>
	 */
	public static function candidate_tokens( string $text ): array {
		$tokens = Golden_Candidate_Validator::tokens( $text );
		$stop = array_flip(
			array(
				'para','como','com','sem','uma','umas','uns','dos','das','que','por','pelo','pela',
				'este','esta','esse','essa','isso','aqui','mais','menos','sobre','entre','onde','quando',
				'qual','quais','fazer','acesso','usuario','usuarios','sistema','servico','servicos',
				'bacen','banco','central','brasil','novo','nova','dados','informacao','informacoes',
			)
		);

		$out = array();
		foreach ( $tokens as $token ) {
			if ( strlen( $token ) < 4 || isset( $stop[ $token ] ) || ctype_digit( $token ) ) {
				continue;
			}
			$out[] = $token;
		}
		return array_values( array_unique( $out ) );
	}

	/**
	 * Escolhe token presente na projeção derivada, ausente do native e único no corpus.
	 *
	 * @param array<int,string> $derived_tokens
	 * @param array<int,string> $native_tokens
	 * @param array<string,int> $document_frequency
	 */
	public static function best_unique_gap_token( array $derived_tokens, array $native_tokens, array $document_frequency ): string {
		$native = array_flip( $native_tokens );
		$candidates = array();

		foreach ( array_values( array_unique( $derived_tokens ) ) as $token ) {
			if ( isset( $native[ $token ] ) ) {
				continue;
			}
			if ( 1 !== (int) ( $document_frequency[ $token ] ?? 0 ) ) {
				continue;
			}
			$candidates[] = $token;
		}

		usort(
			$candidates,
			static function ( string $a, string $b ): int {
				$length_cmp = strlen( $b ) <=> strlen( $a );
				return 0 !== $length_cmp ? $length_cmp : strcmp( $a, $b );
			}
		);

		return $candidates[0] ?? '';
	}

	/**
	 * @param array<int,string> $declared_classes
	 * @return array<string,mixed>
	 */
	private static function case_row(
		string $prefix,
		int $ordinal,
		string $query,
		int $expected_post_id,
		string $source_field,
		string $source_kind,
		array $declared_classes,
		bool $summary_dependent,
		bool $elementor_semantic_gap,
		int $document_frequency
	): array {
		return array(
			'id' => sprintf( 'CH-%s-%03d', $prefix, $ordinal ),
			'query' => $query,
			'expected_post_id' => $expected_post_id,
			'origin' => 'corpus_derived_challenge',
			'declared_classes' => $declared_classes,
			'source_field' => $source_field,
			'source_kind' => $source_kind,
			'summary_dependent' => $summary_dependent,
			'elementor_semantic_gap' => $elementor_semantic_gap,
			'document_frequency' => $document_frequency,
			'active_for_golden_blocking' => false,
		);
	}
}
