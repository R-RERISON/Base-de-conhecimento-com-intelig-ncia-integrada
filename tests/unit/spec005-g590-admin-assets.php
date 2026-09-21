<?php
/**
 * G-590 admin asset lifecycle regression contract.
 */

declare(strict_types=1);

$root = dirname( __DIR__, 2 ) . '/plugin/base-conhecimento-inteligencia-integrada/';
$runner = file_get_contents( $root . 'includes/class-search-section-runner-g590.php' );
$asset = file_get_contents( $root . 'assets/js/search-section-g590.js' );

if ( ! is_string( $runner ) || ! is_string( $asset ) ) {
	fwrite( STDERR, "Falha ao ler runtime G-590.\n" );
	exit( 1 );
}

$render_start = strpos( $runner, 'public static function render_page(): void' );
$render_end = strpos( $runner, 'public static function handle_run(): void', $render_start );
$render = false !== $render_start && false !== $render_end
	? substr( $runner, $render_start, $render_end - $render_start )
	: '';

$checks = array(
	'admin_enqueue_hook_registered' => str_contains(
		$runner,
		"add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_assets' ) )"
	),
	'page_scoped_enqueue' => str_contains( $runner, 'self::PAGE_SLUG !== $page' ),
	'dedicated_asset_enqueued' => str_contains(
		$runner,
		"BDC_KB_URL . 'assets/js/search-section-g590.js'"
	),
	'footer_loading' => str_contains( $runner, "'bdc-kb-g590-runner'" )
		&& str_contains( $runner, 'true' ),
	'localized_runtime_config' => str_contains( $runner, "wp_localize_script" )
		&& str_contains( $runner, "'BDCG590'" )
		&& str_contains( $runner, "'ajaxUrl'" )
		&& str_contains( $runner, "'nonce'" ),
	'no_late_enqueue_in_render' => '' !== $render
		&& ! str_contains( $render, 'wp_enqueue_script' )
		&& ! str_contains( $render, 'wp_add_inline_script' ),
	'inline_browser_method_removed' => ! str_contains(
		$runner,
		'private static function browser_runner_script'
	),
	'asset_binds_start_button' => str_contains(
		$asset,
		"start.addEventListener('click'"
	),
	'asset_binds_restart_button' => str_contains(
		$asset,
		"restart.addEventListener('click'"
	),
	'asset_calls_start_action' => str_contains(
		$asset,
		'cfg.actions.start'
	),
	'asset_calls_step_action' => str_contains(
		$asset,
		'cfg.actions.step'
	),
	'asset_calls_status_action' => str_contains(
		$asset,
		'cfg.actions.status'
	),
	'asset_resume_contract' => str_contains(
		$asset,
		'return recover(jobId)'
	),
	'asset_no_external_origin' => ! str_contains( $asset, 'http://' )
		&& ! str_contains( $asset, 'https://' ),
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
