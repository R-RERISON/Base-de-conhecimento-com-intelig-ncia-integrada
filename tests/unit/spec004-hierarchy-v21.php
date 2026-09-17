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
	require_once $root . 'class-hierarchy-relationships.php';
	require_once $root . 'class-numbered-hierarchy-resolver.php';

	function assert_h21( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		echo "PASS {$message}\n";
	}

	$sections = array(
		array( 'kind' => 'paragraph', 'text' => '1 Visão geral', 'ordinal' => 0, 'source' => 'post_content', 'heading_path' => array() ),
		array( 'kind' => 'paragraph', 'text' => '1.1 Instalação', 'ordinal' => 1, 'source' => 'post_content', 'heading_path' => array() ),
		array( 'kind' => 'paragraph', 'text' => '1.2 Operação', 'ordinal' => 2, 'source' => 'post_content', 'heading_path' => array() ),
		array( 'kind' => 'paragraph', 'text' => '1.2.1 Diagnóstico', 'ordinal' => 3, 'source' => 'post_content', 'heading_path' => array() ),
	);
	$resolved = Numbered_Hierarchy_Resolver::resolve( $sections );
	assert_h21( 'resolved' === $resolved['status'], 'coherent numbered hierarchy resolves' );
	assert_h21( 3 === $resolved['resolved_edges'], 'three deterministic numbered edges emitted' );
	assert_h21( 'numbering_inferred' === $resolved['sections'][1]['meta']['hierarchy_source'], 'numbered child has inferred provenance' );
	assert_h21( 0 === $resolved['sections'][1]['meta']['hierarchy_parent_ordinal'], '1.1 points to section 1 parent' );
	assert_h21( 2 === $resolved['sections'][3]['meta']['hierarchy_parent_ordinal'], '1.2.1 points to 1.2 parent' );

	$isolated = Numbered_Hierarchy_Resolver::resolve(
		array(
			array( 'kind' => 'paragraph', 'text' => '1.2 release', 'ordinal' => 0, 'source' => 'post_content', 'heading_path' => array() ),
		)
	);
	assert_h21( 'none' === $isolated['status'], 'isolated version-like token does not infer hierarchy' );
	assert_h21( 0 === $isolated['strong_signal_count'], 'isolated token is not treated as strong signal' );

	$ambiguous = Numbered_Hierarchy_Resolver::resolve(
		array(
			array( 'kind' => 'paragraph', 'text' => '2.1 Sem pai', 'ordinal' => 0, 'source' => 'post_content', 'heading_path' => array() ),
			array( 'kind' => 'paragraph', 'text' => '2.2 Também sem pai', 'ordinal' => 1, 'source' => 'post_content', 'heading_path' => array() ),
		)
	);
	assert_h21( 'ambiguous' === $ambiguous['status'], 'multiple strong nested tokens without parent require review' );
	assert_h21( 2 === count( $ambiguous['warnings'] ), 'ambiguous unresolved tokens are explicit warnings' );

	$conflict = Numbered_Hierarchy_Resolver::resolve(
		array(
			array( 'kind' => 'list_item', 'text' => '1 Raiz', 'ordinal' => 0, 'source' => 'post_content', 'heading_path' => array(), 'meta' => array( 'list_id' => 'list-0', 'item_id' => 'item-0', 'parent_item_id' => '' ) ),
			array( 'kind' => 'list_item', 'text' => '1.1 DOM diz irmão', 'ordinal' => 1, 'source' => 'post_content', 'heading_path' => array(), 'meta' => array( 'list_id' => 'list-0', 'item_id' => 'item-1', 'parent_item_id' => '' ) ),
		)
	);
	assert_h21( 'ambiguous' === $conflict['status'], 'numbering conflicting with explicit DOM requires review' );
	assert_h21( str_starts_with( $conflict['warnings'][0] ?? '', 'HIERARCHY_NUMBERING_CONFLICT:' ), 'DOM-numbering conflict is explicitly classified' );
	assert_h21( 'explicit_dom' === $conflict['sections'][1]['meta']['hierarchy_source'], 'explicit DOM provenance wins numbering conflict' );

	$ip_like = Numbered_Hierarchy_Resolver::resolve(
		array(
			array( 'kind' => 'paragraph', 'text' => '192.168.1.10 endereço', 'ordinal' => 0, 'source' => 'post_content', 'heading_path' => array() ),
			array( 'kind' => 'paragraph', 'text' => '192.168.1.11 endereço', 'ordinal' => 1, 'source' => 'post_content', 'heading_path' => array() ),
		)
	);
	assert_h21( 'none' === $ip_like['status'], 'IPv4-like identifiers are not parsed as numbered hierarchy' );
	assert_h21( 0 === $ip_like['strong_signal_count'], 'IPv4-like identifiers emit no hierarchy signal' );

	if ( class_exists( '\\DOMDocument' ) ) {
		$html = '<ol><li>A<ul><li>A1</li></ul></li><li>B</li></ol><h2>X</h2><h3>Y</h3>';
		$fragments = array(
			array( 'kind' => 'list_item', 'text' => 'A', 'source' => 'post_content', 'meta' => array( 'list_id' => 'list-0', 'list_type' => 'ordered', 'item_index' => 0, 'item_id' => 'i0', 'parent_item_id' => '' ) ),
			array( 'kind' => 'list_item', 'text' => 'A1', 'source' => 'post_content', 'meta' => array( 'list_id' => 'list-1', 'list_type' => 'unordered', 'item_index' => 0, 'item_id' => 'i1', 'parent_item_id' => 'i0' ) ),
			array( 'kind' => 'list_item', 'text' => 'B', 'source' => 'post_content', 'meta' => array( 'list_id' => 'list-0', 'list_type' => 'ordered', 'item_index' => 1, 'item_id' => 'i2', 'parent_item_id' => '' ) ),
			array( 'kind' => 'heading', 'text' => 'X', 'source' => 'post_content', 'meta' => array( 'level' => 2 ) ),
			array( 'kind' => 'heading', 'text' => 'Y', 'source' => 'post_content', 'meta' => array( 'level' => 3 ) ),
		);
		$fidelity = Hierarchy_Relationships::compare_html( $html, $fragments );
		assert_h21( true === $fidelity['complete'], 'explicit DOM relationship fidelity passes for matching graph' );

		$flat = $fragments;
		$flat[1]['meta']['parent_item_id'] = '';
		$fidelity_fail = Hierarchy_Relationships::compare_html( $html, $flat );
		assert_h21( false === $fidelity_fail['complete'], 'lost parent edge fails relationship fidelity' );
		assert_h21( str_starts_with( $fidelity_fail['reasons'][0], 'HIERARCHY_EDGE_MISMATCH:' ), 'relationship failure emits blocking hierarchy reason' );
	}

	echo "ALL PASS\n";
}
