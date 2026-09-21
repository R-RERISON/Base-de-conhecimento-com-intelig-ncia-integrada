<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';

$paths = array(
	'bootstrap' => 'base-conhecimento-inteligencia-integrada.php',
	'contract' => 'includes/class-word-cloud-contract.php',
	'quality' => 'includes/class-word-cloud-quality.php',
	'service' => 'includes/class-word-cloud-service.php',
	'admin' => 'includes/class-word-cloud-admin.php',
	'home' => 'includes/class-public-home-read-model.php',
	'template' => 'templates/public-home-preview.php',
);

$src = array();
foreach ( $paths as $key => $relative ) {
	$content = file_get_contents( $root . $relative );
	if ( ! is_string( $content ) ) {
		fwrite( STDERR, "Falha ao ler {$relative}.\n" );
		exit( 1 );
	}
	$src[ $key ] = $content;
}

$runtime = $src['contract'] . "\n" . $src['quality'] . "\n" . $src['service'] . "\n" . $src['admin'];

$checks = array(
	'version_p580wc1' => str_contains( $src['bootstrap'], 'Version: 0.5.0-p580wc.1' ),
	'build_flag' => str_contains( $src['bootstrap'], "BDC_KB_WORD_CLOUD_BUILD', true" ),
	'module_loaded' => str_contains( $src['bootstrap'], 'class-word-cloud-service.php' )
		&& str_contains( $src['bootstrap'], 'Word_Cloud_Service::register()' ),
	'bdc_owned_options' => str_contains( $src['contract'], 'bdc_kb_word_cloud_snapshot' )
		&& str_contains( $src['contract'], 'bdc_kb_word_cloud_settings' ),
	'no_asi_runtime' => 1 !== preg_match( '/\basi(?:4)?[_-]/i', $runtime )
		&& ! str_contains( strtolower( $runtime ), 'advanced-search-intelligence' ),
	'no_network' => 1 !== preg_match( '/wp_remote_|curl_|fsockopen|stream_socket_client/i', $runtime ),
	'no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i', $runtime ),
	'snapshot_not_request_generate' => str_contains( $src['home'], 'Word_Cloud_Service::public_terms' )
		&& ! str_contains( $src['home'], 'Word_Cloud_Service::generate' ),
	'content_extractor' => str_contains( $src['service'], 'Content_Extractor::extract' ),
	'title_heading_content_taxonomy' => str_contains( $src['service'], "'title'" )
		&& str_contains( $src['service'], "'heading'" )
		&& str_contains( $src['service'], "'content'" )
		&& str_contains( $src['service'], "'taxonomy'" ),
	'allow_block' => str_contains( $src['service'], 'allowlist' )
		&& str_contains( $src['service'], 'blocklist' ),
	'quality_maturity' => str_contains( $src['quality'], "'candidate'" )
		&& str_contains( $src['quality'], "'observed'" )
		&& str_contains( $src['quality'], "'mature'" )
		&& str_contains( $src['quality'], "'promoted'" ),
	'snapshot_lock' => str_contains( $src['service'], 'set_transient' )
		&& str_contains( $src['service'], 'delete_transient' ),
	'cron_hourly' => str_contains( $src['service'], 'wp_schedule_event' )
		&& str_contains( $src['contract'], 'hourly_generate' ),
	'deactivation_clears_cron' => str_contains( $src['service'], 'register_deactivation_hook' )
		&& str_contains( $src['service'], 'wp_clear_scheduled_hook' ),
	'health_history' => str_contains( $src['service'], 'public static function health' )
		&& str_contains( $src['service'], 'append_history' ),
	'pending_sources' => str_contains( $src['contract'], 'pending_telemetry_spec' )
		&& str_contains( $src['contract'], 'pending_governance_spec' ),
	'admin_post_nonce' => str_contains( $src['admin'], 'check_admin_referer' )
		&& str_contains( $src['admin'], "current_user_can( 'manage_options' )" ),
	'click_to_search' => str_contains( $src['template'], "home_preview_url( 0, (string) \$term['term'] )" ),
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
