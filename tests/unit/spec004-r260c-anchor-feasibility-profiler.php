<?php
/**
 * R-260C anchor feasibility profiler pure behavior test.
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

	require_once dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-r260-anchor-feasibility-profiler.php';

	$html = implode(
		'',
		array(
			'<p>5.1 Alpha</p>',
			'<p>5.2 Beta</p>',
			'<p>5.2 Beta</p>',
			'<div>5.3 Gamma</div>',
			'<div>5.4 Delta</div>',
			'<li>5.4 Delta</li>',
			'<p>noise</p>',
		)
	);

	$candidates = array(
		array( 'state' => 'promotable_shadow', 'ordinal' => 1, 'token' => '5.1', 'title_norm' => '5 1 alpha' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 2, 'token' => '5.2', 'title_norm' => '5 2 beta' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 3, 'token' => '5.3', 'title_norm' => '5 3 gamma' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 4, 'token' => '5.4', 'title_norm' => '5 4 delta' ),
		array( 'state' => 'promotable_shadow', 'ordinal' => 5, 'token' => '5.5', 'title_norm' => '5 5 epsilon' ),
		array( 'state' => 'uncertain', 'ordinal' => 6, 'token' => '5.6', 'title_norm' => '5 6 ignored' ),
	);

	$result = R260_Anchor_Feasibility_Profiler::profile( 123, 'legacy_html', $html, $candidates );
	$states = (array) ( $result['states'] ?? array() );

	$source = file_get_contents(
		dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-r260-anchor-feasibility-profiler.php'
	);
	$source = is_string( $source ) ? $source : '';

	$checks = array(
		'promotable_count_5' => 5 === (int) ( $result['promotable_count'] ?? -1 ),
		'paragraph_unique_1' => 1 === (int) ( $states['paragraph_unique'] ?? -1 ),
		'paragraph_ambiguous_1' => 1 === (int) ( $states['paragraph_ambiguous'] ?? -1 ),
		'block_unique_nonparagraph_1' => 1 === (int) ( $states['block_unique_nonparagraph'] ?? -1 ),
		'block_ambiguous_1' => 1 === (int) ( $states['block_ambiguous'] ?? -1 ),
		'not_rendered_exact_1' => 1 === (int) ( $states['not_rendered_exact'] ?? -1 ),
		'partition_5' => 5 === array_sum( $states ),
		'paragraph_unique_rate' => abs( (float) ( $result['paragraph_unique_rate'] ?? -1 ) - 0.2 ) < 0.00001,
		'any_unique_block_rate' => abs( (float) ( $result['any_unique_block_rate'] ?? -1 ) - 0.4 ) < 0.00001,
		'diagnostic_only' => 'diagnostic_only_no_anchor_injection' === (string) ( $result['interpretation'] ?? '' ),
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
