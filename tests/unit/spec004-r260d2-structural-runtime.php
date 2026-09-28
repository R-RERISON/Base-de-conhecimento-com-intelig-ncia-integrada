<?php
/**
 * R-260D2 structural runtime promotion pure behavior test.
 */
declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
	final class WP_Error {}
	function wp_strip_all_tags( string $v ): string { return strip_tags( $v ); }
	function remove_accents( string $v ): string {
		return strtr( $v, array(
			'ç'=>'c','Ç'=>'C','ã'=>'a','á'=>'a','à'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','õ'=>'o','ô'=>'o','ú'=>'u'
		) );
	}
	function sanitize_key( string $v ): string { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $v ) ) ?? ''; }
	function esc_attr( string $v ): string { return htmlspecialchars( $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); }
}

namespace BDC\KnowledgeBase {
	$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/';
	require_once $root . 'class-search-query-normalizer.php';
	require_once $root . 'class-numbered-hierarchy-resolver.php';
	require_once $root . 'class-r260-contextual-anchor-resolver.php';
	require_once $root . 'class-r260-structural-projector.php';
	require_once $root . 'class-r260-hierarchy-profiler.php';
	require_once $root . 'class-search-section-projector.php';
	require_once $root . 'class-search-anchor-manager.php';

	function assert_r260d2( bool $condition, string $message ): void {
		if ( ! $condition ) { throw new \RuntimeException( $message ); }
	}
	function same_r260d2( mixed $expected, mixed $actual, string $message ): void {
		if ( $expected !== $actual ) {
			throw new \RuntimeException( $message . ' expected=' . var_export( $expected, true ) . ' actual=' . var_export( $actual, true ) );
		}
	}

	$fragments = array(
		array( 'kind'=>'paragraph','text'=>'5 Parent','ordinal'=>0,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'5.1 Section A','ordinal'=>1,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'5.2 Next','ordinal'=>2,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'next one','ordinal'=>3,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'next two','ordinal'=>4,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'5.1 Section A','ordinal'=>5,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'body one','ordinal'=>6,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'body two','ordinal'=>7,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'5.3 End','ordinal'=>8,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'end one','ordinal'=>9,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'end two','ordinal'=>10,'source'=>'legacy' ),
	);
	$html = '<p>5 Parent</p><p>5.1 Section A</p><p>5.2 Next</p><p>next one</p><p>next two</p>'
		. '<p>5.1 Section A</p><p>body one</p><p>body two</p><p>5.3 End</p><p>end one</p><p>end two</p>';

	$projection = R260_Structural_Projector::project( 99, 'legacy_html', $fragments, $html );
	$checks = array();
	$checks['version'] = R260_Structural_Projector::VERSION === (string) ( $projection['version'] ?? '' );
	$checks['candidate_count_4'] = 4 === (int) ( $projection['candidate_count'] ?? -1 );
	$checks['toc_suppressed_1'] = 1 === (int) ( $projection['states']['toc_suppressed'] ?? -1 );
	$checks['body_projected_3'] = 3 === (int) ( $projection['states']['body_projected'] ?? -1 );
	$checks['projected_count_3'] = 3 === (int) ( $projection['projected_count'] ?? -1 );
	$checks['generated_3'] = 3 === (int) ( $projection['generated_anchor_count'] ?? -1 );
	$checks['unresolved_0'] = 0 === (int) ( $projection['unresolved_anchor_count'] ?? -1 );
	$checks['duplicate_body_retained'] = (int) ( $projection['duplicate_body_count'] ?? 0 ) >= 1;

	$target = null;
	foreach ( (array) ( $projection['nodes'] ?? array() ) as $node ) {
		if ( '5 1 section a' === (string) ( $node['title_norm'] ?? '' ) ) { $target = $node; break; }
	}
	$checks['target_found'] = is_array( $target );
	$checks['context_unique'] = is_array( $target ) && 'context_unique' === (string) ( $target['target_state'] ?? '' );
	$checks['target_generated'] = is_array( $target ) && true === (bool) ( $target['anchor_generated'] ?? false );

	$section_projection = Search_Section_Projector::project_with_diagnostics( 99, $fragments, 'legacy_html', $html, $projection );
	$checks['section_count_3'] = 3 === (int) ( $section_projection['structural_count'] ?? -1 );
	$checks['no_overflow'] = 0 === (int) ( $section_projection['overflow_by'] ?? -1 );
	$section = null;
	foreach ( (array) ( $section_projection['sections'] ?? array() ) as $row ) {
		if ( '5 1 section a' === (string) ( $row['title_norm'] ?? '' ) ) { $section = $row; break; }
	}
	$checks['section_generated'] = is_array( $section ) && 'generated' === (string) ( $section['anchor_state'] ?? '' )
		&& 'numbered_paragraph' === (string) ( $section['structural_source'] ?? '' );

