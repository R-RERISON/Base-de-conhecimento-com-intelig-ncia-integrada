<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/base-conhecimento-inteligencia-integrada/';
$bootstrap = file_get_contents( $root . 'base-conhecimento-inteligencia-integrada.php' );
$experience = file_get_contents( $root . 'includes/class-public-experience.php' );
$home = file_get_contents( $root . 'includes/class-public-home-read-model.php' );
$article = file_get_contents( $root . 'includes/class-public-article-read-model.php' );
$tips = file_get_contents( $root . 'includes/class-helpful-tips-store.php' );
$home_template = file_get_contents( $root . 'templates/public-home-preview.php' );
$article_template = file_get_contents( $root . 'templates/public-article-preview.php' );
$foundation_css = file_get_contents( $root . 'assets/css/public-foundation.css' );
$header_css = file_get_contents( $root . 'assets/css/public-header.css' );
$home_css = file_get_contents( $root . 'assets/css/public-home.css' );
$article_css = file_get_contents( $root . 'assets/css/public-article.css' );

foreach ( compact( 'bootstrap','experience','home','article','tips','home_template','article_template','foundation_css','header_css','home_css','article_css' ) as $name => $content ) {
	if ( ! is_string( $content ) ) {
		fwrite( STDERR, "Falha ao ler {$name}.\n" );
		exit( 1 );
	}
}

$strip_comments = static function ( string $source ): string {
	$tokens = token_get_all( $source );
	$out = '';
	foreach ( $tokens as $token ) {
		if ( is_array( $token ) && in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
			continue;
		}
		$out .= is_array( $token ) ? $token[1] : $token;
	}
	return $out;
};

$experience_code = $strip_comments( $experience );
$home_code = $strip_comments( $home );
$article_code = $strip_comments( $article );
$tips_code = $strip_comments( $tips );
$runtime = $experience_code . "\n" . $home_code . "\n" . $article_code . "\n" . $tips_code;

$write_pattern = '/update_(?:post|option|site_option|post_meta)\s*\(|add_(?:option|post_meta)\s*\(|delete_(?:option|post_meta)\s*\(|wp_update_post\s*\(|wp_insert_post\s*\(|wp_set_object_terms\s*\(/i';
$network_pattern = '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|fsockopen\s*\(|stream_socket_client\s*\(/i';

$checks = array(
	'version_preview1' => str_contains( $bootstrap, 'Version: 0.5.0-ux004005.1' ),
	'inventory_runner_off' => str_contains( $bootstrap, "BDC_KB_P580_PUBLIC_INVENTORY_BUILD', false" ),
	'preview_build_on' => str_contains( $bootstrap, "BDC_KB_PUBLIC_EXPERIENCE_PREVIEW_BUILD', true" ),
	'preview_files_loaded' => str_contains( $bootstrap, 'class-public-experience.php' )
		&& str_contains( $bootstrap, 'class-public-home-read-model.php' )
		&& str_contains( $bootstrap, 'class-public-article-read-model.php' )
		&& str_contains( $bootstrap, 'class-helpful-tips-store.php' ),
	'preview_registered' => str_contains( $bootstrap, 'Public_Experience::register()' ),
	'preview_manage_options' => str_contains( $experience, "current_user_can( 'manage_options' )" ),
	'preview_nonce_required' => str_contains( $experience, 'wp_verify_nonce' ) && str_contains( $experience, 'bdc_kb_public_preview_' ),
	'preview_admin_assets' => str_contains( $experience, 'enqueue_admin_assets' ) && str_contains( $experience, 'visual-foundation.css' ),
	'preview_no_default_takeover' => str_contains( $experience, 'null === $kind' ) && str_contains( $experience, 'return $template;' ),
	'preview_no_editorial_writes' => 1 !== preg_match( $write_pattern, $runtime ),
	'preview_no_network' => 1 !== preg_match( $network_pattern, $runtime ),
	'home_uses_bdc_search' => str_contains( $home, 'Search_Service::search' ),
	'home_latest_explicit_date' => str_contains( $home, "'date' => 'DESC'" ),
	'home_popular_compat_comment_count' => str_contains( $home, "'comment_count' => 'DESC'" ),
	'home_categories_curated' => str_contains( $home, 'CURATED_CATEGORIES' ) && str_contains( $home, "'Segurança'" ) && str_contains( $home, "'Software'" ),
	'word_cloud_marked_preview' => str_contains( $home_template, 'content-only' ) && str_contains( $home_template, 'PREVIEW' ),
	'tips_physical_key_preserved' => str_contains( $tips, "'_bdc_es_helpful_tips'" ),
	'tips_read_only_slice' => ! str_contains( $tips_code, 'update_post_meta(' ) && ! str_contains( $tips_code, 'delete_post_meta(' ),
	'summary_read_model_composed' => str_contains( $article, 'Summary_Store::read' ) && str_contains( $article, 'Classification_Store::read' ) && str_contains( $article, "'_bdc_es_affected_service'" ) && str_contains( $article, "'_bdc_es_systems_involved'" ),
	'article_preserves_the_content' => str_contains( $article_template, 'the_content();' ),
	'article_preview_suppresses_gre_only' => str_contains( $experience, 'Helpful_Tips_Renderer' ) && str_contains( $experience, 'Frontend_Renderer' ) && ! str_contains( $experience, 'GAC\\' ) && ! str_contains( $experience, 'WPUI_Frontend' ),
	'article_sticky_rail' => str_contains( $article_css, 'position:sticky' ) && str_contains( $article_css, 'max-height:calc(100vh' ),
	'article_print_contract' => str_contains( $article_css, '@media print' ),
	'breakpoints_782_520' => str_contains( $foundation_css, '@media(max-width:782px)' ) && str_contains( $foundation_css, '@media(max-width:520px)' ) && str_contains( $home_css, '@media(max-width:782px)' ) && str_contains( $article_css, '@media(max-width:520px)' ),
	'scoped_public_css' => str_contains( $foundation_css, '.bdc-public' ) && str_contains( $header_css, '.bdc-public-header' ) && str_contains( $home_css, '.bdc-home-' ) && str_contains( $article_css, '.bdc-reader-' ),
	'no_asi_markup_dependency' => ! str_contains( $runtime . $home_template . $article_template, 'asi_search_form' ) && ! str_contains( $runtime . $home_template . $article_template, 'bdc_word_cloud' ),
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