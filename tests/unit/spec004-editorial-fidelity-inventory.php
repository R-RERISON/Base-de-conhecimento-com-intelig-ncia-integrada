<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
}

namespace BDC\KnowledgeBase {
	require_once __DIR__ . '/../../plugin/base-conhecimento-inteligencia-integrada/includes/class-editorial-fidelity-inventory-smoke.php';

	$assertions = 0;
	function assert_t094_fidelity( bool $condition, string $message ): void {
		global $assertions;
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
		++$assertions;
		echo "PASS {$message}\n";
	}

	$empty_method = new \ReflectionMethod( Editorial_Fidelity_Inventory_Smoke::class, 'empty_features' );
	$empty = $empty_method->invoke( null );
	assert_t094_fidelity( is_array( $empty ) && isset( $empty['links'], $empty['images'], $empty['inline_formatting'] ), 'feature schema present' );
	assert_t094_fidelity( 0 === array_sum( $empty ), 'empty features start zero' );

	$classify = new \ReflectionMethod( Editorial_Fidelity_Inventory_Smoke::class, 'classify_fidelity' );
	assert_t094_fidelity( 'not_applicable' === $classify->invoke( null, 'empty', $empty, 0, false ), 'empty not applicable' );
	assert_t094_fidelity( 'native_core_blocks' === $classify->invoke( null, 'gutenberg', $empty, 0, false ), 'gutenberg native' );
	assert_t094_fidelity( 'elementor_source_adapter_required' === $classify->invoke( null, 'elementor', $empty, 0, true ), 'elementor adapter required' );
	assert_t094_fidelity( 'shortcode_resolution_required' === $classify->invoke( null, 'legacy_html', $empty, 1, false ), 'shortcode requires resolver' );

	$rich = $empty;
	$rich['links'] = 1;
	assert_t094_fidelity( 'rich_html_source_required' === $classify->invoke( null, 'legacy_html', $rich, 0, false ), 'link requires rich source' );
	$rich = $empty;
	$rich['images'] = 1;
	assert_t094_fidelity( 'rich_html_source_required' === $classify->invoke( null, 'legacy_html', $rich, 0, false ), 'image requires rich source' );
	$rich = $empty;
	$rich['inline_formatting'] = 1;
	assert_t094_fidelity( 'rich_html_source_required' === $classify->invoke( null, 'legacy_html', $rich, 0, false ), 'formatting requires rich source' );

	$table = $empty;
	$table['rowspan_cells'] = 1;
	assert_t094_fidelity( 'complex_table_source_required' === $classify->invoke( null, 'legacy_html', $table, 0, false ), 'complex table isolated' );
	assert_t094_fidelity( 'kd_structure_sufficient_candidate' === $classify->invoke( null, 'plain_text', $empty, 0, false ), 'plain structure candidate' );

	echo "ALL PASS {$assertions}\n";
}
