<?php
/**
 * R-260D1 contextual anchor feasibility profiler pure behavior test.
 */

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ );
	}
}

namespace BDC\KnowledgeBase {
	final class Search_Query_Normalizer {
		public static function normalize_document_text( string $value ): string {
			$value = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			$value = strip_tags( $value );
			$value = strtolower( $value );
			$value = preg_replace( '/[^a-z0-9]+/u', ' ', $value ) ?? '';
			$value = preg_replace( '/\s+/u', ' ', $value ) ?? '';
			return trim( $value );
		}
	}

	require_once dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-r260-contextual-anchor-feasibility-profiler.php';

	$fragments = array(
		array( 'kind' => 'paragraph', 'text' => '5.1 Alpha' ),
		array( 'kind' => 'paragraph', 'text' => 'Alpha body one' ),
		array( 'kind' => 'paragraph', 'text' => 'Alpha body two' ),
		array( 'kind' => 'paragraph', 'text' => '5.2 Beta' ),
		array( 'kind' => 'paragraph', 'text' => 'Beta body one' ),
		array( 'kind' => 'paragraph', 'text' => 'Beta body two' ),
		array( 'kind' => 'paragraph', 'text' => '5.3 Gamma' ),
		array( 'kind' => 'paragraph', 'text' => 'Gamma body one' ),
		array( 'kind' => 'paragraph', 'text' => 'Gamma body two' ),
		array( 'kind' => 'paragraph', 'text' => '5.4 Delta' ),
		array( 'kind' => 'paragraph', 'text' => 'Delta only body' ),
		array( 'kind' => 'paragraph', 'text' => '5.5 Epsilon' ),
		array( 'kind' => 'paragraph', 'text' => 'Epsilon body one' ),
		array( 'kind' => 'paragraph', 'text' => 'Epsilon body two' ),
		array( 'kind' => 'paragraph', 'text' => '5.6 Zeta' ),
		array( 'kind' => 'paragraph', 'text' => 'Zeta body one' ),
		array( 'kind' => 'paragraph', 'text' => 'Zeta body two' ),
		array( 'kind' => 'heading', 'text' => 'END', 'meta' => array( 'level' => 2 ) ),
	);

	$hierarchy = array(
		'nodes' => array(
			array( 'ordinal' => 0, 'hierarchy_source' => 'numbering_inferred', 'depth' => 2 ),
			array( 'ordinal' => 3, 'hierarchy_source' => 'numbering_inferred', 'depth' => 2 ),
			array( 'ordinal' => 6, 'hierarchy_source' => 'numbering_inferred', 'depth' => 2 ),
			array( 'ordinal' => 9, 'hierarchy_source' => 'numbering_inferred', 'depth' => 2 ),
			array( 'ordinal' => 11, 'hierarchy_source' => 'numbering_inferred', 'depth' => 2 ),
			array( 'ordinal' => 14, 'hierarchy_source' => 'numbering_inferred', 'depth' => 2 ),
		),
	);

	$candidates = array(
		array( 'state' => 'promotable_shadow', 'ordinal' => 0, 'token' => '5.1', 'title_norm' => '5 1 alpha' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 3, 'token' => '5.2', 'title_norm' => '5 2 beta' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 6, 'token' => '5.3', 'title_norm' => '5 3 gamma' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 9, 'token' => '5.4', 'title_norm' => '5 4 delta' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 11, 'token' => '5.5', 'title_norm' => '5 5 epsilon' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 14, 'token' => '5.6', 'title_norm' => '5 6 zeta' ),
	);

	$html = implode(
		'',
		array(
			'<p>5.1 Alpha</p><p>Alpha body one</p><p>Alpha body two</p>',
			'<p>5.2 Beta</p><p>TOC one</p><p>TOC two</p>',
			'<p>5.2 Beta</p><p>Beta body one</p><p>Beta body two</p>',
			'<p>5.3 Gamma</p><p>Gamma body one</p><p>Gamma body two</p>',
			'<p>5.3 Gamma</p><p>Gamma body one</p><p>Gamma body two</p>',
			'<p>5.4 Delta</p><p>Delta only body</p>',
			'<p>5.4 Delta</p><p>other</p>',
			'<p>5.5 Epsilon</p><p>wrong one</p><p>wrong two</p>',
			'<p>5.5 Epsilon</p><p>wrong three</p><p>wrong four</p>',
		)
	);

	$result = R260_Contextual_Anchor_Feasibility_Profiler::profile(
		123,
		'legacy_html',
		$html,
		$fragments,
		$hierarchy,
		$candidates
	);
	$states = (array) ( $result['states'] ?? array() );

	$source = file_get_contents(
		dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-r260-contextual-anchor-feasibility-profiler.php'
	);
	$source = is_string( $source ) ? $source : '';

	$checks = array(
		'promotable_count_6' => 6 === (int) ( $result['promotable_count'] ?? -1 ),
		'title_unique_1' => 1 === (int) ( $states['title_unique'] ?? -1 ),
		'context_unique_1' => 1 === (int) ( $states['context_unique'] ?? -1 ),
		'context_ambiguous_1' => 1 === (int) ( $states['context_ambiguous'] ?? -1 ),
		'context_insufficient_1' => 1 === (int) ( $states['context_insufficient'] ?? -1 ),
		'context_not_matched_1' => 1 === (int) ( $states['context_not_matched'] ?? -1 ),
		'title_not_rendered_1' => 1 === (int) ( $states['title_not_rendered'] ?? -1 ),
		'partition_6' => 6 === array_sum( $states ),
		'effective_unique_2' => 2 === (int) ( $result['effective_unique_count'] ?? -1 ),
		'context_resolved_1' => 1 === (int) ( $result['context_resolved_count'] ?? -1 ),
		'unresolved_remaining_4' => 4 === (int) ( $result['unresolved_remaining_count'] ?? -1 ),
		'diagnostic_only' => 'diagnostic_only_contextual_disambiguation_no_anchor_injection' === (string) ( $result['interpretation'] ?? '' ),
		'no_editorial_write' => 1 !== preg_match(
			'/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|delete_post_meta\s*\(|wp_set_object_terms\s*\(/i',
			$source
		),
		'no_external_network' => 1 !== preg_match(
			'/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|(?<![A-Za-z0-9_])fsockopen\s*\(|(?<![A-Za-z0-9_])stream_socket_client\s*\(/i',
			$source
		),
	);

	$failed = 0;
	foreach ( $checks as $name => $pass ) {
		echo ( $pass ? 'PASS ' : 'FAIL ' ) . $name . PHP_EOL;
		if ( ! $pass ) {
			++$failed;
		}
	}

	echo PHP_EOL . 'RESULT passed=' . ( count( $checks ) - $failed ) . ' failed=' . $failed . PHP_EOL;
	exit( $failed > 0 ? 1 : 0 );
}
