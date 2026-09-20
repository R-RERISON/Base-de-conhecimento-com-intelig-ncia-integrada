<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';
$bootstrap = file_get_contents( $root . 'base-conhecimento-inteligencia-integrada.php' );
$service = file_get_contents( $root . 'includes/class-search-service.php' );
$lifecycle = file_get_contents( $root . 'includes/class-search-lifecycle.php' );
$rebuild = file_get_contents( $root . 'includes/class-search-rebuild-service.php' );
$runner = file_get_contents( $root . 'includes/class-search-lifecycle-runner-g580.php' );
$uninstall = file_get_contents( $root . 'uninstall.php' );
$ranker = file_get_contents( $root . 'includes/class-lexical-ranker.php' );

foreach ( compact( 'bootstrap', 'service', 'lifecycle', 'rebuild', 'runner', 'uninstall', 'ranker' ) as $name => $content ) {
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

$lifecycle_code = $strip_comments( $lifecycle );
$rebuild_code = $strip_comments( $rebuild );
$uninstall_code = $strip_comments( $uninstall );
$runner_code = $strip_comments( $runner );

$destructive = '/DROP\s+TABLE|TRUNCATE\s+TABLE|delete_option\s*\(|wp_delete_post\s*\(|delete_post_meta\s*\(|wp_delete_term\s*\(/i';

$checks = array(
	'version_g580' => str_contains( $bootstrap, 'Version: 0.5.0-g580.1' ),
	'g530_engine_on' => str_contains( $bootstrap, "BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD', true" ),
	'g570_runner_off' => str_contains( $bootstrap, "BDC_KB_SPEC005_G570_SECURITY_PERFORMANCE_BUILD', false" ),
	'g580_runner_on' => str_contains( $bootstrap, "BDC_KB_SPEC005_G580_LIFECYCLE_BUILD', true" ),
	'search_enabled_default_true' => str_contains( $bootstrap, "define( 'BDC_KB_SEARCH_ENABLED', true )" ),

	'lifecycle_activation_hook' => str_contains( $lifecycle, 'register_activation_hook' ),
	'lifecycle_deactivation_hook' => str_contains( $lifecycle, 'register_deactivation_hook' ),
	'lifecycle_update_hook' => str_contains( $lifecycle, 'upgrader_process_complete' ),
	'lifecycle_schema_only' => str_contains( $lifecycle, 'Search_Projection_Repository::ensure_schema' )
		&& ! str_contains( $lifecycle_code, 'Search_Rebuild_Service::rebuild' )
		&& ! str_contains( $lifecycle_code, 'Search_Document_Builder::build' )
		&& ! str_contains( $lifecycle_code, 'Search_Projection_Repository::upsert' )
		&& ! str_contains( $lifecycle_code, 'delete_stale_rows' ),
	'lifecycle_no_destructive_cleanup' => 1 !== preg_match( $destructive, $lifecycle_code ),

	'rebuild_explicit_manage_options' => str_contains( $rebuild, "current_user_can( 'manage_options' )" ),
	'rebuild_building_state' => str_contains( $rebuild, "'status' => 'building'" ),
	'rebuild_stale_cleanup_after_pass1' => false !== strpos( $rebuild, 'count( $post_ids ) === (int) $pass1' )
		&& false !== strpos( $rebuild, 'delete_stale_rows' )
		&& strpos( $rebuild, 'count( $post_ids ) === (int) $pass1' ) < strpos( $rebuild, 'delete_stale_rows' ),
	'rebuild_second_pass' => str_contains( $rebuild, "self::run_pass( $post_ids, 'pass2' )" ),
	'rebuild_requires_no_change' => str_contains( $rebuild, "0 === (int) $pass2['written']" )
		&& str_contains( $rebuild, "count( $post_ids ) === (int) $pass2['no_change']" ),
	'rebuild_no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i', $rebuild_code ),
	'rebuild_no_network' => 1 !== preg_match( '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|fsockopen\s*\(|stream_socket_client\s*\(/i', $rebuild_code ),
	'rebuild_no_asi' => 1 !== preg_match( '/\basi(?:4)?_/i', $rebuild_code ),

	'service_kill_switch_filter' => str_contains( $service, "apply_filters( 'bdc_kb_search_enabled'" ),
	'service_disabled_fallback' => str_contains( $service, "'search_module_disabled'" )
		&& str_contains( $service, 'self::wordpress_fallback' ),
	'service_projection_fallback_preserved' => str_contains( $service, "'projection_not_ready'" ),
	'no_persistent_enable_option' => ! str_contains( $service, "get_option( 'bdc_kb_search_enabled" )
		&& ! str_contains( $lifecycle, "update_option( 'bdc_kb_search_enabled" ),

	'uninstall_guard' => str_contains( $uninstall, "defined( 'WP_UNINSTALL_PLUGIN' )" ),
	'uninstall_non_destructive' => 1 !== preg_match( $destructive, $uninstall_code ),

	'runner_post_nonce' => str_contains( $runner, 'wp_verify_nonce' ) && str_contains( $runner, "'POST' !== strtoupper" ),
	'runner_manage_options' => str_contains( $runner, "current_user_can( 'manage_options' )" ),
	'runner_fallback_probe' => str_contains( $runner, "'g580_fallback_probe'" ),
	'runner_disable_probe' => str_contains( $runner, "add_filter( 'bdc_kb_search_enabled'" )
		&& str_contains( $runner, "remove_filter( 'bdc_kb_search_enabled'" ),
	'runner_explicit_rebuild' => str_contains( $runner, 'Search_Rebuild_Service::rebuild' ),
	'runner_editorial_fingerprint' => str_contains( $runner, "'editorial_fingerprint_before'" )
		&& str_contains( $runner, "'editorial_fingerprint_after'" ),
	'runner_gate_t580_t584' => str_contains( $runner, "'t580_activation_update_pass'" )
		&& str_contains( $runner, "'t581_rebuild_fallback_pass'" )
		&& str_contains( $runner, "'t582_disable_module_pass'" )
		&& str_contains( $runner, "'t583_uninstall_retention_pass'" )
		&& str_contains( $runner, "'t584_g580_pass'" ),
	'runner_no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|wp_insert_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i', $runner_code ),
	'ranker_version_unchanged' => str_contains( $ranker, "public const VERSION = 'lexical-ranker-v1.0.0';" ),
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
