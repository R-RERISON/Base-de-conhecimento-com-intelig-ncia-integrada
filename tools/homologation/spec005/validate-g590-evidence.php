<?php

declare(strict_types=1);

if ( $argc < 2 ) {
	fwrite( STDERR, "Uso: php validate-g590-evidence.php <evidence.json>\n" );
	exit( 2 );
}

$path = (string) $argv[1];
if ( ! is_readable( $path ) ) {
	fwrite( STDERR, "Arquivo não encontrado: {$path}\n" );
	exit( 2 );
}

$raw = file_get_contents( $path );
$data = is_string( $raw ) ? json_decode( $raw, true ) : null;
if ( ! is_array( $data ) ) {
	fwrite( STDERR, "JSON inválido.\n" );
	exit( 2 );
}

$get = static function ( array $source, array $path, mixed $default = null ): mixed {
	$current = $source;
	foreach ( $path as $key ) {
		if ( ! is_array( $current ) || ! array_key_exists( $key, $current ) ) {
			return $default;
		}
		$current = $current[ $key ];
	}
	return $current;
};

$checks = array(
	'gate_is_g590' => 'G-590' === (string) ( $data['gate'] ?? '' ),
	'mode_is_environmental' => 'spec005_section_retrieval_deeplink_environmental' === (string) ( $data['mode'] ?? '' ),
	'product_version_is_rc' => '0.5.1-rc.2' === (string) $get( $data, array( 'environment', 'plugin' ), '' ),
	'build_id_is_g590' => 1 === preg_match(
		'/^g590\.2-[a-f0-9]{12}$/',
		(string) $get( $data, array( 'environment', 'build_id' ), '' )
	),
	'schema_current' => true === (bool) $get( $data, array( 'lifecycle', 'schema_contract', 'pass' ), false ),
	'no_implicit_reindex' => true === (bool) $get( $data, array( 'lifecycle', 'prepare_did_not_reindex' ), false ),
	'version_transition_safe' => true === (bool) $get( $data, array( 'lifecycle', 'version_transition_safe' ), false ),
	'rebuild_pass' => 'PASS' === (string) $get( $data, array( 'lifecycle', 'explicit_rebuild', 'status' ), '' ),
	'projection_ready' => 'ready' === (string) $get( $data, array( 'lifecycle', 'explicit_rebuild', 'state_after', 'status' ), '' ),
	'rebuild_deterministic' => 0 === (int) $get( $data, array( 'lifecycle', 'explicit_rebuild', 'determinism', 'mismatch_count' ), -1 ),

	'corpus_fully_analyzed' => (int) $get( $data, array( 'coverage', 'corpus_count' ), -1 )
		=== (int) $get( $data, array( 'coverage', 'posts_analyzed' ), -2 ),
	'no_extractor_errors' => 0 === (int) $get( $data, array( 'coverage', 'extractor_error_count' ), -1 ),
	'no_uncontextual_numbered_gap' => 0 === (int) $get( $data, array( 'coverage', 'numbered_without_heading_context' ), -1 ),
	'all_generated_source_kinds_probed' => empty( $get( $data, array( 'coverage', 'unprobed_source_kinds' ), array( '__missing__' ) ) ),

	'minimum_section_probes' => (int) $get( $data, array( 'section_deep_link_probes', 'eligible_probe_count' ), 0 )
		>= (int) $get( $data, array( 'section_deep_link_probes', 'minimum_required' ), 5 ),
	'section_queries_pass' => 0 === (int) $get( $data, array( 'section_deep_link_probes', 'section_query_failed' ), -1 ),
	'deep_links_pass' => 0 === (int) $get( $data, array( 'section_deep_link_probes', 'deep_link_failed' ), -1 ),
	'visible_text_unchanged' => 0 === (int) $get( $data, array( 'section_deep_link_probes', 'visible_text_changed' ), -1 ),

	'performance_pass' => true === (bool) $get( $data, array( 'performance', 'pass' ), false ),
	'performance_p95_budget' => (float) $get( $data, array( 'performance', 'p95_ms' ), INF )
		<= (float) $get( $data, array( 'performance', 'p95_budget_ms' ), 0.0 ),
	'performance_max_budget' => (float) $get( $data, array( 'performance', 'max_ms' ), INF )
		<= (float) $get( $data, array( 'performance', 'max_budget_ms' ), 0.0 ),
	'performance_no_technical_failures' => 0 === (int) $get( $data, array( 'performance', 'technical_failure_count' ), -1 ),

	'post_level_golden_pass' => 'PASS' === (string) $get( $data, array( 'post_level_golden_regression', 'status' ), '' ),
	'golden_blocking_zero' => 0 === (int) $get( $data, array( 'post_level_golden_regression', 'blocking_failed' ), -1 ),
	'golden_technical_zero' => 0 === (int) $get( $data, array( 'post_level_golden_regression', 'technical_failed' ), -1 ),

	'editorial_fingerprint_equal' => true === (bool) $get( $data, array( 'safety', 'editorial_fingerprint_equal' ), false ),
	'no_editorial_write' => true === (bool) $get( $data, array( 'safety', 'runtime_source', 'no_editorial_write' ), false ),
	'no_network' => true === (bool) $get( $data, array( 'safety', 'runtime_source', 'no_network' ), false ),
	'no_asi' => true === (bool) $get( $data, array( 'safety', 'runtime_source', 'no_asi' ), false ),
	'parent_ranker_frozen' => true === (bool) $get( $data, array( 'safety', 'runtime_source', 'parent_ranker_version_frozen' ), false ),
	'candidate_path_lightweight' => true === (bool) $get( $data, array( 'safety', 'runtime_source', 'candidate_query_does_not_load_sections' ), false ),

	't59014' => true === (bool) $get( $data, array( 'gate_result', 't59014_cross_spec_regression_pass' ), false ),
	't59015' => true === (bool) $get( $data, array( 'gate_result', 't59015_coverage_audit_pass' ), false ),
	't59016' => true === (bool) $get( $data, array( 'gate_result', 't59016_section_golden_deeplink_pass' ), false ),
	't59017' => true === (bool) $get( $data, array( 'gate_result', 't59017_lifecycle_schema_pass' ), false ),
	't59018' => true === (bool) $get( $data, array( 'gate_result', 't59018_security_performance_safety_pass' ), false ),
	't59019' => true === (bool) $get( $data, array( 'gate_result', 't59019_g590_pass' ), false ),
);

$failed = array();
foreach ( $checks as $name => $pass ) {
	echo ( $pass ? 'PASS ' : 'FAIL ' ) . $name . PHP_EOL;
	if ( ! $pass ) {
		$failed[] = $name;
	}
}

$review = true === (bool) $get( $data, array( 'coverage', 'numbered_granularity_review_required' ), false );
if ( $review ) {
	echo "REVIEW numbered_granularity_review_required=true\n";
}

echo PHP_EOL . 'RESULT passed=' . ( count( $checks ) - count( $failed ) ) . ' failed=' . count( $failed ) . PHP_EOL;

if ( ! empty( $failed ) ) {
	echo 'FAILED_CHECKS=' . implode( ',', $failed ) . PHP_EOL;
	exit( 1 );
}

exit( 0 );
