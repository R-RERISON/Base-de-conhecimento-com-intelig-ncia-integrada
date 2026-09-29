<?php
/**
 * Contrato estático do packaging P-650.
 *
 * @package BDC_Knowledge_Base
 */

declare(strict_types=1);

$root    = dirname( __DIR__, 2 );
$builder = $root . '/tools/homologation/spec006/build-p650-production.py';
$source  = file_get_contents( $builder );

if ( ! is_string( $source ) ) {
	fwrite( STDERR, 'Unable to read P650 builder.' . PHP_EOL );
	exit( 1 );
}

$checks = array(
	'local_only' => str_contains( $source, '"execution_mode": "LOCAL_ONLY"' ),
	'deterministic_double_build' => str_contains( $source, 'first.read_bytes() == second.read_bytes()' ),
	'single_root_contract' => str_contains( $source, '"single_root"' ),
	'excludes_engineering_loader' => str_contains( $source, 'class-engineering-module-loader.php' ),
	'excludes_declared_engineering_files' => str_contains( $source, 'engineering_files' )
		&& str_contains( $source, 'ENGINEERING_FILE_PATTERN' ),
	'removes_engineering_bootstrap_calls' => str_contains( $source, 'Engineering_Module_Loader::load_enabled();' )
		&& str_contains( $source, 'Engineering_Module_Loader::register_enabled();' ),
	'preserves_search' => str_contains( $source, "Runtime_Module_Registry::load( 'search' )" ),
	'preserves_public_preview' => str_contains( $source, "Runtime_Module_Registry::load( 'public_experience_preview' )" ),
	'preserves_word_cloud' => str_contains( $source, "Runtime_Module_Registry::load( 'word_cloud' )" ),
	'requires_distribution_docs' => str_contains( $source, '"LICENSE"' )
		&& str_contains( $source, '"CHANGELOG.md"' )
		&& str_contains( $source, '"UPGRADE.md"' )
		&& str_contains( $source, '"SECURITY.md"' )
		&& str_contains( $source, '"CONTRIBUTING.md"' )
		&& str_contains( $source, '"readme.txt"' ),
	'forbids_repo_only_paths' => str_contains( $source, '"specs/"' )
		&& str_contains( $source, '"evidence/"' )
		&& str_contains( $source, '"tests/"' )
		&& str_contains( $source, '"tools/"' )
		&& str_contains( $source, '"vendor/"' ),
	'no_source_mutation' => str_contains( $source, '"source_checkout_modified": False' ),
	'no_cutover' => str_contains( $source, '"cutover_authorized": False' ),
	'no_retirement' => str_contains( $source, '"retirement_authorized": False' ),
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
