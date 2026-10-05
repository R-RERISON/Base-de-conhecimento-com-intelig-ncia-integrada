<?php
/**
 * PX-730 package builder static contract.
 */

declare(strict_types=1);

$root    = dirname( __DIR__, 2 );
$builder = $root . '/tools/homologation/spec007/build-px730.py';
$source  = is_file( $builder ) ? file_get_contents( $builder ) : false;

$checks = array(
	'builder_exists' => is_string( $source ),
	'px730_build_identity' => is_string( $source )
		&& str_contains( $source, 'BUILD = "px730.1"' )
		&& str_contains( $source, 'LIVE_SEARCH_CONTRACT = "px730-live-search-v1.0.0"' ),
	'reuses_p650_pruning' => is_string( $source )
		&& str_contains( $source, 'p650.engineering_contract()' )
		&& str_contains( $source, 'p650.transform_bootstrap' )
		&& str_contains( $source, 'p650.inspect_zip' ),
	'deterministic_double_build' => is_string( $source )
		&& str_contains( $source, 'first.read_bytes() == second.read_bytes()' ),
	'px730_static_contract_required' => is_string( $source )
		&& str_contains( $source, 'spec007-px730-live-search-contract.php' )
		&& str_contains( $source, 'run_static_contract()' ),
	'distributed_php_lint_required' => is_string( $source )
		&& str_contains( $source, 'lint_distribution(first)' )
		&& str_contains( $source, '[php, "-l", str(path)]' ),
	'stored_zip_deterministic_surface' => is_string( $source )
		&& str_contains( $source, 'zipfile.ZIP_STORED' )
		&& str_contains( $source, 'p650.FIXED_TIME' ),
	'no_ranker_schema_mutation' => is_string( $source )
		&& str_contains( $source, '"ranker_schema_mutation": False' ),
	'no_cutover' => is_string( $source )
		&& str_contains( $source, '"cutover_authorized": False' ),
	'no_retirement' => is_string( $source )
		&& str_contains( $source, '"retirement_authorized": False' ),
	'next_gate_smoke' => is_string( $source )
		&& str_contains( $source, '"next_gate": "PX730_WORDPRESS_CANDIDATE_SMOKE"' ),
);

$failed = array_keys( array_filter( $checks, static fn ( bool $pass ): bool => ! $pass ) );

echo json_encode(
	array(
		'gate' => 'PX-730-PACKAGE',
		'status' => empty( $failed ) ? 'PASS' : 'FAIL',
		'checks' => $checks,
		'failed' => $failed,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;

exit( empty( $failed ) ? 0 : 1 );
