<?php
/**
 * Contrato estático do preflight P-670.
 *
 * @package BDC_Knowledge_Base
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$validator_path = $root . '/tools/homologation/spec006/validate-p670-preflight.py';
$source = file_get_contents( $validator_path );

if ( ! is_string( $source ) ) {
	fwrite( STDERR, 'Unable to read P670 preflight validator.' . PHP_EOL );
	exit( 1 );
}

$checks = array(
	'local_only' => str_contains( $source, '"execution_mode": "LOCAL_ONLY"' ),
	'frozen_p6504_name' => str_contains( $source, 'base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.4.zip' ),
	'frozen_p6504_sha' => str_contains( $source, '0e4860ed0be34033c63358c9f0798d7c9564420dd738fc66d587180fbf14cf77' ),
	'requires_p640_current' => str_contains( $source, 'spec006-p640-local-validation-current.json' ),
	'requires_p640_environmental_reconciliation' => str_contains( $source, 'spec006-p640-environmental-reconciliation-current.json' )
		&& str_contains( $source, 'p640_environmental_runtime_reconciled' ),
	'requires_p650_current' => str_contains( $source, 'spec006-p650-local-package-validation-current.json' ),
	'requires_p660_current' => str_contains( $source, 'spec006-p660-local-validation-current.json' ),
	'requires_wordpress_final' => str_contains( $source, 'spec006-p6504-wordpress-final-gates-current.json' )
		&& str_contains( $source, 'wordpress_final_same_artifact' ),
	'requires_plugin_disposition' => str_contains( $source, 'spec006-p6504-plugin-check-disposition-current.json' )
		&& str_contains( $source, 'PASS_WITH_EXPLICIT_DISPOSITION' ),
	'requires_complete_rollback' => str_contains( $source, '"rollback", "pass"' )
		&& str_contains( $source, '"rollback", "data_preserved"' ),
	'requires_native_runtime' => str_contains( $source, '"native_preflight", "runtime", "pass"' ),
	'official_static_required' => str_contains( $source, '"official_static_required": True' ),
	'no_global_ignore' => str_contains( $source, '"global_ignore_allowed": False' ),
	'runtime_limitation_explicit' => str_contains( $source, '"runtime_environment_limitation_allowed_only_with_explicit_disposition": True' ),
	'git_plugin_tree_provenance' => str_contains( $source, 'HEAD:plugin/base-conhecimento-inteligencia-integrada' ),
	'no_automatic_ledger_update' => str_contains( $source, '"updated_by_validator": False' ),
	'no_cutover' => str_contains( $source, '"cutover_authorized": False' ),
	'no_retirement' => str_contains( $source, '"retirement_authorized": False' ),
	'no_version_1_0' => str_contains( $source, '"version_1_0_authorized": False' ),
	'pass_is_preconditions_only' => str_contains( $source, '"PASS_PRECONDITIONS"' )
		&& str_contains( $source, '"P670_LEDGER_AND_CLOSEOUT_REVIEW"' ),
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
