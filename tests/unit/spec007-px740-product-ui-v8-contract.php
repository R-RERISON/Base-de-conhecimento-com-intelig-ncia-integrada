<?php
/**
 * PX-740 Premium Product UI v8 static contract.
 */

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$paths = array(
	'admin' => $plugin . '/includes/class-admin-page.php',
	'admin_css' => $plugin . '/assets/css/admin.css',
	'workspace_css' => $plugin . '/assets/css/workspace.css',
	'workspace_js' => $plugin . '/assets/js/workspace.js',
	'home_tpl' => $plugin . '/templates/public-home-preview.php',
	'home_css' => $plugin . '/assets/css/public-home.css',
	'article_css' => $plugin . '/assets/css/public-article.css',
	'reader_js' => $plugin . '/assets/js/public-reader.js',
	'search_js' => $plugin . '/assets/js/public-search.js',
);
$source = array();
foreach ( $paths as $key => $path ) {
	$source[ $key ] = is_file( $path ) ? file_get_contents( $path ) : false;
}

$checks = array(
	'product_shell' => is_string( $source['admin'] ) && str_contains( $source['admin'], 'bdc-kb-product-shell' ),
	'nav_group_article' => is_string( $source['admin'] ) && str_contains( $source['admin'], "'Artigo' => array( 'overview', 'content', 'summary', 'classification', 'details' )" ),
	'nav_group_governance' => is_string( $source['admin'] ) && str_contains( $source['admin'], "'Governança' => array( 'intelligence', 'core_blocks', 'review', 'history' )" ),
	'health_groups' => is_string( $source['admin'] ) && str_contains( $source['admin'], 'bdc-kb-health-groups' ),
	'editorial_list_summary' => is_string( $source['admin'] ) && str_contains( $source['admin'], 'bdc-kb-list-summary' ),
	'admin_v8' => is_string( $source['admin_css'] ) && str_contains( $source['admin_css'], 'premium-v8' ),
	'workspace_v8' => is_string( $source['workspace_css'] ) && str_contains( $source['workspace_css'], 'premium-v8' ),
	'vertical_keyboard' => is_string( $source['workspace_js'] ) && str_contains( $source['workspace_js'], 'ArrowDown' ) && str_contains( $source['workspace_js'], 'ArrowUp' ),
	'home_discovery_open' => is_string( $source['home_tpl'] ) && str_contains( $source['home_tpl'], 'bdc-home-explore bdc-home-explore--product" open' ),
	'home_category_icons' => is_string( $source['home_tpl'] ) && str_contains( $source['home_tpl'], "(string) $category['icon']" ),
	'home_v8' => is_string( $source['home_css'] ) && str_contains( $source['home_css'], 'premium-v8' ),
	'reader_v8' => is_string( $source['article_css'] ) && str_contains( $source['article_css'], 'premium-v8' ),
	'gac_presentation_tag' => is_string( $source['reader_js'] ) && str_contains( $source['reader_js'], 'tagIntegratedActions' ),
	'rail_flow' => is_string( $source['reader_js'] ) && str_contains( $source['reader_js'], "setState('flow')" ),
	'rail_fixed' => is_string( $source['reader_js'] ) && str_contains( $source['reader_js'], "setState('fixed'" ),
	'rail_bottom' => is_string( $source['reader_js'] ) && str_contains( $source['reader_js'], "setState('bottom')" ),
	'no_translate3d' => is_string( $source['reader_js'] ) && ! str_contains( $source['reader_js'], 'translate3d(' ),
	'search_still_live' => is_string( $source['search_js'] ) && str_contains( $source['search_js'], 'AbortController' ),
);

$failed = array_keys( array_filter( $checks, static fn ( bool $pass ): bool => ! $pass ) );
echo json_encode(
	array(
		'gate' => 'PX-740-PRODUCT-UI-V8',
		'status' => empty( $failed ) ? 'PASS' : 'FAIL',
		'checks' => $checks,
		'failed' => $failed,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
exit( empty( $failed ) ? 0 : 1 );
