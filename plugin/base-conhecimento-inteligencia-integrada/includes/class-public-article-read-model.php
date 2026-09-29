<?php
/**
 * Public Article Reader composed read model.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Public_Article_Read_Model {

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	public static function read( int $post_id ): array|\WP_Error {
		$post = get_post( $post_id );
		if ( ! is_object( $post ) || 'post' !== (string) ( $post->post_type ?? '' ) ) {
			return new \WP_Error( 'bdc_kb_public_article_invalid', 'Artigo inválido.' );
		}

		$summary = Summary_Store::read( $post_id );
		if ( is_wp_error( $summary ) ) {
			$summary = array();
		}

		$classification = Classification_Store::read( $post_id );
		if ( is_wp_error( $classification ) ) {
			$classification = array( 'terms' => array() );
		}

		$tips = Helpful_Tips_Store::read( $post_id );
		if ( is_wp_error( $tips ) ) {
			$tips = array();
		}

		$categories = get_the_category( $post_id );
		$category = ! empty( $categories ) && is_object( $categories[0] )
			? (string) $categories[0]->name
			: '';

		return array(
			'post_id' => $post_id,
			'title' => (string) $post->post_title,
			'category' => $category,
			'author' => get_the_author_meta( 'display_name', (int) $post->post_author ),
			'published' => get_the_date( 'd/m/Y', $post_id ),
			'updated' => get_the_modified_date( 'd/m/Y', $post_id ),
			'tips' => $tips,
			'summary_items' => self::summary_items( $post_id, $summary, $classification ),
			'source_kind' => self::source_kind( $post_id ),
		);
	}

	/**
	 * @param array<string,mixed> $summary
	 * @param array<string,mixed> $classification
	 * @return array<int,array{label:string,value:string,kind:string}>
	 */
	private static function summary_items( int $post_id, array $summary, array $classification ): array {
		$items = array();

		self::append( $items, 'Objetivo', (string) ( $summary['objective'] ?? '' ), 'objective' );
		self::append_terms( $items, 'Equipe responsável', $classification, 'responsible_team' );
		self::append_terms( $items, 'Item de Catálogo', $classification, 'catalog_item' );

		self::append(
			$items,
			'Serviço Afetado',
			self::legacy_meta_string( $post_id, '_bdc_es_affected_service' ),
			'fact'
		);
		self::append(
			$items,
			'Sistemas envolvidos',
			self::legacy_meta_string( $post_id, '_bdc_es_systems_involved' ),
			'fact'
		);

		self::append_terms( $items, 'Público Alvo', $classification, 'audience' );
		self::append( $items, 'Escalonamento', (string) ( $summary['escalation'] ?? '' ), 'fact' );
		self::append( $items, 'IMPORTANTE', (string) ( $summary['important'] ?? '' ), 'important' );

		return $items;
	}

	/**
	 * @param array<int,array{label:string,value:string,kind:string}> $items
	 */
	private static function append( array &$items, string $label, string $value, string $kind ): void {
		$value = trim( $value );
		if ( '' === $value ) {
			return;
		}
		$items[] = array(
			'label' => $label,
			'value' => $value,
			'kind' => $kind,
		);
	}

	/**
	 * @param array<int,array{label:string,value:string,kind:string}> $items
	 * @param array<string,mixed> $classification
	 */
	private static function append_terms( array &$items, string $label, array $classification, string $field ): void {
		$definitions = Classification_Contract::fields();
		if ( ! isset( $definitions[ $field ] ) ) {
			return;
		}

		$ids = array_map( 'intval', (array) ( $classification['terms'][ $field ] ?? array() ) );
		if ( empty( $ids ) ) {
			return;
		}

		$terms = get_terms(
			array(
				'taxonomy' => (string) $definitions[ $field ]['taxonomy'],
				'include' => $ids,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			return;
		}

		$names = array();
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && isset( $term->name ) ) {
				$names[] = (string) $term->name;
			}
		}
		self::append( $items, $label, implode( "\n", $names ), 'fact' );
	}

	private static function legacy_meta_string( int $post_id, string $key ): string {
		$value = get_post_meta( $post_id, $key, true );
		return is_scalar( $value ) ? trim( sanitize_textarea_field( (string) $value ) ) : '';
	}

	private static function source_kind( int $post_id ): string {
		$extracted = Content_Extractor::extract( $post_id );
		if ( $extracted instanceof \WP_Error ) {
			return 'unknown';
		}
		return (string) ( $extracted['source_kind'] ?? 'unknown' );
	}
}
