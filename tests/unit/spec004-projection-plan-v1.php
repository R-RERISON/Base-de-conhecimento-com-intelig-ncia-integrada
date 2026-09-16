<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
}

namespace BDC\KnowledgeBase {
	$root = __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/';
	require_once $root . 'class-canonical-json.php';
	require_once $root . 'class-elementor-projection-plan.php';

	function assert_projection( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		echo "PASS {$message}\n";
	}

	function projection_document( string $source_kind, string $compat, string $source_hash = 'source-a', array $warnings = array(), array $reasons = array() ): array {
		return array(
			'schema_version' => '2.1.0',
			'post_id' => 123,
			'source_kind' => $source_kind,
			'source_hash' => $source_hash,
			'extraction' => array(
				'warnings' => $warnings,
				'elementor_compatibility' => array(
					'status' => $compat,
					'reasons' => $reasons,
				),
			),
		);
	}

	$legacy = Elementor_Projection_Plan::from_document( projection_document( 'legacy_html', 'projectable' ) );
	assert_projection( is_array( $legacy ), 'legacy plan built' );
	assert_projection( 'projectable' === $legacy['plan_status'], 'legacy projectable status' );
	assert_projection( 'legacy_html_to_container_html' === $legacy['projection_strategy'], 'legacy strategy selected' );
	assert_projection( false === $legacy['requires_review'], 'clean legacy does not require review' );
	assert_projection( false === $legacy['writer_allowed'], 'writer always disabled' );

	$plain = Elementor_Projection_Plan::from_document( projection_document( 'plain_text', 'projectable' ) );
	assert_projection( 'plain_text_to_text_editor' === $plain['projection_strategy'], 'plain text strategy selected' );

	$native = Elementor_Projection_Plan::from_document( projection_document( 'elementor', 'native' ) );
	assert_projection( 'native_noop' === $native['plan_status'], 'native Elementor is noop' );
	assert_projection( 'preserve_native' === $native['projection_strategy'], 'native is preserved' );

	$mixed = Elementor_Projection_Plan::from_document( projection_document( 'mixed', 'native' ) );
	assert_projection( 'review_required' === $mixed['plan_status'], 'mixed native requires review' );
	assert_projection( 'preserve_native' === $mixed['projection_strategy'], 'mixed native is not rebuilt automatically' );
	assert_projection( in_array( 'MIXED_SOURCE_NATIVE_REVIEW', $mixed['warnings'], true ), 'mixed review warning emitted' );

	$review = Elementor_Projection_Plan::from_document(
		projection_document( 'legacy_html', 'review_required', 'source-a', array( 'SHORTCODE_NOT_EXPANDED:table' ), array( 'SHORTCODE_NOT_EXPANDED:table' ) )
	);
	assert_projection( 'review_required' === $review['plan_status'], 'compatibility review remains review' );
	assert_projection( 'manual_adapter_required' === $review['projection_strategy'], 'review requires manual adapter' );

	$blocked = Elementor_Projection_Plan::from_document( projection_document( 'legacy_html', 'blocked' ) );
	assert_projection( 'blocked' === $blocked['plan_status'], 'blocked compatibility blocks projection' );
	assert_projection( empty( $blocked['operations'] ), 'blocked plan emits no operations' );

	$empty = Elementor_Projection_Plan::from_document( projection_document( 'empty', 'projectable' ) );
	assert_projection( 'not_applicable' === $empty['plan_status'], 'empty source is not applicable' );

	$faq = Elementor_Projection_Plan::from_document(
		projection_document( 'legacy_html', 'projectable' ),
		array( array( 'tag' => 'faq_wd', 'registered' => false, 'origins' => array( 'post_content' ) ) )
	);
	assert_projection( true === $faq['requires_review'], 'faq_wd forces review' );
	assert_projection( in_array( 'LEGACY_SHORTCODE_ORPHAN:faq_wd', $faq['warnings'], true ), 'faq_wd classified as legacy orphan' );
	assert_projection( 'manual_adapter_required' === $faq['projection_strategy'], 'faq_wd never auto-projects' );

	$wpt = Elementor_Projection_Plan::from_document(
		projection_document( 'legacy_html', 'projectable' ),
		array( array( 'tag' => 'wpt', 'registered' => false, 'origins' => array( 'post_content' ) ) )
	);
	assert_projection( true === $wpt['requires_review'], 'wpt forces review' );
	assert_projection( in_array( 'LEGACY_SHORTCODE_UNKNOWN:wpt', $wpt['warnings'], true ), 'wpt remains unknown legacy dependency' );

	$missing = Elementor_Projection_Plan::from_document(
		projection_document( 'legacy_html', 'projectable' ),
		array( array( 'tag' => 'custom_missing', 'registered' => false, 'origins' => array( 'elementor_data' ) ) )
	);
	assert_projection( in_array( 'SHORTCODE_HANDLER_MISSING:custom_missing', $missing['warnings'], true ), 'generic missing handler forces explicit warning' );

	$repeat_a = Elementor_Projection_Plan::from_document(
		projection_document( 'legacy_html', 'projectable' ),
		array(
			array( 'tag' => 'table', 'registered' => true, 'origins' => array( 'post_content', 'elementor_data' ) ),
			array( 'tag' => 'caption', 'registered' => true, 'origins' => array( 'post_content' ) ),
		)
	);
	$repeat_b = Elementor_Projection_Plan::from_document(
		projection_document( 'legacy_html', 'projectable' ),
		array(
			array( 'tag' => 'caption', 'registered' => true, 'origins' => array( 'post_content' ) ),
			array( 'tag' => 'table', 'registered' => true, 'origins' => array( 'elementor_data', 'post_content' ) ),
		)
	);
	assert_projection( $repeat_a['projection_hash'] === $repeat_b['projection_hash'], 'dependency ordering does not change projection hash' );

	$changed = Elementor_Projection_Plan::from_document( projection_document( 'legacy_html', 'projectable', 'source-b' ) );
	assert_projection( $legacy['projection_hash'] !== $changed['projection_hash'], 'source hash change changes projection hash' );

	echo "ALL PASS\n";
}
