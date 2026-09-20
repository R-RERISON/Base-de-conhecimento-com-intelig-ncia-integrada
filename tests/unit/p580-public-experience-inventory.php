<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';
$bootstrap = file_get_contents( $root . 'base-conhecimento-inteligencia-integrada.php' );
$runner = file_get_contents( $root . 'includes/class-public-experience-inventory-runner-p580.php' );

foreach ( compact( 'bootstrap', 'runner' ) as $name => $content ) {
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

$runner_code = $strip_comments( $runner );
$write_pattern = '/update_(?:post|option|site_option|post_meta)\s*\(|add_(?:option|post_meta)\s*\(|delete_(?:option|post_meta)\s*\(|wp_update_post\s*\(|wp_insert_post\s*\(|wp_set_object_terms\s*\(/i';
$network_pattern = '/wp_remote_[A-Za-z0-9_]*\s*\(|curl_[A-Za-z0-9_]+\s*\(|fsockopen\s*\(|stream_socket_client\s*\(/i';

$checks = array(
	'version_p580a1' => str_contains( $bootstrap, 'Version: 0.5.0-p580a.1' ),
	'g585_runner_off' => str_contains( $bootstrap, "BDC_KB_SPEC005_G585_ASI_INDEPENDENCE_BUILD', false" ),
	'p580_runner_on' => str_contains( $bootstrap, "BDC_KB_P580_PUBLIC_INVENTORY_BUILD', true" ),
	'p580_runner_loaded' => str_contains( $bootstrap, "class-public-experience-inventory-runner-p580.php" ),
	'p580_runner_registered' => str_contains( $bootstrap, "Public_Experience_Inventory_Runner_P580::register()" ),

	'admin_post_nonce' => str_contains( $runner, "admin_post_" ) && str_contains( $runner, 'wp_verify_nonce' ),
	'manage_options' => str_contains( $runner, "current_user_can( 'manage_options' )" ),
	'no_write_calls' => 1 !== preg_match( $write_pattern, $runner_code ),
	'no_network_calls' => 1 !== preg_match( $network_pattern, $runner_code ),
	'no_do_shortcode' => ! str_contains( $runner_code, 'do_shortcode(' ),
	'no_the_content_apply' => ! str_contains( $runner_code, "apply_filters( 'the_content'" ),

	'inventory_home_settings' => str_contains( $runner, "'show_on_front'" )
		&& str_contains( $runner, "'page_on_front'" )
		&& str_contains( $runner, "'page_for_posts'" ),
	'inventory_theme_custom_css_hash_only' => str_contains( $runner, 'wp_get_custom_css' )
		&& str_contains( $runner, "'sha256'" )
		&& str_contains( $runner, "'content_exported' => false" ),
	'inventory_required_shortcodes' => str_contains( $runner, "'bc_home_config'" )
		&& str_contains( $runner, "'bc_ultimas'" )
		&& str_contains( $runner, "'bc_populares'" )
		&& str_contains( $runner, "'asi_search_form'" )
		&& str_contains( $runner, "'bdc_word_cloud'" )
		&& str_contains( $runner, "'bdc_entra_login'" ),
	'inventory_ajax_filter' => str_contains( $runner, "'wp_ajax_bdc_home_filter_v270'" )
		&& str_contains( $runner, "'wp_ajax_nopriv_bdc_home_filter_v270'" ),
	'inventory_runtime_hooks' => str_contains( $runner, "'the_content'" )
		&& str_contains( $runner, "'wp_footer'" )
		&& str_contains( $runner, "'template_include'" )
		&& str_contains( $runner, "'single_template'" ),
	'callback_reflection' => str_contains( $runner, 'ReflectionFunction' )
		&& str_contains( $runner, 'ReflectionMethod' )
		&& str_contains( $runner, "'source_scope'" )
		&& str_contains( $runner, "'source_path'" ),
	'corpus_source_kinds' => str_contains( $runner, 'Content_Extractor::extract' )
		&& str_contains( $runner, "'source_kind_counts'" ),
	'tips_discovery_no_values' => str_contains( $runner, "'structured_tips_discovery'" )
		&& str_contains( $runner, 'contains_tips_marker' )
		&& ! str_contains( $runner, "'meta_values'" ),
	'gre_eight_keys' => str_contains( $runner, "'_bdc_es_objective'" )
		&& str_contains( $runner, "'_bdc_es_responsible_team'" )
		&& str_contains( $runner, "'_bdc_es_catalog_item'" )
		&& str_contains( $runner, "'_bdc_es_affected_service'" )
		&& str_contains( $runner, "'_bdc_es_systems_involved'" )
		&& str_contains( $runner, "'_bdc_es_target_audience'" )
		&& str_contains( $runner, "'_bdc_es_escalation'" )
		&& str_contains( $runner, "'_bdc_es_important'" ),
	'safety_contract' => str_contains( $runner, "'exports_editorial_content' => false" )
		&& str_contains( $runner, "'exports_gre_values' => false" )
		&& str_contains( $runner, "'calls_external_network' => false" ),
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
