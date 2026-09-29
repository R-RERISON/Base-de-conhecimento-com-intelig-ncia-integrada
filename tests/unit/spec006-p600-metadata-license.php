<?php

declare(strict_types=1);

$root = dirname( __DIR__, 2 );
$plugin = $root . '/plugin/base-conhecimento-inteligencia-integrada';
$bootstrap = file_get_contents( $plugin . '/base-conhecimento-inteligencia-integrada.php' );

if ( ! is_string( $bootstrap ) ) {
	fwrite( STDERR, "Unable to read plugin bootstrap.\n" );
	exit( 1 );
}

$checks = array(
	'plugin_name' => str_contains( $bootstrap, 'Plugin Name: Base de Conhecimento com Inteligência Integrada' ),
	'plugin_uri' => str_contains( $bootstrap, 'Plugin URI: https://github.com/R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada' ),
	'version_header' => str_contains( $bootstrap, 'Version: 0.6.0-dev' ),
	'version_constant' => str_contains( $bootstrap, "define( 'BDC_KB_VERSION', '0.6.0-dev' );" ),
	'requires_wp' => str_contains( $bootstrap, 'Requires at least: 6.6' ),
	'requires_php' => str_contains( $bootstrap, 'Requires PHP: 8.1' ),
	'license' => str_contains( $bootstrap, 'License: GPL-2.0-or-later' ),
	'license_uri' => str_contains( $bootstrap, 'License URI: https://www.gnu.org/licenses/gpl-2.0.html' ),
	'update_uri' => str_contains( $bootstrap, 'Update URI: https://github.com/R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada' ),
	'text_domain' => str_contains( $bootstrap, 'Text Domain: bdc-knowledge-base' ),
	'license_file' => is_file( $plugin . '/LICENSE' ),
	'readme_file' => is_file( $plugin . '/readme.txt' ),
	'changelog_file' => is_file( $plugin . '/CHANGELOG.md' ),
	'upgrade_file' => is_file( $plugin . '/UPGRADE.md' ),
	'security_file' => is_file( $plugin . '/SECURITY.md' ),
	'contributing_file' => is_file( $plugin . '/CONTRIBUTING.md' ),
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
