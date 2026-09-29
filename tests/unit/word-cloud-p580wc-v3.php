<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';

$paths = array(
	'bootstrap' => 'base-conhecimento-inteligencia-integrada.php',
	'contract' => 'includes/class-word-cloud-contract.php',
	'consult' => 'includes/class-word-cloud-consultations.php',
	'service' => 'includes/class-word-cloud-service.php',
	'experience' => 'includes/class-public-experience.php',
	'js' => 'assets/js/public-search.js',
	'home' => 'templates/public-home-preview.php',
	'home_css' => 'assets/css/public-home.css',
	'admin' => 'includes/class-word-cloud-admin.php',
	'model' => 'includes/class-public-home-read-model.php',
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
	'version_p580wc3' => str_contains( $src['bootstrap'], 'Version: 0.5.0-p580wc.3' ),
	'consult_class_loaded' => str_contains( $src['bootstrap'], 'class-word-cloud-consultations.php' )
		&& str_contains( $src['bootstrap'], 'Word_Cloud_Consultations::register()' ),
	'contract_v12' => str_contains( $src['contract'], 'word-cloud-v1.2.0' ),
	'consult_source_available' => str_contains( $src['contract'], "'consultations' => 'available_aggregate_preview'" ),
	'aggregate_option' => str_contains( $src['consult'], 'bdc_kb_word_cloud_consultations' ),
	'autoload_false' => str_contains( $src['consult'], 'add_option( self::OPTION' )
		&& str_contains( $src['consult'], "'', false" ),
	'preview_only_ajax' => str_contains( $src['consult'], "wp_ajax_' . self::AJAX_ACTION" )
		&& ! str_contains( $src['consult'], 'wp_ajax_nopriv_' ),
	'manage_options' => str_contains( $src['consult'], "current_user_can( 'manage_options' )" ),
	'bounded_public_terms_only' => str_contains( $src['consult'], 'current_public_term_map' )
		&& str_contains( $src['consult'], 'MAX_TERMS = 500' ),
	'no_identity_storage' => 1 !== preg_match( '/user_id|ip_address|session_id|remote_addr/i', $src['consult'] ),
	'no_raw_event_log' => ! str_contains( $src['consult'], 'query_log' ),
	'confirmed_sources_only' => str_contains( $src['consult'], "'topic_click', 'result_click'" ),
	'usage_boost_logarithmic' => str_contains( $src['consult'], '12.0 * log( 1.0 + $count )' ),
	'word_cloud_decorated' => str_contains( $src['service'], 'Word_Cloud_Consultations::decorate_terms' ),
	'live_result_tracking' => str_contains( $src['js'], 'data-bdc-consult-term' )
		&& str_contains( $src['js'], 'recordConsultation' ),
	'keepalive_navigation_safe' => str_contains( $src['js'], 'keepalive: true' ),
	'server_result_tracking' => str_contains( $src['experience'], 'data-bdc-consult-source="result_click"' ),
	'nonce_localized' => str_contains( $src['experience'], "'consultNonce'" )
		&& str_contains( $src['experience'], "'consultAction'" ),
	'twelve_topics' => str_contains( $src['home'], 'array_slice( $cloud, 0, 12 )' ),
	'count_visible' => str_contains( $src['home'], 'consultation_count' )
		&& str_contains( $src['home'], 'consultas' ),
	'topic_click_tracking' => str_contains( $src['home'], 'data-bdc-consult-source="topic_click"' ),
	'larger_topic_pool' => str_contains( $src['model'], 'public_terms( 32 )' ),
	'count_badge_css' => str_contains( $src['home_css'], '.bdc-home-suggestions a b' ),
	'admin_metric' => str_contains( $src['admin'], 'consultas agregadas' ),
	'no_network_php' => 1 !== preg_match( '/wp_remote_|curl_|fsockopen|stream_socket_client/i', $src['consult'] ),
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
