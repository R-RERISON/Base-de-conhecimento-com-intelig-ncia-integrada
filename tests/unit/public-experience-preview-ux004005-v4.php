<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';

$files = array(
	'bootstrap' => 'base-conhecimento-inteligencia-integrada.php',
	'experience' => 'includes/class-public-experience.php',
	'auth' => 'includes/class-public-auth-bridge.php',
	'header_css' => 'assets/css/public-header.css',
	'article_css' => 'assets/css/public-article.css',
	'search_js' => 'assets/js/public-search.js',
	'home_template' => 'templates/public-home-preview.php',
	'article_template' => 'templates/public-article-preview.php',
);

$src = array();
foreach ( $files as $key => $relative ) {
	$content = file_get_contents( $root . $relative );
	if ( ! is_string( $content ) ) {
		fwrite( STDERR, "Falha ao ler {$relative}.\n" );
		exit( 1 );
	}
	$src[ $key ] = $content;
}

$checks = array(
	'version_v4' => str_contains( $src['bootstrap'], 'Version: 0.5.0-ux004005.4' ),
	'exact_entra_profile_menu_contract' => str_contains(
		$src['auth'],
		'bdc_entra_login' . ' mode="profile-menu" show_department="true" show_job_title="true" show_logout="true" show_admin_link="auto"'
	),
	'header_home_link' => str_contains( $src['experience'], "'Página Inicial'" ),
	'header_consulta_link' => str_contains( $src['experience'], "/consulta-avancada/" ),
	'header_telefones_link' => str_contains( $src['experience'], "/telefones-importantes/" ),
	'header_links_uteis' => str_contains( $src['experience'], "/links-uteis/" ),
	'header_posti_external' => str_contains( $src['experience'], 'vok-smb2.cloud-p.bcnet.bcb.gov.br/app/manual/posti/publico' )
		&& str_contains( $src['experience'], 'target="_blank" rel="noopener noreferrer"' ),
	'header_profile_bridge' => str_contains( $src['experience'], 'Public_Auth_Bridge::render();' ),
	'header_mobile_toggle' => str_contains( $src['experience'], 'data-bdc-header-toggle' )
		&& str_contains( $src['search_js'], 'is-menu-open' )
		&& str_contains( $src['search_js'], 'aria-expanded' ),
	'article_search_preserves_quick_links' => str_contains( $src['experience'], 'bdc-public-quicknav' )
		&& str_contains( $src['experience'], 'bdc-public-header__searchrow' ),
	'article_search_everywhere' => str_contains( $src['experience'], 'render_global_search( true )' )
		&& str_contains( $src['search_js'], "event.key.toLowerCase() === 'k'" ),
	'home_search_first_preserved' => str_contains( $src['home_template'], 'O que você precisa encontrar?' ),
	'article_docs_v4_label' => str_contains( $src['article_template'], 'reader-docs v4' ),
	'summary_rail_right_column' => str_contains( $src['article_css'], 'grid-template-columns:minmax(0,980px) 350px' ),
	'summary_rail_sticky' => str_contains( $src['article_css'], 'position:sticky' )
		&& str_contains( $src['article_css'], 'top:calc(var(--wp-admin--admin-bar--height,0px) + 142px)' ),
	'article_reading_width_protected' => str_contains( $src['article_css'], 'width:min(1540px,calc(100% - 48px))' )
		&& str_contains( $src['article_css'], 'width:min(1420px,100%)' ),
	'article_visual_separation' => str_contains( $src['article_css'], 'background:#f3f6fa' )
		&& str_contains( $src['article_css'], 'bdc-reader-content' )
		&& str_contains( $src['article_css'], 'border:1px solid #dfe5ec' ),
	'responsive_rail_reflow' => str_contains( $src['article_css'], '@media(max-width:1040px)' )
		&& str_contains( $src['article_css'], '.bdc-reader-summary{position:static' ),
	'print_static_rail' => str_contains( $src['article_css'], '@media print' ),
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
