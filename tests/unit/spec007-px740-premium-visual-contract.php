<?php
/**
 * PX-740 premium visual refinement v7 static contract.
 */

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$paths = array(
	'foundation' => $plugin . '/assets/css/public-foundation.css',
	'header' => $plugin . '/assets/css/public-header.css',
	'home' => $plugin . '/assets/css/public-home.css',
	'article' => $plugin . '/assets/css/public-article.css',
	'visual' => $plugin . '/assets/css/visual-foundation.css',
	'experience' => $plugin . '/includes/class-public-experience.php',
	'reader_js' => $plugin . '/assets/js/public-reader.js',
);
$source = array();
foreach ( $paths as $key => $path ) {
	$source[ $key ] = is_file( $path ) ? file_get_contents( $path ) : false;
}
$checks = array(
	'foundation_v7' => is_string( $source['foundation'] ) && str_contains( $source['foundation'], 'premium-v7' ) && str_contains( $source['foundation'], '--bdc-shadow-lg' ),
	'header_v7' => is_string( $source['header'] ) && str_contains( $source['header'], 'premium-v7' ) && str_contains( $source['header'], '.bdc-public-quicknav__link{min-height:36px' ),
	'home_v7' => is_string( $source['home'] ) && str_contains( $source['home'], 'premium-v7' ) && str_contains( $source['home'], 'intentional search-first landing' ),
	'article_v7' => is_string( $source['article'] ) && str_contains( $source['article'], 'premium-v7' ) && str_contains( $source['article'], 'editorial Reader' ),
	'admin_preview_v7' => is_string( $source['visual'] ) && str_contains( $source['visual'], '.bdc-kb-admin--public-preview' ),
	'admin_markup_v7' => is_string( $source['experience'] ) && str_contains( $source['experience'], 'bdc-kb-preview-hero' ) && str_contains( $source['experience'], 'Homologação ativa' ),
	'ui_version_v7' => is_string( $source['experience'] ) && str_contains( $source['experience'], "'uiVersion' => 'premium-v7'" ),
	'final_visual_samples' => is_string( $source['experience'] ) && str_contains( $source['experience'], 'array( 396, 367, 36431, 515, 358 )' ),
	'reader_rail_state_machine_unchanged' => is_string( $source['reader_js'] ) && str_contains( $source['reader_js'], "setState('flow')" ) && str_contains( $source['reader_js'], "setState('fixed'" ) && str_contains( $source['reader_js'], "setState('bottom')" ) && ! str_contains( $source['reader_js'], 'translate3d(' ),
	'no_cutover_write' => is_string( $source['experience'] ) && ! str_contains( $source['experience'], "update_option( 'page_on_front'" ) && ! str_contains( $source['experience'], 'wp_update_post(' ),
);
$failed = array_keys( array_filter( $checks, static fn ( bool $pass ): bool => ! $pass ) );
echo json_encode(
	array(
		'gate' => 'PX-740-VISUAL-V7',
		'status' => empty( $failed ) ? 'PASS' : 'FAIL',
		'checks' => $checks,
		'failed' => $failed,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
exit( empty( $failed ) ? 0 : 1 );
