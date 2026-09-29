<?php
/**
 * Static contract checks for G-585 Decommission Readiness v2.
 */

$root = dirname( __DIR__, 2 );
$runner = file_get_contents( $root . '/plugin/base-conhecimento-inteligencia-integrada/includes/class-search-independence-runner-g585.php' );

if ( ! is_string( $runner ) ) {
	fwrite( STDERR, "FAIL: runner unreadable\n" );
	exit( 1 );
}

$checks = array(
	'schema v2.1' => str_contains( $runner, "'schema_version' => '2.1.0'" ),
	'surface probe' => str_contains( $runner, 'legacy_surface_dependency_probe' ),
	'safe front page evidence' => str_contains( $runner, "'front_page_legacy_evidence'" ),
	'source fingerprint' => str_contains( $runner, "'front_page_content_sha256'" ),
	'context sanitizer' => str_contains( $runner, 'sanitize_legacy_excerpt' ),
	'shortcode locator' => str_contains( $runner, 'legacy_shortcode_tag_at_offset' ),
	't585.1' => str_contains( $runner, "'t585_1_surface_dependency_zero'" ),
	'cutover false' => str_contains( $runner, "'cutover_authorized' => false" ),
	'master ledger preflight' => str_contains( $runner, 'MASTER_LEDGER_PREFLIGHT_REQUIRED' ),
	'next gate boundary' => str_contains( $runner, "SPEC005_BOUNDARY_REVIEW" ),
	'no automatic deactivation' => str_contains( $runner, "'automatic_legacy_deactivation' => false" ),
	'no legacy cleanup' => str_contains( $runner, "'legacy_data_cleanup' => false" ),
	'parent ranker frozen' => str_contains( $runner, "'lexical-ranker-v1.0.0' === Lexical_Ranker::VERSION" ),
);

$failed = array();
foreach ( $checks as $label => $pass ) {
	echo ( $pass ? 'PASS' : 'FAIL' ) . ': ' . $label . PHP_EOL;
	if ( ! $pass ) {
		$failed[] = $label;
	}
}

if ( $failed ) {
	exit( 1 );
}

echo count( $checks ) . '/' . count( $checks ) . " PASS\n";
