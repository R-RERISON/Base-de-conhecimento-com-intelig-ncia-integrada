<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';

$paths = array(
	'bootstrap' => 'base-conhecimento-inteligencia-integrada.php',
	'consult' => 'includes/class-word-cloud-consultations.php',
	'admin' => 'includes/class-word-cloud-admin.php',
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
	'version_patch' => str_contains( $src['bootstrap'], 'Version: 0.5.0-p580wc.3.1' ),
	'aggregate_v11' => str_contains( $src['consult'], "consultation-aggregate-v1.1.0" ),
	'event_id_required' => str_contains( $src['consult'], "event_id ausente" ),
	'event_id_bounded' => str_contains( $src['consult'], 'EVENT_ID_MAX_LENGTH = 96' ),
	'server_dedupe_transient' => str_contains( $src['consult'], "bdc_kb_wc_evt_" )
		&& str_contains( $src['consult'], 'EVENT_DEDUPE_TTL' ),
	'duplicate_response' => str_contains( $src['consult'], "'duplicate' => true" )
		&& str_contains( $src['consult'], "'recorded' => false" ),
	'failed_record_releases_dedupe' => str_contains( $src['consult'], 'delete_transient( $dedupe_key )' ),
	'legacy_counts_preserved' => str_contains( $src['consult'], "'consultation-aggregate-v1.0.0'" ),
	'explicit_reset' => str_contains( $src['consult'], 'public static function reset' )
		&& str_contains( $src['admin'], 'Resetar consultas de homologação' ),
	'reset_guarded' => str_contains( $src['admin'], 'handle_reset_consultations' )
		&& str_contains( $src['admin'], 'self::guard()' ),
	'client_weakset' => str_contains( $src['js'], 'new WeakSet()' ),
	'gesture_id_persisted_on_element' => str_contains( $src['js'], "data-bdc-consult-event-id" )
		&& str_contains( $src['js'], "element.setAttribute('data-bdc-consult-event-id', eventId)" ),
	'uuid_fallback' => str_contains( $src['js'], 'crypto.randomUUID' )
		&& str_contains( $src['js'], "Math.random().toString(36)" ),
	'event_id_sent' => str_contains( $src['js'], "data.append('event_id', eventId)" ),
	'keepalive_preserved' => str_contains( $src['js'], 'keepalive: true' ),
	'keypress_not_counted' => ! str_contains( $src['js'], "recordConsultation(input" ),
	'no_identity_storage' => 1 !== preg_match( '/user_id|ip_address|session_id|remote_addr/i', $src['consult'] ),
	'no_editorial_write' => 1 !== preg_match( '/wp_update_post\s*\(|update_post_meta\s*\(|wp_set_object_terms\s*\(/i', $src['consult'] . $src['admin'] ),
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