	$after = is_array( $section ) ? Search_Anchor_Manager::inject_for_sections( $html, array( $section ) ) : $html;
	$anchor = is_array( $section ) ? (string) ( $section['anchor_id'] ?? '' ) : '';
	$checks['anchor_once'] = '' !== $anchor && 1 === substr_count( $after, 'id="' . $anchor . '"' );
	$first = strpos( $after, '<p>5.1 Section A</p>' );
	$anchored = strpos( $after, 'id="' . $anchor . '"' );
	$checks['anchor_body_not_toc'] = false !== $first && false !== $anchored && $anchored > $first;
	$checks['visible_text_preserved'] = strip_tags( $html ) === strip_tags( $after );
	$checks['idempotent'] = $after === Search_Anchor_Manager::inject_for_sections( $after, is_array( $section ) ? array( $section ) : array() );

	$shifted = $fragments;
	array_splice( $shifted, 0, 0, array( array( 'kind'=>'paragraph','text'=>'unrelated intro','ordinal'=>100,'source'=>'legacy' ) ) );
	$shifted_projection = R260_Structural_Projector::project( 99, 'legacy_html', $shifted, '' );
	$keys1 = array_map( static fn ( array $n ): string => (string) $n['section_key'], (array) $projection['nodes'] );
	$keys2 = array_map( static fn ( array $n ): string => (string) $n['section_key'], (array) $shifted_projection['nodes'] );
	sort( $keys1 ); sort( $keys2 );
	$checks['stable_identity_unrelated_insert'] = $keys1 === $keys2;


	/*
	 * Regression RC11: R-260A occurrence evidence must be collected from all
	 * hierarchy nodes before the runtime candidate subset is filtered.
	 */
	$parity_fragments = array(
		array( 'kind'=>'paragraph','text'=>'5 Parent','ordinal'=>0,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'5.1 Repeat','ordinal'=>1,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'5.2 Boundary','ordinal'=>2,'source'=>'legacy' ),
		array( 'kind'=>'heading','text'=>'Real heading','ordinal'=>3,'meta'=>array( 'level'=>2 ),'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'5 Parent','ordinal'=>4,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'5.1 Repeat','ordinal'=>5,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'body one','ordinal'=>6,'source'=>'legacy' ),
		array( 'kind'=>'paragraph','text'=>'body two','ordinal'=>7,'source'=>'legacy' ),
	);
	$parity_hierarchy = Numbered_Hierarchy_Resolver::resolve( $parity_fragments );
	$parity_profile = R260_Hierarchy_Profiler::profile( 777, 'legacy_html', $parity_fragments, $parity_hierarchy );
	$parity_runtime = R260_Structural_Projector::project( 777, 'legacy_html', $parity_fragments, '' );
	$checks['r260a_runtime_candidate_count_parity'] =
		(int) ( $parity_profile['summary']['candidate_count'] ?? -1 )
		=== (int) ( $parity_runtime['candidate_count'] ?? -2 );
	$checks['r260a_runtime_toc_parity'] =
		(int) ( $parity_profile['summary']['toc_signal_count'] ?? -1 )
		=== (int) ( $parity_runtime['states']['toc_suppressed'] ?? -2 );
	$checks['r260a_runtime_body_parity'] =
		(int) ( $parity_profile['summary']['body_signal_count'] ?? -1 )
		=== (int) ( $parity_runtime['body_bearing_count'] ?? -2 );
	$checks['r260a_runtime_uncertain_parity'] =
		(int) ( $parity_profile['summary']['uncertain_count'] ?? -1 )
		=== (int) ( $parity_runtime['states']['uncertain'] ?? -2 );

	$failed = 0;
	foreach ( $checks as $name => $pass ) {
		echo ( $pass ? 'PASS ' : 'FAIL ' ) . $name . PHP_EOL;
		if ( ! $pass ) { ++$failed; }
	}
	echo PHP_EOL . 'RESULT passed=' . ( count( $checks ) - $failed ) . ' failed=' . $failed . PHP_EOL;
	exit( $failed > 0 ? 1 : 0 );
}
