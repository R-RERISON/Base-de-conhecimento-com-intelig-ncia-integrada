<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';
$bootstrap = file_get_contents( $root . 'base-conhecimento-inteligencia-integrada.php' );
$runner = file_get_contents( $root . 'includes/class-search-independence-runner-g585.php' );
$ranker = file_get_contents( $root . 'includes/class-lexical-ranker.php' );
$service = file_get_contents( $root . 'includes/class-search-service.php' );
$rebuild = file_get_contents( $root . 'includes/class-search-rebuild-service.php' );
$lifecycle = file_get_contents( $root . 'includes/class-search-lifecycle.php' );

foreach ( compact( 'bootstrap', 'runner', 'ranker', 'service', 'rebuild', 'lifecycle' ) as $name => $content ) {
	if ( ! is_string( $content ) ) {
		fwrite( STDERR, "Falha ao ler {$name}.\n" );
		exit( 1 );
	}
}

$strip_comments = static function ( string $source ): string {
	$tokens = token_get_all( $source );
	$out = '';
	foreach ( $tokens as $token ) {
		if ( is_array( $token ) && in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
			continue;
		}
		$out .= is_array( $token ) ? $token[1] : $token;
	}
	return $out;
};

$runner_code = $strip_comments( $runner );
$destructive = '/DROP\s+TABLE|TRUNCATE\s+TABLE|delete_option\s*\(|wp_delete_post\s*\(|delete_post_meta\s*\(|wp_delete_term\s*\(/i';
$editorial_write = '/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i';
$network = '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|fsockopen\s*\(|stream_socket_client\s*\(/i';

$checks = array(
	'version_g585' => str_contains( $bootstrap, 'Version: 0.5.0-g585.1' ),
	'g530_engine_on' => str_contains( $bootstrap, "BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD', true" ),
	'g580_runner_off' => str_contains( $bootstrap, "BDC_KB_SPEC005_G580_LIFECYCLE_BUILD', false" ),
	'g585_runner_on' => str_contains( $bootstrap, "BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD', true" ),
	'g585_loads_golden_dependencies' => str_contains( $bootstrap, "class-golden-suite-loader.php" )
		&& str_contains( $bootstrap, "class-golden-gate-runner-g550.php" ),
	'g585_runner_loaded' => str_contains( $bootstrap, "class-search-independence-runner-g585.php" ),
	'g585_runner_registered' => str_contains( $bootstrap, "Search_Independence_Runner_G585::register()" ),

	'runner_post_nonce' => str_contains( $runner, 'wp_verify_nonce' ) && str_contains( $runner, "'POST' !== strtoupper" ),
	'runner_manage_options' => str_contains( $runner, "current_user_can( 'manage_options' )" ),
	'runner_never_deactivates_plugins' => ! str_contains( $runner_code, 'deactivate_plugins(' ),
	'runner_no_legacy_cleanup' => 1 !== preg_match( $destructive, $runner_code ),
	'runner_no_editorial_write' => 1 !== preg_match( $editorial_write, $runner_code ),
	'runner_no_network' => 1 !== preg_match( $network, $runner_code ),

	't585_scans_loaded_runtime' => str_contains( $runner, 'get_included_files()' )
		&& str_contains( $runner, 'runtime_static_scan' ),
	't585_excludes_only_gate_runner' => str_contains( $runner, '$normalized === $self' )
		&& str_contains( $runner, "'excluded_gate_runner'" ),
	't585_scans_symbols_hooks' => str_contains( $runner, 'get_declared_classes()' )
		&& str_contains( $runner, 'get_defined_functions()' )
		&& str_contains( $runner, 'array_keys( is_array( $wp_filter )' ),
	't586_reads_active_plugins' => str_contains( $runner, "get_option( 'active_plugins'" )
		&& str_contains( $runner, "get_site_option( 'active_sitewide_plugins'" ),
	't586_blocks_active_legacy' => str_contains( $runner, "'BLOCKED_LEGACY_ACTIVE'" ),
	't587_runs_search' => str_contains( $runner, 'Search_Service::search' ),
	't587_runs_golden' => str_contains( $runner, 'Golden_Gate_Runner_G550::run()' ),
	't588_runs_rebuild' => str_contains( $runner, 'Search_Rebuild_Service::rebuild()' )
		&& str_contains( $runner, "'mismatch_count'" ),
	't589_lifecycle_prepare' => str_contains( $runner, 'Search_Lifecycle::prepare_schema()' ),
	't589_kill_switch' => str_contains( $runner, "add_filter( 'bdc_kb_search_enabled'" )
		&& str_contains( $runner, "'search_module_disabled'" ),
	't589_deactivation_retention' => str_contains( $runner, 'Search_Lifecycle::deactivate()' )
		&& str_contains( $runner, "'rows_retained'" )
		&& str_contains( $runner, "'state_retained'" ),
	'runner_gate_t585_t5892' => str_contains( $runner, "'t585_static_runtime_dependency_zero'" )
		&& str_contains( $runner, "'t586_legacy_inactive'" )
		&& str_contains( $runner, "'t587_search_golden_without_legacy'" )
		&& str_contains( $runner, "'t588_rebuild_without_legacy'" )
		&& str_contains( $runner, "'t589_lifecycle_rollback_without_legacy'" )
		&& str_contains( $runner, "'t589_1_dependency_zero_evidence_generated'" )
		&& str_contains( $runner, "'t589_2_g585_pass'" ),
	'ranker_version_unchanged' => str_contains( $ranker, "public const VERSION = 'lexical-ranker-v1.0.0';" ),
	'search_core_unchanged_contract' => str_contains( $service, "public const RESULT_VERSION = 'search-result-v1.0.0';" ),
	'rebuild_still_explicit' => str_contains( $rebuild, "current_user_can( 'manage_options' )" ),
	'lifecycle_still_no_implicit_rebuild' => ! str_contains( $strip_comments( $lifecycle ), 'Search_Rebuild_Service::rebuild' ),
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
