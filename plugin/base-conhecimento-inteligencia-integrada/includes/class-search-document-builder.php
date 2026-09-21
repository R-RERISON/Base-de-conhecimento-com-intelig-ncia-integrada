<?php
/**
 * Constrói Search Documents post-level derivados do WordPress.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Search_Document_Builder {

	public const VERSION = 'search-document-v1.0.0';

	/** @var array<int,string> */
	private const ALLOWED_STATUSES = array( 'publish', 'draft', 'pending', 'private', 'future' );

	/** @var array<int,string> */
	private const BODY_KINDS = array(
		'paragraph',
		'list_item',
		'table_caption',
		'table_row',
		'quote',
		'code',
		'image',
	);

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function build( int $post_id ): array|\WP_Error {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || Meta_Contract::POST_TYPE !== (string) ( $post->post_type ?? '' ) ) {
			return new \WP_Error( 'search_document_invalid_post', 'Post inválido para Search Document.', array( 'status' => 404 ) );
		}

		$status = (string) ( $post->post_status ?? '' );
		if ( ! in_array( $status, self::ALLOWED_STATUSES, true ) ) {
			return new \WP_Error( 'search_document_status_not_indexable', 'Status não indexável no Search v1.', array( 'status' => 422 ) );
		}

		$summary_parts = array();
		foreach ( Meta_Contract::fields() as $name => $definition ) {
			$value = get_post_meta( $post_id, (string) $definition['key'], true );
			$summary_parts[ (string) $name ] = is_scalar( $value ) ? (string) $value : '';
		}

		$taxonomy = self::taxonomy_source( $post_id );
		$taxonomy_error = $taxonomy instanceof \WP_Error;
		$taxonomy_rows = $taxonomy_error ? array() : $taxonomy;

		$extraction = Content_Extractor::extract( $post_id );
		$extractor_error = $extraction instanceof \WP_Error;
		$source_kind = $extractor_error ? 'unknown' : sanitize_key( (string) ( $extraction['source_kind'] ?? 'unknown' ) );
		$fragments = $extractor_error ? array() : (array) ( $extraction['fragments'] ?? array() );

		$material = $extractor_error
			? self::raw_source_material( $post_id, (string) $post->post_content )
			: (array) ( $extraction['source_material'] ?? self::raw_source_material( $post_id, (string) $post->post_content ) );

		$components = array(
			'post_id' => $post_id,
			'post_title' => (string) $post->post_title,
			'post_modified_gmt' => (string) $post->post_modified_gmt,
			'summary_parts' => $summary_parts,
			'taxonomy_rows' => $taxonomy_rows,
			'fragments' => $fragments,
			'source_kind' => $source_kind,
			'source_material' => $material,
			'extractor_error' => $extractor_error,
			'taxonomy_error' => $taxonomy_error,
		);

		$document = self::compose( $components );
		$document['diagnostics'] = array(
			'extractor_error_code' => $extractor_error ? $extraction->get_error_code() : '',
			'taxonomy_error_code' => $taxonomy_error ? $taxonomy->get_error_code() : '',
			'extractor_warning_count' => $extractor_error ? 0 : count( (array) ( $extraction['warnings'] ?? array() ) ),
		);

		return $document;
	}

	/**
	 * Composição determinística e testável sem I/O.
	 *
	 * @param array<string,mixed> $components
	 * @return array<string,mixed>
	 */
	public static function compose( array $components ): array {
		$post_id = max( 0, (int) ( $components['post_id'] ?? 0 ) );
		$title_raw = (string) ( $components['post_title'] ?? '' );
		$summary_parts = is_array( $components['summary_parts'] ?? null ) ? $components['summary_parts'] : array();
		$taxonomy_rows = is_array( $components['taxonomy_rows'] ?? null ) ? $components['taxonomy_rows'] : array();
		$fragments = is_array( $components['fragments'] ?? null ) ? $components['fragments'] : array();
		$source_kind = sanitize_key( (string) ( $components['source_kind'] ?? 'unknown' ) );
		$extractor_error = true === ( $components['extractor_error'] ?? false );
		$taxonomy_error = true === ( $components['taxonomy_error'] ?? false );

		$summary_raw = implode(
			' ',
			array(
				(string) ( $summary_parts['objective'] ?? '' ),
				(string) ( $summary_parts['escalation'] ?? '' ),
				(string) ( $summary_parts['important'] ?? '' ),
			)
		);

		$headings = array();
		$body = array();
		foreach ( $fragments as $fragment ) {
			if ( ! is_array( $fragment ) ) {
				continue;
			}
			$kind = sanitize_key( (string) ( $fragment['kind'] ?? '' ) );
			$text = (string) ( $fragment['text'] ?? '' );
			if ( '' === trim( $text ) ) {
				continue;
			}
			if ( 'heading' === $kind ) {
				$headings[] = $text;
				continue;
			}
			if ( in_array( $kind, self::BODY_KINDS, true ) ) {
				$body[] = $text;
			}
		}

		$taxonomy_raw_parts = array();
		foreach ( $taxonomy_rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = trim( (string) ( $row['name'] ?? '' ) );
			$slug = trim( (string) ( $row['slug'] ?? '' ) );
			if ( '' !== $name ) {
				$taxonomy_raw_parts[] = $name;
			}
			if ( '' !== $slug ) {
				$taxonomy_raw_parts[] = $slug;
			}
		}

		$document_state = ( $extractor_error || $taxonomy_error ) ? 'degraded' : 'ready';

		$document = array(
			'post_id' => $post_id,
			'document_state' => $document_state,
			'source_kind' => '' !== $source_kind ? $source_kind : 'unknown',
			'title_norm' => Search_Query_Normalizer::normalize_document_text( $title_raw ),
			'summary_norm' => Search_Query_Normalizer::normalize_document_text( $summary_raw ),
			'headings_norm' => Search_Query_Normalizer::normalize_document_text( implode( ' ', $headings ) ),
			'taxonomy_norm' => Search_Query_Normalizer::normalize_document_text( implode( ' ', $taxonomy_raw_parts ) ),
			'body_norm' => Search_Query_Normalizer::normalize_document_text( implode( ' ', $body ) ),
			'document_version' => self::VERSION,
			'normalizer_version' => Search_Query_Normalizer::VERSION,
			'post_modified_gmt' => (string) ( $components['post_modified_gmt'] ?? '' ),
			'indexed_at_gmt' => function_exists( 'current_time' ) ? (string) current_time( 'mysql', true ) : gmdate( 'Y-m-d H:i:s' ),
		);

		$source_material = is_array( $components['source_material'] ?? null ) ? $components['source_material'] : array();
		$document['source_hash'] = Canonical_JSON::hash(
			array(
				'post_id' => $post_id,
				'post_title' => $title_raw,
				'summary_parts' => array(
					'objective' => (string) ( $summary_parts['objective'] ?? '' ),
					'escalation' => (string) ( $summary_parts['escalation'] ?? '' ),
					'important' => (string) ( $summary_parts['important'] ?? '' ),
				),
				'taxonomy_rows' => $taxonomy_rows,
				'post_content_sha256' => (string) ( $source_material['post_content_sha256'] ?? '' ),
				'elementor_data_sha256' => (string) ( $source_material['elementor_data_sha256'] ?? '' ),
				'document_version' => self::VERSION,
				'normalizer_version' => Search_Query_Normalizer::VERSION,
			)
		);

		$document['document_hash'] = Canonical_JSON::hash(
			array(
				'title_norm' => $document['title_norm'],
				'summary_norm' => $document['summary_norm'],
				'headings_norm' => $document['headings_norm'],
				'taxonomy_norm' => $document['taxonomy_norm'],
				'body_norm' => $document['body_norm'],
				'document_state' => $document['document_state'],
				'source_kind' => $document['source_kind'],
				'document_version' => self::VERSION,
				'normalizer_version' => Search_Query_Normalizer::VERSION,
			)
		);

		return $document;
	}

	/**
	 * @return array<int,array{taxonomy:string,term_id:int,name:string,slug:string}>|\WP_Error
	 */
	private static function taxonomy_source( int $post_id ): array|\WP_Error {
		$rows = array();

		foreach ( Classification_Contract::fields() as $definition ) {
			$taxonomy = (string) $definition['taxonomy'];
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( is_wp_error( $terms ) ) {
				return $terms;
			}
			if ( false === $terms || empty( $terms ) ) {
				continue;
			}

			usort(
				$terms,
				static fn ( object $a, object $b ): int => (int) $a->term_id <=> (int) $b->term_id
			);

			foreach ( $terms as $term ) {
				$rows[] = array(
					'taxonomy' => $taxonomy,
					'term_id' => (int) $term->term_id,
					'name' => (string) $term->name,
					'slug' => (string) $term->slug,
				);
			}
		}

		return $rows;
	}

	/**
	 * @return array{post_content_sha256:string,elementor_data_sha256:string}
	 */
	private static function raw_source_material( int $post_id, string $post_content ): array {
		$elementor = get_post_meta( $post_id, '_elementor_data', true );
		return array(
			'post_content_sha256' => hash( 'sha256', $post_content ),
			'elementor_data_sha256' => hash( 'sha256', self::stable_value( $elementor ) ),
		);
	}

	private static function stable_value( mixed $value ): string {
		if ( is_string( $value ) ) {
			return $value;
		}
		if ( is_scalar( $value ) || null === $value ) {
			return (string) $value;
		}
		if ( function_exists( 'wp_json_encode' ) ) {
			$json = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			if ( is_string( $json ) ) {
				return $json;
			}
		}
		$json = json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		return is_string( $json ) ? $json : '';
	}
}
