<?php
/**
 * PX-720 Public Shell static contract.
 */

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';

$files = array(
	'public_experience' => $plugin . '/includes/class-public-experience.php',
	'home' => $plugin . '/templates/public-home-preview.php',
	'article' => $plugin . '/templates/public-article-preview.php',
	'foundation_css' => $plugin . '/assets/css/public-foundation.css',
);

$source = array();
foreach ( $files as $key => $path ) {
	$source[ $key ] = is_file( $path ) ? file_get_contents( $path ) : false;
}

$checks = array(
	'shell_version_constant' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], "public const SHELL_VERSION = 'public-shell-v1.0.0';" ),

	'quick_links_centralized' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], 'public static function quick_links(): array' )
		&& str_contains( $source['public_experience'], '$links = self::quick_links();' ),

	'exact_quick_link_labels' =>
		is_string( $source['public_experience'] )
		&& 1 === substr_count( $source['public_experience'], "'label' => 'Página Inicial'" )
		&& 1 === substr_count( $source['public_experience'], "'label' => 'Consulta Avançada'" )
		&& 1 === substr_count( $source['public_experience'], "'label' => 'Telefones'" )
		&& 1 === substr_count( $source['public_experience'], "'label' => 'Links Úteis'" )
		&& 1 === substr_count( $source['public_experience'], "'label' => 'POSTI'" ),

	'header_toggle_controls_nav' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], 'id="bdc-public-quicknav"' )
		&& str_contains( $source['public_experience'], 'aria-controls="bdc-public-quicknav"' ),

	'shell_version_localized' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], "'shellVersion' => self::SHELL_VERSION" ),

	'body_shell_class' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], "'bdc-' . sanitize_html_class" ),

	'home_skip_link' =>
		is_string( $source['home'] )
		&& str_contains( $source['home'], 'Public_Experience::render_skip_link();' )
		&& str_contains( $source['home'], 'id="bdc-public-main"' )
		&& str_contains( $source['home'], 'data-bdc-public-shell=' ),

	'article_skip_link' =>
		is_string( $source['article'] )
		&& str_contains( $source['article'], 'Public_Experience::render_skip_link();' )
		&& str_contains( $source['article'], 'id="bdc-public-main"' )
		&& str_contains( $source['article'], 'data-bdc-public-shell=' ),

	'skip_link_css_scoped' =>
		is_string( $source['foundation_css'] )
		&& str_contains( $source['foundation_css'], 'body.bdc-public-preview .bdc-public-skip-link' )
		&& str_contains( $source['foundation_css'], 'body.bdc-public-preview .bdc-public-skip-link:focus' ),

	'preview_gate_preserved' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], "! current_user_can( 'manage_options' )" )
		&& str_contains( $source['public_experience'], "wp_verify_nonce( $nonce, 'bdc_kb_public_preview_' . $kind )" ),

	'no_page_on_front_write' =>
		is_string( $source['public_experience'] )
		&& ! str_contains( $source['public_experience'], "update_option( 'page_on_front'" ),

	'canonical_search_preserved' =>
		is_string( $source['public_experience'] )
		&& str_contains( $source['public_experience'], 'Public_Search_Facade::AJAX_ACTION' )
		&& str_contains( $source['public_experience'], 'Public_Home_Read_Model::preview_search' ),
);

$failed = array_keys( array_filter( $checks, static fn ( bool $pass ): bool => ! $pass ) );

echo json_encode(
	array(
		'gate' => 'PX-720',
		'status' => empty( $failed ) ? 'PASS' : 'FAIL',
		'checks' => $checks,
		'failed' => $failed,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) . PHP_EOL;

exit( empty( $failed ) ? 0 : 1 );
