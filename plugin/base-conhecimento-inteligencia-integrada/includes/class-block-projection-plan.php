<?php
/**
 * Deterministic, read-only Block Projection Plan for SPEC-004 / G-245 rebaseline.
 *
 * @package BDC_Knowledge_Base
 */

namespace BDC\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Block_Projection_Plan {

	public const SCHEMA_VERSION = '1.0.0';
	public const TARGET = 'wordpress_core_blocks';

	/** @return array<string,mixed>|\WP_Error */
	public static function from_document( array $document ): array|\WP_Error {
		$post_id = (int) ( $document['post_id'] ?? 0 );
		$source_hash = strtolower( trim( (string) ( $document['source_hash'] ?? '' ) ) );
		$source_kind = (string) ( $document['source_kind'] ?? 'empty' );
		$readiness = is_array( $document['ai_readiness'] ?? null ) ? $document['ai_readiness'] : array();
		$status = (string) ( $readiness['status'] ?? 'not_ready' );
		$blocks = is_array( $document['blocks'] ?? null ) ? $document['blocks'] : array();

		if ( $post_id <= 0 || ! self::is_sha256( $source_hash ) ) {
			return new \WP_Error( 'bdc_kb_block_projection_invalid_document', 'Knowledge Document inválido para Block Projection.' );
		}

		$warnings = array();
		$projected = array();
		$review_required = false;
		$blocking = false;

		if ( 'not_ready' === $status ) {
			$blocking = true;
			$warnings[] = 'KNOWLEDGE_DOCUMENT_NOT_READY';
		} elseif ( 'review_required' === $status ) {
			$review_required = true;
			$warnings[] = 'KNOWLEDGE_DOCUMENT_REVIEW_REQUIRED';
		}

		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			$mapped = self::map_block( $block );
			if ( $mapped instanceof \WP_Error ) {
				$review_required = true;
				$warnings[] = $mapped->get_error_code();
				continue;
			}
			if ( is_array( $mapped['warnings'] ?? null ) && ! empty( $mapped['warnings'] ) ) {
				$review_required = true;
				$warnings = array_merge( $warnings, array_map( 'strval', $mapped['warnings'] ) );
			}
			unset( $mapped['warnings'] );
			$mapped['ordinal'] = count( $projected );
			$projected[] = $mapped;
		}

		if ( 'empty' === $source_kind && empty( $projected ) ) {
			$plan_status = 'not_applicable';
			$strategy = 'noop_empty';
		} elseif ( 'gutenberg' === $source_kind && ! $blocking && ! $review_required ) {
			$plan_status = 'native_noop';
			$strategy = 'preserve_core_blocks';
		} elseif ( $blocking ) {
			$plan_status = 'blocked';
			$strategy = 'preserve_source';
		} elseif ( $review_required ) {
			$plan_status = 'review_required';
			$strategy = 'manual_review_before_block_projection';
		} else {
			$plan_status = 'projectable';
			$strategy = 'project_to_core_blocks';
		}

		$plan = array(
			'schema_version' => self::SCHEMA_VERSION,
			'post_id' => $post_id,
			'source_kind' => $source_kind,
			'source_hash_before' => $source_hash,
			'target' => self::TARGET,
			'plan_status' => $plan_status,
			'projection_strategy' => $strategy,
			'blocks' => $projected,
			'warnings' => array_values( array_unique( $warnings ) ),
			'requires_review' => $review_required,
			'writer_allowed' => false,
			'migration_execution_allowed' => false,
			'serialized_post_content' => null,
			'safety' => array(
				'read_only' => true,
				'persists_state' => false,
				'writes_post_content' => false,
				'writes_elementor_data' => false,
				'calls_external_network' => false,
				'executes_shortcodes' => false,
				'renders_dynamic_blocks' => false,
				'depends_on_gutenberg_plugin' => false,
			),
			'block_projection_hash' => '',
		);

