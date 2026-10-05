<?php
/**
 * PX-740 Reader / Tips / Rail static contract.
 */

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$paths = array(
	'template' => $plugin . '/templates/public-article-preview.php',
	'content' => $plugin . '/includes/class-public-article-content.php',
	'model' => $plugin . '/includes/class-public-article-read-model.php',
	'experience' => $plugin . '/includes/class-public-experience.php',
	'tips' => $plugin . '/includes/class-helpful-tips-store.php',
	'summary' => $plugin . '/includes/class-summary-store.php',
	'css' => $plugin . '/assets/css/public-article.css',
	'js' => $plugin . '/assets/js/public-search.js',
);

$source = array();
foreach ( $paths as $key => $path ) {
	$source[ $key ] = is_file( $path ) ? file_get_contents( $path ) : false;
}

$checks = array(
	'canonical_loop_content' =>
		is_string( $source['template'] )
		&& str_contains( $source['template'], 'while ( have_posts() )' )
		&& str_contains( $source['template'], 'the_post();' )
		&& str_contains( $source['template'], 'Public_Article_Content::capture_current_loop();' )
		&& is_string( $source['content'] )
		&& str_contains( $source['content'], 'the_content();' ),

	'no_raw_content_replacement' =>
		is_string( $source['template'] )
		&& ! str_contains( $source['template'], 'post_content' ),

	'tips_canonical_store' =>
		is_string( $source['tips'] )
		&& str_contains( $source['tips'], "public const META_KEY          = '_bdc_es_helpful_tips';" )
		&& is_string( $source['model'] )
		&& str_contains( $source['model'], 'Helpful_Tips_Store::read( $post_id )' ),

	'summary_composed_read_model' =>
		is_string( $source['model'] )
		&& str_contains( $source['model'], 'Summary_Store::read( $post_id )' )
		&& str_contains( $source['model'], 'Classification_Store::read( $post_id )' )
		&& str_contains( $source['model'], 'Knowledge_Facts_Store::read( $post_id )' )
		&& str_contains( $source['model'], 'self::summary_items(' ),

	'candidate_only_gre_suppression' =>
		is_string( $source['experience'] )
		&& str_contains( $source['experience'], "if ( 'article' !== $kind ) { return; }" )
		&& str_contains( $source['experience'], "Helpful_Tips_Renderer', 'prepend_to_content" )
		&& str_contains( $source['experience'], "Frontend_Renderer', 'append_side_panel" )
		&& str_contains( $source['experience'], "Frontend_Renderer', 'render_side_panel" ),

	'no_gac_wpui_suppression' =>
		is_string( $source['experience'] )
		&& ! str_contains( $source['experience'], 'PostActionsService' )
		&& ! str_contains( $source['experience'], 'AuthenticatedKnowledgeContributionBridge' )
		&& ! str_contains( $source['experience'], 'WPUI_Frontend' ),

	'tips_before_document' =>
		is_string( $source['template'] )
		&& strpos( $source['template'], 'bdc-reader-tips' ) < strpos( $source['template'], 'bdc-reader-document' ),

	'summary_optional' =>
		is_string( $source['template'] )
		&& str_contains( $source['template'], "if ( ! empty( $model['summary_items'] ) )" )
		&& str_contains( $source['template'], 'bdc-reader-layout--single' ),

	'empty_content_explicit' =>
		is_string( $source['template'] )
		&& str_contains( $source['template'], 'O conteúdo deste artigo não pôde ser apresentado nesta prévia.' ),

	'legacy_chrome_heuristic_bounded' =>
		is_string( $source['content'] )
		&& str_contains( $source['content'], 'min( 80, $nodes->length )' )
		&& str_contains( $source['content'], "if ( $hits < 2 )" ),

	'responsive_rail_reflow' =>
		is_string( $source['css'] )
		&& str_contains( $source['css'], '@media(max-width:1040px)' )
		&& str_contains( $source['css'], 'transform:none!important' ),

	'no_editorial_or_cutover_write' =>
		is_string( $source['experience'] )
		&& ! str_contains( $source['experience'], "update_option( 'page_on_front'" )
		&& ! str_contains( $source['experience'], 'wp_update_post(' )
		&& ! str_contains( $source['experience'], "update_post_meta(" ),
);

$resolved = array(
	'PX740-GAP-001_native_sticky_only' =>
		is_string( $source['css'] )
		&& str_contains( $source['css'], '.bdc-reader-summary{position:sticky;' )
		&& ! str_contains( $source['css'], '.bdc-reader-summary{position:relative;' )
		&& is_string( $source['js'] )
		&& ! str_contains( $source['js'], 'function initReaderRail()' )
		&& ! str_contains( $source['js'], 'translate3d(' ),
);

$failed = array_keys( array_filter( $checks, static fn ( bool $pass ): bool => ! $pass ) );
$failed = array_merge( $failed, array_keys( array_filter( $resolved, static fn ( bool $pass ): bool => ! $pass ) ) );

echo json_encode(
	array(
		'gate' => 'PX-740-DISCOVERY',
		'status' => empty( $failed ) ? 'PASS_DISCOVERY' : 'FAIL',
		'checks' => $checks,
		'resolved' => $resolved,
		'failed' => $failed,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;

exit( empty( $failed ) ? 0 : 1 );
