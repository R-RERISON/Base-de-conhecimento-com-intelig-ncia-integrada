<?php
/**
 * R-260B structural shadow projector pure behavior test.
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
			return strtolower( trim( preg_replace( '/\s+/', ' ', $value ) ?? '' ) );
		}
	}

	final class Search_Section_Projector {
		public const MAX_SECTIONS = 64;
	}

	require_once dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-r260-structural-shadow-projector.php';

	$fragments = array(
		array( 'kind' => 'heading', 'text' => '2.1 EXISTING' ),
		array( 'kind' => 'paragraph', 'text' => '5.1 TOC' ),
		array( 'kind' => 'paragraph', 'text' => '5.2 BODY' ),
		array( 'kind' => 'paragraph', 'text' => '5.3 DUP' ),
		array( 'kind' => 'paragraph', 'text' => '5.3 DUP' ),
		array( 'kind' => 'paragraph', 'text' => '2.1 EXISTING' ),
		array( 'kind' => 'paragraph', 'text' => '5.4 UNCERTAIN' ),
	);

	$profile = array(
		'candidates' => array(
			array( 'ordinal' => 1, 'token' => '5.1', 'body_span' => 0, 'position_bucket' => 'early', 'toc_signal' => true, 'body_signal' => false ),
			array( 'ordinal' => 2, 'token' => '5.2', 'body_span' => 3, 'position_bucket' => 'middle', 'toc_signal' => false, 'body_signal' => true ),
			array( 'ordinal' => 3, 'token' => '5.3', 'body_span' => 2, 'position_bucket' => 'middle', 'toc_signal' => false, 'body_signal' => true ),
			array( 'ordinal' => 4, 'token' => '5.3', 'body_span' => 2, 'position_bucket' => 'late', 'toc_signal' => false, 'body_signal' => true ),
			array( 'ordinal' => 5, 'token' => '2.1', 'body_span' => 4, 'position_bucket' => 'late', 'toc_signal' => false, 'body_signal' => true ),
			array( 'ordinal' => 6, 'token' => '5.4', 'body_span' => 1, 'position_bucket' => 'late', 'toc_signal' => false, 'body_signal' => false ),
		),
	);

	$existing_63 = array_fill( 0, 63, array( 'section_key' => 'x' ) );
	$result = R260_Structural_Shadow_Projector::project( 123, 'legacy_html', $fragments, $existing_63, $profile );
	$states = (array) ( $result['states'] ?? array() );

	$existing_64 = array_fill( 0, 64, array( 'section_key' => 'x' ) );
	$overflow = R260_Structural_Shadow_Projector::project( 123, 'legacy_html', $fragments, $existing_64, $profile );

	$source = file_get_contents(
		dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-r260-structural-shadow-projector.php'
	);
	$source = is_string( $source ) ? $source : '';

	$checks = array(
		'toc_suppressed_1' => 1 === (int) ( $states['toc_suppressed'] ?? -1 ),
		'existing_heading_redundant_1' => 1 === (int) ( $states['existing_heading_redundant'] ?? -1 ),
		'duplicate_candidate_ambiguous_2' => 2 === (int) ( $states['duplicate_candidate_ambiguous'] ?? -1 ),
		'promotable_shadow_1' => 1 === (int) ( $states['promotable_shadow'] ?? -1 ),
		'uncertain_1' => 1 === (int) ( $states['uncertain'] ?? -1 ),
		'candidate_partition_6' => 6 === array_sum( $states ),
		'shadow_at_limit_not_exceeded' => 64 === (int) ( $result['shadow_section_count'] ?? -1 )
			&& false === (bool) ( $result['max_sections_exceeded'] ?? true ),
		'overflow_detected' => true === (bool) ( $overflow['max_sections_exceeded'] ?? false )
			&& 1 === (int) ( $overflow['overflow_by'] ?? 0 ),
		'paragraph_anchor_blocked' => 1 === (int) ( $result['paragraph_anchor_contract_blocked_count'] ?? -1 ),
		'shadow_only_contract' => 'shadow_only_no_runtime_promotion' === (string) ( $result['interpretation'] ?? '' ),
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
