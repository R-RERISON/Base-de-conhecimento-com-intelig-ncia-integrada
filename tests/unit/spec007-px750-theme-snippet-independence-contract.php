<?php
/**
 * PX-750 Theme/Snippet Independence static contract.
 */

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$paths = array(
	'experience' => $plugin . '/includes/class-public-experience.php',
	'home' => $plugin . '/templates/public-home-preview.php',
	'search_js' => $plugin . '/assets/js/public-search.js',
	'reader_js' => $plugin . '/assets/js/public-reader.js',
	'facade' => $plugin . '/includes/class-public-search-facade.php',
);

$source = array();
foreach ( $paths as $key => $path ) {
	$source[ $key ] = is_file( $path ) ? file_get_contents( $path ) : false;
}

$checks = array(
	'isolation_query_key' => is_string( $source['experience'] ) && str_contains( $source['experience'], "private const ISOLATION_KEY = 'bdc_kb_isolation'" ),
	'legacy_shortcodes_bounded' => is_string( $source['experience'] )
		&& str_contains( $source['experience'], "'bc_home_config'" )
		&& str_contains( $source['experience'], "'bc_ultimas'" )
		&& str_contains( $source['experience'], "'bc_populares'" ),
	'custom_css_request_only' => is_string( $source['experience'] ) && str_contains( $source['experience'], "remove_action( 'wp_head', 'wp_custom_css_cb', 101 )" ),
	'legacy_shortcode_request_only' => is_string( $source['experience'] ) && str_contains( $source['experience'], 'remove_shortcode( $shortcode )' ),
	'theme_styles_dequeued' => is_string( $source['experience'] ) && str_contains( $source['experience'], 'wp_dequeue_style' ),
	'theme_scripts_dequeued' => is_string( $source['experience'] ) && str_contains( $source['experience'], 'wp_dequeue_script' ),
	'theme_source_detected' => is_string( $source['experience'] )
		&& str_contains( $source['experience'], '/wp-content/themes/' )
		&& str_contains( $source['experience'], 'get_template_directory_uri()' )
		&& str_contains( $source['experience'], 'get_stylesheet_directory_uri()' ),
	'admin_isolation_entrypoints' => is_string( $source['experience'] )
		&& str_contains( $source['experience'], 'Home isolada' )
		&& str_contains( $source['experience'], 'Reader isolado' ),
	'isolation_url_propagation' => is_string( $source['experience'] )
		&& str_contains( $source['experience'], '?bool $isolated = null' )
		&& str_contains( $source['experience'], '$args[ self::ISOLATION_KEY ] = \'1\'' ),
	'home_form_propagation' => is_string( $source['home'] ) && str_contains( $source['home'], 'name="bdc_kb_isolation" value="1"' ),
	'live_search_propagation' => is_string( $source['search_js'] ) && str_contains( $source['search_js'], "data.append('isolation', '1')" ),
	'ajax_result_propagation' => is_string( $source['experience'] ) && str_contains( $source['experience'], "article_preview_url( $post_id, '', $isolated )" ),
	'isolation_body_marker' => is_string( $source['experience'] ) && str_contains( $source['experience'], 'bdc-public-isolation' ),
	'isolation_banner' => is_string( $source['experience'] ) && str_contains( $source['experience'], 'isolamento PX-750' ),
	'no_option_mutation' => is_string( $source['experience'] )
		&& ! str_contains( $source['experience'], 'update_option(' )
		&& ! str_contains( $source['experience'], 'delete_option(' ),
	'no_theme_mutation' => is_string( $source['experience'] )
		&& ! str_contains( $source['experience'], 'set_theme_mod(' )
		&& ! str_contains( $source['experience'], 'remove_theme_mod(' ),
	'no_plugin_activation' => is_string( $source['experience'] )
		&& ! str_contains( $source['experience'], 'activate_plugin(' )
		&& ! str_contains( $source['experience'], 'deactivate_plugins(' ),
	'search_facade_still_present' => is_string( $source['facade'] ) && str_contains( $source['facade'], 'final class Public_Search_Facade' ),
	'reader_state_machine_preserved' => is_string( $source['reader_js'] )
		&& str_contains( $source['reader_js'], "setState('flow')" )
		&& str_contains( $source['reader_js'], "setState('fixed'" )
		&& str_contains( $source['reader_js'], "setState('bottom')" ),
);

$failed = array_keys( array_filter( $checks, static fn ( bool $pass ): bool => ! $pass ) );

echo json_encode(
	array(
		'gate' => 'PX-750',
		'status' => empty( $failed ) ? 'PASS' : 'FAIL',
		'checks' => $checks,
		'failed' => $failed,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;

exit( empty( $failed ) ? 0 : 1 );
