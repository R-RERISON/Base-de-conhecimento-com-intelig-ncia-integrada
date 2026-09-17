<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! class_exists( 'WP_Error' ) ) {
		class WP_Error {
			public function __construct( public string $code = '', public string $message = '', public mixed $data = null ) {}
			public function get_error_code(): string { return $this->code; }
		}
	}
}

namespace BDC\KnowledgeBase {
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-block-migration-stale-source-guard.php';
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-core-block-editorial-parity.php';

	$assertions = 0;
	function assert_t097( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		++$assertions;
		echo "PASS {$message}\n";
	}

	$raw = '<p><strong>A</strong> <a href="/x">B</a></p>';
	$source = array(
		'source_kind' => 'legacy_html',
		'fidelity_hash' => str_repeat( 'a', 64 ),
		'source_material' => array(
			'post_content_sha256' => str_repeat( 'b', 64 ),
			'elementor_data_sha256' => str_repeat( 'c', 64 ),
		),
		'units' => array(
			array( 'kind' => 'post_content_rich_html', 'raw' => $raw, 'raw_sha256' => hash( 'sha256', $raw ) ),
		),
	);

	$fresh = Block_Migration_Stale_Source_Guard::assess( $source, $source );
	assert_t097( 'fresh' === $fresh['status'], 'same source is fresh' );
	assert_t097( array() === $fresh['reasons'], 'fresh has no reasons' );
	assert_t097( false === $fresh['writer_allowed'], 'stale guard writer false' );

	$changed = $source;
	$changed['fidelity_hash'] = str_repeat( 'd', 64 );
	$stale = Block_Migration_Stale_Source_Guard::assess( $source, $changed );
	assert_t097( 'stale' === $stale['status'], 'changed fidelity hash stale' );
	assert_t097( in_array( 'FIDELITY_HASH_CHANGED', $stale['reasons'], true ), 'fidelity reason' );

	$changed = $source;
	$changed['source_kind'] = 'plain_text';
	$stale = Block_Migration_Stale_Source_Guard::assess( $source, $changed );
	assert_t097( in_array( 'SOURCE_KIND_CHANGED', $stale['reasons'], true ), 'kind reason' );

	$changed = $source;
	$changed['source_material']['post_content_sha256'] = str_repeat( 'e', 64 );
	$stale = Block_Migration_Stale_Source_Guard::assess( $source, $changed );
	assert_t097( in_array( 'SOURCE_MATERIAL_CHANGED:post_content_sha256', $stale['reasons'], true ), 'post content reason' );

	$serialization = array( 'status' => 'serialized_in_memory', 'serialized_post_content' => 'x' );
	$parsed = array( array( 'blockName' => 'core/freeform', 'innerHTML' => $raw ) );
	$parity = Core_Block_Editorial_Parity::assess( $source, $serialization, $parsed );
	assert_t097( 'pass' === $parity['status'], 'freeform parity pass' );
	assert_t097( 0 === $parity['mismatches'], 'freeform zero mismatch' );
	assert_t097( false === $parity['writer_allowed'], 'parity writer false' );

	$bad = $parsed;
	$bad[0]['innerHTML'] .= 'x';
	$parity = Core_Block_Editorial_Parity::assess( $source, $serialization, $bad );
	assert_t097( 'mismatch' === $parity['status'], 'payload drift detected' );
	assert_t097( in_array( 'EDITORIAL_PARITY_RAW_PAYLOAD_MISMATCH', $parity['warnings'], true ), 'payload warning' );

	$bad = $parsed;
	$bad[0]['blockName'] = 'core/paragraph';
	$parity = Core_Block_Editorial_Parity::assess( $source, $serialization, $bad );
	assert_t097( in_array( 'EDITORIAL_PARITY_BLOCK_NAME_MISMATCH', $parity['warnings'], true ), 'block name drift detected' );

	$shortcode = '[table id=1 /]';
	$short_source = $source;
	$short_source['source_kind'] = 'elementor';
	$short_source['units'] = array( array( 'kind' => 'elementor_shortcode', 'raw' => $shortcode, 'raw_sha256' => hash( 'sha256', $shortcode ) ) );
	$short_parsed = array( array( 'blockName' => 'core/shortcode', 'innerHTML' => $shortcode ) );
	$parity = Core_Block_Editorial_Parity::assess( $short_source, $serialization, $short_parsed );
	assert_t097( 'pass' === $parity['status'], 'shortcode preserved without execution' );

	$review = Core_Block_Editorial_Parity::assess( $source, array( 'status' => 'review_required' ), array() );
	assert_t097( 'review_required' === $review['status'], 'review stays review' );
	$na = Core_Block_Editorial_Parity::assess( $source, array( 'status' => 'not_applicable' ), array() );
	assert_t097( 'not_applicable' === $na['status'], 'not applicable stays not applicable' );

	$native_raw = '<!-- wp:paragraph --><p>x</p><!-- /wp:paragraph -->';
	$native = $source;
	$native['units'] = array( array( 'kind' => 'native_core_blocks', 'raw' => $native_raw, 'raw_sha256' => hash( 'sha256', $native_raw ) ) );
	$native_parity = Core_Block_Editorial_Parity::assess( $native, array( 'status' => 'native_noop', 'serialized_post_content' => $native_raw ), array() );
	assert_t097( 'native_noop' === $native_parity['status'], 'native noop byte parity' );
	$native_parity = Core_Block_Editorial_Parity::assess( $native, array( 'status' => 'native_noop', 'serialized_post_content' => 'changed' ), array() );
	assert_t097( 'mismatch' === $native_parity['status'], 'native noop drift detected' );

	$unknown = $source;
	$unknown['units'] = array( array( 'kind' => 'unknown', 'raw' => 'x', 'raw_sha256' => hash( 'sha256', 'x' ) ) );
	$parity = Core_Block_Editorial_Parity::assess( $unknown, $serialization, array() );
	assert_t097( 'mismatch' === $parity['status'], 'unsupported unit fails closed' );
	assert_t097( in_array( 'EDITORIAL_PARITY_UNSUPPORTED_UNIT:unknown', $parity['warnings'], true ), 'unsupported unit warning' );

	echo "ALL PASS {$assertions}\n";
}
