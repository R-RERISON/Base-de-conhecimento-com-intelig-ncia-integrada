<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';

$paths = array(
	'bootstrap' => 'base-conhecimento-inteligencia-integrada.php',
	'facade' => 'includes/class-public-search-facade.php',
	'runner' => 'includes/class-public-home-technical-runner-h030.php',
	'experience' => 'includes/class-public-experience.php',
	'model' => 'includes/class-public-home-read-model.php',
	'js' => 'assets/js/public-search.js',
);

$src = array();
foreach ( $paths as $key => $relative ) {
	$value = file_get_contents( $root . $relative );
	if ( ! is_string( $value ) ) {
		fwrite( STDERR, "Falha ao ler {$relative}.\n" );
		exit( 1 );
	}
	$src[ $key ] = $value;
}

$checks = array(
	'version_h030' => str_contains( $src['bootstrap'], 'Version: 0.5.0-h030.1' ),
	'h030_flag' => str_contains( $src['bootstrap'], "BDC_KB_UX004_H030_TECHNICAL_BUILD', true" ),
	'facade_loaded' => str_contains( $src['bootstrap'], 'class-public-search-facade.php' )
		&& str_contains( $src['bootstrap'], 'Public_Search_Facade::register()' ),
	'runner_loaded' => str_contains( $src['bootstrap'], 'class-public-home-technical-runner-h030.php' )
		&& str_contains( $src['bootstrap'], 'Public_Home_Technical_Runner_H030::register()' ),
	'golden_for_h030' => str_contains( $src['bootstrap'], 'class-golden-gate-runner-g550.php' ),
	'public_ajax_nopriv' => str_contains( $src['facade'], "wp_ajax_nopriv_' . self::AJAX_ACTION" ),
	'public_nonce' => str_contains( $src['facade'], 'check_ajax_referer( self::NONCE_ACTION' ),
	'query_bounds' => str_contains( $src['facade'], 'MAX_QUERY_LENGTH = 160' )
		&& str_contains( $src['facade'], 'MAX_LIMIT = 20' ),
	'rate_limit' => str_contains( $src['facade'], 'RATE_LIMIT = 60' )
		&& str_contains( $src['facade'], 'hash_hmac' ),
	'hard_status_guard' => str_contains( $src['facade'], "array( 'publish', 'private' )" )
		&& str_contains( $src['facade'], 'post_password' ),
	'same_ranker' => str_contains( $src['facade'], 'Lexical_Ranker::rank' )
		&& ! str_contains( $src['facade'], 'class Public_Lexical_Ranker' ),
	'same_normalizer' => str_contains( $src['facade'], 'Search_Query_Normalizer::normalize' ),
	'same_projection' => str_contains( $src['facade'], 'Search_Projection_Repository::retrieve_candidates' ),
	'preview_uses_facade' => str_contains( $src['model'], 'Public_Search_Facade::search' ),
	'js_public_action' => str_contains( $src['experience'], "'action' => Public_Search_Facade::AJAX_ACTION" )
		&& str_contains( $src['js'], "config.action || 'bdc_kb_public_search'" ),
	'preview_keeps_candidate_links' => str_contains( $src['facade'], 'Public_Experience::article_preview_url' ),
	'no_auto_legacy_deactivation' => ! str_contains( $src['runner'], 'deactivate_plugins(' ),
	'h030_subgates' => str_contains( $src['runner'], "'h030_functional_parity'" )
		&& str_contains( $src['runner'], "'h034_search_golden_regression'" ),
	'editorial_fingerprint' => str_contains( $src['runner'], 'editorial_fingerprint' ),
	'word_cloud_h030' => str_contains( $src['runner'], 'Word_Cloud_Service::public_terms' ),
	'legacy_shortcode_scan' => str_contains( $src['runner'], '[asi_search_form]' )
		&& str_contains( $src['runner'], '[bc_home_config]' ),
	'legacy_active_blocks' => str_contains( $src['runner'], 'BLOCKED_ASI_ACTIVE' ),
	'golden_run' => str_contains( $src['runner'], 'Golden_Gate_Runner_G550::run()' ),
	'anonymous_probe' => str_contains( $src['runner'], 'wp_set_current_user( 0 )' )
		&& str_contains( $src['runner'], 'is_post_publicly_viewable' ),
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
