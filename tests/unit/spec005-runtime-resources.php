<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada/';
$builder = file_get_contents( $root . '/tools/t100e/build_release.py' );
$regression = file_get_contents( $root . '/tools/t100e/regression_runner.py' );
$loader = file_get_contents( $plugin . 'includes/class-golden-suite-loader.php' );

foreach ( compact( 'builder', 'regression', 'loader' ) as $name => $content ) {
	if ( ! is_string( $content ) ) {
		fwrite( STDERR, "Falha ao ler {$name}.\n" );
		exit( 1 );
	}
}

$resources = array(
	'resources/search/golden-relevance-v1.0.0.json',
	'resources/search/technical-challenge-v1.0.0.json',
);
$flags = array(
	'BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD',
	'BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD',
	'BDC_KB_SPEC005_G590_SECTION_BUILD',
	'BDC_KB_UX004_H030_TECHNICAL_BUILD',
);

$checks = array(
	'loader_declares_golden_resource' => str_contains( $loader, $resources[0] ),
	'loader_declares_challenge_resource' => str_contains( $loader, $resources[1] ),
	'builder_has_conditional_runtime_resources' => str_contains( $builder, 'CONDITIONAL_RUNTIME_RESOURCES' ),
	'regression_has_conditional_runtime_resources' => str_contains( $regression, 'CONDITIONAL_RUNTIME_RESOURCES' ),
	'regression_promotes_resources_to_active_required' => str_contains( $regression, 'active_required.update(paths)' ),
	'regression_checks_missing_runtime_resources' => str_contains( $regression, 'missing_runtime_resources' ),
);

foreach ( $resources as $resource ) {
	$checks[ 'source_exists_' . basename( $resource ) ] = is_file( $plugin . $resource );
	$checks[ 'builder_knows_' . basename( $resource ) ] = str_contains( $builder, '"' . $resource . '"' );
	$checks[ 'regression_knows_' . basename( $resource ) ] = str_contains( $regression, '"' . $resource . '"' );
}
foreach ( $flags as $flag ) {
	$checks[ 'builder_flag_' . strtolower( $flag ) ] = str_contains( $builder, '"' . $flag . '"' );
	$checks[ 'regression_flag_' . strtolower( $flag ) ] = str_contains( $regression, '"' . $flag . '"' );
}

$failed = 0;
foreach ( $checks as $name => $pass ) {
	echo ( $pass ? 'PASS ' : 'FAIL ' ) . $name . PHP_EOL;
	if ( ! $pass ) {
		++$failed;
	}
}

echo PHP_EOL . 'RESULT passed=' . ( count( $checks ) - $failed ) . ' failed=' . $failed . PHP_EOL;
exit( $failed > 0 ? 1 : 0 );
