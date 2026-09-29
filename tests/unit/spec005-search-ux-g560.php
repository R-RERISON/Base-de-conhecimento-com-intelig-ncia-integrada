<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';
$admin = file_get_contents( $root . 'includes/class-admin-page.php' );
$css = file_get_contents( $root . 'assets/css/visual-foundation.css' );
$bootstrap = file_get_contents( $root . 'base-conhecimento-inteligencia-integrada.php' );

if ( ! is_string( $admin ) || ! is_string( $css ) || ! is_string( $bootstrap ) ) {
	fwrite( STDERR, "Falha ao ler arquivos G-560.\n" );
	exit( 1 );
}

$checks = array(
	'version_g560' => str_contains( $bootstrap, "Version: 0.5.0-g560.1" ),
	'g530_engine_on' => str_contains( $bootstrap, "BDC_KB_SPEC005_G530_SEARCH_ENGINE_BUILD', true" ),
	'g550_runner_off' => str_contains( $bootstrap, "BDC_KB_SPEC005_G550_GOLDEN_RUNNER_BUILD', false" ),
	'g560_runner_on' => str_contains( $bootstrap, "BDC_KB_SPEC005_G560_SEARCH_UX_RUNNER_BUILD', true" ),
	'search_service_integration' => str_contains( $admin, 'Search_Service::search' ),
	'search_branch_is_explicit' => str_contains( $admin, '$is_search && class_exists( Search_Service::class )' ),
	'normal_list_preserves_modified_order' => str_contains( $admin, "'orderby'             => 'modified'" ),
	'search_has_no_legacy_pagination' => str_contains( $admin, 'if ( ! $is_search && $query instanceof \\WP_Query )' ),
	'role_search' => str_contains( $admin, 'role="search"' ),
	'visible_search_label' => str_contains( $admin, 'bdc-kb-search-label' ),
	'aria_describedby' => str_contains( $admin, 'aria-describedby="bdc-kb-search-help"' ),
	'aria_live_feedback' => str_contains( $admin, 'aria-live="polite"' ),
	'zero_results_copy' => str_contains( $admin, "'zero_results' === \$state" ),
	'invalid_query_copy' => str_contains( $admin, "'invalid_query' === \$state" ),
	'technical_error_copy' => str_contains( $admin, "'technical_error' === \$state" ),
	'degraded_copy' => str_contains( $admin, "'degraded' === \$state" ),
	'relevance_metadata' => str_contains( $admin, 'Relevância #%d' ),
	'css_search_feedback' => str_contains( $css, '.bdc-kb-search-feedback' ),
	'css_focus_visible' => str_contains( $css, ':focus-visible' ),
	'breakpoint_782' => str_contains( $css, '@media (max-width: 782px)' ),
	'breakpoint_520' => str_contains( $css, '@media (max-width: 520px)' ),
	'mobile_search_actions' => str_contains( $css, '.bdc-kb-search-actions { flex-direction: column; }' ),
	'no_external_css_import' => ! str_contains( $css, '@import' ) && ! str_contains( $css, 'url(http' ),
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
