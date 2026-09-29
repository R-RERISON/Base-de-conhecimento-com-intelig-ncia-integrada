<?php
/**
 * R-260 hierarchy profiler pure behavior test.
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
			$value = strtolower( trim( preg_replace( '/\s+/', ' ', $value ) ?? '' ) );
			return $value;
		}
	}

	require_once dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-r260-hierarchy-profiler.php';

	$fragments = array(
		array( 'kind' => 'paragraph', 'text' => '5.1 INTRO', 'ordinal' => 0 ),
		array( 'kind' => 'paragraph', 'text' => 'item de índice', 'ordinal' => 1 ),
		array( 'kind' => 'paragraph', 'text' => '5.2 CORPO', 'ordinal' => 2 ),
		array( 'kind' => 'paragraph', 'text' => 'detalhe A', 'ordinal' => 3 ),
		array( 'kind' => 'paragraph', 'text' => 'detalhe B', 'ordinal' => 4 ),
		array( 'kind' => 'paragraph', 'text' => '5.1 INTRO', 'ordinal' => 5 ),
		array( 'kind' => 'paragraph', 'text' => 'detalhe C', 'ordinal' => 6 ),
	);

	$hierarchy = array(
		'nodes' => array(
			array(
				'ordinal' => 0,
				'token' => '5.1',
				'depth' => 2,
				'hierarchy_source' => 'numbering_inferred',
				'hierarchy_confidence' => 'deterministic',
			),
			array(
				'ordinal' => 2,
				'token' => '5.2',
				'depth' => 2,
				'hierarchy_source' => 'numbering_inferred',
				'hierarchy_confidence' => 'deterministic',
			),
			array(
				'ordinal' => 5,
				'token' => '5.1',
				'depth' => 2,
				'hierarchy_source' => 'numbering_inferred',
				'hierarchy_confidence' => 'deterministic',
			),
		),
	);

	$result = R260_Hierarchy_Profiler::profile( 123, 'legacy_html', $fragments, $hierarchy );
	$summary = (array) ( $result['summary'] ?? array() );

	$source = file_get_contents(
		dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-r260-hierarchy-profiler.php'
	);
	$source = is_string( $source ) ? $source : '';

	$checks = array(
		'candidate_count_3' => 3 === (int) ( $summary['candidate_count'] ?? -1 ),
		'toc_signal_1' => 1 === (int) ( $summary['toc_signal_count'] ?? -1 ),
		'body_signal_1' => 1 === (int) ( $summary['body_signal_count'] ?? -1 ),
		'uncertain_1' => 1 === (int) ( $summary['uncertain_count'] ?? -1 ),
		'later_same_label_1' => 1 === (int) ( $summary['later_same_label_count'] ?? -1 ),
		'body_span_ge2_1' => 1 === (int) ( $summary['body_span_ge2_count'] ?? -1 ),
		'samples_bounded' => count( (array) ( $result['samples'] ?? array() ) ) <= 5,
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
