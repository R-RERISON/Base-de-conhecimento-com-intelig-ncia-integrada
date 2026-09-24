<?php
/**
 * Structural contract for G-590 Evidence Contract v1.3.
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';
$runner = file_get_contents( $root . 'includes/class-search-section-runner-g590.php' );

if ( ! is_string( $runner ) ) {
	fwrite( STDERR, "Falha ao ler runner G-590.\n" );
	exit( 1 );
}

$checks = array(
	'strong_hierarchy_fields' => str_contains( $runner, "'strong_numbered_non_heading_nodes'" )
		&& str_contains( $runner, "'strong_numbered_with_heading_context'" )
		&& str_contains( $runner, "'strong_numbered_without_heading_context'" ),
	'strong_hierarchy_only_numbering_inferred' => str_contains(
		$runner,
		"'numbering_inferred' === $hierarchy_source && $depth > 1"
	),
	'raw_hierarchy_still_observed' => str_contains( $runner, "'numbered_non_heading_nodes'" )
		&& str_contains( $runner, "'hierarchy_node_source_counts'" ),
	'strong_samples_preserved' => str_contains( $runner, "'strong_numbered_samples'" )
		&& str_contains( $runner, "'text_excerpt'" )
		&& str_contains( $runner, "'hierarchy_confidence'" ),
	'gate_uses_strong_metric' => str_contains(
		$runner,
		"$coverage['strong_numbered_without_heading_context']"
	),
	'probe_repeated_title_runtime_fallback' => str_contains(
		$runner,
		"'repeated_title_runtime_probe'"
	),
	'probe_fallback_not_auto_pass' => str_contains( $runner, 'section_query_found_expected' )
		&& str_contains( $runner, 'section_query_failed' ),
	'probe_strategy_diagnostics' => str_contains( $runner, "'probe_strategy_counts'" )
		&& str_contains( $runner, "'probe_candidate_source_kinds'" ),
	'coverage_single_semantics' => str_contains(
		$runner,
		'return self::coverage_finalize( $accumulator, count( $post_ids ) );'
	),
	'production_ranker_untouched' => ! str_contains( $runner, 'Search_Section_Ranker::VERSION =' ),
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
