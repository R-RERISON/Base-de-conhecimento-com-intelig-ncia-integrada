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

	function anchor_assert( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}

	$parent = Content_Normalizer::fragment(
		'list_item',
		'',
		'post_content',
		0,
		array(
			'list_id'        => 'list-0',
			'list_type'      => 'unordered',
			'depth'          => 0,
			'item_index'     => 0,
			'item_id'        => 'list-0-item-0',
			'parent_item_id' => '',
		)
	);

	anchor_assert( is_array( $parent ), 'Empty list item must be preserved as a structural anchor.' );
	anchor_assert( '' === ( $parent['text'] ?? null ), 'Structural anchor must not invent text.' );
	anchor_assert( true === ( $parent['meta']['structural_anchor'] ?? false ), 'Structural anchor marker must be explicit.' );

	$child = Content_Normalizer::fragment(
		'list_item',
		'Filho',
		'post_content',
		1,
		array(
			'list_id'        => 'list-1',
			'list_type'      => 'unordered',
			'depth'          => 1,
			'item_index'     => 0,
			'item_id'        => 'list-1-item-0',
			'parent_item_id' => 'list-0-item-0',
		)
	);

	$sections = Semantic_Structure::sections( array( $parent, $child ) );
	anchor_assert( 2 === count( $sections ), 'Structural anchor must survive section projection.' );

	$blocks = Semantic_Structure::blocks( $sections );
	anchor_assert( 1 === count( $blocks ), 'Expected one top-level list.' );
	anchor_assert( 'list' === ( $blocks[0]['kind'] ?? '' ), 'Top-level block must be a list.' );
	anchor_assert( '' === ( $blocks[0]['items'][0]['text'] ?? null ), 'Anchor must remain textless in semantic block.' );
	anchor_assert( 1 === count( $blocks[0]['items'][0]['children'] ?? array() ), 'Nested list must attach to the empty parent anchor.' );

	$extraction = array(
		'source_kind' => 'legacy_html',
		'structure'   => array(
			'headings'    => 0,
			'lists'       => 2,
			'list_items'  => 2,
			'tables'      => 0,
			'table_rows'  => 0,
			'table_cells' => 0,
		),
		'warnings'    => array(),
	);
	$readiness = Semantic_Structure::ai_readiness( $extraction, $sections, $blocks );
	anchor_assert( true === ( $readiness['structure_complete'] ?? false ), 'Structural counts must reconcile.' );
	anchor_assert( 'candidate_ready' === ( $readiness['status'] ?? '' ), 'Anchor alone must not create a readiness blocker.' );

	echo "RESULT passed=9 failed=0\n";
}
