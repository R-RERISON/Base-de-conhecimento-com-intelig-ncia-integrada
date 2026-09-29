<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';

$paths = array(
	'bootstrap' => 'base-conhecimento-inteligencia-integrada.php',
	'experience' => 'includes/class-public-experience.php',
	'content' => 'includes/class-public-article-content.php',
	'home' => 'templates/public-home-preview.php',
	'article' => 'templates/public-article-preview.php',
	'foundation' => 'assets/css/public-foundation.css',
	'header' => 'assets/css/public-header.css',
	'home_css' => 'assets/css/public-home.css',
	'article_css' => 'assets/css/public-article.css',
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
	'version_v5' => str_contains( $src['bootstrap'], 'Version: 0.5.0-ux004005.5' ),
	'ajax_search_registered' => str_contains( $src['experience'], 'wp_ajax_bdc_kb_public_search_preview' ),
	'ajax_nonce' => str_contains( $src['experience'], "check_ajax_referer( 'bdc_kb_public_search_preview', 'nonce' )" ),
	'ajax_admin_only' => str_contains( $src['experience'], "current_user_can( 'manage_options' )" ),
	'ajax_uses_search_service_facade' => str_contains( $src['experience'], 'Public_Home_Read_Model::preview_search( $query )' ),
	'ajax_result_enrichment' => str_contains( $src['experience'], "'excerpt' => wp_trim_words" ),
	'live_search_config' => str_contains( $src['experience'], "'minChars' => 2" )
		&& str_contains( $src['experience'], "'debounceMs' => 180" ),
	'home_live_search' => str_contains( $src['home'], 'data-bdc-live-search-form' )
		&& str_contains( $src['home'], 'data-bdc-live-search-input' ),
	'article_live_search' => str_contains( $src['experience'], 'data-bdc-live-search-form' )
		&& str_contains( $src['experience'], 'data-bdc-live-search-input' ),
	'search_input_driven' => str_contains( $src['js'], "document.addEventListener('input'" )
		&& str_contains( $src['js'], 'liveSearch(form, input)' ),
	'search_debounce_abort' => str_contains( $src['js'], 'window.setTimeout' )
		&& str_contains( $src['js'], 'AbortController' )
		&& str_contains( $src['js'], 'controller.abort()' ),
	'ctrl_k_preserved' => str_contains( $src['js'], "event.key.toLowerCase() === 'k'" ),
	'gac_real_loop_context' => str_contains( $src['content'], 'capture_current_loop' )
		&& str_contains( $src['content'], 'the_content();' )
		&& str_contains( $src['article'], 'while ( have_posts() )' )
		&& str_contains( $src['article'], 'the_post();' ),
	'legacy_chrome_sanitizer_preserved' => str_contains( $src['content'], 'strip_duplicate_legacy_chrome' ),
	'summary_slot' => str_contains( $src['article'], 'data-bdc-summary-slot' )
		&& str_contains( $src['article'], 'data-bdc-summary-rail' ),
	'summary_follow_scroll' => str_contains( $src['js'], 'initReaderRail' )
		&& str_contains( $src['js'], 'slot.offsetHeight - rail.offsetHeight' )
		&& str_contains( $src['js'], 'translate3d' ),
	'summary_width_optimized' => str_contains( $src['article_css'], 'grid-template-columns:minmax(0,1060px) 300px' ),
	'single_column_no_summary' => str_contains( $src['article'], 'bdc-reader-layout--single' )
		&& str_contains( $src['article_css'], '.bdc-reader-layout--single' ),
	'title_density' => str_contains( $src['article_css'], 'padding:16px 20px' )
		&& str_contains( $src['article_css'], 'font-size:30px' ),
	'article_search_emphasis' => str_contains( $src['header'], 'width:min(900px,100%)' )
		&& str_contains( $src['header'], 'background:linear-gradient(180deg,#f5f9ff,#f9fbfd)' ),
	'live_result_visuals' => str_contains( $src['foundation'], '.bdc-live-result' ),
	'home_search_first_preserved' => str_contains( $src['home'], 'O que você precisa encontrar?' ),
	'header_quicklinks_preserved' => str_contains( $src['experience'], 'Página Inicial' )
		&& str_contains( $src['experience'], 'Consulta Avançada' )
		&& str_contains( $src['experience'], 'Telefones' )
		&& str_contains( $src['experience'], 'Links Úteis' )
		&& str_contains( $src['experience'], 'POSTI' ),
	'no_editorial_write' => ! preg_match( '/update_post_meta\s*\(|wp_update_post\s*\(|update_option\s*\(/', $src['experience'] . $src['content'] ),
	'preview_only' => str_contains( $src['bootstrap'], "BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD', true" ),
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
