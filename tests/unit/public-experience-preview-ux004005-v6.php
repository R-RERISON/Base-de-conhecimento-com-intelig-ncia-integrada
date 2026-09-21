<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';

$files = array(
	'bootstrap' => 'base-conhecimento-inteligencia-integrada.php',
	'experience' => 'includes/class-public-experience.php',
	'home' => 'templates/public-home-preview.php',
	'article' => 'templates/public-article-preview.php',
	'foundation' => 'assets/css/public-foundation.css',
	'header' => 'assets/css/public-header.css',
	'home_css' => 'assets/css/public-home.css',
	'article_css' => 'assets/css/public-article.css',
	'js' => 'assets/js/public-search.js',
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
	'version_v6' => str_contains( $src['bootstrap'], 'Version: 0.5.0-ux004005.6' ),
	'live_search_preserved' => str_contains( $src['experience'], 'wp_ajax_bdc_kb_public_search_preview' )
		&& str_contains( $src['js'], "document.addEventListener('input'" ),
	'live_search_loading_state' => str_contains( $src['js'], 'setLoading' )
		&& str_contains( $src['foundation'], '.bdc-home-live-panel.is-loading' ),
	'stale_request_abort_preserved' => str_contains( $src['js'], 'AbortController' )
		&& str_contains( $src['js'], 'controller.abort()' ),
	'premium_result_metadata' => str_contains( $src['experience'], 'bdc-live-result__meta' )
		&& str_contains( $src['js'], 'bdc-live-result__meta' ),
	'premium_result_excerpt' => str_contains( $src['experience'], 'wp_trim_words' )
		&& str_contains( $src['foundation'], '-webkit-line-clamp:2' ),
	'home_duplicate_server_panel_removed' => ! str_contains( $src['home'], 'bdc-home-search-results-wrap' ),
	'home_search_first_preserved' => str_contains( $src['home'], 'O que você precisa encontrar?' ),
	'reader_gac_pipeline_preserved' => str_contains( $src['article'], 'capture_current_loop' ),
	'reader_summary_follow_preserved' => str_contains( $src['article'], 'data-bdc-summary-slot' )
		&& str_contains( $src['js'], 'initReaderRail' )
		&& str_contains( $src['js'], 'translate3d' ),
	'reader_heading_decardified' => str_contains( $src['article_css'], 'border:0;border-bottom:1px solid #dce3eb' )
		&& str_contains( $src['article_css'], 'background:transparent;box-shadow:none' ),
	'reader_tips_integrated' => str_contains( $src['article_css'], 'border-left:3px solid #7da9e8' )
		&& str_contains( $src['article_css'], '.bdc-reader-tip+.bdc-reader-tip' ),
	'reader_readability' => str_contains( $src['article_css'], 'max-width:82ch' )
		&& str_contains( $src['article_css'], 'line-height:1.72' ),
	'summary_visual_quiet' => str_contains( $src['article_css'], 'border-radius:13px' )
		&& str_contains( $src['article_css'], 'box-shadow:0 8px 24px rgba(11,31,77,.045)' ),
	'header_behavior_preserved' => str_contains( $src['experience'], 'Página Inicial' )
		&& str_contains( $src['experience'], 'Consulta Avançada' )
		&& str_contains( $src['experience'], 'Telefones' )
		&& str_contains( $src['experience'], 'Links Úteis' )
		&& str_contains( $src['experience'], 'POSTI' ),
	'visual_contract_tokens_preserved' => str_contains( $src['foundation'], '--bdc-brand-navy:#0b1f4d' )
		&& str_contains( $src['foundation'], '--bdc-blue-600:#0b63e5' ),
	'no_architecture_reopen' => ! str_contains( $src['bootstrap'], '0.5.0-ux004005.6-architecture' ),
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
