<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';

$paths = array(
	'bootstrap' => 'base-conhecimento-inteligencia-integrada.php',
	'consult' => 'includes/class-word-cloud-consultations.php',
	'js' => 'assets/js/public-search.js',
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

$checks = array(
	'version_patch' => str_contains( $src['bootstrap'], 'Version: 0.5.0-p580wc.3.2' ),
	'aggregate_v12' => str_contains( $src['consult'], "consultation-aggregate-v1.2.0" ),
	'atomic_claim_add_option' => str_contains( $src['consult'], 'add_option( $dedupe_key, $expires_at' ),
	'no_transient_check_then_set' => ! str_contains( $src['consult'], 'get_transient( $dedupe_key )' )
		&& ! str_contains( $src['consult'], 'set_transient( $dedupe_key' ),
	'unique_option_comment' => str_contains( $src['consult'], 'option_name is unique in wp_options' ),
	'expired_claim_retry' => str_contains( $src['consult'], '$existing_expires_at <= $now' )
		&& str_contains( $src['consult'], 'delete_option( $dedupe_key )' ),
	'duplicate_response_preserved' => str_contains( $src['consult'], "'duplicate' => true" )
		&& str_contains( $src['consult'], "'recorded' => false" ),
	'failed_record_releases_claim' => str_contains( $src['consult'], 'if ( ! $recorded )' )
		&& str_contains( $src['consult'], 'delete_option( $dedupe_key )' ),
	'claim_gc' => str_contains( $src['consult'], 'cleanup_expired_event_claims' )
		&& str_contains( $src['consult'], 'EVENT_GC_TRANSIENT' )
		&& str_contains( $src['consult'], 'LIMIT 500' ),
	'reset_deletes_claims' => str_contains( $src['consult'], 'delete_all_event_claims' ),
	'legacy_aggregate_compat' => str_contains( $src['consult'], "'consultation-aggregate-v1.1.0'" )
		&& str_contains( $src['consult'], "'consultation-aggregate-v1.0.0'" ),
	'event_id_still_required' => str_contains( $src['consult'], 'event_id ausente' ),
	'gesture_id_still_reused' => str_contains( $src['js'], "data-bdc-consult-event-id" )
		&& str_contains( $src['js'], "element.setAttribute('data-bdc-consult-event-id', eventId)" ),
	'keepalive_preserved' => str_contains( $src['js'], 'keepalive: true' ),
	'no_identity_storage' => 1 !== preg_match( '/user_id|ip_address|session_id|remote_addr/i', $src['consult'] ),
	'no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i', $src['consult'] ),
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
