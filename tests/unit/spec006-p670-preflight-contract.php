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
	'frozen_p6503_name' => str_contains( $source, 'base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip' ),
	'frozen_p6503_sha' => str_contains( $source, '985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231' ),
	'requires_p640_current' => str_contains( $source, 'spec006-p640-local-validation-current.json' ),
	'requires_p640_environmental_reconciliation' => str_contains( $source, 'spec006-p640-environmental-reconciliation-current.json' )
		&& str_contains( $source, 'p640_environmental_runtime_reconciled' ),
	'requires_p650_current' => str_contains( $source, 'spec006-p650-local-package-validation-current.json' ),
	'requires_p660_current' => str_contains( $source, 'spec006-p660-local-validation-current.json' ),
	'requires_plugin_check' => str_contains( $source, 'spec006-p650-p660-plugin-check-current.json' ),
	'requires_rollback' => str_contains( $source, 'spec006-p650-rollback-current.json' ),
	'requires_environmental_smoke' => str_contains( $source, 'spec006-p6503-environmental-smoke-pass-20260930.json' ),
	'requires_plugin_tree_provenance' => str_contains( $source, 'HEAD:plugin/base-conhecimento-inteligencia-integrada' )
		&& str_contains( $source, 'p640_current_plugin_tree' )
		&& str_contains( $source, 'p650_current_plugin_tree' ),
	'requires_plugin_check_local' => str_contains( $source, 'official_plugin_check_local_pass' )
		&& str_contains( $source, '"github_actions_used"' ),
	'requires_data_preservation' => str_contains( $source, 'rollback_pass_and_data_preserved' )
		&& str_contains( $source, '"data_preserved"' ),
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
