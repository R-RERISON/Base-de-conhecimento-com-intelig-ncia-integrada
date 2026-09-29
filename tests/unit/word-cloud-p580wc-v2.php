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
	'version_p580wc2' => str_contains( $src['bootstrap'], 'Version: 0.5.0-p580wc.2' ),
	'contract_v110' => str_contains( $src['contract'], 'word-cloud-v1.1.0' )
		&& str_contains( $src['contract'], 'semantic-balanced-v2' ),
	'body_default_off' => str_contains( $src['contract'], "'include_body_terms' => false" ),
	'governed_allowlist' => str_contains( $src['contract'], 'Windows 11' )
		&& str_contains( $src['contract'], 'BitLocker' )
		&& str_contains( $src['contract'], 'MSTeams' ),
	'noise_blocklist' => str_contains( $src['contract'], "'usuario'" )
		&& str_contains( $src['contract'], "'sistema'" )
		&& str_contains( $src['contract'], "'clicar'" )
		&& str_contains( $src['contract'], "'objetivo'" ),
	'pt_stopwords' => str_contains( $src['quality'], 'stopwords()' )
		&& str_contains( $src['quality'], "'nao'" )
		&& str_contains( $src['quality'], "'sera'" ),
	'phrase_candidates' => str_contains( $src['quality'], 'phrase_candidate' )
		&& str_contains( $src['service'], 'add_phrase' ),
	'frequency_not_public' => str_contains( $src['quality'], 'content_only_not_public' ),
	'document_signal' => str_contains( $src['quality'], 'document_count' ),
	'allowlist_observed_hits' => str_contains( $src['service'], 'add_allowlist_hits' )
		&& ! str_contains( $src['service'], "self::add_term( $terms, $label, 25, 'allowlist'" ),
	'snapshot_guard' => str_contains( $src['service'], 'SNAPSHOT_VERSION !==' )
		&& str_contains( $src['service'], 'QUALITY_PROFILE !==' ),
	'profile_migration' => str_contains( $src['service'], 'maybe_migrate_quality_profile' )
		&& str_contains( $src['service'], 'stale_quality_profile' ),
	'public_request_no_generate' => str_contains( $src['home'], 'Word_Cloud_Service::public_terms' )
		&& ! str_contains( $src['home'], 'Word_Cloud_Service::generate' ),
	'truthful_home_label' => str_contains( $src['template'], 'Assuntos em destaque' )
		&& ! str_contains( $src['template'], 'Assuntos frequentes' ),
	'admin_stale_warning' => str_contains( $src['admin'], 'Snapshot requer regeneração' ),
	'no_asi_runtime' => 1 !== preg_match( '/\basi(?:4)?[_-]/i', $runtime )
		&& ! str_contains( strtolower( $runtime ), 'advanced-search-intelligence' ),
	'no_network' => 1 !== preg_match( '/wp_remote_|curl_|fsockopen|stream_socket_client/i', $runtime ),
	'no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i', $runtime ),
	'deactivation_retention' => str_contains( $src['service'], 'wp_clear_scheduled_hook' )
		&& ! str_contains( $src['service'], 'delete_option( Word_Cloud_Contract::SNAPSHOT_OPTION' ),
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