		$payload = $plan;
		unset( $payload['block_projection_hash'] );
		try {
			$plan['block_projection_hash'] = Canonical_JSON::hash( $payload );
		} catch ( \JsonException $error ) {
			return new \WP_Error( 'bdc_kb_block_projection_hash_failed', 'Falha ao calcular block_projection_hash.' );
		}
		return $plan;
	}

	/** @return array<string,mixed>|\WP_Error */
	private static function map_block( array $block ): array|\WP_Error {
		$kind = (string) ( $block['kind'] ?? '' );
		$text = (string) ( $block['text'] ?? '' );
		$meta = is_array( $block['meta'] ?? null ) ? $block['meta'] : array();

		if ( 'heading' === $kind ) {
			return array(
				'block_name' => 'core/heading',
				'attrs' => array( 'level' => max( 1, min( 6, (int) ( $meta['level'] ?? 2 ) ) ) ),
				'text' => $text,
				'inner_blocks' => array(),
			);
		}
		if ( 'paragraph' === $kind ) {
			return array( 'block_name' => 'core/paragraph', 'attrs' => array(), 'text' => $text, 'inner_blocks' => array() );
		}
		if ( 'code' === $kind ) {
			return array( 'block_name' => 'core/code', 'attrs' => array(), 'text' => $text, 'inner_blocks' => array() );
		}
		if ( 'list' === $kind ) {
			return self::map_list( $block );
		}
		if ( 'table' === $kind ) {
			return self::map_table( $block );
		}
		return new \WP_Error(
			'BLOCK_PROJECTION_UNSUPPORTED_KIND:' . ( '' !== $kind ? $kind : 'unknown' ),
			'Tipo sem mapeamento seguro para Core Block.'
		);
	}

	/** @return array<string,mixed> */
	private static function map_list( array $block ): array {
		$list_type = strtolower( (string) ( $block['list_type'] ?? '' ) );
		$ordered = in_array( $list_type, array( 'ol', 'ordered', 'numbered' ), true );
		$warnings = array();
		if ( ! in_array( $list_type, array( 'ul', 'unordered', 'bullet', 'ol', 'ordered', 'numbered' ), true ) ) {
			$warnings[] = 'BLOCK_PROJECTION_LIST_TYPE_REVIEW:' . ( '' !== $list_type ? $list_type : 'unknown' );
		}
		$items = array();
		foreach ( (array) ( $block['items'] ?? array() ) as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$children = array();
			foreach ( (array) ( $item['children'] ?? array() ) as $child ) {
				if ( ! is_array( $child ) ) {
					continue;
				}
				$children[] = self::map_list( $child );
			}
			$items[] = array(
				'block_name' => 'core/list-item',
				'attrs' => array(),
				'text' => (string) ( $item['text'] ?? '' ),
				'inner_blocks' => $children,
			);
		}
		return array(
			'block_name' => 'core/list',
			'attrs' => array( 'ordered' => $ordered ),
			'text' => '',
			'inner_blocks' => $items,
			'warnings' => $warnings,
		);
	}

	/** @return array<string,mixed> */
	private static function map_table( array $block ): array {
		$warnings = array();
		$rows = array();
		foreach ( (array) ( $block['rows'] ?? array() ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$cells = array();
			foreach ( (array) ( $row['cells'] ?? array() ) as $cell ) {
				if ( ! is_array( $cell ) ) {
					continue;
				}
				$rowspan = max( 1, (int) ( $cell['rowspan'] ?? 1 ) );
				$colspan = max( 1, (int) ( $cell['colspan'] ?? 1 ) );
				if ( 1 !== $rowspan || 1 !== $colspan ) {
					$warnings[] = 'BLOCK_PROJECTION_TABLE_SPAN_REVIEW';
				}
				$cells[] = array(
					'text' => (string) ( $cell['text'] ?? '' ),
					'tag' => (string) ( $cell['tag'] ?? 'td' ),
					'rowspan' => $rowspan,
					'colspan' => $colspan,
				);
			}
			$rows[] = array( 'cells' => $cells );
		}
		return array(
			'block_name' => 'core/table',
			'attrs' => array(),
			'text' => '',
			'table' => array(
				'caption' => (string) ( $block['caption'] ?? '' ),
				'rows' => $rows,
			),
			'inner_blocks' => array(),
			'warnings' => array_values( array_unique( $warnings ) ),
		);
	}

	private static function is_sha256( string $value ): bool {
		return 1 === preg_match( '/^[a-f0-9]{64}$/', $value );
	}
}
