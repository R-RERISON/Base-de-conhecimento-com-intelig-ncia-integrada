<?php
/**
 * PX-730 Live Search static contract.
 */

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$paths = array(
	'js' => $plugin . '/assets/js/public-search.js',
	'public_experience' => $plugin . '/includes/class-public-experience.php',
	'facade' => $plugin . '/includes/class-public-search-facade.php',
	'home_model' => $plugin . '/includes/class-public-home-read-model.php',
	'home' => $plugin . '/templates/public-home-preview.php',
	'foundation_css' => $plugin . '/assets/css/public-foundation.css',
);

$source = array();
foreach ( $paths as $key => $path ) {
	$source[ $key ] = is_file( $path ) ? file_get_contents( $path ) : false;
}

$checks = array(
	'canonical_facade_action' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], "'action' => Public_Search_Facade::AJAX_ACTION" )
		&& is_string( $source['facade'] )
		&& str_contains( $source['facade'], "public const AJAX_ACTION = 'bdc_kb_public_search';" ),

	'canonical_ranker_unchanged' =>
		is_string( $source['facade'] )
		&& str_contains( $source['facade'], 'Lexical_Ranker::rank(' )
		&& str_contains( $source['facade'], "self::wordpress_fallback(" ),

	'home_server_fallback' =>
		is_string( $source['home'] )
		&& str_contains( $source['home'], 'method="get"' )
		&& str_contains( $source['home'], 'Public_Home_Read_Model::preview_search( $query_value )' )
		&& is_string( $source['home_model'] )
		&& str_contains( $source['home_model'], 'return Public_Search_Facade::search( $query, 8 );' ),

	'per_form_request_state' =>
		is_string( $source['js'] )
		&& str_contains( $source['js'], 'var searchStates = new WeakMap();' )
		&& str_contains( $source['js'], 'stateForForm(form)' ),

	'debounce_and_abort' =>
		is_string( $source['js'] )
		&& str_contains( $source['js'], 'window.setTimeout(function ()' )
		&& str_contains( $source['js'], "typeof AbortController !== 'undefined'" )
		&& str_contains( $source['js'], 'state.controller.abort();' ),

	'stale_response_guard' =>
		is_string( $source['js'] )
		&& substr_count( $source['js'], 'requestId !== state.requestId' ) >= 3
		&& str_contains( $source['js'], 'input.value.trim() !== query' ),

	'failure_not_empty' =>
		is_string( $source['js'] )
		&& str_contains( $source['js'], "state: payload.state || 'request_error'" )
		&& str_contains( $source['js'], 'Não foi possível concluir a pesquisa agora.' )
		&& str_contains( $source['js'], 'class="bdc-search-error"' ),

	'zero_results_distinct' =>
		is_string( $source['js'] )
		&& str_contains( $source['js'], "state === 'empty' || state === 'zero_results'" )
		&& str_contains( $source['js'], 'Nenhum resultado encontrado.' ),

	'home_live_region' =>
		is_string( $source['home'] )
		&& str_contains( $source['home'], 'data-bdc-live-search-results aria-live="polite" aria-busy="false"' ),

	'reader_live_region' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], 'data-bdc-live-search-results aria-live="polite" aria-busy="false"' ),

	'keyboard_entry_and_escape' =>
		is_string( $source['js'] )
		&& str_contains( $source['js'], "(event.ctrlKey || event.metaKey)" )
		&& str_contains( $source['js'], "event.key === 'Escape'" )
		&& str_contains( $source['js'], 'cancelPending(liveForm);' ),

	'error_visual_contract' =>
		is_string( $source['foundation_css'] )
		&& str_contains( $source['foundation_css'], '.bdc-search-error{' )
		&& str_contains( $source['foundation_css'], '.bdc-global-search-panel.is-error' ),

	'candidate_result_urls' =>
		is_string( $source['facade'] )
		&& str_contains( $source['facade'], 'Public_Experience::article_preview_url( $post_id )' ),

	'no_public_cutover' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], "! current_user_can( 'manage_options' )" )
		&& ! str_contains( $source['public_experience'], "update_option( 'page_on_front'" ),
);

$failed = array_keys( array_filter( $checks, static fn ( bool $pass ): bool => ! $pass ) );

echo json_encode(
	array(
		'gate' => 'PX-730',
		'status' => empty( $failed ) ? 'PASS' : 'FAIL',
		'checks' => $checks,
		'failed' => $failed,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;

exit( empty( $failed ) ? 0 : 1 );
