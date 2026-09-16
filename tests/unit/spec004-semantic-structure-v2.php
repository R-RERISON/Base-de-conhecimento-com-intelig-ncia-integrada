<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	final class WP_Error {
		public function __construct( public string $code = '', public string $message = '', public mixed $data = null ) {}
	}
}

namespace BDC\KnowledgeBase {
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-content-normalizer.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-canonical-json.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-semantic-structure.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-knowledge-document.php';

	function assert_v2( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		echo "PASS {$message}\n";
	}

	$fragments = array(
		array( 'kind' => 'heading', 'text' => 'Rede', 'source' => 'post_content', 'ordinal' => 0, 'meta' => array( 'level' => 2 ) ),
		array( 'kind' => 'paragraph', 'text' => 'Introdução', 'source' => 'post_content', 'ordinal' => 1 ),
		array( 'kind' => 'list_item', 'text' => 'Item A', 'source' => 'post_content', 'ordinal' => 2, 'meta' => array( 'list_id' => 'list-0', 'list_type' => 'unordered', 'depth' => 0, 'item_index' => 0, 'item_id' => 'list-0-item-0', 'parent_item_id' => '' ) ),
		array( 'kind' => 'list_item', 'text' => 'Sub A1', 'source' => 'post_content', 'ordinal' => 3, 'meta' => array( 'list_id' => 'list-1', 'list_type' => 'ordered', 'depth' => 1, 'item_index' => 0, 'item_id' => 'list-1-item-0', 'parent_item_id' => 'list-0-item-0' ) ),
		array( 'kind' => 'list_item', 'text' => 'Item B', 'source' => 'post_content', 'ordinal' => 4, 'meta' => array( 'list_id' => 'list-0', 'list_type' => 'unordered', 'depth' => 0, 'item_index' => 1, 'item_id' => 'list-0-item-1', 'parent_item_id' => '' ) ),
		array( 'kind' => 'heading', 'text' => 'Tabela', 'source' => 'post_content', 'ordinal' => 5, 'meta' => array( 'level' => 3 ) ),
		array( 'kind' => 'table_row', 'text' => 'Nome | Valor', 'source' => 'post_content', 'ordinal' => 6, 'meta' => array( 'table_id' => 'table-0', 'row_index' => 0, 'cells' => array(
			array( 'cell_index' => 0, 'kind' => 'header', 'text' => 'Nome', 'colspan' => 1, 'rowspan' => 1 ),
			array( 'cell_index' => 1, 'kind' => 'header', 'text' => 'Valor', 'colspan' => 1, 'rowspan' => 1 ),
		) ) ),
		array( 'kind' => 'table_row', 'text' => 'A | 10', 'source' => 'post_content', 'ordinal' => 7, 'meta' => array( 'table_id' => 'table-0', 'row_index' => 1, 'cells' => array(
			array( 'cell_index' => 0, 'kind' => 'data', 'text' => 'A', 'colspan' => 1, 'rowspan' => 1 ),
			array( 'cell_index' => 1, 'kind' => 'data', 'text' => '10', 'colspan' => 1, 'rowspan' => 1 ),
		) ) ),
	);

	$sections = Semantic_Structure::sections( $fragments );
	assert_v2( 'Rede' === $sections[1]['heading_path'][0]['text'], 'paragraph inherits heading context' );
	assert_v2( 2 === count( $sections[5]['heading_path'] ), 'nested heading path preserved' );

	$blocks = Semantic_Structure::blocks( $sections );
	$lists = array_values( array_filter( $blocks, static fn ( array $block ): bool => 'list' === $block['kind'] ) );
	$tables = array_values( array_filter( $blocks, static fn ( array $block ): bool => 'table' === $block['kind'] ) );
	assert_v2( 2 === count( $lists[0]['items'] ), 'top list preserves two items' );
	assert_v2( 'ordered' === $lists[0]['items'][0]['children'][0]['list_type'], 'nested ordered list preserved' );
	assert_v2( 'header' === $tables[0]['rows'][0]['cells'][0]['kind'], 'table header cell kind preserved' );
	assert_v2( '10' === $tables[0]['rows'][1]['cells'][1]['text'], 'table cell value preserved' );

	$extraction = array(
		'source_kind' => 'legacy_html',
		'fragments' => $fragments,
		'structure' => array( 'headings' => 2, 'paragraphs' => 1, 'lists' => 2, 'list_items' => 3, 'tables' => 1, 'table_rows' => 2, 'table_cells' => 4, 'images' => 0, 'links' => 0, 'code_blocks' => 0, 'shortcodes' => 0 ),
		'warnings' => array(),
		'strategies' => array( 'legacy_html' ),
		'fallback_used' => false,
		'elementor_compatibility' => array( 'status' => 'projectable', 'reasons' => array() ),
	);
	$ready = Semantic_Structure::ai_readiness( $extraction, $sections, $blocks );
	assert_v2( 'candidate_ready' === $ready['status'], 'AI readiness is calculated objectively' );
	assert_v2( true === $ready['structure_complete'], 'structural counts reconcile with semantic blocks' );

	$post = (object) array( 'ID' => 1, 'post_title' => 'Teste', 'post_modified_gmt' => '2026-09-16 00:00:00' );
	$doc_a = Knowledge_Document::from_extraction( $post, $extraction, 'https://example.test/a' );
	$doc_b = Knowledge_Document::from_extraction( $post, $extraction, 'https://example.test/b' );
	assert_v2( is_array( $doc_a ) && '2.0.0' === $doc_a['schema_version'], 'Knowledge Document schema v2' );
	assert_v2( $doc_a['source_hash'] === $doc_b['source_hash'], 'URL remains neutral to source hash' );
	assert_v2( $doc_a['document_hash'] === $doc_b['document_hash'], 'URL remains neutral to document hash' );
	assert_v2( 'candidate_ready' === $doc_a['ai_readiness']['status'], 'Knowledge Document exposes AI readiness' );

	$changed = $extraction;
	$changed['fragments'][3]['meta']['list_type'] = 'unordered';
	$doc_changed = Knowledge_Document::from_extraction( $post, $changed, 'https://example.test/a' );
	assert_v2( is_array( $doc_changed ) && $doc_changed['source_hash'] !== $doc_a['source_hash'], 'semantic structural change alters source hash' );

	echo "ALL PASS\n";
}
