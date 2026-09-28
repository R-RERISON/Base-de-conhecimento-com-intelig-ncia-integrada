<?php
/**
 * G-590 Evidence Contract v1.5 pure gate test.
 */
declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
}

namespace BDC\KnowledgeBase {
	final class R260_Structural_Projector {
		public const VERSION = 'r260-structural-projection-v1.0.0';
	}

	require_once dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-search-section-runner-g590.php';

	$coverage = array(
		'structural_recovery_candidate_count' => 548,
		'r260_discovery' => array(
			'totals' => array(
				'candidate_count' => 548,
				'toc_signal_count' => 77,
				'body_signal_count' => 289,
				'uncertain_count' => 182,
			),
			'shadow_projection' => array(
				'states' => array( 'duplicate_candidate_ambiguous' => 78 ),
			),
			'runtime_structural_projection' => array(
				'version' => R260_Structural_Projector::VERSION,
				'deterministic_candidate_count' => 548,
				'disposition_counts' => array(
					'toc_suppressed' => 77,
					'body_projected' => 289,
					'existing_heading_redundant' => 0,
					'uncertain' => 182,
				),
				'body_bearing_count' => 289,
				'canonical_projected_count' => 289,
				'runtime_projected_count' => 289,
				'duplicate_body_count' => 78,
				'runtime_generated_anchor_count' => 250,
				'runtime_unresolved_anchor_count' => 39,
				'projection_gap_count' => 0,
				'projection_extra_count' => 0,
				'unsafe_promotion_count' => 0,
				'overflow_post_count' => 0,
				'overflow_total' => 0,
				'identity_collision_count' => 0,
				'repository_error_count' => 0,
				'generated_anchor_materialization_failed' => 0,
				'visible_text_changed' => 0,
			),
		),
	);

	$method = new \ReflectionMethod( Search_Section_Runner_G590::class, 'structural_coverage_pass' );
	$method->setAccessible( true );

	$checks = array();
	$checks['clean_pass'] = true === $method->invoke( null, $coverage );

	$blockers = array(
		'projection_gap_count',
		'projection_extra_count',
		'unsafe_promotion_count',
		'overflow_post_count',
		'identity_collision_count',
		'repository_error_count',
		'generated_anchor_materialization_failed',
		'visible_text_changed',
	);
	foreach ( $blockers as $field ) {
		$case = $coverage;
		$case['r260_discovery']['runtime_structural_projection'][ $field ] = 1;
		$checks[ $field . '_blocks' ] = false === $method->invoke( null, $case );
	}

	$case = $coverage;
	$case['r260_discovery']['runtime_structural_projection']['runtime_projected_count'] = 288;
	$checks['missing_projected_section_blocks'] = false === $method->invoke( null, $case );

	$case = $coverage;
	$case['r260_discovery']['runtime_structural_projection']['runtime_generated_anchor_count'] = 251;
	$checks['anchor_partition_blocks'] = false === $method->invoke( null, $case );

	$case = $coverage;
	$case['r260_discovery']['runtime_structural_projection']['duplicate_body_count'] = 77;
	$checks['duplicate_body_reconciliation_blocks'] = false === $method->invoke( null, $case );

	$case = $coverage;
	$case['r260_discovery']['runtime_structural_projection']['disposition_counts']['toc_suppressed'] = 76;
	$checks['disposition_partition_blocks'] = false === $method->invoke( null, $case );

	$failed = 0;
	foreach ( $checks as $name => $pass ) {
		echo ( $pass ? 'PASS ' : 'FAIL ' ) . $name . PHP_EOL;
		if ( ! $pass ) { ++$failed; }
	}
	echo PHP_EOL . 'RESULT passed=' . ( count( $checks ) - $failed ) . ' failed=' . $failed . PHP_EOL;
	exit( $failed > 0 ? 1 : 0 );
}
