<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
}

namespace BDC\KnowledgeBase {
	$root = __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/';
	require_once $root . 'class-content-normalizer.php';
	require_once $root . 'class-semantic-structure.php';

	function ns_assert( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	function local_fragments( string $label ): array {
		return array(
			array(
				'kind' => 'list_item', 'text' => $label . ' item', 'source' => 'test', 'ordinal' => 0,
				'meta' => array(
					'list_id' => 'list-0', 'list_type' => 'unordered', 'depth' => 0,
					'item_index' => 0, 'item_id' => 'list-0-item-0', 'parent_item_id' => '',
				),
			),
			array(
				'kind' => 'table_row', 'text' => $label . ' | 1', 'source' => 'test', 'ordinal' => 1,
				'meta' => array(
					'table_id' => 'table-0', 'row_index' => 0,
					'cells' => array(
						array( 'cell_index' => 0, 'kind' => 'data', 'text' => $label, 'colspan' => 1, 'rowspan' => 1 ),
						array( 'cell_index' => 1, 'kind' => 'data', 'text' => '1', 'colspan' => 1, 'rowspan' => 1 ),
					),
				),
			),
		);
	}

	$a = Content_Normalizer::namespace_structural_ids( local_fragments( 'A' ), 'part-a' );
	$b = Content_Normalizer::namespace_structural_ids( local_fragments( 'B' ), 'part-b' );
	$merged = array_merge( $a, $b );
	foreach ( $merged as $i => &$fragment ) {
		$fragment['ordinal'] = $i;
	}
	unset( $fragment );

	$sections = Semantic_Structure::sections( $merged );
	$blocks = Semantic_Structure::blocks( $sections );
	$lists = array_values( array_filter( $blocks, static fn ( array $block ): bool => 'list' === ( $block['kind'] ?? '' ) ) );
	$tables = array_values( array_filter( $blocks, static fn ( array $block ): bool => 'table' === ( $block['kind'] ?? '' ) ) );

	ns_assert( 2 === count( $lists ), 'Duas listas independentes devem permanecer duas listas.' );
	ns_assert( 2 === count( $tables ), 'Duas tabelas independentes devem permanecer duas tabelas.' );
	ns_assert( $lists[0]['list_id'] !== $lists[1]['list_id'], 'list_id deve ser único entre merges.' );
	ns_assert( $tables[0]['table_id'] !== $tables[1]['table_id'], 'table_id deve ser único entre merges.' );
	ns_assert( str_starts_with( $lists[0]['items'][0]['item_id'], 'part-a::' ), 'item_id deve acompanhar namespace da origem.' );
	ns_assert( str_starts_with( $lists[1]['items'][0]['item_id'], 'part-b::' ), 'item_id deve acompanhar namespace da origem.' );

	echo "RESULT passed=6 failed=0\n";
}
